<?php
namespace Apps\phpFox_Shoutbox\Installation\Database;

use \Core\App\Install\Database\Field as Field;
use \Core\App\Install\Database\Table as Table;

/**
 * Class Shoutbox
 * @package Apps\phpFox_Shoutbox\Installation\Database
 */
class Shoutbox_Quoted_Message extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'shoutbox_quoted_message';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'shoutbox_id'      => [
                Field::FIELD_PARAM_PRIMARY_KEY    => true,
                Field::FIELD_PARAM_TYPE           => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE     => 11,
                Field::FIELD_PARAM_OTHER          => 'UNSIGNED NOT NULL UNIQUE',
            ],
            'user_id'          => [
                Field::FIELD_PARAM_TYPE       => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11
            ],
            'text'             => [
                Field::FIELD_PARAM_TYPE       => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 1023
            ],
        ];
    }
}