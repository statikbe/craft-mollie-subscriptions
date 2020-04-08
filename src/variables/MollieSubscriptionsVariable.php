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

    public function getPlanByid($id)
    {
        return MollieSubscriptions::$plugin->plans->getPlanById($id);
    }

    public function getAllForUser($uid)
    {
        $subscriber = MollieSubscriptions::$plugin->subscriber->getSubscriberByUid($uid);
        $subscriptions = MollieSubscriptions::$plugin->subscriber->getAllSubscriptionsForSubscriber($subscriber);
        return $subscriptions;
    }

    public function parseDescription($string, $element)
    {
        return \Craft::$app->view->renderObjectTemplate($string, $element);
    }
}
