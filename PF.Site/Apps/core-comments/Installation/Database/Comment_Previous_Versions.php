<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * class Comment_Previous_Versions
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Previous_Versions extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_previous_versions';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'version_id'      => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'comment_id'      => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'time_update'     => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'user_id'         => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'text'            => [
                'type'  => 'text',
                'other' => 'NULL'
            ],
            'text_parsed'     => [
                'type'  => 'text',
                'other' => 'NULL'
            ],
            'attachment_text' => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL'
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
            'user_id'    => ['user_id', 'comment_id']
        ];
    }
}