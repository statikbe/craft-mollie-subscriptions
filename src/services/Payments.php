<?php

namespace studioespresso\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use Mollie\Api\Resources\Customer;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\records\PaymentTransactionRecord;
use studioespresso\molliesubscriptions\elements\Subscription;
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

    public function updatePayment(SubscriptionPaymentRecord $paymentRecord, $molliePayment)
    {

        $paymentRecord->status = $molliePayment->status;
        $paymentRecord->method = $molliePayment->method;
        if ($molliePayment->isPaid() == 'paid') {
            $paymentRecord->paidAt = $molliePayment->paidAt;
        } elseif ($molliePayment->status == 'failed') {
            $paymentRecord->failedAt = $molliePayment->failedAt;
        } elseif ($molliePayment->status == 'canceled') {
            $paymentRecord->canceledAt = $molliePayment->canceledAt;
        } elseif ($molliePayment->status == 'expired') {
            $paymentRecord->expiresAt = $molliePayment->expiresAt;
        }

        if ($paymentRecord->validate() && $paymentRecord->save()) {
            $subscription = Subscription::findOne(['id' => $paymentRecord->subscription]);
            $subscription->subscriptionStatus = $molliePayment->status;
            Craft::$app->getElements()->saveElement($subscription);
            return $subscription;
        }
    }


    public function getPaymentBySubscriptionId($id)
    {
        $paymentRecord = SubscriptionPaymentRecord::findOne(['subscription' => $id]);
        return $paymentRecord;
    }

    public function getPaymentById($id)
    {
        $paymentRecord = SubscriptionPaymentRecord::findOne(['id' => $id]);
        return $paymentRecord;
    }
}
