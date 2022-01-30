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
 * Class m200821_031033_update_category_table.
 */
class m200821_031033_update_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%category}}', 'direction', 'transaction_type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200821_031033_update_category_table cannot be reverted.\n";
        $this->renameColumn('{{%category}}', 'transaction_type', 'direction');

        return true;
    }
}
