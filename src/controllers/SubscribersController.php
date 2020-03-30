<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.studioespresso.co
 * @copyright Copyright (c) 2020 Studio Espresso
 */

namespace studioespresso\molliesubscriptions\controllers;

use craft\web\Controller;
use studioespresso\molliesubscriptions\elements\Subscriber;
use studioespresso\molliesubscriptions\MollieSubscriptions;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Studio Espresso
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class SubscribersController extends Controller
{
    protected $allowAnonymous = [];

    // Public Methods// =========================================================================
    public function actionIndex()
    {
        return $this->renderTemplate('mollie-subscriptions/_elements/_subscribers/_index.twig');
    }

    public function actionEdit($uid)
    {
        $subscriber = Subscriber::findOne(['uid' => $uid]);
        $this->renderTemplate('mollie-subscriptions/_elements/_subscribers/_edit', [
            'element' => $subscriber,
        ]);
    }



}
