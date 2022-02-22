<?php
namespace Apps\P_StatusBg\Installation\Database;


use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Pstatusbg_Backgrounds
 * @package Apps\P_StatusBg\Installation\Database
 */
class Pstatusbg_Backgrounds extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'pstatusbg_backgrounds';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'background_id' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'UNSIGNED NOT NULL',
                'primary_key' => true,
                'auto_increment' => true,
            ],
            'collection_id' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'NOT NULL',
            ],
            'image_path' => [
                'type' => 'varchar',
                'type_value' => '255',
                'other' => 'DEFAULT NULL',
            ],
            'server_id' => [
                'type' => 'tinyint',
                'type_value' => '3',
                'other' => 'NOT NULL DEFAULT \'0\'',
            ],
            'ordering' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'NOT NULL DEFAULT \'0\''
            ],
            'is_deleted' => [
                'type' => 'tinyint',
                'type_value' => '1',
                'other' => 'NOT NULL DEFAULT \'0\''
            ],
            'time_stamp' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'NOT NULL DEFAULT \'0\''
            ],
            'view_id' => [
                'type' => 'tinyint',
                'type_value' => '1',
                'other' => 'NOT NULL DEFAULT \'0\''
            ],
        ];
    }

    /**
     * Set keys of table
     */
    protected function setKeys()
    {
        $this->_key = [
            'background_id' => ['background_id'],
            'background_id_1' => ['background_id', 'is_deleted'],
        ];
    }
}