<?php

namespace Apps\Core_BetterAds\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

/**
 * Class Country
 * @package Apps\Core_BetterAds\Installation
 */
class Country extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'better_ads_country';
    }

    public function getTableName()
    {
        return $this->_table_name;
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'ads_country_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL',
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true
            ],
            'ads_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'country_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_CHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 2,
            ],
            'child_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMINT,
                Field::FIELD_PARAM_TYPE_VALUE => 8,
            ],
        ];
    }
}
