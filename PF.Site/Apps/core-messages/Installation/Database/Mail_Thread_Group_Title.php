<?php
namespace Apps\Core_Messages\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

class Mail_Thread_Group_Title extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'mail_thread_group_title';
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
           'title' => [
               Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
               Field::FIELD_PARAM_OTHER => 'DEFAULT NULL'
           ],
        ];
    }
}
