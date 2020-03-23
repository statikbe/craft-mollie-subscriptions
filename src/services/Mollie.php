<?php

namespace studioespresso\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use studioespresso\molliesubscriptions\elements\Subscription;
use studioespresso\molliesubscriptions\models\SubscriberModel;
use studioespresso\molliesubscriptions\models\SubscriptionPaymentModel;
use studioespresso\molliesubscriptions\models\SubscriptionPlanModel;
use studioespresso\molliesubscriptions\MollieSubscriptions;

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

    public function createFirstPayment(Subscription $subscription, SubscriberModel $subscriber, SubscriptionPlanModel $plan, $redirect)
    {
        $response = $this->mollie->payments->create([
            "amount" => [
                "value" => $plan->amount,
                "currency" => $plan->currency
            ],
            "customerId" => $subscriber->id,
            "sequenceType" => "first",
            "description" => $plan->description,
            "redirectUrl" => UrlHelper::url("{$this->baseUrl}mollie-subscriptions/subscriptions/process", [
                "planUid" => $plan->uid,
                "subscriptionUid" => $subscription->uid,
                "redirect" => $redirect
            ]),
            "webhookUrl" => "{$this->baseUrl}mollie-subscriptions/subscriptions/webhook",
            "metadata" => [
                "redirectUrl" => $redirect,
                "plan" => $plan->id,
                "customer" => $subscriber->id,
            ],
        ]);

        $payment = new SubscriptionPaymentModel();
        $payment->id = $response->id;
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
        $customer = $this->getCustomer($subscription->subscriber);
        $data = [
            "amount" => [
                "value" => $plan->amount,
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

        dd($response);

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
