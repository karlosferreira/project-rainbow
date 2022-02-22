<?php

namespace Apps\Core_Comments\Installation\Database;

use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Comment_Text
 * @package Apps\Core_Comments\Installation\Database
 */
class Comment_Text extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'comment_text';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'comment_id'  => [
                'type'        => 'int',
                'type_value'  => '10',
                'other'       => 'UNSIGNED NOT NULL',
                'primary_key' => true
            ],
            'text'        => [
                'type' => 'mediumtext',
            ],
            'text_parsed' => [
                'type' => 'mediumtext',
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [];
    }
}