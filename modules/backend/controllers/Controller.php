<?php

/**
 * author     : forecho
 * createTime : 2021/8/11 11:45 上午
 * description:
 */

namespace app\modules\backend\controllers;

use app\core\models\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class Controller extends \yii\web\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // 自定义一个规则，返回true表示满足该规则，可以访问，false表示不满足规则，也就不可以访问actions里面的操作啦
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            return User::currUserIsSuperAdmin();
                        },
                    ],
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
}
