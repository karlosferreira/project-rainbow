<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Comment_Hash
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Hash extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_hash';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'user_id'    => [
                'type'        => 'int',
                'type_value'  => '10',
                'other'       => 'UNSIGNED NOT NULL',
            ],
            'item_hash'  => [
                'type'       => 'char',
                'type_value' => '32',
                'other'      => 'NOT NULL',
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
        $this->_key = [];
    }
}