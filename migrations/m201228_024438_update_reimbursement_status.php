<?php

use yii\db\Migration;

/**
 * Class m201228_024438_update_reimbursement_status
 */
class m201228_024438_update_reimbursement_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{record}}',
            'reimbursement_status',
            $this->tinyInteger()->defaultValue(0)->after('exclude_from_stats')
        );
        $this->dropColumn('{{%transaction}}', 'reimbursement_status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201228_024438_update_reimbursement_status cannot be reverted.\n";
        $this->addColumn('{{transaction}}', 'reimbursement_status', $this->tinyInteger()->after('status'));
        $this->dropColumn('{{%record}}', 'reimbursement_status');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201228_024438_update_reimbursement_status cannot be reverted.\n";

        return false;
    }
    */
}