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
 * Handles the creation of table `{{%category}}`.
 */
class m200730_065801_create_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'direction' => $this->tinyInteger()->notNull(),
            'name' => $this->string(120)->notNull(),
            'color' => $this->string(7)->notNull(),
            'icon_name' => $this->string(120)->notNull(),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createIndex('category_user_id', '{{%category}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%category}}');
    }
}
