<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%budget}}`.
 */
class m201026_073711_create_budget_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%budget}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'amount_cent' => $this->integer()->notNull(),
            'period' => $this->tinyInteger()->notNull()->comment('周期'),
            'category_ids' => $this->string()->defaultValue(null)->comment('分类，英文逗号隔开，默认是所有'),
            'account_ids' => $this->string()->defaultValue(null)->comment('账户，英文逗号隔开，默认是所有'),
            'include_tags' => $this->string()->comment('包含标签，英文逗号隔开'),
            'exclude_tags' => $this->string()->comment('不包含标签，英文逗号隔开'),
            'started_at' => $this->timestamp()->defaultValue(null),
            'ended_at' => $this->timestamp()->defaultValue(null),
            'status' => $this->tinyInteger()->defaultValue(1),
            'rollover' => $this->tinyInteger()->defaultValue(0)->comment('结转'),
            'carried_balance_cent' => $this->integer()->defaultValue(0)->comment('结转余额'),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%budget}}');
    }
}
