<?php

namespace Apps\Core_Music\Installation\Database;

use \Core\App\Install\Database\Table as Table;

class Music_Playlist extends Table
{
    /**
     *
     */
    protected function setTableName()
    {
        $this->_table_name = 'music_playlist';
    }

    /**
     *
     */
    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'playlist_id'        => [
                'type'           => 'int',
                'type_value'     => '10',
                'other'          => 'UNSIGNED NOT NULL',
                'primary_key'    => true,
                'auto_increment' => true,
            ],
            'user_id'            => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL',
            ],
            'name'               => [
                'type'       => 'varchar',
                'type_value' => '255',
                'other'      => 'NOT NULL',
            ],
            'description'        => [
                'type'  => 'text',
                'other' => 'NULL',
            ],
            'description_parsed' => [
                'type'  => 'text',
                'other' => 'NULL',
            ],
            'image_path'         => [
                'type'       => 'varchar',
                'type_value' => '75',
                'other'      => 'DEFAULT NULL',
            ],
            'server_id'          => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],
            'total_track'        => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'total_view'         => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'total_attachment'   => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'time_stamp'         => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'view_id'            => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],
            'privacy'            => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],
            'privacy_comment'    => [
                'type'       => 'tinyint',
                'type_value' => '1',
                'other'      => 'NOT NULL DEFAULT \'0\'',
            ],
            'total_comment'      => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
            'total_like'         => [
                'type'       => 'int',
                'type_value' => '10',
                'other'      => 'UNSIGNED NOT NULL DEFAULT \'0\'',
            ],
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'playlist_id' => ['playlist_id', 'user_id']
        ];
    }
}