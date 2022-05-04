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
 * Class m220501_100920_update_user_table.
 */
class m220501_100920_update_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'parent_id', $this->integer()->defaultValue(0)->after('id'));
        $this->addColumn(
            '{{%user}}',
            'role',
            $this->tinyInteger()->defaultValue(\app\core\types\UserRole::ROLE_OWNER)->after('status')
        );
        $this->createIndex('user_parent_id', '{{%user}}', 'parent_id');

        \app\core\services\FixDataService::fixChildUserData();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220501_100920_update_user_table cannot be reverted.\n";
        $this->dropColumn('{{%user}}', 'parent_id');
        $this->dropColumn('{{%user}}', 'role');
        $this->dropIndex('user_parent_id', '{{%user}}');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220501_100920_update_user_table cannot be reverted.\n";

        return false;
    }
    */
}
