<?php

namespace Apps\Core_Music\Installation\Database;

use \Core\App\Install\Database\Table as Table;

class Music_Playlist_Data extends Table
{
    /**
     *
     */
    protected function setTableName()
    {
        $this->_table_name = 'music_playlist_data';
    }

    /**
     *
     */
    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'id'          => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'playlist_id' => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'song_id'     => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'ordering'    => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'time_stamp'  => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ]
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'playlist_data' => ['playlist_id', 'song_id']
        ];
    }
}