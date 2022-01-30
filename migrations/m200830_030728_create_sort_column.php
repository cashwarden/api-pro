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
 * Class m200830_030728_create_sort_column.
 */
class m200830_030728_create_sort_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%rule}}', 'sort', $this->tinyInteger()->defaultValue(99)->after('status'));
        $this->addColumn('{{%account}}', 'sort', $this->tinyInteger()->defaultValue(99)->after('default'));
        $this->addColumn('{{%category}}', 'sort', $this->tinyInteger()->defaultValue(99)->after('default'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200830_030728_create_sort_column cannot be reverted.\n";
        $this->dropColumn('{{%rule}}', 'sort');
        $this->dropColumn('{{%account}}', 'sort');
        $this->dropColumn('{{%category}}', 'sort');

        return true;
    }
}
