<?php

namespace statikbe\molliesubscriptions\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriberQuery extends ElementQuery
{

    public $email;

    public function email($value)
    {
        $this->email = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('mollie_subscribers');
        // select the columns
        $this->query->select([
            'mollie_subscribers.email',
            'mollie_subscribers.customerId',
        ]);

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('mollie_subscribers.email', $this->email));
        }

        return parent::beforePrepare();
    }
}
