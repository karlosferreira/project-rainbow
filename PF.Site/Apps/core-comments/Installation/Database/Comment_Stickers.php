<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * class Comment_Stickers
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Stickers extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_stickers';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'sticker_id' => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'set_id'     => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'NOT NULL',
            ],
            'image_path' => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL',
            ],
            'server_id'  => [
                'type'       => 'tinyint',
                'type_value' => '3',
                'other'      => 'NOT NULL',
            ],
            'ordering'   => [
                'type'       => 'int',
                'type_value' => '11',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'view_only'  => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'is_deleted' => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'set_id'     => ['set_id'],
            'image_path' => ['sticker_id', 'image_path', 'server_id']
        ];
    }
}