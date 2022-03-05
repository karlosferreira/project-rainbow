<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class ManageSponsorController
 * @package Apps\Core_BetterAds\Controller
 */
class ManageSponsorController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        // DELETE SPONSOR
        if (($iId = $this->request()->getInt('delete'))) {
            if (Phpfox::getService('ad.process')->deleteSponsor($iId)) {
                $this->url()->send('ad.manage-sponsor', null, _p('ad_successfully_deleted'));
            }
        }
        // MANAGE SPONSOR
        $this->template()->setTitle(_p('my_sponsorships'))
            ->setBreadCrumb(_p('better_ads_advertise'), $this->url()->makeUrl('ad'))
            ->setBreadCrumb(_p('my_sponsorships'), $this->url()->makeUrl('ad.manage-sponsor'), true)
            ->assign([
                'aAds' => $this->_getSponsors(),
            ]);
        Phpfox::getService('ad.get')->getSectionMenu();
    }

    private function _getSponsors()
    {
        $aCond = ['AND s.user_id = ' . Phpfox::getUserId()];

        if ($aSearch = $this->request()->getArray('search')) {
            // search by start date

            // date from
            if (!empty($aSearch['from_month']) && !empty($aSearch['from_day']) && !empty($aSearch['from_year'])) {
                $iFromTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aSearch['from_day'], $aSearch['from_month'], $aSearch['from_year']);
                $aCond[] = " AND s.start_date>=$iFromTimestamp";
            }
            // date to
            if (!empty($aSearch['to_month']) && !empty($aSearch['to_day']) && !empty($aSearch['to_year'])) {
                $iToTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aSearch['to_day'], $aSearch['to_month'], $aSearch['to_year'], 'end');
                $aCond[] = " AND s.start_date<=$iToTimestamp";
            }

            // sponsor status
            if (!empty($aSearch['is_custom'])) {
                if (is_numeric($aSearch['is_custom'])) {
                    $aCond[] = ' AND s.is_custom=' . $aSearch['is_custom'];
                } else {
                    $aCond[] = ' AND s.is_custom=3 AND ' . Phpfox::getService('ad.get')->getApprovedCond($aSearch['is_custom'], 's');
                }
            }
            // sponsor type
            if (!empty($aSearch['type'])) {
                if ($aSearch['type'] == 1) {
                    $aCond[] = ' AND s.module_id!="feed" ';
                } else {
                    $aCond[] = ' AND s.module_id="feed" ';
                }
            }

            $this->template()->assign('aForms', $aSearch);
        } else {
            $aForms = [
                'from_day' => Phpfox::getTime('j', PHPFOX_TIME - 30*24*3600),
                'from_month' => Phpfox::getTime('n', PHPFOX_TIME - 30*24*3600),
                'from_year' => Phpfox::getTime('Y', PHPFOX_TIME - 30*24*3600),
                'to_day' => Phpfox::getTime('j', PHPFOX_TIME),
                'to_month' => Phpfox::getTime('n', PHPFOX_TIME),
                'to_year' => Phpfox::getTime('Y', PHPFOX_TIME),
            ];
            $this->template()->assign('aForms', $aForms);
            $iFromTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aForms['from_day'], $aForms['from_month'], $aForms['from_year']);
            $aCond[] = " AND s.start_date>=$iFromTimestamp";
            $iToTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aForms['to_day'], $aForms['to_month'], $aForms['to_year'], 'end');
            $aCond[] = " AND s.start_date<=$iToTimestamp";
        }

        $sSort = $this->request()->get('sort');
        if ($sSort) {
            $this->search()->setSort($sSort);
        }
        $this->template()->assign(['sCurrentSort' => empty($sSort) ? '' : $sSort]);

        $aAds = Phpfox::getService('ad.get')->getSponsorForUser($aCond, $sSort);

        return $aAds;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_manage_sponsor_clean')) ? eval($sPlugin) : false);
    }
}
