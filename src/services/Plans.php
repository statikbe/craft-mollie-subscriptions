<?php

namespace studioespresso\molliesubscriptions\services;

use craft\base\Component;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;
use studioespresso\molliepayments\elements\Payment as PaymentElement;
use studioespresso\molliesubscriptions\models\SubscriptionPlanModel;
use studioespresso\molliesubscriptions\records\SubscriptionPlanRecord;

class Plans extends Component
{
    public function save(SubscriptionPlanModel $model)
    {
        dd($model);
    }

    public function getPlanById($id)
    {
        $plans = SubscriptionPlanRecord::findOne(['id' => $id]);
        return $plans;
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
