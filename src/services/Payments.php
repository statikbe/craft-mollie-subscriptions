<?php

namespace statikbe\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\events\PaymentUpdateEvent;
use statikbe\molliesubscriptions\models\SubscriptionPaymentModel;
use statikbe\molliesubscriptions\MollieSubscriptions;
use statikbe\molliesubscriptions\records\SubscriptionPaymentRecord;

class Payments extends Component
{
    public function saveElement($subscription)
    {
        if (Craft::$app->getElements()->saveElement($subscription)) {
            return $subscription;
        } else {
            return false;
        }
    }

    public function save(SubscriptionPaymentModel $model)
    {
        $record = new SubscriptionPaymentRecord();
        $record->id = $model->id;
        $record->subscription = $model->subscription;
        $record->customerId = $model->customerId;
        $record->amount = $model->amount;
        $record->currency = $model->currency;
        $record->status = $model->status;
        $record->paidAt = $model->paidAt;
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
            $this->fireEventAfterTransactionUpdate($paymentRecord, $subscription, $molliePayment->status);
            return $subscription;
        }
    }


    public function fireEventAfterTransactionUpdate($payment, $subscription, $status)
    {
        $this->trigger(MollieSubscriptions::EVENT_AFTER_PAYMENT_UPDATE,
            new PaymentUpdateEvent([
                'subscription' => $subscription,
                'payment' => $payment,
                'status' => $status
            ])
        );
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

    public function getAllPaymentsForSubscription($id)
    {
        $paymentRecords = SubscriptionPaymentRecord::findAll(['subscription' => $id]);
        return $paymentRecords;
    }
}
