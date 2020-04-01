<?php

namespace statikbe\molliesubscriptions\records;

use craft\db\ActiveRecord;


class SubscriptionRecord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%mollie_subscriptions}}';
    }
}