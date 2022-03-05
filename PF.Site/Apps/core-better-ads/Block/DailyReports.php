<?php

namespace Apps\Core_BetterAds\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class DailyReports
 * @package Apps\Core_BetterAds\Block
 */
class DailyReports extends Phpfox_Component
{
    /**
     * Class process method which is used to execute this component.
     */
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_daily_reports_process__start')) ? eval($sPlugin) : false);

        $iAdId = $this->getParam('ad_id');
        if (empty($iAdId)) {
            return false;
        }

        $iPage = $this->getParam('page', 1);
        $iLimit = 5;
        $iDays = Phpfox::getService('ad.report')->getNumberOfDayFromStartDay($iAdId);

        Phpfox_Pager::instance()->set(array(
            'page' => $iPage,
            'size' => $iLimit,
            'count' => $iDays,
            'paging_mode' => 'pagination',
            'ajax_paging' => [
                'block' => 'ad.daily-reports',
                'params' => [
                    'ad_id' => $iAdId
                ],
                'container' => '.bts-daily-reports'
            ]
        ));

        $this->template()->assign([
            "aDailyReports" => Phpfox::getService('ad.report')->getDailyReports($iAdId, $iLimit, $iPage),
            'iDays' => $iDays
        ]);

        (($sPlugin = Phpfox_Plugin::get('ad.component_block_daily_reports_process__end')) ? eval($sPlugin) : false);

        return 'block';
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_daily_reports_clean')) ? eval($sPlugin) : false);
    }
}
