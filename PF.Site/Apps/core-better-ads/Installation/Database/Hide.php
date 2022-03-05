<?php
namespace Apps\Core_BetterAds\Installation\Database;

use \Core\App\Install\Database\Field as Field;
use \Core\App\Install\Database\Table as Table;

/**
 * Class Hide
 * @package Apps\Core_BetterAds\Installation
 */
class Hide extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'better_ads_hide';
    }

    public function getTableName()
    {
        return $this->_table_name;
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'hide_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL',
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true
            ],
            'module_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 50,
                Field::FIELD_PARAM_OTHER => 'NULL',
            ],
            'ads_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'user_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
        ];
    }
}
