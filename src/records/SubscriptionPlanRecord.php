<?php

namespace studioespresso\molliesubscriptions\records;

use craft\db\ActiveRecord;


class SubscriptionPlanRecord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%mollie_plans}}';
    }
}