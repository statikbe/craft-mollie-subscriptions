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

use Craft;
use craft\helpers\ConfigHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\models\SubscriptionPaymentModel;
use statikbe\molliesubscriptions\MollieSubscriptions;
use statikbe\molliesubscriptions\services\Export;
use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
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
    protected int|bool|array $allowAnonymous = ['subscribe', 'donate', 'process', 'webhook', 'cancel'];

    /**
     * @throws \craft\web\ServiceUnavailableHttpException
     * @throws InvalidConfigException
     * @throws UnauthorizedHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id === 'webhook') {
            $this->enableCsrfValidation = false;
        }

        if (!ConfigHelper::localizedValue(MollieSubscriptions::getInstance()->getSettings()->apiKey)) {
            throw new InvalidConfigException("No Mollie API key set");
        }

        return parent::beforeAction($action);
    }

    // Public Methods// =========================================================================
    public function actionIndex(): \yii\web\Response
    {
        return $this->renderTemplate('mollie-subscriptions/_elements/_subscriptions/_index.twig');
    }

    /**
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     * @throws UnauthorizedHttpException
     * @throws \yii\web\BadRequestHttpException
     * @throws \Throwable
     */
    public function actionSubscribe(): ?\yii\web\Response
    {
        $redirect = Craft::$app->request->getBodyParam('redirect');
        $redirect = Craft::$app->security->validateData($redirect);

        $plan = Craft::$app->getRequest()->getValidatedBodyParam('plan');
        if (!$plan) {
            throw new UnauthorizedHttpException('Plan not found');
        }
        $plan = MollieSubscriptions::$plugin->plans->getPlanByHandle(Craft::$app->getRequest()->getValidatedBodyParam('plan'));
        if (!$plan) {
            throw new UnauthorizedHttpException('Plan not found');
        }
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

        if (!$subscription->validate()) { // Send the subscription back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $subscription,
            ]);
            return null;
        }

        if (MollieSubscriptions::getInstance()->payments->saveElement($subscription)) {
            $url = MollieSubscriptions::getInstance()->mollie->createFirstPayment($subscription, $subscriber, $plan, $redirect);
            return $this->redirect($url);
        }

        return null;
    }

    /**
     * @throws \craft\errors\ElementNotFoundException
     * @throws InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     * @throws HttpException
     */
    public function actionDonate(): ?\yii\web\Response
    {
        $redirect = Craft::$app->request->getBodyParam('redirect');
        $redirect = Craft::$app->security->validateData($redirect);

        $plan = Craft::$app->getRequest()->getValidatedBodyParam('plan');
        if (!$plan) {
            throw new UnauthorizedHttpException('Plan not found');
        }
        $plan = MollieSubscriptions::getInstance()->plans->getPlanById($plan);

        if (!$plan) {
            throw new UnauthorizedHttpException('Plan not found');
        }
        $email = Craft::$app->getRequest()->getRequiredBodyParam('email');
        $subscriber = MollieSubscriptions::getInstance()->subscriber->getOrCreateSubscriberByEmail($email);

        $amount = Craft::$app->getRequest()->getRequiredBodyParam('amount');
        if ($amount === false) {
            throw new HttpException(400, "Incorrent payment submitted");
        }

        $subscription = new Subscription();
        $subscription->email = $email;
        $subscription->subscriber = $subscriber->id;
        $subscription->plan = $plan->id;
        $subscription->amount = $amount;
        $subscription->subscriptionStatus = 'pending';
        $subscription->fieldLayoutId = $plan->fieldLayout;

        $subscription->setFieldValuesFromRequest('fields');

        if (!$subscription->validate()) { // Send the subscription back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $subscription,
            ]);
            return null;
        }

        if (MollieSubscriptions::getInstance()->payments->saveElement($subscription)) {
            $url = MollieSubscriptions::getInstance()->mollie->createFirstPayment($subscription, $subscriber, $plan, $redirect);
            return $this->redirect($url);
        }
        return null;
    }

    /**
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCancel()
    {
        $mollieId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $customerId = Craft::$app->getRequest()->getRequiredBodyParam('customer');
        $response = MollieSubscriptions::$plugin->mollie->cancelSubscription($mollieId, $customerId);
        if($response && $response->status === 'canceled') {
            $subscription = Subscription::findOne(['subscriptionId' => $mollieId]);
            $subscription->subscriptionStatus = Subscription::STATUS_CANCELED;
            if (MollieSubscriptions::getInstance()->payments->saveElement($subscription)) {
                $this->redirectToPostedUrl();
            }
        }
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

    /**
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionWebhook(): void
    {
        $id = Craft::$app->getRequest()->getRequiredParam('id');
        $molliePayment = MollieSubscriptions::getInstance()->mollie->getPayment($id);
        $payment = MollieSubscriptions::getInstance()->payments->getPaymentById($id);

        if($molliePayment->subscriptionId) {
            $subscriptionElement = Subscription::findOne(['subscriptionId' => $molliePayment->subscriptionId]);
            $subscriptionPayment = new SubscriptionPaymentModel();
            $subscriptionPayment->id = $molliePayment->id;
            $subscriptionPayment->subscription = $subscriptionElement->id;
            $subscriptionPayment->customerId = $molliePayment->customerId;
            $subscriptionPayment->amount = $molliePayment->amount->value;
            $subscriptionPayment->currency = $molliePayment->amount->currency;
            $subscriptionPayment->status = $molliePayment->status;
            $subscriptionPayment->method = $molliePayment->method;
            $subscriptionPayment->paidAt = $molliePayment->paidAt;

            if($molliePayment->status === 'paid') {
                MollieSubscriptions::$plugin->payments->save($subscriptionPayment);
                $subscriptionElement->subscriptionStatus = Subscription::STATUS_ACTIVE;
            }else{
                $subscriptionPayment->status = $molliePayment->status;
                MollieSubscriptions::$plugin->payments->save($subscriptionPayment);
            }
            Craft::$app->getElements()->saveElement($subscriptionElement);
        } else {
            $paymentElement = MollieSubscriptions::getInstance()->payments->updatePayment($payment, $molliePayment);
            if ($paymentElement && $molliePayment->metadata->createSubscription) {
                if($molliePayment->status === 'paid') {
                    MollieSubscriptions::$plugin->mollie->createSubscription($paymentElement);
                }
            }
        }
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionExportAll(): \craft\web\Response|\yii\console\Response
    {
        $subscriptions = Subscription::findAll();
        return Export::instance()->subscriptions($subscriptions);
    }
}
