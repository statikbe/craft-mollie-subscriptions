<?php

namespace studioespresso\molliesubscriptions\services;

use craft\base\Component;
use Mollie\Api\Resources\Customer;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliesubscriptions\models\SubscriberModel;
use studioespresso\molliesubscriptions\models\SubscriptionPaymentModel;
use studioespresso\molliesubscriptions\MollieSubscriptions;
use studioespresso\molliesubscriptions\records\SubscriberRecord;
use studioespresso\molliesubscriptions\records\SubscriptionPaymentRecord;

class Payments extends Component
{
    public function save(SubscriptionPaymentModel $model)
    {
        $record = new SubscriptionPaymentRecord();
        $record->id = $model->id;
        $record->subscription = $model->subscription;
        $record->amount = $model->amount;
        $record->currency = $model->currency;
        $record->status = $model->status;
        return $record->save();
    }
}
