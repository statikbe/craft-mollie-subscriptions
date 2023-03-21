<?php

namespace statikbe\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use statikbe\molliesubscriptions\elements\Subscriber;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\models\SubscriptionPaymentModel;
use statikbe\molliesubscriptions\MollieSubscriptions;

class Mollie extends Component
{
    private $mollie;

    private $baseUrl;

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function init(): void
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey(Craft::parseEnv(MollieSubscriptions::$plugin->getSettings()->apiKey));
        $this->baseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
    }

    /**
     * @throws \yii\base\Exception
     * @throws \Throwable
     */
    public function createFirstPayment(Subscription $subscription, Subscriber $subscriber, $plan, $redirect)
    {
        if($plan->description) {
            $description = Craft::$app->getView()->renderObjectTemplate($plan->description, $subscription);
        } else {
            $description = "Order #{$subscription->id}";
        }

        $response = $this->mollie->payments->create([
            "amount" => [
                "value" => number_format((float)$subscription->amount, 2, '.', ''),
                "currency" => $plan->currency
            ],
            "customerId" => $subscriber->customerId,
            "sequenceType" => "first",
            "description" => $description,
            "redirectUrl" => UrlHelper::url("{$this->baseUrl}mollie-subscriptions/subscriptions/process", [
                "planUid" => $plan->uid,
                "subscriptionUid" => $subscription->uid,
                "redirect" => $redirect
            ]),
            "webhookUrl" => "{$this->baseUrl}mollie-subscriptions/subscriptions/webhook",
            "metadata" => [
                "plan" => $plan->id,
                "customer" => $subscriber->id,
                "createSubscription" => $plan->times == 1 ? false : true
            ],
        ]);

        $payment = new SubscriptionPaymentModel();
        $payment->id = $response->id;
        $payment->customerId = $subscriber->customerId;
        $payment->subscription = $subscription->id;
        $payment->currency = $plan->currency;
        $payment->amount = $subscription->amount;
        $payment->status = $response->status;

        MollieSubscriptions::$plugin->payments->save($payment);


        return $response->_links->checkout->href;
    }


    /**
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws \yii\base\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function createSubscription(Subscription $subscription)
    {
        /** @var Customer $customer */
        $plan = MollieSubscriptions::$plugin->plans->getPlanById($subscription->plan);

        if($plan->description) {
            $description = Craft::$app->getView()->renderObjectTemplate($plan->description, $subscription);
        } else {
            $description = "Order #{$subscription->id}";
        }

        $subscriber = Subscriber::findOne(['id' => $subscription->subscriber]);
        $customer = $this->getCustomer($subscriber->customerId);
        $data = [
            "amount" => [
                "value" => $subscription->amount,
                "currency" => $plan->currency
            ],
            "interval" => $plan->interval . ' ' . $plan->intervalType,
            "description" => $description,
            "webhookUrl" => "{$this->baseUrl}mollie-subscriptions/subscriptions/webhook"
        ];

        if ($plan->times) {
            $data["times"] = $plan->times;
        }

        $response = $customer->createSubscription($data);
        if ($response) {
            $subscription->subscriptionStatus = "Active";
            $subscription->subscriptionId = $response->id;
            Craft::$app->getElements()->saveElement($subscription);
        }

    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getSubscriptionsForUser($customer): \Mollie\Api\Resources\SubscriptionCollection
    {
        $customer = $this->getCustomer($customer);
        return $customer->subscriptions();
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function cancelSubscription($id, $customer)
    {
        $customer = $this->getCustomer($customer);
        return $customer->cancelSubscription($id);
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function createCustomer($email): Customer
    {
        $customer = $this->mollie->customers->create([
            "email" => $email,
        ]);
        return $customer;
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getCustomer($id): Customer
    {
        return $this->mollie->customers->get($id);
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getPayment($orderId)
    {
        return $this->mollie->payments->get($orderId);
    }

}
