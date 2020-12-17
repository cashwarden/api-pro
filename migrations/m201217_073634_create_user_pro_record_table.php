<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_pro_record}}`.
 */
class m201217_073634_create_user_pro_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_pro_record}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'out_sn' => $this->string(20)->notNull()->comment('流水号'),
            'source' => $this->smallInteger()->notNull()->comment('来源：1系统授予 2购买 3邀请'),
            'amount_cent' => $this->integer()->notNull(),
            'status' => $this->tinyInteger()->defaultValue(0)->comment('状态：1审核通过 0待审核'),
            'remark' => $this->string()->comment('备注'),
            'ended_at' => $this->timestamp()->defaultValue(null),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('fk_user_id', '{{%user_pro_record}}', ['user_id']);
        $this->createIndex('fk_out_sn', '{{%user_pro_record}}', ['out_sn']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_pro_record}}');
    }
}
