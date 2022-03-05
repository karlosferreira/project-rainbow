<?php
namespace Apps\Core_Subscriptions\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

class Subscribe_Cancel_Reason extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'subscribe_cancel_reason';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'cancel_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'purchase_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'reason_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_SMALLINT,
                Field::FIELD_PARAM_TYPE_VALUE => 4,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'time_stamp' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
        ];
    }
}
