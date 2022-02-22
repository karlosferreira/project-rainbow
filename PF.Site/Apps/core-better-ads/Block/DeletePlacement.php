<?php

namespace Apps\Core_BetterAds\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class DeletePlacement
 * @package Apps\Core_BetterAds\Block
 */
class DeletePlacement extends Phpfox_Component
{
    /**
     * Class process method which is used to execute this component.
     */
    public function process()
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);
        $iPlacementId = $this->request()->getInt('placement_id');
        list($iAdsCount,) = Phpfox::getService('ad.get')->get(['bads.location' => $iPlacementId]);
        $aAllPlacements = Phpfox::getService('ad.get')->getPlacements();

        $this->template()->assign(compact('iPlacementId', 'iAdsCount', 'aAllPlacements'));
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_deleteplacement_clean')) ? eval($sPlugin) : false);
    }
}
