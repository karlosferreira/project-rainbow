<?php
namespace Apps\Core_Messages\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

class Mail_Thread_Custom_List extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mail_thread_custom_list';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'custom_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'folder_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ],
            'user_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 10,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL'
            ]
        ];
    }
    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'folder_id' => ['folder_id'],
            'folder_id_1' => ['folder_id', 'user_id'],
        ];
    }
}
