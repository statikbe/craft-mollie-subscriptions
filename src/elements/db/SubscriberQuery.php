<?php

namespace statikbe\molliesubscriptions\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriberQuery extends ElementQuery
{

    public $email;
    public $customerId;


    public function email($value): self
    {
        $this->email = $value;
        return $this;
    }

    public function customerId($value): self
    {
        $this->customerId = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('mollie_subscribers');
//        // select the columns
        $this->query->select([
            'mollie_subscribers.email',
            'mollie_subscribers.customerId',
        ]);

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscribers.email', $this->email));
        }

        if ($this->customerId) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscribers.customerId', $this->customerId));
        }

        return parent::beforePrepare();
    }
}
