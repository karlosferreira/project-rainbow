<?php

namespace Apps\Core_Activity_Points\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Activity_Point_Setting
 * @package Apps\Core_Activity_Points\Installation\Database
 */
class Activity_Point_Setting extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'activitypoint_setting';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'setting_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'var_name' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 250,
                Field::FIELD_PARAM_OTHER => 'NOT NULL'
            ],
            'phrase_var_name' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 250,
                Field::FIELD_PARAM_OTHER => 'NOT NULL'
            ],
            'module_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 75,
                Field::FIELD_PARAM_OTHER => 'NOT NULL'
            ],
            'max_earned' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'period' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'is_active' => [
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

        ];
    }
}
