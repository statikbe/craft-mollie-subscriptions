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
class SubscriptionPlanModel extends Model
{

    public $title;

    public $id;

    public $handle;

    public $currency;

    public $description;

    public $interval;

    public $times;

    public $fieldLayout;

    public function rules()
    {
        return [
            [['title', 'handle', 'currency'], 'required'],
            [['title', 'handle', 'currency', 'description', 'interval', 'times'], 'safe'],
            ['handle', 'validateHandle'],
            ['interval', 'validateInterval'],
        ];
    }

    public function validateInterval() {
        dd($this);

        if($data && $data->id != $this->id) {
            $this->addError('handle', Craft::t('mollie-subscriptions', 'Handle "{handle}" is already in use', ['handle' => $this->handle]));

        }

    }


    public function validateHandle() {
        $validator = new HandleValidator();
        $validator->validateAttribute($this, 'handle');
        $data = MollieSubscriptions::getInstance()->plans->getPlanByHandle($this->handle);
        if($data && $data->id != $this->id) {
            $this->addError('handle', Craft::t('mollie-subscriptions', 'Handle "{handle}" is already in use', ['handle' => $this->handle]));

        }

    }
}
