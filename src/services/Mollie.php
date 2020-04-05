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
use statikbe\molliesubscriptions\models\SubscriptionPlanModel;
use statikbe\molliesubscriptions\MollieSubscriptions;

class Mollie extends Component
{
    private $mollie;

    private $baseUrl;

    public function init()
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey(Craft::parseEnv(MollieSubscriptions::$plugin->getSettings()->apiKey));
        $this->baseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();

    }

    public function createFirstPayment(Subscription $subscription, Subscriber $subscriber, SubscriptionPlanModel $plan, $redirect)
    {
        if($plan->description) {
            $description = Craft::$app->getView()->renderObjectTemplate($plan->description, $subscription);
        } else {
            $description = "Order #{$payment->id}";
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


    public function createSubscription(Subscription $subscription)
    {
        /** @var Customer $customer */
        $plan = MollieSubscriptions::$plugin->plans->getPlanById($subscription->plan);
        $subscriber = Subscriber::findOne(['id' => $subscription->subscriber]);
        $customer = $this->getCustomer($subscriber->customerId);
        $data = [
            "amount" => [
                "value" => $subscription->amount,
                "currency" => $plan->currency
            ],
            "interval" => $plan->interval . ' ' . $plan->intervalType,
            "description" => $plan->description,
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

    public function getSubscriptionsForUser($customer)
    {
        $customer = $this->getCustomer($customer);
        return $customer->subscriptions();
    }

    public function cancelSubscription($id, $customer)
    {
        $customer = $this->getCustomer($customer);
        return $customer->cancelSubscription($id);
    }

    public function createCustomer($email)
    {
        $customer = $this->mollie->customers->create([
            "email" => $email,
        ]);
        return $customer;
    }

    public function getCustomer($id)
    {
        return $this->mollie->customers->get($id);
    }

    public function getPayment($orderId)
    {
        return $this->mollie->payments->get($orderId);
    }

}
