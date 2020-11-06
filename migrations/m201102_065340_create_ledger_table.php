<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ledger}}`.
 */
class m201102_065340_create_ledger_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ledger}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'type' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('类型'),
            'user_id' => $this->integer()->notNull(),
            'cover' => $this->string(),
            'remark' => $this->string(),
            'default' => $this->tinyInteger(1)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('ledger_user_id', '{{%ledger}}', ['user_id']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ledger}}');
    }
}
