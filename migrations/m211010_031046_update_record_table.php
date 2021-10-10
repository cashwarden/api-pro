<?php

use yii\db\Migration;

/**
 * Class m211010_031046_update_record_table
 */
class m211010_031046_update_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%record}}',
            'review',
            $this->tinyInteger()->defaultValue(0)->after('reimbursement_status')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211010_031046_update_record_table cannot be reverted.\n";
        $this->dropColumn('{{%record}}', 'review');

        return true;
    }
}
