<?php

use yii\db\Migration;

/**
 * Class m201102_072500_add_ledger_id
 */
class m201102_072500_add_ledger_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{category}}', 'ledger_id', $this->integer()->after('id'));
        $this->createIndex('category_ledger_id', '{{%category}}', ['ledger_id']);

        $this->addColumn('{{record}}', 'ledger_id', $this->integer()->after('id'));
        $this->createIndex('record_ledger_id', '{{%record}}', ['ledger_id']);

        $this->addColumn('{{rule}}', 'then_ledger_id', $this->integer()->after('if_keywords'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201102_072500_add_ledger_id cannot be reverted.\n";
        $this->dropColumn('{{%category}}', 'ledger_id');
        $this->dropColumn('{{%record}}', 'ledger_id');
        $this->dropColumn('{{%rule}}', 'then_ledger_id');
        return true;
    }
}
