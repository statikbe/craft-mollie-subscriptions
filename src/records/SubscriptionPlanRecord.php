<?php

namespace statikbe\molliesubscriptions\records;

use craft\db\ActiveRecord;


class SubscriptionPlanRecord extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%mollie_plans}}';
    }
}