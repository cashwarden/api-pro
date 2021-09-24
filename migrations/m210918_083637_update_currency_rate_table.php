<?php

use yii\db\Migration;

/**
 * Class m210918_083637_update_currency_rate_table
 */
class m210918_083637_update_currency_rate_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%currency_rate}}', 'currency_code');
        $this->dropColumn('{{%currency_rate}}', 'currency_name');
        $this->addColumn(
            '{{%currency_rate}}',
            'ledger_id',
            $this->integer(11)->notNull()->after('user_id')
        );
        $this->addColumn(
            '{{%currency_rate}}',
            'currency_code_to',
            $this->char(3)->notNull()->after('ledger_id')
        );

        $this->addColumn(
            '{{%currency_rate}}',
            'currency_code_from',
            $this->char(3)->notNull()->after('ledger_id')
        );

        $this->addColumn(
            '{{%ledger}}',
            'base_currency_code',
            $this->char(3)->notNull()->after('user_id')
        );
        \app\core\models\Ledger::updateAll(['base_currency_code' => \app\core\types\CurrencyType::CNY_KEY]);

        $this->renameTable('{{%currency_rate}}', '{{%currency}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210918_083637_update_currency_rate_table cannot be reverted.\n";

        $this->dropColumn('{{%currency_rate}}', 'ledger_id');
        $this->dropColumn('{{%currency_rate}}', 'currency_code_from');
        $this->dropColumn('{{%currency_rate}}', 'currency_code_to');
        $this->dropColumn('{{%ledger}}', 'base_currency_code');

        $this->addColumn(
            '{{%currency_rate}}',
            'currency_code',
            $this->char(3)->notNull()->after('user_id')
        );
        $this->addColumn(
            '{{%currency_rate}}',
            'currency_name',
            $this->string(60)->notNull()->after('currency_code')
        );
        $this->renameTable('{{%currency}}', '{{%currency_rate}}');
        return true;
    }
}
