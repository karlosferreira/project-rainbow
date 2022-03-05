<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Comment
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'comment_id'    => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'parent_id'     => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'type_id'       => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'NOT NULL',
            ],
            'item_id'       => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'user_id'       => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'owner_user_id' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'time_stamp'    => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'update_time'   => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'update_user'   => [
                'type'       => 'varchar',
                'type_value' => '100',
                'other'      => 'DEFAULT NULL',
            ],
            'rating'        => [
                'type'       => 'varchar',
                'type_value' => '10',
                'other'      => 'DEFAULT NULL',
            ],
            'ip_address'    => [
                'type'       => 'varchar',
                'type_value' => '50',
                'other'      => 'NOT NULL',
            ],
            'author'        => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL',
            ],
            'author_email'  => [
                'type'       => 'varchar',
                'type_value' => '100',
                'other'      => 'DEFAULT NULL',
            ],
            'author_url'    => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL',
            ],
            'view_id'       => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],
            'child_total'   => [
                'type'       => 'smallint',
                'type_value' => '4',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'total_like'    => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'total_dislike' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'feed_table'    => [
                'type'       => 'varchar',
                'type_value' => '10',
                'other'      => 'NOT NULL DEFAULT \'feed\'',
            ],
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'user_id'       => ['user_id', 'view_id'],
            'owner_user_id' => ['owner_user_id', 'view_id'],
            'type_id'       => ['type_id', 'item_id', 'view_id'],
            'parent_id'     => ['parent_id', 'view_id'],
            'parent_id_2'   => ['parent_id', 'type_id', 'item_id', 'view_id'],
            'view_id'       => ['view_id'],
        ];
    }
}