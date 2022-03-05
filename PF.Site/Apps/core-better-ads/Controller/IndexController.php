<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class IndexController
 * @package Apps\Core_BetterAds\Controller
 */
class IndexController extends Phpfox_Component
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_process__start')) ? eval($sPlugin) : false);

        if (($iAd = $this->request()->getInt('id'))) {
            if (($sUrl = Phpfox::getService('ad.get')->getAdRedirect($iAd))) {
                $this->url()->forward($sUrl);
            }
        }

        return \Phpfox_Module::instance()->setController('ad.manage');
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}
