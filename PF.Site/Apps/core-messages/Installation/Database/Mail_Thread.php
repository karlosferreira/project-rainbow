<?php
namespace Apps\Core_Messages\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

class Mail_Thread extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mail_thread';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'thread_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'hash_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_CHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 32,
                Field::FIELD_PARAM_OTHER => 'NOT NULL'
            ],
            'time_stamp' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'last_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
            'last_id_for_admin' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
            'is_group' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
        ];
    }
    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'last_id' => ['last_id'],
        ];
    }
}
