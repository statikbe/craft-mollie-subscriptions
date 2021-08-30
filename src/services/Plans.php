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
    public function save(SubscriptionPlanModel $planModel)
    {
        $planRecord = false;
        if (isset($planModel->id)) {
            $planRecord = SubscriptionPlanRecord::findOne([
                'id' => $planModel->id
            ]);
        }

        if (!$planRecord) {
            $planRecord = new SubscriptionPlanRecord();
        }

        $planRecord->title = $planModel->title;
        $planRecord->handle = $planModel->handle;
        $planRecord->currency = $planModel->currency;
        $planRecord->description = $planModel->description;
        $planRecord->amount = $planModel->amount;
        $planRecord->times = $planModel->times;
        $planRecord->intervalType = $planModel->intervalType;
        $planRecord->interval = $planModel->interval;
        $planRecord->fieldLayout = $planModel->fieldLayout;

        return $planRecord->save();
    }

    public function handleAddPlan(ConfigEvent $event)
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
    }

    public function getPlanById($id)
    {
        $plan = SubscriptionPlanRecord::findOne(['id' => $id]);
        if(!$plan) {
            throw new NotFoundHttpException("Subscription plan not found", 404);
        }
        $model = new SubscriptionPlanModel();
        $model->setAttributes($plan->getAttributes(), false);
        return $model;
    }

    public function getAllPlans()
    {
        $plans = SubscriptionPlanRecord::find()->all();
        return $plans;
    }

    public function getPlanByHandle($handle)
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

    public function delete($id) {
        $plan = SubscriptionPlanRecord::findOne(['id' => $id]);
        if ($plan) {
            Craft::$app->projectConfig->remove(MollieSubscriptions::CONFIG_PATH . '.' . $plan->uid, "Removing plan '{$plan->formName()}'");
        }
        return true;
    }

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

    public function rebuildProjectConfig()
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

    private function getPlanRecord(string $uid)
    {
        $query = SubscriptionPlanRecord::find();
        $query->andWhere(['uid' => $uid]);
        return $query->one() ?? new SubscriptionPlanRecord();
    }
}
