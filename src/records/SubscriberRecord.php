<?php

namespace statikbe\molliesubscriptions\records;

use craft\db\ActiveRecord;


class SubscriberRecord extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%mollie_subscribers}}';
    }
}