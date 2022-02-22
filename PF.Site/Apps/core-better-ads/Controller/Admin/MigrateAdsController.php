<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Pager;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class MigrateAdsController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class MigrateAdsController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_migrate_ads_process__start')) ? eval($sPlugin) : false);

        // delete ad
        if ($this->request()->getInt('delete')) {
            $this->_deleteAd();

            return 'controller';
        }

        // mass delete ads
        if ($this->request()->getArray('val')) {
            $this->_massDeleteAds();

            return 'controller';
        }

        $this->template()->setTitle(_p('migrate_ads'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(_p('migrate_ads'), $this->url()->makeUrl('admincp.ad.migrate-ads'))
            ->setPhrase([
                'are_you_sure_you_want_to_delete_selected_ads_permanently',
            ])
            ->assign([
                'aAds' => $this->_getAds()
            ]);
        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();

        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_migrate_ads_process__end')) ? eval($sPlugin) : false);

        return 'controller';
    }

    private function _massDeleteAds()
    {
        $aVals = $this->request()->getArray('val');

        if (!empty($aVals['delete'])) {
            foreach ($aVals['id'] as $id) {
                Phpfox::getService('ad.migrate')->deleteAd($id);
            }
            $this->url()->send('admincp.ad.migrate-ads', null, _p('migrate_ads_successfully_deleted'));
        }
    }

    private function _deleteAd()
    {
        $iAdId = $this->request()->getInt('delete');
        Phpfox::getService('ad.migrate')->deleteAd($iAdId);
        $this->url()->send('admincp.ad.migrate-ads', null, _p('migrate_ad_successfully_deleted'));
    }

    private function _getAds()
    {
        $iPage = $this->request()->getInt('page');
        $iLimit = 10;
        $aSearch = $this->request()->getArray('search');
        $sSort = $this->request()->get('sort', 'a.start_date asc');
        $aConds = ['1'];

        if (!empty($aSearch)) {
            if ($aSearch['from_day'] && $aSearch['from_month'] && $aSearch['from_year']) {
                $aConds[] = ' AND a.start_date >= ' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['from_day'],
                        $aSearch['from_month'], $aSearch['from_year']);
            }
            if ($aSearch['to_day'] && $aSearch['to_month'] && $aSearch['to_year']) {
                $aConds[] = ' AND a.start_date <= ' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['to_day'],
                        $aSearch['to_month'], $aSearch['to_year'], 'end');
            }
            if (!empty($aSearch['name'])) {
                $aConds[] = " AND a.name LIKE '%$aSearch[name]%'";
            }
            if (!empty($aSearch['creator'])) {
                $aConds[] = " AND u.full_name LIKE '%$aSearch[creator]%'";
            }
            if (!empty($aSearch['status'])) {
                if (is_numeric($aSearch['status'])) {
                    $aConds[] = ' AND a.is_custom = ' . $aSearch['status'];
                } else {
                    $aConds[] = ' AND a.is_custom=3 AND ' . Phpfox::getService('ad.get')->getApprovedCond($aSearch['status'], 'a');
                }
            }
            if ($aSearch['active'] != -1) {
                $aConds[] = ' AND a.is_active = ' . $aSearch['active'];
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

        $this->search()->setSort(reset(explode(' ', $sSort)));
        $this->template()->assign('sCurrentSort', $sSort);
        list($iCnt, $aAds) = Phpfox::getService('ad.migrate')->getAds($aConds, $sSort, $iPage, $iLimit);

        Phpfox_Pager::instance()->set([
            'page' => $iPage,
            'size' => $iLimit,
            'count' => $iCnt
        ]);

        return $aAds;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_migrate_ads__clean')) ? eval($sPlugin) : false);
    }
}
