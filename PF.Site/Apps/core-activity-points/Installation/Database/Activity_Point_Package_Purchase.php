<?php

namespace Apps\Core_Activity_Points\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Activity_Point_Package_Purchase
 * @package Apps\Core_Activity_Points\Installation\Database
 */
class Activity_Point_Package_Purchase extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'activitypoint_package_purchase';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'purchase_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'user_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'package_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'status' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 20,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'time_stamp' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
            'currency_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_CHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 3,
                Field::FIELD_PARAM_OTHER => 'NOT NULL'
            ],
            'price' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_DECIMAL,
                Field::FIELD_PARAM_TYPE_VALUE => '14,2',
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0.00'
            ],
            'payment_method' =>  [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 50,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'points' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL DEFAULT 0'
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
           'purchase_id' => ['purchase_id'],
            'purchase_status' => ['purchase_id', 'status'],
            'package_id' => ['package_id', 'user_id']
        ];
    }
}
