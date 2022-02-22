<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Comment_Track
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Track extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_track';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'track_id'   => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'comment_id' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'item_id'    => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'track_type' => [
                'type'  => 'enum',
                'other' => '(\'emoticon\',\'sticker\') DEFAULT \'emoticon\''
            ],
            'user_id'    => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'time_stamp' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'item_id' => ['item_id'],
            'user_id' => ['user_id']
        ];
    }
}