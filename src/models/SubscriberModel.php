<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2020 Statik
 */

namespace statikbe\molliesubscriptions\models;

use craft\base\Model;

/**
 * MollieSubscriptionsModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Statik
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class SubscriberModel extends Model
{
    public $id;

    public $name;

    public $email;

    public $userId;

    public $locale;

    public $metadata;

    public $links;


    public function rules(): array
    {
        return [
            [['email'], 'required'],
            [['name', 'email', 'id', 'locale', 'metadata','links'], 'safe'],
        ];
    }

}
