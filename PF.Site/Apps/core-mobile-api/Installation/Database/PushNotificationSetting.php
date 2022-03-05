<?php

namespace Apps\Core_MobileApi\Installation\Database;

use Core\App\Install\Database\Table as Table;

/**
 * Class MenuItem
 * @package Apps\Core_MobileApi\Installation\Database
 */
class PushNotificationSetting extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mobile_api_push_notification_setting';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'push_notification_id' => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'module_id' => [
                'type'       => 'varchar',
                'type_value' => '50',
                'other'      => 'NOT NULL',
            ],
            'time_stamp' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT 0',
            ]
        ];
    }
    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'user_id' => ['user_id'],
            'user_id_2' => ['user_id', 'module_id']
        ];
    }
}