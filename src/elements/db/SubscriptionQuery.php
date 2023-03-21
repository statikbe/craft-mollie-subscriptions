<?php

namespace statikbe\molliesubscriptions\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriptionQuery extends ElementQuery
{

    public $plan;

    public $subscriptionStatus;

    public $email;

    public $amount;

    public $subscriber;

    public $subscriptionId;

    public function email($value): SubscriptionQuery|static
    {
        $this->email = $value;
        return $this;
    }

    public function status($value): SubscriptionQuery
    {
        $this->subscriptionStatus = $value;
        return $this;
    }


    public function subscriptionStatus($value): SubscriptionQuery
    {
        $this->subscriptionStatus = $value;
        return $this;
    }

    public function plan($value): SubscriptionQuery
    {
        $this->plan = $value;
        return $this;
    }

    public function subscriber($value): SubscriptionQuery
    {
        $this->subscriber = $value;
        return $this;
    }

    public function subscriptionId($value): SubscriptionQuery
    {
        $this->subscriptionId = $value;
        return $this;
    }


    protected function statusCondition(string $status): mixed
    {
        switch ($status) {
            case 'pending':
                return ['subscriptionStatus' => 'pending'];
            case 'Paid':
                return ['subscriptionStatus' => 'Paid'];
            case 'Active':
                return ['subscriptionStatus' => 'Active'];
            case 'expired':
                return ['subscriptionStatus' => 'expired'];
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

        if ($this->amount) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.amount', $this->amount));
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
