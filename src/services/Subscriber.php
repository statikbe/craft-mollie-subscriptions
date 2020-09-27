<?php

namespace statikbe\molliesubscriptions\services;


use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Db;
use Mollie\Api\Resources\Customer;
use statikbe\molliesubscriptions\elements\Subscriber as SubscriberElement;
use statikbe\molliesubscriptions\MollieSubscriptions;
use statikbe\molliesubscriptions\records\SubscriberRecord;

class Subscriber extends Component
{

    public function getOrCreateSubscriberByEmail($email)
    {
        $subscriber = SubscriberElement::findOne(['email' => $email]);
        if (!$subscriber) {
            /** @var Customer $customer */
            $customer = MollieSubscriptions::$plugin->mollie->createCustomer($email);
            $subscriber = new SubscriberElement();
            $subscriber->customerId = $customer->id;
            $subscriber->email = $customer->email;
            $subscriber->name = $customer->name;
            $subscriber->locale = $customer->locale;
            $subscriber->metadata = $customer->metadata;
            $subscriber->links = $customer->_links;

            if (Craft::$app->getUser()->getIdentity()) {
                $subscriber->userId = Craft::$app->getUser()->getIdentity()->id;
                $subscriber->name = Craft::$app->getUser()->getIdentity()->fullName;
            }
            Craft::$app->getElements()->saveElement($subscriber);
        }
        return $subscriber;
    }

    public function getTotalByYear(SubscriberElement $subscriber)
    {
        $firstDayOfYear = date('Y-01-01');
        $lastDayOfYear = date('Y-12-31');

        $query = new Query();
        $query->from('{{%mollie_subscriptions_payments}}');
        $query->select('SUM(amount) as amount');
        $query->where(['=', 'status', 'paid']);
        $query->andWhere(['=', 'customerId', $subscriber->customerId]);
        $query->andWhere(['between', 'paidAt', $firstDayOfYear, $lastDayOfYear]);
        return $query->column()[0];
    }

    public function getSubscriberByUid($uid)
    {
        return SubscriberElement::findOne(['uid' => $uid]);
    }

    public function getAllSubscriptionsForSubscriber($subscriber) {
        $subscriptions = MollieSubscriptions::$plugin->mollie->getSubscriptionsForUser($subscriber->customerId);
        return $subscriptions;
    }

}
