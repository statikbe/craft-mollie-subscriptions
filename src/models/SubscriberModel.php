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

use craft\validators\HandleValidator;
use studioespresso\molliesubscriptions\MollieSubscriptions;

use Craft;
use craft\base\Model;

/**
 * MollieSubscriptionsModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Studio Espresso
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class SubscriberModel extends Model
{
    public $id;

    public $name;

    public $email;

    public $locale;

    public $metadata;

    public $links;


    public function rules()
    {
        return [
            [['email'], 'required'],
            [['name', 'email', 'id', 'locale', 'metadata','links'], 'safe'],
        ];
    }

}
