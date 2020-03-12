<?php

namespace studioespresso\molliesubscriptions\services;

use craft\base\Component;
use Mollie\Api\Resources\Customer;
use studioespresso\molliesubscriptions\models\SubscriberModel;
use studioespresso\molliesubscriptions\MollieSubscriptions;
use studioespresso\molliesubscriptions\records\SubscriberRecord;

class Subscriber extends Component
{

    public function getOrCreateSubscriberByEmail($email)
    {
        $subscriber = SubscriberRecord::findOne(['email' => $email]);
        if(!$subscriber) {
            /** @var Customer $customer */
            $customer =  MollieSubscriptions::$plugin->mollie->createCustomer($email);
            $subscriber = new SubscriberRecord();
            $subscriber->id = $customer->id;
            $subscriber->email = $customer->email;
            $subscriber->name  = $customer->name;
            $subscriber->locale = $customer->locale;
            $subscriber->metadata  = $customer->metadata;
            $subscriber->links = $customer->_links;
            $subscriber->save();
        }

        $model = new SubscriberModel();
        $model->setAttributes($subscriber->getAttributes());
        return $model;
    }

}
