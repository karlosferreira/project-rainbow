<?php

namespace Apps\Core_MobileApi\Installation\Database;

use Core\App\Install\Database\Table as Table;

/**
 * Class MenuItem
 * @package Apps\Core_MobileApi\Installation\Database
 */
class MenuItem extends Table
{
    /**
     *
     */
    protected function setTableName()
    {
        $this->_table_name = 'mobile_api_menu_item';
    }

    /**
     *
     */
    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'item_id'     => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'section_id'  => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'name'        => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL',
            ],
            'item_type'   => [
                'type'  => 'enum',
                'other' => '(\'item\',\'section-item\',\'header\',\'section-header\',\'footer\',\'section-footer\',\'section-helper\',\'helper\') DEFAULT \'item\''
            ],
            'is_active'   => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'1\'',
            ],
            'icon_name'   => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'NOT NULL DEFAULT \'box\'',
            ],
            'icon_family' => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'NOT NULL DEFAULT \'Lineficon\'',
            ],
            'icon_color'  => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'DEFAULT NULL',
            ],
            'path'        => [
                'type'       => 'varchar',
                'type_value' => '250',
                'other'      => 'NOT NULL'
            ],
            'is_system'   => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'1\'',
            ],
            'module_id'   => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'NOT NULL',
            ],
            'is_url'      => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],
            'disallow_access' => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL',
            ],
            'ordering'    => [
                'type'       => 'int',
                'type_value' => '11',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'name' => ['name', 'item_id'],
        ];
    }
}