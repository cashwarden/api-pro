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

class m201026_073711_create_budget_module_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%budget_config}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'amount_cent' => $this->integer()->notNull(),
            'init_amount_cent' => $this->integer()->defaultValue(0),
            'period' => $this->tinyInteger()->notNull()->comment('周期'),
            'ledger_id' => $this->integer(),
            'transaction_type' => $this->tinyInteger()->notNull(),
            'category_ids' => $this->string()->notNull()->comment('分类，英文逗号隔开，默认是所有'),
            'include_tags' => $this->string()->comment('包含标签，英文逗号隔开'),
            'exclude_tags' => $this->string()->comment('不包含标签，英文逗号隔开'),
            'started_at' => $this->timestamp()->defaultValue(null), // 不能为空
            'ended_at' => $this->timestamp()->defaultValue(null),
            'status' => $this->tinyInteger()->defaultValue(1),
            'rollover' => $this->tinyInteger()->defaultValue(0)->comment('结转'),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('budget_config_user_id_ledger_id', '{{%budget_config}}', ['user_id', 'ledger_id']);

        $this->createTable('{{%budget}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'budget_config_id' => $this->integer()->notNull(),
            'budget_amount_cent' => $this->integer()->notNull(),
            'actual_amount_cent' => $this->integer()->notNull(),
            'record_ids' => $this->text(),
            'relation_budget_id' => $this->integer(), // 表示是转来的预算
            'started_at' => $this->timestamp()->defaultValue(null), // 不能为空
            'ended_at' => $this->timestamp()->defaultValue(null), // 不能为空
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('budget_budget_config_id', '{{%budget}}', ['budget_config_id']);
        $this->createIndex('budget_user_id', '{{%budget}}', ['user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%budget}}');
        $this->dropTable('{{%budget_config}}');
    }
}
