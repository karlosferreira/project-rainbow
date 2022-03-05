<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class SampleController
 * @package Apps\Core_BetterAds\Controller
 */
class SampleController extends Phpfox_Component
{
    public function process()
    {
        define('PHPFOX_IS_ADS_SAMPLE', true);

        if ($this->request()->get('no-click') == '1') {
            define('PHPFOX_NO_WINDOW_CLICK', true);
        }

        $this->template()->testStyle();
        $this->template()->bIsSample = true;

        if ($this->request()->get('click')) {
            $this->template()->setHeader('<style type="text/css">.sample { cursor:pointer; }</style>');
        }
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_sample_clean')) ? eval($sPlugin) : false);
    }
}
