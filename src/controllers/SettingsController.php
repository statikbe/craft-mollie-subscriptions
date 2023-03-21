<?php

namespace statikbe\molliesubscriptions\controllers;

use Craft;
use craft\web\Controller;
use statikbe\molliesubscriptions\MollieSubscriptions;

class SettingsController extends Controller
{
    protected int|bool|array $allowAnonymous = ['index', 'save'];

    public function actionIndex(): \yii\web\Response
    {
        $settings = MollieSubscriptions::getInstance()->getSettings();
        return $this->renderTemplate('mollie-subscriptions/_settings.twig', ['settings' => $settings]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave(): \yii\web\Response
    {
        $this->requirePostRequest();

        $params = Craft::$app->getRequest()->getBodyParams();
        $data = $params['settings'];

        $settings = MollieSubscriptions::getInstance()->getSettings();
        $settings->apiKey = $data['apiKey'] ?? $settings->orderReferenceFormat;

        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('mollie-subscriptions', 'Couldn’t save settings.'));
            return $this->renderTemplate('mollie-subscriptions/settings', compact('settings'));
        }

        $pluginSettingsSaved = Craft::$app->getPlugins()->savePluginSettings(MollieSubscriptions::getInstance(), $settings->toArray());

        if (!$pluginSettingsSaved) {
            Craft::$app->getSession()->setError(Craft::t('mollie-subscriptions', 'Couldn’t save settings.'));
            return $this->renderTemplate('mollie-subscriptions/settings', compact('settings'));
        }

        Craft::$app->getSession()->setNotice(Craft::t('mollie-subscriptions', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}