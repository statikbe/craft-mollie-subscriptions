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

use studioespresso\molliesubscriptions\models\SubscriptionPlanModel;
use studioespresso\molliesubscriptions\MollieSubscriptions;

use Craft;
use craft\web\Controller;

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
class PlanController extends Controller
{
    // Public Methods
    // =========================================================================
    public function actionIndex()
    {
        return $this->renderTemplate('mollie-subscriptions/_plans/_index.twig', [
            'plans' => MollieSubscriptions::$plugin->plans->getAllPlans()
        ]);
    }

    public function actionEdit($planId = null)
    {
        $currencies = MollieSubscriptions::$plugin->currency->getCurrencies();
        if (!$planId) {
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', ['currencies' => $currencies]);
        } else {
            $plan = MollieSubscriptions::$plugin->plans->getPlanById($planId);
            $layout = Craft::$app->getFields()->getLayoutById($plan->fieldLayout);
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', ['plan' => $plan, 'layout' => $layout, 'currencies' => $currencies]);

        }
    }

    public function actionSave()
    {
        $planId = Craft::$app->getRequest()->getBodyParam('planId');
        if(!$planId) {
            $planModel = new SubscriptionPlanModel();
            $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
            $fieldLayout->type = Payment::class;
            Craft::$app->getFields()->saveLayout($fieldLayout);
            $planModel->fieldLayout = $fieldLayout->id;
        } else {
            /** @var SubscriptionPlanModel $record */
            $planRecord = MollieSubscriptions::$plugin->plans->getPlanById($planId);
            $planModel = new SubscriptionPlanModel();
            $planModel->setAttributes($planRecord->getAttributes(), false);
        }

        $planModel->title = Craft::$app->getRequest()->getBodyParam('title');
        $planModel->handle = Craft::$app->getRequest()->getBodyParam('handle');
        $planModel->currency = Craft::$app->getRequest()->getBodyParam('currency');
        $planModel->amount = Craft::$app->getRequest()->getBodyParam('amount');
        $planModel->description = Craft::$app->getRequest()->getBodyParam('description');
        $planModel->interval = Craft::$app->getRequest()->getBodyParam('interval');
        $planModel->intervalType = Craft::$app->getRequest()->getBodyParam('intervalType');

        // Save it
        if (!$planModel->validate()) {
            Craft::$app->getSession()->setError(Craft::t('mollie-subscriptions', 'Couldn’t save plan.'));
            Craft::$app->getUrlManager()->setRouteParams([
                'plan' => $planModel
            ]);
            $currencies = MollieSubscriptions::$plugin->currency->getCurrencies();
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', ['plan' => $planModel, 'layout' => $fieldLayout, 'currencies' => $currencies]);

        }

        if(MollieSubscriptions::$plugin->plans->save($planModel)) {
            $this->redirectToPostedUrl();
        };


    }

}
