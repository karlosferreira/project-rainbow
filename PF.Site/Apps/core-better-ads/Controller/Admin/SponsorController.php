<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Pager;
use Phpfox_Plugin;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class SponsorController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class SponsorController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_sponsor_process__start')) ? eval($sPlugin) : false);

        // process mass action
        if ($this->request()->getArray('val')) {
            $this->_processMassAction();

            return 'controller';
        }

        if (($iId = $this->request()->getInt('approve'))) {
            if (Phpfox::getService('ad.process')->approveSponsor($iId)) {
                $this->url()->send('admincp.ad.sponsor', null, _p('better_ads_ad_successfully_approved'));
            }
        }

        if (($iId = $this->request()->getInt('deny'))) {
            if (Phpfox::getService('ad.process')->denySponsor($iId)) {
                $this->url()->send('admincp.ad.sponsor', null, _p('better_ads_ad_successfully_denied'));
            }
        }

        if (($iId = $this->request()->getInt('delete'))) {
            if (Phpfox::getService('ad.process')->deleteSponsor($iId)) {
                $this->url()->send('admincp.ad.sponsor', null, _p('better_ads_ad_successfully_deleted'));
            }
        }

        $this->template()->setTitle(_p('manage_sponsorships'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(_p('manage_sponsorships'),
                $this->url()->makeUrl('admincp.ad.sponsor'))
            ->setPhrase([
                'are_you_sure_you_want_to_deny_selected_ads',
                'are_you_sure_you_want_to_delete_selected_ads_permanently',
                'are_you_sure_you_want_to_approve_selected_ads'
            ])
            ->assign([
                'aAds' => $this->_getSponsorships(),
                'iPendingCount' => (int)Phpfox::getService('ad.get')->getPendingSponsorCount(),
                'sPendingLink' => Phpfox_Url::instance()->makeUrl('admincp.ad.sponsor', ['search[status]' => 2]),
                'bIsSearch' => ($this->request()->get('search-id') ? true : false),
            ]);
        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();

        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_sponsor_process__end')) ? eval($sPlugin) : false);

        return 'controller';
    }

    private function _processMassAction()
    {
        $aVals = $this->request()->getArray('val');

        if ($aVals['approve']) {
            foreach ($aVals['id'] as $iSponsorId) {
                Phpfox::getService('ad.process')->approveSponsor($iSponsorId);
            }
            Phpfox::addMessage(_p('better_ads_ads_successfully_approved'));
        } elseif ($aVals['deny']) {
            foreach ($aVals['id'] as $iSponsorId) {
                Phpfox::getService('ad.process')->denySponsor($iSponsorId);
            }
            Phpfox::addMessage(_p('better_ads_ads_successfully_denied'));
        } elseif ($aVals['delete']) {
            foreach ($aVals['id'] as $iSponsorId) {
                Phpfox::getService('ad.process')->deleteSponsor($iSponsorId);
            }
            Phpfox::addMessage(_p('better_ads_ads_successfully_deleted'));
        }

        $this->url()->send('admincp.ad.sponsor');
    }

    private function _getSponsorships()
    {
        $iPage = $this->request()->getInt('page');
        $iLimit = 10;
        $aSearch = $this->request()->getArray('search');
        $sSort = $this->request()->get('sort', 's.start_date DESC');
        $aConds = ['1'];
        if (!empty($aSearch)) {
            if (!empty($aSearch['from_day']) && !empty($aSearch['from_month']) && !empty($aSearch['from_year'])) {
                $aConds[] = ' AND s.start_date>=' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['from_day'],
                        $aSearch['from_month'], $aSearch['from_year']);
            }
            if (!empty($aSearch['to_day']) && !empty($aSearch['to_month']) && !empty($aSearch['to_year'])) {
                $aConds[] = ' AND s.start_date<=' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['to_day'],
                        $aSearch['to_month'], $aSearch['to_year'], 'end');
            }
            if (!empty($aSearch['ad_name'])) {
                $aConds[] = " AND s.campaign_name LIKE '%$aSearch[ad_name]%'";
            }
            if (!empty($aSearch['creator'])) {
                $aConds[] = " AND u.full_name LIKE '%$aSearch[creator]%'";
            }
            if (!empty($aSearch['status'])) {
                if (is_numeric($aSearch['status'])) {
                    $aConds[] = ' AND s.is_custom = ' . $aSearch['status'];
                } else {
                    $aConds[] = ' AND s.is_custom=3 AND ' . Phpfox::getService('ad.get')->getApprovedCond($aSearch['status'], 's');
                }

                if ($aSearch['status'] == 2) {
                    $this->template()->assign('bViewingPending', true);
                }
            }
            if (isset($aSearch['active']) && $aSearch['active'] != -1) {
                $aConds[] = ' AND s.is_active = ' . $aSearch['active'];
                $aConds[] = ' AND s.is_custom NOT IN (1,2)';
            }
            $this->template()->assign('aForms', $aSearch);
        } else {
            $this->template()->assign('aForms', [
                'from_day' => date('j'),
                'from_month' => date('n'),
                'from_year' => date('Y'),
                'to_day' => date('j'),
                'to_month' => date('n'),
                'to_year' => date('Y'),
            ]);
        }
        $this->template()->assign('sCurrentSort', $sSort);
        list($iCnt, $aAds) = Phpfox::getService('ad.get')->getAdSponsor($aConds, $sSort, $iPage, $iLimit);

        Phpfox_Pager::instance()->set(array(
            'page' => $iPage,
            'size' => $iLimit,
            'count' => $iCnt
        ));

        return $aAds;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_sponsor__clean')) ? eval($sPlugin) : false);
    }
}
