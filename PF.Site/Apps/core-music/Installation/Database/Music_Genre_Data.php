<?php

namespace Apps\Core_Music\Installation\Database;

use \Core\App\Install\Database\Table as Table;

class Music_Genre_Data extends Table
{
    /**
     *
     */
    protected function setTableName()
    {
        $this->_table_name = 'music_genre_data';
    }

    /**
     *
     */
    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'song_id'  => [
                'type'        => 'int',
                'type_value'  => '10',
                'other'       => 'UNSIGNED NOT NULL',
                'primary_key' => true,
            ],
            'genre_id' => [
                'type'        => 'int',
                'type_value'  => '10',
                'other'       => 'UNSIGNED NOT NULL',
                'primary_key' => true,
            ],
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