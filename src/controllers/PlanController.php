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
    public function actionIndex() {
        return  $this->renderTemplate('mollie-subscriptions/_plans/_index.twig', [
            'plans' => []
        ]);
    }

    public function actionEdit($planId = null)
    {
        $currencies = MollieSubscriptions::$plugin->currency->getCurrencies();
        if (!$planId) {
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', ['currencies' => $currencies]);
        } else {
            $form = MollieSubscriptions::$plugin->plans->getPlanById($planId);
            $layout = Craft::$app->getFields()->getLayoutById($form->fieldLayout);
            return $this->renderTemplate('mollie-subscriptions/_plans/_edit', ['plzn' => $plan, 'layout' => $layout, 'currencies' => $currencies]);

        }
    }

}
