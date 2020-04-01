<?php

namespace statikbe\molliesubscriptions\controllers;

use Craft;
use craft\web\Controller;
use statikbe\molliepayments\MolliePayments;
use statikbe\molliesubscriptions\MollieSubscriptions;

class SettingsController extends Controller
{
    public function actionIndex()
    {
        $settings = MollieSubscriptions::$plugin->getSettings();
        return $this->renderTemplate('mollie-subscriptions/_settings.twig', ['settings' => $settings]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $params = Craft::$app->getRequest()->getBodyParams();
        $data = $params['settings'];

        $settings = MollieSubscriptions::$plugin->getSettings();
        $settings->apiKey = $data['apiKey'];

        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('mollie-subscriptions', 'Couldn’t save settings.'));
            return $this->renderTemplate('mollie-subscriptions/settings', compact('settings'));
        }

        $pluginSettingsSaved = Craft::$app->getPlugins()->savePluginSettings(MollieSubscriptions::$plugin, $settings->toArray());

        if (!$pluginSettingsSaved) {
            Craft::$app->getSession()->setError(Craft::t('mollie-subscriptions', 'Couldn’t save settings.'));
            return $this->renderTemplate('mollie-subscriptions/settings', compact('settings'));
        }

        Craft::$app->getSession()->setNotice(Craft::t('mollie-subscriptions', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}