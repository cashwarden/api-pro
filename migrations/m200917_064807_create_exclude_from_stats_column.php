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
 * Class m200917_064807_create_exclude_from_stats_column.
 */
class m200917_064807_create_exclude_from_stats_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%record}}', 'exclude_from_stats', $this->tinyInteger()->defaultValue(0)->after('source'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200917_064807_create_exclude_from_stats_column cannot be reverted.\n";
        $this->dropColumn('{{%record}}', 'exclude_from_stats');
        return true;
    }
}
