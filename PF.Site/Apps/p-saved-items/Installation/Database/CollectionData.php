<?php

namespace Apps\P_SavedItems\Installation\Database;

use Core\App\Install\Database\Table as Table;
use Core\App\Install\Database\Field as Field;

class CollectionData extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'saved_collection_data';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'saved_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL',
            ],
            'collection_id' => [
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL',
            ],
        ];
    }
}