<?php

namespace studioespresso\molliesubscriptions\records;

use craft\db\ActiveRecord;


class SubscriptionPaymentRecord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%mollie_subscriptions_payments}}';
    }
}