<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2020 Statik
 */

namespace statikbe\molliesubscriptions\variables;

use Craft;
use statikbe\molliesubscriptions\elements\Subscriber;
use statikbe\molliesubscriptions\elements\Subscriber as SubscriberElement;
use statikbe\molliesubscriptions\MollieSubscriptions;

/**
 * Mollie Subscriptions Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.mollieSubscriptions }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Statik
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class MollieSubscriptionsVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @throws \yii\web\NotFoundHttpException
     */
    public function getPlanByid($id): \statikbe\molliesubscriptions\models\SubscriptionPlanModel
    {
        return MollieSubscriptions::$plugin->plans->getPlanById($id);
    }

    /**
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function getAllForUser($uid): \Mollie\Api\Resources\SubscriptionCollection
    {
        $subscriber = MollieSubscriptions::$plugin->subscriber->getSubscriberByUid($uid);
        $subscriptions = MollieSubscriptions::$plugin->subscriber->getAllSubscriptionsForSubscriber($subscriber);
        return $subscriptions;
    }

    /**
     * @throws \yii\base\Exception
     * @throws \Throwable
     */
    public function parseDescription($string, $element): string
    {
        return \Craft::$app->view->renderObjectTemplate($string, $element);
    }

    public static function getStatusDetails($status): array
    {
        switch ($status) {
            case 'pending':
                return ['label' => Craft::t('mollie-subscriptions', 'Pending'), 'color' => 'orange', 'class' => 'pending'];
                break;
            case 'Active':
                return ['label' => Craft::t('mollie-subscriptions', 'Active'), 'color' => 'green', 'class' => 'active'];
                break;
            case 'paid':
                return ['label' => Craft::t('mollie-subscriptions', 'Paid'), 'color' => 'green', 'class' => 'active'];
                break;
            case 'expired':
                return ['label' => Craft::t('mollie-subscriptions', 'Expired'), 'color' => 'red', 'class' => 'expired'];
                break;
            case 'canceled':
                return ['label' => Craft::t('mollie-subscriptions', 'Canceled'), 'color' => 'red', 'class' => 'expired'];
                break;
            default:
                return ['label' => Craft::t('mollie-subscriptions', 'Unknown'), 'color' => 'none', 'class' => 'disabled'];

        }
    }

    public function getSubscriberById($id): Subscriber|\craft\base\Element|array|null
    {
        return SubscriberElement::findOne(['id' => $id]);
    }
}
