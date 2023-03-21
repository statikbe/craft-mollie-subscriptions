<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2020 Statik
 */

namespace statikbe\molliesubscriptions\controllers;

use craft\web\Controller;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\MollieSubscriptions;

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
 * @author    Statik
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class DefaultController extends Controller
{
    // Public Methods
    // =========================================================================
    protected int|bool|array $allowAnonymous = ['index', 'edit'];

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/mollie-subscriptions/default
     *
     * @return \yii\web\Response
     */
    public function actionIndex(): \yii\web\Response
    {
        return $this->renderTemplate('mollie-subscriptions/_elements/_subscriptions/_index.twig');
    }

    /**
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionEdit($uid)
    {
        $subscription = Subscription::findOne(['uid' => $uid]);
        $payments = MollieSubscriptions::$plugin->payments->getAllPaymentsForSubscription($subscription->id);
        $plan = MollieSubscriptions::$plugin->plans->getPlanById($subscription->plan);
        $this->renderTemplate('mollie-subscriptions/_elements/_subscriptions/_edit', [
            'element' => $subscription,
            'payments' => $payments,
            'plan' => $plan
        ]);
    }

}
