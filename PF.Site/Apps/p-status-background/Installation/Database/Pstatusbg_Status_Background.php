<?php
namespace Apps\P_StatusBg\Installation\Database;


use Core\App\Install\Database\Table as Table;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Pstatusbg_Status_Background
 * @package Apps\P_StatusBg\Installation\Database
 */
class Pstatusbg_Status_Background extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'pstatusbg_status_background';
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
            'item_id' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'UNSIGNED NOT NULL',
            ],
            'user_id' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'UNSIGNED NOT NULL',
            ],
            'type_id' => [
                'type' => 'varchar',
                'type_value' => '255',
                'other' => 'NOT NULL',
            ],
            'module_id' => [
                'type' => 'varchar',
                'type_value' => '75',
                'other' => 'NOT NULL',
            ],
            'background_id' => [
                'type' => 'int',
                'type_value' => '10',
                'other' => 'UNSIGNED NOT NULL',
            ],
            'is_active' => [
                'type' => 'tinyint',
                'type_value' => '1',
                'other' => 'NOT NULL DEFAULT \'1\'',
            ],
            'time_stamp' => [
                'type' => 'int',
                'type_value' => '10',
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
            'background_id' => ['background_id', 'item_id'],
            'type_id' => ['type_id', 'item_id', 'background_id']
        ];
    }
}