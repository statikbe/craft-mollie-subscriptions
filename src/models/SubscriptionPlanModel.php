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

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\db\Table;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\validators\HandleValidator;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\MollieSubscriptions;

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

    public $fieldLayoutId;

    public $uid;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['fieldLayout'] = [
            'class' => FieldLayoutBehavior::class,
            'elementType' => Subscription::class,
        ];
        return $behaviors;
    }

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
        // TODO
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

    public function getConfig()
    {
        $config = [
            'title' => $this->title,
            'handle' => $this->handle,
            'currency' => $this->currency,
            'description' => $this->description,
            'intervalType' => $this->intervalType,
            'interval' => $this->interval,
            'times' => $this->times,
        ];

        $fieldLayout = $this->getFieldLayout();

        if ($fieldLayoutConfig = $fieldLayout->getConfig()) {
            if (!$fieldLayout->uid) {
                $fieldLayout->uid = $fieldLayout->id ? Db::uidById(Table::FIELDLAYOUTS, $fieldLayout->id) : StringHelper::UUID();
            }
            $config['fieldLayouts'] = [
                $fieldLayout->uid => $fieldLayoutConfig,
            ];
        }

        return $config;
    }
}
