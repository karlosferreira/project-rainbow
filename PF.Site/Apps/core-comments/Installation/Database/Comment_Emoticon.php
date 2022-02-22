<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Comment_Emoticon
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Emoticon extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_emoticon';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'emoticon_id' => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'title'       => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'NOT NULL',
            ],
            'code'        => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'NOT NULL',
            ],
            'unicode'        => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'DEFAULT NULL',
            ],
            'image'       => [
                'type'       => 'varchar',
                'type_value' => '128',
                'other'      => 'NOT NULL',
            ],
            'ordering'    => [
                'type'       => 'int',
                'type_value' => '6',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],

        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'code'   => ['code'],
            'code_2' => ['code', 'image'],
            'code_3' => ['code', 'title', 'image']
        ];
    }
}