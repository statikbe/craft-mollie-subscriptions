<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2020 Statik
 */

namespace statikbe\molliesubscriptions\elements;

use craft\elements\User;
use craft\helpers\UrlHelper;
use statikbe\molliesubscriptions\actions\ExportAllSubscriptionsAction;
use statikbe\molliesubscriptions\elements\db\SubscriptionQuery;
use statikbe\molliesubscriptions\MollieSubscriptions;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use statikbe\molliesubscriptions\records\SubscriptionRecord;

/**
 * Subscription Element
 *
 * @author    Statik
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class Subscription extends Element
{
    // Constants
    // =========================================================================
    const STATUS_CANCELED = 'Canceled';
    const STATUS_PAID = 'Paid';
    const STATUS_ACTIVE = 'Active';


    // Public Properties
    // =========================================================================
    public $email;

    public $amount;

    public $subscriber;

    public $plan;

    public $subscriptionId;

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

    public function __toString(): string
    {
        if ($this->email) {
            return (string)$this->email;
        }
        return (string)$this->id;
    }

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function getUiLabel(): string
    {
        $plan = $this->getPlan();
        return "{$plan->title} - {$this->email}";
    }

    /**
     * @return \statikbe\molliesubscriptions\models\SubscriptionPlanModel
     * @throws \yii\web\NotFoundHttpException
     */
    public function getPlan(): \statikbe\molliesubscriptions\models\SubscriptionPlanModel
    {
        return MollieSubscriptions::$plugin->plans->getPlanById($this->plan);
    }

    /**
     * @return string|null
     */
    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl("mollie-subscriptions/subscription/" . $this->uid);
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

    protected static function defineActions(string $source = null): array
    {
        return [
            ExportAllSubscriptionsAction::class,
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'email' => Craft::t('mollie-subscriptions', 'Email'),
            'amount' => Craft::t('mollie-subscriptions', 'Amount'),
            'subscriptionStatus' => Craft::t('mollie-subscriptions', 'Status'),
            'dateCreated' => Craft::t('mollie-subscriptions', 'Date Created'),
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'dateCreated' => \Craft::t('app', 'Date created'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['email'];
    }

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return $rules = [
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

    public function getStatus(): ?string
    {
        return $this->subscriptionStatus;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    public static function statuses(): array
    {
        return [
            'pending' => ['label' => Craft::t('mollie-subscriptions', 'Pending'), 'color' => 'orange'],
            'Active' => ['label' => Craft::t('mollie-subscriptions', 'Active'), 'color' => 'green'],
            'paid' => ['label' => Craft::t('mollie-subscriptions', 'Paid'), 'color' => 'green'],
            'expired' => ['label' => Craft::t('mollie-subscriptions', 'Expired'), 'color' => 'red'],
            'canceled' => ['label' => Craft::t('mollie-subscriptions', 'Canceled'), 'color' => 'red'],
        ];
    }


    // Indexes, etc.
    // -------------------------------------------------------------------------

//    /**
//     * @return string The HTML for the editor HUD
//     */
//    Not in use so was removed
//    public function getEditorHtml(): string
//    {
//        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
//            [
//                'label' => Craft::t('app', 'Title'),
//                'siteId' => $this->siteId,
//                'id' => 'title',
//                'name' => 'title',
//                'value' => $this->title,
//                'errors' => $this->getErrors('title'),
//                'first' => true,
//                'autofocus' => true,
//                'required' => true
//            ]
//        ]);
//
//        $html .= parent::getEditorHtml();
//
//        return $html;
//    }

    public function canView(User $user): bool
    {
        if($user->can("accessPlugin-mollie-subscriptions")) {
            return true;
        }
        return false;
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
     * @throws \yii\db\Exception
     */
    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert(SubscriptionRecord::tableName(), [
                    'id' => $this->id,
                    'email' => $this->email,
                    'subscriber' => $this->subscriber,
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
                    'subscriptionId' => $this->subscriptionId,
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

    /**
     * @throws \yii\db\Exception
     */
    public function afterDelete(): void
    {
        \Craft::$app->db->createCommand()
            ->delete(SubscriptionRecord::tableName(), ['id' => $this->id])
            ->execute();
    }
}
