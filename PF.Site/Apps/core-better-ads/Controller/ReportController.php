<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class ReportController
 * @package Apps\Core_BetterAds\Controller
 */
class ReportController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::getService('ad.get')->getSectionMenu();
        $iAdsId = $this->request()->get('ads_id');
        //Get Ads information
        $aAds = Phpfox::getService('ad.report')->getAds($iAdsId);

        if ($aAds['user_id'] != Phpfox::getUserId()) {
            return \Phpfox_Error::display(_p('you_dont_have_permission_to_edit_this_ad'));
        }

        //If ads not exist
        if (!isset($aAds['ads_id'])) {
            $this->url()->send('ad.manage', [], _p('better_ads_ad_not_found'));
        }
        defined('PHPFOX_APP_DETAIL_PAGE') or define('PHPFOX_APP_DETAIL_PAGE', true);

        $aReports = $this->_getReports($iAdsId);

        if ($this->request()->get('export')) {
            $this->_exportAd($aAds, $aReports);
        }

        $this->template()->assign([
            "aReports" => $aReports,
            "aAds" => $aAds,
            "aPlacement" => Phpfox::getService('ad.get')->getPlan($aAds['location']),
            "bAdvancedAdFilters" => setting('better_ads_advanced_ad_filters'),
            "iFilterFromYear" => $aAds['start_date'] ? date('Y', $aAds['start_date']) : 1990,
            "iFilterToYear" => $aAds['end_date'] ? date('Y', $aAds['end_date']) : date('Y'),
        ])
            ->setTitle($aAds['name'])
            ->setBreadCrumb(_p('better_ads_advertise'), $this->url()->makeUrl('ad'))
            ->setBreadCrumb($aAds['name'], $this->url()->makeUrl('ad.report', ['ads_id' => $aAds['ads_id']]), true)
            ->setPhrase([
                'better_ads_period',
                'better_ads_click',
                'better_ads_view',
            ]);

        return 'controller';
    }

    /**
     * @param $aAds
     * @param $aReports
     */
    private function _exportAd($aAds, $aReports)
    {
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename=' . $aAds['name'] . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, [
            _p('better_ads_period'),
            _p('better_ads_click'),
            _p('better_ads_view')
        ]);
        foreach ($aReports as $aReport) {
            fputcsv($out, [
                $aReport['day_string'],
                $aReport['total_click'],
                $aReport['total_view'],
            ]);
        }
        fclose($out);
        exit();
    }

    private function _getReports($iAdsId)
    {
        $aVals = $this->request()->getArray('val');

        if (empty($aVals)) {
            $aWeekAgoTimestamp = strtotime('-7 days');
            $aVals = [
                'from_day' => date('j', $aWeekAgoTimestamp),
                'from_month' => date('n', $aWeekAgoTimestamp),
                'from_year' => date('Y', $aWeekAgoTimestamp),
                'to_day' => date('j'),
                'to_month' => date('n'),
                'to_year' => date('Y'),
                'type' => 1
            ];
        }

        $this->template()->assign('aForms', $aVals);

        return Phpfox::getService('ad.report')->getReportForView($iAdsId, $aVals);
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_report_clean')) ? eval($sPlugin) : false);
    }
}
