<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wish_list}}`.
 */
class m201224_070846_create_wish_list_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wish_list}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'ledger_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'amount_cent' => $this->integer()->notNull(), // base currency
            'currency_amount_cent' => $this->integer()->notNull(),
            'currency_code' => $this->string(3)->notNull(),
            'remark' => $this->text(),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('wish_list_ledger_id_user_id', '{{%wish_list}}', ['ledger_id', 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wish_list}}');
    }
}
