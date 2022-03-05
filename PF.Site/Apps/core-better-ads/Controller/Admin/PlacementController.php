<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class PlacementController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class PlacementController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        // delete placement
        if (($aDelete = $this->request()->get('val')) && Phpfox::getService('ad.process')->deletePlacement($aDelete)) {
            $this->url()->send('admincp.ad.placements', null, _p('better_ads_ad_placement_successfully_deleted'));
        }

        $this->template()->setTitle(_p('manage_placements'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(_p('manage_placements'), $this->url()->makeUrl('admincp.ad.placements'))
            ->assign(array(
                    'aPlacements' => Phpfox::getService('ad.get')->getPlacements()
                )
            );
        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_placement_index_clean')) ? eval($sPlugin) : false);
    }
}
