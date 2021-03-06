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

use craft\validators\HandleValidator;
use statikbe\molliesubscriptions\MollieSubscriptions;

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
 * @author    Statik
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

    public $amount;

    public $intervalType;

    public $interval;

    public $times;

    public $fieldLayout;

    public $uid;

    public function rules()
    {
        return [
            [['title', 'handle', 'currency', 'description', 'interval', 'intervalType'], 'required'],
            [['title', 'handle', 'currency', 'description', 'times','interval', 'intervalType', 'amount'], 'safe'],
            ['handle', 'validateHandle'],
//            ['interval', 'validateInterval'],
        ];
    }

    public function validateInterval()
    {
        dd($this);
    }

    public function validateHandle()
    {
        $validator = new HandleValidator();
        $validator->validateAttribute($this, 'handle');
        $data = MollieSubscriptions::getInstance()->plans->getPlanByHandle($this->handle);
        if ($data && $data->id != $this->id) {
            $this->addError('handle', Craft::t('mollie-subscriptions', 'Handle "{handle}" is already in use', ['handle' => $this->handle]));

        }

    }
}
