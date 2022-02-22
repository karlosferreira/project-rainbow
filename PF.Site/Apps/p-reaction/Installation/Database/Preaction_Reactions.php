<?php
namespace Apps\P_Reaction\Installation\Database;


use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class preaction_Reactions
 * @package Apps\P_Reaction\Installation\Database
 */
class Preaction_Reactions extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'preaction_reactions';
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'id' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'UNSIGNED NOT NULL',
                'primary_key' => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type' => 'varchar',
                'type_value' => '255',
                'other' => 'NOT NULL'
            ],
            'is_active' => [
                'type' => 'tinyint',
                'type_value' => '1',
                'other' => 'NOT NULL DEFAULT \'1\''
            ],
            'is_deleted' => [
                'type' => 'tinyint',
                'type_value' => '1',
                'other' => 'NOT NULL DEFAULT \'0\''
            ],
            'icon_path' => [
                'type' => 'varchar',
                'type_value' => '255',
                'other' => 'DEFAULT NULL'
            ],
            'color' => [
                'type' => 'varchar',
                'type_value' => '75',
                'other' => 'NOT NULL DEFAULT \'#2681D5\''
            ],
            'server_id' => [
                'type' => 'tinyint',
                'type_value' => '1',
                'other' => 'NOT NULL DEFAULT \'0\''
            ],
            'view_id' => [
                'type' => 'tinyint',
                'type_value' => '1',
                'other' => 'NOT NULL DEFAULT \'0\''
            ],
            'ordering' => [
                'type' => 'int',
                'type_value' => '11',
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
            'id' => ['id', 'is_active'],
            'id_1' => ['id', 'is_deleted'],
        ];
    }
}