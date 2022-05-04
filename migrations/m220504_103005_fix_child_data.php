<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

use yii\db\Migration;

/**
 * Class m220504_103005_fix_child_data.
 */
class m220504_103005_fix_child_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \app\core\services\FixDataService::fixChildUserData();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220504_103005_fix_child_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220504_103005_fix_child_data cannot be reverted.\n";

        return false;
    }
    */
}
