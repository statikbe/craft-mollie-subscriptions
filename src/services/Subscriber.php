<?php

namespace studioespresso\molliesubscriptions\services;


use Craft;
use craft\base\Component;
use Mollie\Api\Resources\Customer;
use studioespresso\molliesubscriptions\models\SubscriberModel;
use studioespresso\molliesubscriptions\MollieSubscriptions;
use studioespresso\molliesubscriptions\records\SubscriberRecord;
use studioespresso\molliesubscriptions\elements\Subscriber as SubscriberElement;

class Subscriber extends Component
{

    public function getOrCreateSubscriberByEmail($email)
    {
        $subscriber = SubscriberElement::findOne(['email' => $email]);
        if(!$subscriber) {
            /** @var Customer $customer */
            $customer =  MollieSubscriptions::$plugin->mollie->createCustomer($email);
            $subscriber = new SubscriberElement();
            $subscriber->customerId = $customer->id;
            $subscriber->email = $customer->email;
            $subscriber->name  = $customer->name;
            $subscriber->locale = $customer->locale;
            $subscriber->metadata  = $customer->metadata;
            $subscriber->links = $customer->_links;

            if (Craft::$app->getUser()->getIdentity()) {
                $subscriber->userId = Craft::$app->getUser()->getIdentity()->id;
                $subscriber->name = Craft::$app->getUser()->getIdentity()->fullName;
            }
            Craft::$app->getElements()->saveElement($subscriber);
        }
        dd($subscriber);

        return $subscriber;
    }

}
