<?php

namespace studioespresso\molliesubscriptions\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriptionQuery extends ElementQuery
{

    public $plan;

    public $subscriptionStatus;

    public $email;

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

    protected function statusCondition(string $status)
    {
        switch ($status) {
            case 'pending':
                return ['subscriptiontStatus' => 'pending'];
            case 'paid':
                return ['subscriptiontStatus' => 'paid'];
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
        ]);

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.email', $this->email));
        }

        if ($this->plan) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.plan', $this->plan));
        }

        if ($this->subscriptionStatus) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscriptions.subscriptionStatus', $this->status));
        }

        return parent::beforePrepare();
    }
}
