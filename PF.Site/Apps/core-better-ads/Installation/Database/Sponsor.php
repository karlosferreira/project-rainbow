<?php

namespace Apps\Core_BetterAds\Installation\Database;

use Core\App\Install\Database\Field as Field;
use Core\App\Install\Database\Table as Table;

/**
 * Class Sponsor
 * @package Apps\Core_BetterAds\Installation
 */
class Sponsor extends Table
{
    protected function setTableName()
    {
        $this->_table_name = 'better_ads_sponsor';
    }

    public function getTableName()
    {
        return $this->_table_name;
    }

    protected function setFieldParams()
    {
        $this->_aFieldParams = [
            'sponsor_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
                Field::FIELD_PARAM_OTHER => 'UNSIGNED NOT NULL',
                Field::FIELD_PARAM_PRIMARY_KEY => true,
                Field::FIELD_PARAM_AUTO_INCREMENT => true
            ],
            'module_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 50,
            ],
            'item_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'user_id' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'country_iso' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TEXT,
            ],
            'gender' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 75
            ],
            'age_from' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 2,
            ],
            'age_to' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 2,
            ],
            'campaign_name' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_VARCHAR,
                Field::FIELD_PARAM_TYPE_VALUE => 511,
            ],
            'impressions' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_MEDIUMINT,
                Field::FIELD_PARAM_TYPE_VALUE => 8,
            ],
            'cpm' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_DECIMAL,
                Field::FIELD_PARAM_TYPE_VALUE => '(14 ,2)',
            ],
            'start_date' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'end_date' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'auto_publish' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
            ],
            /*
               The field is_custom tells the state of the ad as follows:
                   1: Pending Payment
                   2: Pending Approval
                   3: Approved
                   4: Denied
             */
            'is_custom' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
            ],
            'is_active' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TINYINT,
                Field::FIELD_PARAM_TYPE_VALUE => 1,
            ],
            'total_view' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'total_click' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_INT,
                Field::FIELD_PARAM_TYPE_VALUE => 11,
            ],
            'postal_code' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TEXT,
            ],
            'city_location' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TEXT,
            ],
            'languages' => [
                Field::FIELD_PARAM_TYPE => Field::TYPE_TEXT,
            ],
        ];
    }
}
