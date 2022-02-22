<?php

namespace Apps\Core_Pages\Installation\Database;

use Core\App\Install\Database\Field;
use Core\App\Install\Database\Table;

class PagesTextTable extends Table
{
    /**
     * Set name of this table, can't missing
     */
    protected function setTableName()
    {
        $this->_table_name = 'pages_text';
    }

    /**
     * Set all fields of table
     */
    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'page_id' => [
                'type' => Field::TYPE_INT,
                'type_value' => 10,
                'other' => 'UNSIGNED NOT NULL',
                'primary_key' => true,
            ],
            'text' => [
                'type' => Field::TYPE_MEDIUMTEXT
            ],
            'text_parsed' => [
                'type' => Field::TYPE_MEDIUMTEXT
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
