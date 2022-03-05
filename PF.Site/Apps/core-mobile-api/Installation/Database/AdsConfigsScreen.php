<?php

namespace Apps\Core_MobileApi\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class DeviceToken
 * @package Apps\Core_MobileApi\Installation\Database
 */
class AdsConfigsScreen extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mobile_api_ads_config_screen';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'id'        => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'config_id' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'module_id' => [
                'type'       => 'varchar',
                'type_value' => '100',
                'other'      => 'NOT NULL'
            ],
            'screen'    => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'NOT NULL'
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'screen' => ['config_id', 'screen']
        ];
    }
}