<?php
namespace Apps\Core_Messages\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

class Mail_Thread_User extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mail_thread_user';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'thread_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'user_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'is_read' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'is_archive' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'is_sent' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'is_sent_update' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'is_spam' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
                Field::FIELD_PARAM_OTHER => 'DEFAULT 0'
            ],
            'last_message_id_for_delete' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED DEFAULT 0'
            ],
        ];
    }
    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'user_id' => ['user_id'],
            'user_id_3' => ['user_id', 'is_sent'],
            'user_id_2' => ['user_id', 'is_archive', 'is_sent_update'],
            'user_id_4' => ['user_id', 'is_archive', 'is_sent'],
            'user_id_5' => ['user_id', 'is_archive']
        ];
    }
}
