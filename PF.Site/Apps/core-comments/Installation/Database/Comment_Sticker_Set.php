<?php

namespace Apps\Core_Comments\Installation\Database;


use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * class Comment_Sticker_Set
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Sticker_Set extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_sticker_set';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'set_id'        => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'title'         => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'NOT NULL'
            ],
            'used'          => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'total_sticker' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'is_active'     => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'1\''
            ],
            'is_default'    => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'thumbnail_id'  => [
                'type'       => 'int',
                'type_value' => '11',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'ordering'      => [
                'type'       => 'int',
                'type_value' => '11',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'view_only'     => [
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
            'set_id' => ['set_id', 'is_active'],
        ];
    }
}