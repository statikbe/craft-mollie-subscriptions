<?php

namespace statikbe\molliesubscriptions\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriptionQuery extends ElementQuery
{

    public $plan;

    public $subscriptionStatus;

    public $email;

    public $subscriber;

    public $subscriptionId;

    public function email($value)
    {
        $this->email = $value;
        return $this;
    }

    public function status($value)
    {
        $this->subscriptiontStatus = $value;
        return $this;
    }


    public function subscriptiontStatus($value)
    {
        $this->subscriptiontStatus = $value;
        return $this;
    }

    public function plan($value)
    {
        $this->plan = $value;
        return $this;
    }

    public function subscriber($value)
    {
        $this->subscriber = $value;
        return $this;
    }

    public function subscriptionId($value)
    {
        $this->subscriptionId = $value;
        return $this;
    }


    protected function statusCondition(string $status)
    {
        switch ($status) {
            case 'pending':
                return ['subscriptiontStatus' => 'pending'];
            case 'Paid':
                return ['subscriptiontStatus' => 'Paid'];
            case 'Active':
                return ['subscriptiontStatus' => 'Active'];
            case 'expired':
                return ['subscriptiontStatus' => 'expired'];
            default:
                return parent::statusCondition($status);
        }
    }


    protected function beforePrepare(): bool
    {
        $this->joinElementTable('mollie_subscriptions');
        // select the columns
        $this->query->select([
            'mollie_subscriptions.email',
            'mollie_subscriptions.amount',
            'mollie_subscriptions.plan',
            'mollie_subscriptions.subscriptionStatus',
            'mollie_subscriptions.subscriber',
            'mollie_subscriptions.subscriptionId',
        ]);

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.email', $this->email));
        }

        if ($this->plan) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.plan', $this->plan));
        }

        if ($this->subscriptionStatus) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.subscriptionStatus', $this->subscriptionStatus));
        }

        if ($this->subscriber) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.subscriber', $this->subscriber));
        }

        if ($this->subscriptionId) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.subscriptionId', $this->subscriptionId));
        }

        return parent::beforePrepare();
    }
}
