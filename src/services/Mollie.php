<?php

namespace studioespresso\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use Mollie\Api\MollieApiClient;
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

    public function init()
    {
        $this->mollie = new \Mollie\Api\MollieApiClient();
        $this->mollie->setApiKey(Craft::parseEnv(MollieSubscriptions::$plugin->getSettings()->apiKey));
    }

    public function createSubscription(SubscriberModel $subscriber, SubscriptionPlanModel $subscription)
    {
        dd($subscriber, $subscription);
    }

    public function createCustomer($email) {
        $customer = $this->mollie->customers->create([
            "email" => $email,
        ]);
        return $customer;
    }

}
