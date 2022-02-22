<?php

namespace Apps\Core_BetterAds\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class MigrateAd
 * @package Apps\Core_BetterAds\Block
 */
class MigrateAd extends Phpfox_Component
{
    /**
     * Class process method which is used to execute this component.
     */
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_migrate_ad_process__start')) ? eval($sPlugin) : false);

        $iId = $this->getParam('id');
        $aIds = $this->getParam('ids');
        $aPlacements = Phpfox::getService('ad.get')->getPlacements();
        $this->template()->assign(compact('iId', 'aIds', 'aPlacements'));

        (($sPlugin = Phpfox_Plugin::get('ad.component_block_migrate_ad_process__end')) ? eval($sPlugin) : false);

        return 'block';
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_migrate_ad_clean')) ? eval($sPlugin) : false);
    }
}
