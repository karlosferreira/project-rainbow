<?php
namespace Apps\Core_Subscriptions\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

class Subscribe_Package extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'subscribe_package';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'package_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_SMALLINT,
                Field::FIELD_PARAM_TYPE_VALUE => 4,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'title' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 255,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'description' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'cost' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'recurring_cost' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'recurring_period' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'user_group_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 3,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'fail_user_group' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 3,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'image_path' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 50,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'server_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'is_active' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 3,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'is_registration' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'is_required' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'show_price' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 1'
            ],
            'ordering' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_SMALLINT,
                Field::FIELD_PARAM_TYPE_VALUE => 4,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
            'total_active' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
            'background_color' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 50,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'is_popular' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'visible_group' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'is_free' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'number_day_notify_before_expiration'=> [
                Field::FIELD_PARAM_TYPE => Field::TYPE_SMALLINT,
                Field::FIELD_PARAM_TYPE_VALUE => 4,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
            'is_removed' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'time_updated' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
            'allow_payment_methods' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ]
        ];
    }
    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'is_active' => ['is_active'],
            'package_id' => ['package_id', 'is_active'],
            'is_active_2' => ['is_active', 'is_registration']
        ];
    }
}
