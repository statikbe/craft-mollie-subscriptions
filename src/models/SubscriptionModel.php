<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.studioespresso.co
 * @copyright Copyright (c) 2020 Studio Espresso
 */

namespace studioespresso\molliesubscriptions\models;

use studioespresso\molliesubscriptions\MollieSubscriptions;

use Craft;
use craft\base\Model;

/**
 * MollieSubscriptionsModel Model

 *
 * @author    Studio Espresso
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class SubscriptionModel extends Model
{
    public $email;

    public $amount;

    public $plan;

}
