<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class AddPlacementController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class AddPlacementController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        $bIsEdit = false;
        $aPlacement = [];
        $aCount = [];

        if (($iId = $this->request()->getInt('ads_id')) && ($aPlacement = Phpfox::getService('ad.get')->getPlacement($iId))) {
            $bIsEdit = true;
            $this->setParam('currency_value_val[cost]', unserialize($aPlacement['cost']));
        }

        if (($aVals = $this->request()->getArray('val'))) {
            if ($bIsEdit) {
                if (Phpfox::getService('ad.process')->updatePlacement($aPlacement['plan_id'], $aVals)) {
                    $this->url()->send('admincp.ad.placements', ['ads_id' => $aPlacement['plan_id']],
                        _p('better_ads_ad_placement_successfully_updated'));
                }
            } else {
                if (Phpfox::getService('ad.process')->addPlacement($aVals)) {
                    $this->url()->send('admincp.ad.placements', null,
                        _p('better_ads_ad_placement_successfully_added'));
                }
            }
        }

        for ($i = 1; $i <= 12; $i++) {
            $aCount[$i] = $i;
        }

        if ($bIsEdit) {
            $aCount[$aPlacement['block_id']] = $aPlacement['block_id'];
        }

        $this->template()->setTitle($bIsEdit ? _p('better_ads_edit_ad_placement') : _p('better_ads_add_ad_placement'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(($bIsEdit ? _p('better_ads_edit_ad_placement') : _p('add_placement')), null, true)
            ->assign([
                'bIsEdit'      => $bIsEdit,
                'aForms'       => $aPlacement,
                'aPlanBlocks'  => $aCount,
                'aControllers' => Phpfox::getService('admincp.component')->get(true),
                'aUserGroups'  => Phpfox::getService('user.group')->get()
            ]);
        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_placement_add_clean')) ? eval($sPlugin) : false);
    }
}
