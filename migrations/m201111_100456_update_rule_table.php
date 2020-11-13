<?php

use yii\db\Migration;

/**
 * Class m201111_100456_update_rule_table
 */
class m201111_100456_update_rule_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{rule}}', 'then_currency_code', $this->string(3)->after('then_to_account_id'));
        $this->addColumn('{{rule}}', 'then_currency_amount_cent', $this->integer()->after('then_to_account_id'));

        \app\core\models\Rule::updateAll(['then_currency_code' => 'CNY']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201111_100456_update_rule_table cannot be reverted.\n";
        $this->dropColumn('{{%rule}}', 'then_currency_amount_cent');
        $this->dropColumn('{{%rule}}', 'then_currency_code');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201111_100456_update_rule_table cannot be reverted.\n";

        return false;
    }
    */
}
