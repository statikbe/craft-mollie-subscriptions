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

use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\models\SubscriptionPlanModel;
use statikbe\molliesubscriptions\MollieSubscriptions;

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
 * @author    Statik
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
            'plans' => MollieSubscriptions::getInstance()->plans->getAllPlans()
        ]);
    }

    public function actionEdit($planId = null)
    {
        $currencies = MollieSubscriptions::getInstance()->currency->getCurrencies();
        if (!$planId) {
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', ['currencies' => $currencies]);
        } else {
            $plan = MollieSubscriptions::getInstance()->plans->getPlanById($planId);
            if ($plan->fieldLayout) {
                $layout = Craft::$app->getFields()->getLayoutById($plan->fieldLayout);
            }
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', ['plan' => $plan, 'layout' => $layout ?? null, 'currencies' => $currencies]);

        }
    }

    public function actionSave()
    {
        $data = Craft::$app->getRequest()->getBodyParam('data');

        if (!isset($data['id']) or empty($data['planId'])) {
            $planModel = new SubscriptionPlanModel();
        } else {
            /** @var SubscriptionPlanModel $planRecord */
            $planRecord = MollieSubscriptions::getInstance()->plans->getPlanById($data['id']);
            $planModel = new SubscriptionPlanModel();
            $planModel->setAttributes($planRecord->getAttributes(), false);
            $planModel->id = $planRecord->id;
            $planModel->uid = $planRecord->uid;
        }

        $planModel->title = $data['title'];
        $planModel->handle = $data['handle'];
        $planModel->currency = $data['currency'];
        $planModel->amount = $data['amount'];
        $planModel->times = $data['times'];
        $planModel->description = $data['description'];
        $planModel->interval = $data['interval'];
        $planModel->intervalType = $data['intervalType'];

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = Subscription::class;
        $planModel->setFieldLayout($fieldLayout);

        // Save it
        if (!$planModel->validate()) {
            MollieSubscriptions::getInstance()->plans->save($planModel);
            $this->redirectToPostedUrl();
        } else {
            $layout = Craft::$app->getFields()->getLayoutById($fieldLayout->id);
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', [
                'plan' => $planModel,
                'layout' => $layout,
                'errors' => $planModel->getErrors(),
                'currencies' => MollieSubscriptions::getInstance()->currency->getCurrencies()
            ]);
        }
    }

    public function actionDelete() {
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');
        if(MollieSubscriptions::getInstance()->plans->delete($id)) {
            $returnData['success'] = true;
            return $this->asJson($returnData);
        };
    }
}
