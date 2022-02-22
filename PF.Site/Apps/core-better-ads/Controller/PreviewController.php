<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class PreviewController
 * @package Apps\Core_BetterAds\Controller
 */
class PreviewController extends Phpfox_Component
{
    public function process()
    {
        $aVals = $this->request()->getArray('val');
        define('PHPFOX_IS_AD_PREVIEW', true);

        if (empty($aVals['location'])) {
            $iLocation = $this->request()->get('location');
        } else {
            $iLocation = $aVals['location'];
        }
        $aPlacement = Phpfox::getService('ad.get')->getPlacement($iLocation);

        $this->setParam([
            'betterads_preview_block' => $aPlacement['block_id'],
            'betterads_preview_type_id' => $aVals['type_id'],
            'betterads_preview_image' => empty($aVals['temp_file']) ? '' : $aVals['temp_file'],
            'betterads_preview_image_tooltip_text' => $aVals['image_tooltip_text'],
            'betterads_preview_url_link' => $aVals['url_link'],
            'betterads_preview_title' => $aVals['title'],
            'betterads_preview_body' => $aVals['body'],
            'betterads_preview_ad_id' => $this->request()->getInt('ad_id'),
        ]);

        return 'controller';
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_preview_clean')) ? eval($sPlugin) : false);
    }
}
