<?php

namespace studioespresso\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliesubscriptions\models\SubscriberModel;
use studioespresso\molliesubscriptions\models\SubscriptionPlanModel;
use studioespresso\molliesubscriptions\MollieSubscriptions;
use studioespresso\molliesubscriptions\records\SubscriptionPlanRecord;

class Mollie extends Component
{
    private $mollie;

    private $baseUrl;

    public function init()
    {
        $this->mollie = new \Mollie\Api\MollieApiClient();
        $this->mollie->setApiKey(Craft::parseEnv(MollieSubscriptions::$plugin->getSettings()->apiKey));
        $this->baseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();

    }

    public function createFirstPayment(SubscriberModel $subscriber, SubscriptionPlanModel $subscription, $redirect)
    {
        $response = $this->mollie->payments->create([
            "amount" => [
                "value" => $subscription->amount,
                "currency" => $subscription->currency
            ],
            "customerId" => $subscriber->id,
            "sequenceType" => "first",
            "description" => $subscription->description,
            "redirectUrl" => UrlHelper::url("{$this->baseUrl}mollie-subscriptions/subscriptions/process", [
                "paymentId" => $subscription->uid,
                "redirect" => $redirect
            ]),
            "webhookUrl" => "{$this->baseUrl}mollie-subscriptions/subscriptions/webhook",
            "metadata" => [
                "redirectUrl" => $redirect,
                "plan" => $subscription->id,
                "customer" => $subscriber->id,
            ],
        ]);


        return $response->_links->checkout->href;
    }


    public function createSubscription(SubscriberModel $subscriber, SubscriptionPlanModel $subscription)
    {
        /** @var Customer $customer */
        $customer = $this->mollie->customers->get($subscriber->id);
        $data = [
            "amount" => [
                "value" => $subscription->amount,
                "currency" => $subscription->currency
            ],
            "interval" => $subscription->interval . ' ' . $subscription->intervalType,
            "description" => $subscription->description,
            "webhookUrl" => "{$this->baseUrl}mollie-subscriptions/subscriptions/webhook"
        ];

        if ($subscription->times) {
            $data["times"] = $subscription->times;
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

}
