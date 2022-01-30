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
 * Class m201102_072500_add_ledger_id.
 */
class m201102_072500_add_ledger_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%category}}', 'ledger_id', $this->integer()->after('id'));
        $this->createIndex('category_ledger_id', '{{%category}}', ['ledger_id']);

        $this->addColumn('{{%transaction}}', 'ledger_id', $this->integer()->after('id'));
        $this->createIndex('transaction_ledger_id', '{{%transaction}}', ['ledger_id']);

        $this->addColumn('{{%record}}', 'ledger_id', $this->integer()->after('id'));
        $this->createIndex('record_ledger_id', '{{%record}}', ['ledger_id']);

        $this->addColumn('{{%tag}}', 'ledger_id', $this->integer()->after('id'));
        $this->createIndex('tag_ledger_id', '{{%tag}}', ['ledger_id']);

        $this->addColumn('{{%rule}}', 'ledger_id', $this->integer()->after('if_keywords'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201102_072500_add_ledger_id cannot be reverted.\n";
        $this->dropColumn('{{%category}}', 'ledger_id');
        $this->dropColumn('{{%transaction}}', 'ledger_id');
        $this->dropColumn('{{%record}}', 'ledger_id');
        $this->dropColumn('{{%tag}}', 'ledger_id');
        $this->dropColumn('{{%rule}}', 'ledger_id');

        \app\core\models\LedgerMember::deleteAll();
        \app\core\models\Ledger::deleteAll();
        return true;
    }
}
