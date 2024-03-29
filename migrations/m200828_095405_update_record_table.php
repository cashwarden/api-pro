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
 * Class m200828_095405_update_record_table.
 */
class m200828_095405_update_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%record}}', 'date', $this->timestamp()->defaultValue(null));
        $this->alterColumn('{{%transaction}}', 'date', $this->timestamp()->defaultValue(null));

        $this->addColumn('{{%record}}', 'transaction_type', $this->tinyInteger()->after('account_id'));
        $this->addColumn('{{%record}}', 'source', $this->tinyInteger()->after('date'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200828_095405_update_record_table cannot be reverted.\n";
        $this->dropColumn('{{%record}}', 'transaction_type');
        $this->dropColumn('{{%record}}', 'source');

        return true;
    }
}
