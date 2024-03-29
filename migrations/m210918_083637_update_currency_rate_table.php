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
 * Class m210918_083637_update_currency_rate_table.
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

        $this->createIndex(
            'currency_ledger_id_from_to',
            '{{%currency}}',
            ['ledger_id', 'currency_code_from', 'currency_code_to'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210918_083637_update_currency_rate_table cannot be reverted.\n";
        $this->renameTable('{{%currency}}', '{{%currency_rate}}');

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
        $this->dropIndex('currency_ledger_id_from_to', '{{%currency_rate}}');
        return true;
    }
}
