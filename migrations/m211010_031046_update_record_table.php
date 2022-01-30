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
 * Class m211010_031046_update_record_table.
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
