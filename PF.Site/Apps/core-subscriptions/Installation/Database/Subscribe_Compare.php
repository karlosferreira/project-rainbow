<?php
namespace Apps\Core_Subscriptions\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

class Subscribe_Compare extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'subscribe_compare';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'compare_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'feature_title' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 255,
                Field::FIELD_PARAM_OTHER => 'NOT NULL'
            ],
            'feature_value' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
                Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
            ],
            'ordering' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_SMALLINT,
                Field::FIELD_PARAM_TYPE_VALUE => 4,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ]
        ];
    }

}