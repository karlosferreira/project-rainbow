<?php

namespace Apps\Core_BetterAds\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

/**
 * Class Plan
 * @package Apps\Core_BetterAds\Installation
 */
class Plan extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'better_ads_plan';
    }

    public function getTableName()
    {
        return $this->_table_name;
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'plan_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL',
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true
            ],
            'block_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 3,
            ],
            'title' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 255,
            ],
            'disallow_controller' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
            ],
            'cost' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMTEXT,
            ],
            'user_group' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 255
            ],
            'is_active' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
            ],
            'is_cpm' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
            ],
        ];
    }
}
