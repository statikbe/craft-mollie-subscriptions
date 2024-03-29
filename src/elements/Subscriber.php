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

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;
use statikbe\molliesubscriptions\actions\ExportAllSubscribersAction;
use statikbe\molliesubscriptions\elements\db\SubscriberQuery;
use statikbe\molliesubscriptions\MollieSubscriptions;
use statikbe\molliesubscriptions\records\SubscriberRecord;

/**
 * Subscriber Element
 *
 * @author    Statik
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class Subscriber extends Element
{
    // Public Properties
    // =========================================================================

    /**
     * Some attribute
     *
     * @var string
     */

    public $name;

    public $email;

    public $customerId;

    public $userId;

    public $locale;

    public $metadata;

    public $links;

    // Static Methods
    // =========================================================================

    /**
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('mollie-subscriptions', 'Subscriber');
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


    public static function find(): ElementQueryInterface
    {
        return new SubscriberQuery(static::class);
    }

    public function getTotalForThisYear()
    {
        return MollieSubscriptions::$plugin->subscriber->getTotalByYear($this) ?? '0';
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
            'label' => 'All',
        ];

        return $sources;
    }

    protected static function defineSortOptions(): array
    {
        return [
            'email' => Craft::t('app', 'E-mail address'),
            'dateCreated' => Craft::t('app', 'Date created'),
        ];
    }


    protected static function defineSearchableAttributes(): array
    {
        return ['email', 'customerId'];
    }

    public function getUiLabel(): string
    {
        return $this->email;
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'email' => Craft::t('mollie-subscriptions', 'Email'),
            'customerId' => Craft::t('mollie-subscriptions', 'Customer ID'),
            'totalForThisYear' => Craft::t('mollie-subscriptions', 'Total this year')
        ];
    }

    public function getTableAttributeHtml(string $attribute): string
    {

        switch ($attribute) {
            case 'customerId':
                return $this->customerId;
            case 'totalForThisYear':
                return $this->getTotalForThisYear();

        }
        return '';

    }


    protected static function defineActions(string $source = null): array
    {
        return [
            ExportAllSubscribersAction::class
        ];
    }

    /**
     * @return string|null
     */
    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl("mollie-subscriptions/subscribers/" . $this->uid);
    }

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            ['email', 'string'],
            ['customerId', 'string'],
            [['email', 'customerId'], 'required']
        ];
    }

    /**
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return false;
    }

    public function canView(User $user): bool
    {
        if($user->can("accessPlugin-mollie-subscriptions")) {
            return true;
        }
        return false;
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

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
                ->insert(SubscriberRecord::tableName(), [
                    'id' => $this->id,
                    'name' => $this->name,
                    'email' => $this->email,
                    'customerId' => $this->customerId,
                    'userId' => $this->userId,
                    'locale' => $this->locale,
                    'metadata' => $this->metadata,
                    'links' => $this->metadata
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(SubscriberRecord::tableName(), [
                    'email' => $this->email,
                    'customerId' => $this->customerId,
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
