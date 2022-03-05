<?php

namespace Apps\Core_Comments\Installation\Database;


use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * class Comment_User_Sticker_Set
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_User_Sticker_Set extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_user_sticker_set';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'user_id'    => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'set_id'     => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'time_stamp' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'user_id' => ['user_id', 'set_id'],
        ];
    }
}