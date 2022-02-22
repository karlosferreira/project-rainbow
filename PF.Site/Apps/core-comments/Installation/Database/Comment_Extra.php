<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Comment_Extra
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Extra extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_extra';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'extra_id'   => [
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
            'extra_type' => [
                'type'  => 'enum',
                'other' => '(\'sticker\',\'photo\',\'preview\') DEFAULT \'sticker\''
            ],
            'item_id'    => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'image_path' => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL',
            ],
            'server_id'  => [
                'type'       => 'tinyint',
                'type_value' => '3',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],
            'params'     => [
                'type'  => 'text',
                'other' => 'NULL'
            ],
            'is_deleted' => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'comment_id' => ['comment_id'],
            'extra_type' => ['comment_id', 'extra_type'],
            'item_id'    => ['comment_id', 'item_id']
        ];
    }
}