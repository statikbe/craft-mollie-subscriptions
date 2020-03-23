<?php

namespace studioespresso\molliesubscriptions\services;

use craft\base\Component;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\elements\Payment as PaymentElement;
use studioespresso\molliesubscriptions\models\SubscriptionPlanModel;
use studioespresso\molliesubscriptions\records\SubscriptionPlanRecord;
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
        return $plan;
    }

    public function delete($id) {
        $plan = SubscriptionPlanRecord::find(['id' => $id])->one();
        return $plan->delete();
    }
}
