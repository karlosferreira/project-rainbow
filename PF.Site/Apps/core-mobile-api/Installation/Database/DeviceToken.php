<?php

namespace Apps\Core_MobileApi\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class DeviceToken
 * @package Apps\Core_MobileApi\Installation\Database
 */
class DeviceToken extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mobile_api_device_token';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'id'           => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'user_id'      => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'time_stamp'   => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'token'        => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'NOT NULL',
            ],
            'device_id'    => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'NOT NULL DEFAULT \'\'',
            ],
            'platform'     => [
                'type'  => 'enum',
                'other' => '(\'android\',\'ios\') DEFAULT \'iOS\''
            ],
            'token_source' => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'NOT NULL DEFAULT \'firebase\'',
            ],
            'is_active'    => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'1\''
            ]

        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'user_id'   => ['user_id'],
            'user_id_1' => ['user_id', 'token'],
            'token'     => ['user_id', 'token', 'is_active'],
        ];
    }
}