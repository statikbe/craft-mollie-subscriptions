<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.studioespresso.co
 * @copyright Copyright (c) 2020 Studio Espresso
 */

namespace studioespresso\molliesubscriptions\elements;

use studioespresso\molliesubscriptions\elements\db\SubscriptionQuery;
use studioespresso\molliesubscriptions\MollieSubscriptions;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use studioespresso\molliesubscriptions\records\SubscriptionRecord;

/**
 * Subscription Element
 *
 * @author    Studio Espresso
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class Subscription extends Element
{
    // Public Properties
    // =========================================================================

    /**
     * Some attribute
     *
     * @var string
     */
    public $email;

    public $amount;

    public $plan;

    public $subscriptionStatus;

    // Static Methods
    // =========================================================================

    /**
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('mollie-subscriptions', 'Subscription');
    }

    /**
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * @return bool Whether elements of this type have statuses.
     * @see statuses()
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new SubscriptionQuery(static::class);
    }

    /**
     * @param string|null $context The context ('index' or 'modal').
     *
     * @return array The sources.
     * @see sources()
     */
    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources[] = [
            'key' => '*',
            'label' => 'All subscriptions',
            'criteria' => ['id' => '*'],
        ];

        $plans = MollieSubscriptions::$plugin->plans->getAllPlans();
        foreach($plans as $plan) {

            $sources[] = [
                'key' => $plan->uid,
                'label' => $plan->title,
                'criteria' => ['plan' => $plan->id]
            ];
        }

        return $sources;
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'email' => Craft::t('mollie-subscriptions', 'Email'),
            'amount' => Craft::t('mollie-subscriptions', 'Amount'),
            'status' => Craft::t('mollie-subscriptions', 'Status'),
            'dateCreated' => Craft::t('mollie-subscriptions', 'Date Created'),
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['email', 'string'],
            ['amount', 'number'],
            [['email'], 'required']
        ];
    }

    /**
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return false;
    }

    public function getStatus()
    {
        return $this->subscriptionStatus;
    }


    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @return string The HTML for the editor HUD
     */
    public function getEditorHtml(): string
    {
        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('app', 'Title'),
                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true
            ]
        ]);

        $html .= parent::getEditorHtml();

        return $html;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert(SubscriptionRecord::tableName(), [
                    'id' => $this->id,
                    'email' => $this->email,
                    'subscriptionStatus' => $this->subscriptionStatus,
                    'amount' => $this->amount,
                    'plan' => $this->plan,
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(SubscriptionRecord::tableName(), [
                    'email' => $this->email,
                    'subscriptionStatus' => $this->subscriptionStatus,
                    'amount' => $this->amount,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool
    {
        return true;
    }

}
