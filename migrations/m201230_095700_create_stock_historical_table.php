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
 * Handles the creation of table `{{%stock_historical}}`.
 */
class m201230_095700_create_stock_historical_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stock_historical}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(10)->notNull(),
            'open_price_cent' => $this->integer()->notNull(), // 开市价格
            'current_price_cent' => $this->integer()->notNull(), // 每小时更新一次，直到闭市
            'change_price_cent' => $this->integer()->notNull(),
            'date' => $this->timestamp()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('stock_historical_code_date', '{{%stock_historical}}', ['code', 'date'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stock_historical}}');
    }
}
