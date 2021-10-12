<?php

namespace app\modules\backend;

/**
 * backend module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\backend\controllers';

    public $defaultRoute = 'site';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->layout = '@app/modules/backend/views/layouts/main';
        // custom initialization code goes here
    }
}
