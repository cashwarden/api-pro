<?php

use yii\db\Migration;

/**
 * Class m201125_025510_update_category_table
 */
class m201125_025510_update_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{category}}', 'keywords', $this->string()->after('name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201125_025510_update_category_table cannot be reverted.\n";
        $this->dropColumn('{{%category}}', 'keywords');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201125_025510_update_category_table cannot be reverted.\n";

        return false;
    }
    */
}
