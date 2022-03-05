<?php

namespace Apps\Core_MobileApi\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class DeviceToken
 * @package Apps\Core_MobileApi\Installation\Database
 */
class AdsConfigs extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mobile_api_ads_configs';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'id'                      => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'user_id'                 => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'time_stamp'              => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'name'                    => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'NOT NULL',
            ],
            'type'                    => [
                'type'       => 'varchar',
                'type_value' => '100',
                'other'      => 'NOT NULL',
            ],
            'frequency_capping'       => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'DEFAULT NULL'
            ],
            'view_capping'            => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'DEFAULT NULL'
            ],
            'time_capping_impression' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'DEFAULT NULL'
            ],
            'time_capping_frequency'  => [
                'type'       => 'varchar',
                'type_value' => '50',
                'other'      => 'DEFAULT NULL'
            ],
            'location_priority'       => [
                'type'  => 'text',
                'other' => 'DEFAULT NULL'
            ],
            'disallow_access'         => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'DEFAULT NULL'
            ],
            'is_stick'                => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\''
            ],
            'is_active'               => [
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
            'user_id'             => ['user_id'],
            'type'                => ['type', 'frequency_capping'],
            'frequency_capping'   => ['frequency_capping', 'view_capping'],
            'frequency_capping_1' => ['frequency_capping', 'time_capping_impression', 'time_capping_frequency'],
        ];
    }
}