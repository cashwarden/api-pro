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
 * Handles the creation of table `{{%ledger_member}}`.
 */
class m201102_071807_create_ledger_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ledger_member}}', [
            'id' => $this->primaryKey(),
            'ledger_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'permission' => $this->tinyInteger()->notNull(),
            'status' => $this->tinyInteger()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('ledger_member_ledger_id_status', '{{%ledger_member}}', ['ledger_id', 'status']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ledger_member}}');
    }
}
