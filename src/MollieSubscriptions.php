<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.studioespresso.co
 * @copyright Copyright (c) 2020 Studio Espresso
 */

namespace studioespresso\molliesubscriptions;

use studioespresso\molliesubscriptions\models\Settings;
use studioespresso\molliesubscriptions\services\Currency;
use studioespresso\molliesubscriptions\services\Mollie;
use studioespresso\molliesubscriptions\services\Plans;
use studioespresso\molliesubscriptions\services\Subscriber;
use studioespresso\molliesubscriptions\variables\MollieSubscriptionsVariable;
use studioespresso\molliesubscriptions\elements\Subscription as SubscriptionElement;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Studio Espresso
 * @package   MollieSubscriptions
 * @since     1.0.0
 * @property Plans plans
 * @property Currency currency
 * @property Mollie mollie
 * @property Subscriber subscriber
 *
 */
class MollieSubscriptions extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * MollieSubscriptions::$plugin
     *
     * @var MollieSubscriptions
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * MollieSubscriptions::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;


        $this->setComponents([
            'mollie' => Mollie::class,
            'currency' => Currency::class,
            'plans' => Plans::class,
            'subscriber' => Subscriber::class,
        ]);

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['mollie-subscriptions'] = 'mollie-subscriptions/default/index';
                $event->rules['mollie-subscriptions/edit/<uid:{uid}>'] = 'mollie-subscriptions/payment/edit';
                $event->rules['mollie-subscriptions/edit/<uid:{uid}>'] = 'mollie-subscriptions/payment/edit';
                $event->rules['mollie-subscriptions/plans'] = 'mollie-subscriptions/plan/index';
                $event->rules['mollie-subscriptions/plans/add'] = 'mollie-subscriptions/plan/edit';
                $event->rules['mollie-subscriptions/plans/<planId:\d+>'] = 'mollie-subscriptions/plan/edit';
                $event->rules['mollie-subscriptions/settings'] = 'mollie-subscriptions/settings/index';
            }
        );

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SubscriptionElement::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('subscriptions', MollieSubscriptionsVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );
    }

    public function getCpNavItem()
    {
        $subNavs = [];
        $navItem = parent::getCpNavItem();
        $navItem['label'] = Craft::t("mollie-subscriptions", "Subscriptions");

        $subNavs['subscriptions'] = [
            'label' => 'Subscriptions',
            'url' => 'mollie-subscriptions',
        ];
        
        
        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $subNavs['plans'] = [
                'label' => 'Plans',
                'url' => 'mollie-subscriptions/plans',
            ];
        }
        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $subNavs['settings'] = [
                'label' => 'Settings',
                'url' => 'mollie-subscriptions/settings',
            ];
        }
        
        $navItem = array_merge($navItem, [
            'subnav' => $subNavs,
        ]);

        return $navItem;
    }

    public function getSettingsResponse()
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('mollie-subscriptions/settings'));
    }

    // Protected Methods
    // =========================================================================
    protected function createSettingsModel()
    {
        return new Settings();
    }

}
