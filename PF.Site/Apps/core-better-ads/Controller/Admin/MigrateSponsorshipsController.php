<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Pager;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class MigrateSponsorshipsController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class MigrateSponsorshipsController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_migrate_sponsorships_process__start')) ? eval($sPlugin) : false);

        // delete ad
        if ($this->request()->getInt('delete')) {
            $this->_deleteSponsorship();

            return 'controller';
        }

        // mass delete ads
        if ($this->request()->getArray('val')) {
            $this->_massDeleteAds();

            return 'controller';
        }

        $this->template()->setTitle(_p('migrate_sponsorships'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(_p('migrate_sponsorships'), $this->url()->makeUrl('admincp.ad.migrate-sponsorships'))
            ->setPhrase([
                'are_you_sure_you_want_to_delete_selected_ads_permanently',
            ])
            ->assign([
                'aAds' => $this->_getSponsorships()
            ]);
        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();

        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_migrate_sponsorships_process__end')) ? eval($sPlugin) : false);

        return 'controller';
    }

    private function _massDeleteAds()
    {
        $aVals = $this->request()->getArray('val');

        if (!empty($aVals['delete'])) {
            foreach ($aVals['id'] as $id) {
                Phpfox::getService('ad.migrate')->deleteSponsorship($id);
            }
            $this->url()->send('admincp.ad.migrate-sponsorships', null,
                _p('migrate_sponsorships_successfully_deleted'));
        }
    }

    private function _deleteSponsorship()
    {
        $iSponsorId = $this->request()->getInt('delete');
        Phpfox::getService('ad.migrate')->deleteSponsorship($iSponsorId);
        $this->url()->send('admincp.ad.migrate-sponsorships', null, _p('migrate_sponsorship_successfully_deleted'));
    }

    private function _getSponsorships()
    {
        $iPage = $this->request()->getInt('page');
        $iLimit = 10;
        $aSearch = $this->request()->getArray('search');
        $sSort = $this->request()->get('sort', 's.start_date asc');
        $aConds = ['1'];

        if (!empty($aSearch)) {
            if ($aSearch['from_day'] && $aSearch['from_month'] && $aSearch['from_year']) {
                $aConds[] = ' AND s.start_date >= ' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['from_day'],
                        $aSearch['from_month'], $aSearch['from_year']);
            }
            if ($aSearch['to_day'] && $aSearch['to_month'] && $aSearch['to_year']) {
                $aConds[] = ' AND s.start_date <= ' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['to_day'],
                        $aSearch['to_month'], $aSearch['to_year'], 'end');
            }
            if (!empty($aSearch['name'])) {
                $aConds[] = " AND s.campaign_name LIKE '%$aSearch[name]%'";
            }
            if (!empty($aSearch['creator'])) {
                $aConds[] = " AND u.full_name LIKE '%$aSearch[creator]%'";
            }
            if (!empty($aSearch['status'])) {
                if (is_numeric($aSearch['status'])) {
                    $aConds[] = ' AND s.is_custom = ' . $aSearch['status'];
                } else {
                    $sApprovedCond = Phpfox::getService('ad.get')->getApprovedCond($aSearch['status'], 's', true);
                    $aConds[] = ' AND s.is_custom = 3 ' . ($sApprovedCond ? " AND $sApprovedCond" : '');
                }
            }
            if ($aSearch['active'] != -1) {
                $aConds[] = ' AND s.is_active = ' . $aSearch['active'];
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

        $aSort = explode(' ', $sSort);
        $aSort = reset($aSort);
        $this->search()->setSort($aSort);
        $this->template()->assign('sCurrentSort', $sSort);
        list($iCnt, $aSponsorships) = Phpfox::getService('ad.migrate')->getSponsorships($aConds, $sSort, $iPage,
            $iLimit);

        Phpfox_Pager::instance()->set([
            'page' => $iPage,
            'size' => $iLimit,
            'count' => $iCnt
        ]);

        return $aSponsorships;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_migrate_sponsorships__clean')) ? eval($sPlugin) : false);
    }
}
