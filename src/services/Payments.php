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
    /**
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function saveElement($subscription): Subscription|bool
    {
        if (Craft::$app->getElements()->saveElement($subscription)) {
            return $subscription;
        } else {
            return false;
        }
    }

    public function save($model): bool
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

    /**
     * @throws \yii\base\Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    public function updatePayment($paymentRecord, $molliePayment): Subscription|null
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
        return null;
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

    public function getPaymentBySubscriptionId($id): ?SubscriptionPaymentRecord
    {
        $paymentRecord = SubscriptionPaymentRecord::findOne(['subscription' => $id]);
        return $paymentRecord;
    }

    public function getPaymentById($id): SubscriptionPaymentRecord|null
    {
        $paymentRecord = SubscriptionPaymentRecord::findOne(['id' => $id]);
        return $paymentRecord;
    }

    public function getAllPaymentsForSubscription($id): array
    {
        $paymentRecords = SubscriptionPaymentRecord::findAll(['subscription' => $id]);
        return $paymentRecords;
    }
}
