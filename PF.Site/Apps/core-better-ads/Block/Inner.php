<?php

namespace Apps\Core_BetterAds\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Inner
 * @package Apps\Core_BetterAds\Block
 */
class Inner extends Phpfox_Component
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_inner_process__start')) ? eval($sPlugin) : false);

        if ($this->getParam('sClass', '') == '') {
            return false;
        }

        if (!Phpfox::getParam('ad.better_enable_ads', true)) {
            return false;
        }

        $aAd = Phpfox::getService('ad.get')->getForLocation($this->getParam('sClass'));

        if (!is_array($aAd)) {
            return false;
        }

        if (empty($aAd)) {
            return false;
        }

        $this->template()->assign(array(
                'aAd' => $aAd
            )
        );

        (($sPlugin = Phpfox_Plugin::get('ad.component_block_inner_process__end')) ? eval($sPlugin) : false);

        return 'block';
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_inner_clean')) ? eval($sPlugin) : false);
    }
}
