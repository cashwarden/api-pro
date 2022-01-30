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
 * Class m201125_030508_update_account_table.
 */
class m201125_030508_update_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%account}}', 'keywords', $this->string()->after('name'));
        $this->addColumn('{{%account}}', 'remark', $this->string()->after('sort'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201125_030508_update_account_table cannot be reverted.\n";
        $this->dropColumn('{{%account}}', 'keywords');
        $this->dropColumn('{{%account}}', 'remark');
        return true;
    }
}
