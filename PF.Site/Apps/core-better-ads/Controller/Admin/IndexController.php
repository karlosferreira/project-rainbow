<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Pager;
use Phpfox_Plugin;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class IndexController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class IndexController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_process__start')) ? eval($sPlugin) : false);

        $this->_processAds();
        $iLimit = 10;
        $iPage = $this->request()->getInt('page', 1);
        $aSearch = $this->request()->getArray('search');
        $sSort = $this->request()->get('sort');
        $iLocation = $this->request()->get('location');
        $aConditions = $this->_getSearchConditions($aSearch);
        if (!empty($iLocation)) {
            $aConditions = array_merge($aConditions, [
                'bads.location' => $iLocation
            ]);
        }

        if (!empty($sSort)) {
            $this->search()->setSort(reset(explode(' ', $sSort)));
        }

        list($iCnt, $aAds) = Phpfox::getService('ad.get')->get($aConditions,
            empty($sSort) ? 'bads.ads_id DESC' : 'bads.' . $sSort, $iPage, $iLimit);

        Phpfox_Pager::instance()->set(array(
            'page' => $iPage,
            'size' => $iLimit,
            'count' => $iCnt
        ));

        $this->template()
            ->setTitle(_p('manage_ads'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(_p('manage_ads'))
            ->setPhrase([
                'are_you_sure_you_want_to_deny_selected_ads',
                'are_you_sure_you_want_to_delete_selected_ads_permanently',
                'are_you_sure_you_want_to_approve_selected_ads'
            ])
            ->assign([
                'aAds' => $aAds,
                'iPendingCount' => (int)Phpfox::getService('ad.get')->getPendingCount(),
                'sPendingLink' => Phpfox_Url::instance()->makeUrl('admincp.ad', array('search[status]' => '2')),
                'bIsSearch' => !empty($aSearch),
                'bCanApprovalAds' => user('better_can_approval_ad_campaigns'),
                'sCurrentSort' => empty($sSort) ? '' : $sSort,
                'aForms' => $aSearch ? $aSearch : [
                    'from_day' => date('j'),
                    'from_month' => date('n'),
                    'from_year' => date('Y'),
                    'to_day' => date('j'),
                    'to_month' => date('n'),
                    'to_year' => date('Y'),
                ]
            ]);

        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();

        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_process__end')) ? eval($sPlugin) : false);
    }

    private function _processAds()
    {
        $aVals = $this->request()->getArray('val');
        $ids = [];

        if (!empty($aVals)) {
            $ids = array_filter($aVals['id']);
        }

        if (($iId = $this->request()->getInt('approve')) || (!empty($aVals) && isset($aVals['approve']))) {
            if (!empty($iId)) {
                $ids[] = $iId;
            }

            foreach ($ids as $id) {
                Phpfox::getService('ad.process')->approve($id);
            }

            $this->url()->send('admincp.ad', null, _p('better_ads_ad_successfully_approved'));
        }

        if (($iId = $this->request()->getInt('deny')) || (!empty($aVals) && isset($aVals['deny']))) {
            if (!empty($iId)) {
                $ids[] = $iId;
            }

            foreach ($ids as $id) {
                Phpfox::getService('ad.process')->deny($id);
            }

            $this->url()->send('admincp.ad', null, _p('better_ads_ad_successfully_denied'));
        }

        if (($iId = $this->request()->getInt('delete')) || (!empty($aVals) && isset($aVals['delete']))) {
            if (!empty($iId)) {
                $ids[] = $iId;
            }

            foreach ($ids as $id) {
                Phpfox::getService('ad.process')->delete($id);
            }

            $this->url()->send('admincp.ad', null, _p('better_ads_ad_successfully_deleted'));
        }
    }

    private function _getSearchConditions($aParams)
    {
        if (empty($aParams)) {
            return [];
        }

        $aConditions = [];

        if (!empty($aParams['name'])) {
            $aConditions['bads.name'] = ['like' => '%' . $aParams['name'] . '%'];
        }

        if (!empty($aParams['creator'])) {
            $aConditions['u.full_name'] = ['like' => '%' . $aParams['creator'] . '%'];
        }
        if (!empty($aParams['status'])) {
            if (is_numeric($aParams['status'])){
                $aConditions['bads.is_custom'] = $aParams['status'];
            } else {
                $aConditions[] = (count($aConditions) ? ' AND ' : '') . 'bads.is_custom=3 AND ' . Phpfox::getService('ad.get')->getApprovedCond($aParams['status'], 'bads');
            }
        }

        if (isset($aParams['active']) && $aParams['active'] != -1) {
            $aConditions['bads.is_active'] = $aParams['active'];
        }

        if ($aParams['from_day'] && $aParams['from_month'] && $aParams['from_year']) {
            $aConditions[] = ' AND bads.start_date>=' . Phpfox::getService('ad.process')->convertToTimestamp($aParams['from_day'],
                    $aParams['from_month'], $aParams['from_year']);
        }

        if ($aParams['to_day'] && $aParams['to_month'] && $aParams['to_year']) {
            $aConditions[] = ' AND bads.start_date<=' . Phpfox::getService('ad.process')->convertToTimestamp($aParams['to_day'],
                    $aParams['to_month'], $aParams['to_year'], 'end');
        }

        return $aConditions;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_index_clean')) ? eval($sPlugin) : false);
    }
}
