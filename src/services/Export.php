<?php

namespace statikbe\molliesubscriptions\services;

use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\MollieSubscriptions;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class Export extends Component
{
    private $plans = [];

    public function subscribers($subscribers, $format = 'csv')
    {
        $results = [];
        $header = ['E-mail', 'Customer Id', 'Total this year'];

        foreach ($subscribers as $subscriber) {
            /** @var \statikbe\molliesubscriptions\elements\Subscriber $subscriber */
            $results[$subscriber->id] = array_merge([
                'email' => $subscriber->email,
                'customerId' => $subscriber->customerId,
                'totalForThisYear' => $subscriber->getTotalForThisYear(),
            ]);
        }
        $header = array_merge($header);
        array_unshift($results, $header);

        switch ($format) {
            case 'csv':
                $file = tempnam(sys_get_temp_dir(), 'export');
                $fp = fopen($file, 'wb');
                foreach ($results as $result) {
                    fputcsv($fp, $result, ',');
                }
                fclose($fp);
                $contents = file_get_contents($file);
                unlink($file);
                break;
            default:
                throw new BadRequestHttpException('Invalid export format: ' . $format);
        }

        $filename = 'Export-Mollie-Subscribers.' . $format;
        $path = Craft::$app->getPath()->getTempPath() . '/' . $filename;
        $mimeType = FileHelper::getMimeTypeByExtension($filename);

        $response = Craft::$app->getResponse();
        $response->content = $contents;
        $response->format = Response::FORMAT_RAW;
        $response->setDownloadHeaders($filename, $mimeType);
        return $response;
    }

    public function subscriptions($subscriptions, $format = 'csv')
    {
        $results = [];
        $header = ['E-mail', 'Plan', 'Amount', 'Currency', 'Status', 'Date'];
        $customFields = [];

        // get all plans for subscriptions and custom fields
        foreach ($subscriptions as $subscription) {
            if (!isset($this->plans[$subscription->plan])) {
                $plan = MollieSubscriptions::getInstance()->plans->getPlanByid($subscription->plan);
                $this->plans[$plan->id] = $plan;

                foreach ($subscription->getFieldValues() as $key => $value) {
                    if (!in_array($key, $customFields)) {
                        array_push($customFields, $key);
                    }
                }
            }
        }

        // get all subscriptions info
        foreach ($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            $results[$subscription->id] = array_merge([
                'email' => $subscription->email,
                'plan' => $this->plans[$subscription->plan]->title,
                'amount' => $subscription->amount,
                'currency' => $this->plans[$subscription->plan]->currency,
                'status' => $subscription->status,
                'date' => $subscription->dateCreated->format('d/m/Y'),
            ]);

            $values = $subscription->getFieldValues();
            foreach ($customFields as $fieldHandle) {
                if (isset($values[$fieldHandle])) {
                    $results[$subscription->id][$fieldHandle] = $values[$fieldHandle];
                } else {
                    $results[$subscription->id][$fieldHandle] = '';
                }
            }

        }
        $header = array_merge($header, $customFields);
        array_unshift($results, $header);
        switch ($format) {
            case 'csv':
                $file = tempnam(sys_get_temp_dir(), 'export');
                $fp = fopen($file, 'wb');
                foreach ($results as $result) {
                    fputcsv($fp, $result, ',');
                }
                fclose($fp);
                $contents = file_get_contents($file);
                unlink($file);
                break;
            default:
                throw new BadRequestHttpException('Invalid export format: ' . $format);
        }

        $filename = 'Export-Mollie-Subscriptions.' . $format;
        $path = Craft::$app->getPath()->getTempPath() . '/' . $filename;
        $mimeType = FileHelper::getMimeTypeByExtension($filename);

        $response = Craft::$app->getResponse();
        $response->content = $contents;
        $response->format = Response::FORMAT_RAW;
        $response->setDownloadHeaders($filename, $mimeType);
        return $response;
    }

}
