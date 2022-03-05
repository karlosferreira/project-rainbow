<?php

namespace Apps\Core_BetterAds\Service;

use Phpfox;

/**
 * Class Browse
 * @package Apps\Core_BetterAds\Service
 */
class Browse extends \Phpfox_Service
{
    public function getQueryJoins()
    {

    }

    public function query()
    {

    }

    public function processRows(&$aRows)
    {
        foreach ($aRows as &$aRow) {
            $aPlan = Phpfox::getService('ad.get')->getPlan($aRow['location']);
            $aRow['location_name'] = $aPlan['title'];
            $aRow['status'] = Phpfox::getService('ad.get')->getStatusPhrase($aRow['is_custom'], $aRow['start_date'], $aRow['end_date']);
            $aRow['date'] = Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aRow['start_date']);
        }
    }
}
