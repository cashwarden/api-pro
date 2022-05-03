<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\behaviors;

use Yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;

class LoggerBehavior extends Behavior
{
    public function events()
    {
        return [
            Response::EVENT_BEFORE_SEND => 'beforeSend',
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * @param $event
     * @throws \Exception
     */
    public function beforeSend($event)
    {
        $response = $event->sender;
        if ($response->format != 'html') {
            $request = \Yii::$app->request;
            if ($response->data === null) {
                return;
            }
            $params = Yii::$app->params;
            $requestId = Yii::$app->requestId->id;
            $code = ArrayHelper::getValue($response->data, 'code');

            $ignoredKeys = explode(',', ArrayHelper::getValue($params, 'logFilterIgnoredKeys', ''));
            $hideKeys = explode(',', ArrayHelper::getValue($params, 'logFilterHideKeys', ''));
            $halfHideKeys = explode(',', ArrayHelper::getValue($params, 'logFilterHalfHideKeys', ''));
            $ignoredHeaderKeys = explode(',', ArrayHelper::getValue($params, 'logFilterIgnoredHeaderKeys', ''));
            $requestHeaderParams = $this->headerFilter($request->headers->toArray(), $ignoredHeaderKeys);

            $requestParams = $this->paramsFilter($request->bodyParams, $ignoredKeys, $hideKeys, $halfHideKeys);

            $message = [
                'request_id' => $requestId,
                'type' => $code === 0 ? 'response_data_success' : 'response_data_error',
                'header' => Json::encode($requestHeaderParams),
                'params' => Json::encode($requestParams),
                'url' => $request->absoluteUrl,
                'response' => Json::encode($response->data),
            ];
            if (is_array($response->data)) {
                $response->data = ['request_id' => $requestId] + $response->data;
            }
            $code === 0 ? Yii::info($message, 'request') : Yii::error($message, 'request');
        }
    }

    public function beforeAction(): ?string
    {
        return Yii::$app->requestId->id;
    }


    protected function headerFilter(array $params, array $ignoredHeaderKeys): array
    {
        foreach ($params as $key => $item) {
            if ($key && in_array($key, $ignoredHeaderKeys)) {
                unset($params[$key]);
            }
        }
        return $params;
    }

    protected function paramsFilter(array $params, array $ignoredKeys, array $hideKeys, array $halfHideKeys): array
    {
        if (!$hideKeys && !$halfHideKeys && !$ignoredKeys) {
            return $params;
        }
        foreach ($params as $key => &$item) {
            if (is_array($item)) {
                $item = $this->paramsFilter($item, $ignoredKeys, $hideKeys, $halfHideKeys);
                continue;
            }
            if ($key && in_array($key, $ignoredKeys)) {
                unset($params[$key]);
            } elseif ($key && in_array($key, $hideKeys)) {
                $item = $this->paramReplace($item);
            } elseif ($key && in_array($key, $halfHideKeys)) {
                $item = $this->paramPartialHiddenReplace($item);
            }
        }
        return $params;
    }

    protected function paramReplace(string $value): string
    {
        return str_repeat('*', strlen($value));
    }


    protected function paramPartialHiddenReplace(string $value): string
    {
        $valueLength = strlen($value);
        if ($valueLength > 2) {
            $showLength = ceil($valueLength * 0.2);
            $hideLength = $valueLength - $showLength * 2;
            $newValue = mb_substr($value, 0, $showLength)
                . str_repeat('*', $hideLength)
                . mb_substr($value, -1 * $showLength);
        } else {
            $newValue = $this->paramReplace($value);
        }

        return $newValue;
    }
}
