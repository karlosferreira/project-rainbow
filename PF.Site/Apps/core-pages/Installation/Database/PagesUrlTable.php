<?php

namespace Apps\Core_Pages\Installation\Database;

use Core\App\Install\Database\Field;
use Core\App\Install\Database\Table;

class PagesUrlTable extends Table
{
    /**
     * Set name of this table, can't missing
     */
    protected function setTableName()
    {
        $this->_table_name = 'pages_url';
    }

    /**
     * Set all fields of table
     */
    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'vanity_url' => [
                'type' => Field::TYPE_VARCHAR,
                'type_value' => 255,
                'other' => 'NOT NULL',
            ],
            'page_id' => [
                'type' => Field::TYPE_INT,
                'type_value' => 10,
                'other' => 'UNSIGNED NOT NULL',
                'primary_key' => true,
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'vanity_url' => ['vanity_url']
        ];
    }
}
