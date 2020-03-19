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

use craft\commerce\models\Customer;
use studioespresso\molliesubscriptions\elements\Subscription;
use studioespresso\molliesubscriptions\MollieSubscriptions;

use Craft;
use craft\web\Controller;
use yii\web\UnauthorizedHttpException;

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
class SubscriptionsController extends Controller
{
    protected $allowAnonymous = ['subscribe', 'process', 'webhook'];


    public function beforeAction($action)
    {
        if ($action->id === 'webhook') {
            $this->enableCsrfValidation = false;
        }

        if (!MollieSubscriptions::$plugin->getSettings()->apiKey) {
            throw new InvalidConfigException("No Mollie API key set");
        }
        return parent::beforeAction($action);
    }
    // Public Methods// =========================================================================
    public function actionIndex()
    {
        return $this->renderTemplate('mollie-subscriptions/_elements/_index.twig');
    }

    public function actionSubscribe()
    {
        $plan = Craft::$app->getRequest()->getValidatedBodyParam('plan');
        $email = Craft::$app->getRequest()->getRequiredBodyParam('email');
        if (!$plan) {
            throw new UnauthorizedHttpException('Plan not found');
        }
        $plan = MollieSubscriptions::$plugin->plans->getPlanById(Craft::$app->getRequest()->getValidatedBodyParam('plan'));

        $subscription = new Subscription();
        $subscription->email = $email;
        $subscription->plan = $plan->id;
        $subscription->amount = $plan->amount;
        $subscription->subscriptionStatus = 'pending';
        $subscription->setFieldValuesFromRequest('fields');

        if(!$subscription->validate()) {
            // return with errors here?
        }

        Craft::$app->getElements()->saveElement($subscription);

        $redirect = Craft::$app->getRequest()->getValidatedBodyParam('redirect');
        $email = Craft::$app->getRequest()->getRequiredBodyParam('email');
        $subscriber = MollieSubscriptions::$plugin->subscriber->getOrCreateSubscriberByEmail($email);
        $url = MollieSubscriptions::$plugin->mollie->createFirstPayment($subscription, $subscriber, $plan, $redirect);
        return $this->redirect($url);
    }

    public function actionProcess()
    {
        d(Craft::$app->getRequest()->getBodyParams());
        dd(Craft::$app->getRequest()->getQueryParams());
    }

}
