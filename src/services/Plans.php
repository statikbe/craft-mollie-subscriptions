<?php

namespace statikbe\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\models\FieldLayout;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\models\SubscriptionPlanModel;
use statikbe\molliesubscriptions\MollieSubscriptions;
use statikbe\molliesubscriptions\records\SubscriptionPlanRecord;
use yii\web\NotFoundHttpException;

class Plans extends Component
{
    public const CONFIG_FORMS_PATH = 'mollieSubscriptions';

    public function save(SubscriptionPlanModel $planModel): bool
    {
        $planRecord = false;
        if (isset($planModel->id)) {
            $planRecord = SubscriptionPlanRecord::findOne([
                'id' => $planModel->id
            ]);
        }

        $configPath = self::CONFIG_FORMS_PATH . '.' . $planModel->uid;
        $configData = $planModel->getConfig();
        Craft::$app->projectConfig->set($configPath, $configData);

        return true;
    }

    /**
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function handleAddPlan(ConfigEvent $event): ?bool
    {
        $planUid = $event->tokenMatches[0];
        $data = $event->newValue;

        $planRecord = $this->getPlanRecord($planUid);
        if (!$planRecord) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        $planRecord->uid = $planUid;
        $planRecord->title = $data['title'];
        $planRecord->handle = $data['handle'];
        $planRecord->currency = $data['currency'];
        $planRecord->description = $data['description'];
        $planRecord->intervalType = $data['intervalType'];
        $planRecord->interval = $data['interval'];
        $planRecord->times = $data['times'];


        if (!empty($data['fieldLayouts'])) {
            // Save the field layout
            $layout = FieldLayout::createFromConfig(reset($data['fieldLayouts']));
            $layout->id = $planRecord->fieldLayout;
            $layout->type = Subscription::class;
            $layout->uid = key($data['fieldLayouts']);
            Craft::$app->getFields()->saveLayout($layout);
            $planRecord->fieldLayout = $layout->id;
        } else if ($planRecord->fieldLayout) {
            // Delete the field layout
            Craft::$app->getFields()->deleteLayoutById($planRecord->fieldLayout);
            $planRecord->fieldLayout = null;
        }

        $planRecord->save();
        $transaction->commit();
        return null;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getPlanById($id): SubscriptionPlanModel
    {
        $plan = SubscriptionPlanRecord::findOne(['id' => $id]);
        if(!$plan) {
            throw new NotFoundHttpException("Subscription plan not found", 404);
        }
        $model = new SubscriptionPlanModel();
        $model->setAttributes($plan->getAttributes(), false);
        return $model;
    }

    public function getAllPlans(): array
    {
        $plans = SubscriptionPlanRecord::find()->all();
        return $plans;
    }

    /**
     * @throws \craft\errors\DeprecationException
     * @throws NotFoundHttpException
     */
    public function getPlanByHandle($handle): SubscriptionPlanModel|SubscriptionPlanRecord|null
    {
        $plan = SubscriptionPlanRecord::findOne(['handle' => $handle]);
        if (!$plan) {
            $plan = $this->getPlanByid($handle);
            if ($plan) {
                Craft::$app->deprecator->log('mollieSubscriptions.plans.handle',
                    'The form parameter now needs to be a hashed handle instead of a hashed id', __FILE__, 93);
                return $plan;
            }
        }
        return $plan;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function delete($id): bool
    {
        $plan = SubscriptionPlanRecord::findOne(['id' => $id]);
        if ($plan) {
            Craft::$app->projectConfig->remove(MollieSubscriptions::CONFIG_PATH . '.' . $plan->uid, "Removing plan '{$plan->formName()}'");
        }
        return true;
    }

    /**
     * @throws \yii\db\StaleObjectException
     * @throws \Throwable
     */
    public function handleDeletePlan(ConfigEvent $event)
    {
        $record = SubscriptionPlanRecord::findOne([
            'uid' => $event->tokenMatches[0]
        ]);
        if (!$record) {
            return false;
        }

        if ($record->delete()) {
            return 1;
        };
    }

    public function rebuildProjectConfig(): array
    {
        $forms = SubscriptionPlanRecord::find();
        $data = [];
        foreach ($forms->all() as $form) {
            $model = new SubscriptionPlanModel();
            $model->setAttributes($form->getAttributes());
            $fieldLayout = Craft::$app->getFields()->getLayoutById($form->fieldLayout);
            $fieldLayout->type = Subscription::class;
            $model->setFieldLayout($fieldLayout);
            $data[$form->uid] = $model->getConfig();
        }
        return $data;
    }

    private function getPlanRecord(string $uid): SubscriptionPlanRecord|array|\yii\db\ActiveRecord
    {
        $query = SubscriptionPlanRecord::find();
        $query->andWhere(['uid' => $uid]);
        return $query->one() ?? new SubscriptionPlanRecord();
    }
}
