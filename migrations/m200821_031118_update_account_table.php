<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

use yii\db\Migration;

/**
 * Class m200821_031118_update_account_table.
 */
class m200821_031118_update_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%account}}', 'currency_balance_cent', $this->bigInteger()->after('balance_cent'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200821_031118_update_account_table cannot be reverted.\n";
        $this->dropColumn('{{%account}}', 'currency_balance_cent');

        return true;
    }
}
