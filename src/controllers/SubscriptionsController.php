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

use craft\commerce\models\Customer;
use craft\helpers\UrlHelper;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\MollieSubscriptions;

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
 * @author    Statik
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

        return $this->renderTemplate('mollie-subscriptions/_elements/_subscriptions/_index.twig');
    }

    public function actionSubscribe()
    {
        $plan = Craft::$app->getRequest()->getValidatedBodyParam('plan');
        $email = Craft::$app->getRequest()->getRequiredBodyParam('email');
        if (!$plan) {
            throw new UnauthorizedHttpException('Plan not found');
        }
        $plan = MollieSubscriptions::$plugin->plans->getPlanById(Craft::$app->getRequest()->getValidatedBodyParam('plan'));
        $email = Craft::$app->getRequest()->getRequiredBodyParam('email');
        $subscriber = MollieSubscriptions::$plugin->subscriber->getOrCreateSubscriberByEmail($email);


        $subscription = new Subscription();
        $subscription->email = $email;
        $subscription->subscriber = $subscriber->id;
        $subscription->plan = $plan->id;
        $subscription->amount = $plan->amount;
        $subscription->subscriptionStatus = 'Pending first payment';
        $subscription->fieldLayoutId = $plan->fieldLayout;

        $subscription->setFieldValuesFromRequest('fields');

        if (!$subscription->validate()) {
            // return with errors here?
        }

        Craft::$app->getElements()->saveElement($subscription);

        $redirect = Craft::$app->getRequest()->getValidatedBodyParam('redirect');
        $url = MollieSubscriptions::$plugin->mollie->createFirstPayment($subscription, $subscriber, $plan, $redirect);
        return $this->redirect($url);
    }

    public function actionProcess()
    {
        $request = Craft::$app->getRequest();
        $uid = $request->getQueryParam('subscriptionUid');
        $redirect = $request->getQueryParam('redirect');
        $subscriptionElement = Subscription::findOne(['uid' => $uid]);

        $transaction = MollieSubscriptions::$plugin->payments->getPaymentBySubscriptionId($subscriptionElement->id);

        try {
            $molliePayment = MollieSubscriptions::$plugin->mollie->getPayment($transaction->id);
            $this->redirect(UrlHelper::url($redirect, ['subscription' => $uid, 'status' => $molliePayment->status]));
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Payments not found', '404');
        }
    }

    public function actionWebhook()
    {
        $this->requirePostRequest();
        $id = Craft::$app->getRequest()->getRequiredParam('id');
        $payment = MollieSubscriptions::getInstance()->payments->getPaymentById($id);
        $molliePayment = MollieSubscriptions::getInstance()->mollie->getPayment($id);
        $paymentElement = MollieSubscriptions::getInstance()->payments->updatePayment($payment, $molliePayment);
        if($paymentElement) {
            MollieSubscriptions::$plugin->mollie->createSubscription($paymentElement);
        }
        return;
    }

}
