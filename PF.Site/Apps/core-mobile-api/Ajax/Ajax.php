<?php

namespace Apps\Core_MobileApi\Ajax;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Ajax;

/**
 * Class Ajax
 * @package Apps\Core_MobileApi\Ajax
 */
class Ajax extends Phpfox_Ajax
{
    /**
     * AdminCP only. Active or deactivate a category
     */
    public function toggleActiveMenu()
    {
        $iMenuId = $this->get('id');
        $iActive = $this->get('active');
        Phpfox::getService('mobile.admincp.menu')->toggleActiveMenu($iMenuId, $iActive);
    }

    public function sampleLayout()
    {
        $sampleImage = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/sample_ads_layout.jpg';
        $info = _p('mobile_sample_ads_layout_info');
        echo $info . '<br/><hr/>';
        echo '<img src="' . $sampleImage . '" style="width: 100%"/>';
    }

    public function toggleActiveAdConfig()
    {
        $iConfigId = $this->get('id');
        $iActive = $this->get('active');
        $aConfig = Phpfox::getService('mobile.ad-config')->getAdConfigs($iConfigId);
        if (!$aConfig) {
            return false;
        }
        $aIds = [];
        if ((int)$iActive == 1) {
            $aDuplicate = Phpfox::getService('mobile.ad-config')->getAdConfigByScreen($aConfig['screens'], $aConfig['type'], $aConfig['id']);
            if (count($aDuplicate)) {
                $aIds = array_map(function ($config) {
                    return $config['id'];
                }, $aDuplicate);
            }
        }
        if (Phpfox::getService('mobile.ad-config')->toggleActiveMenu($iConfigId, $iActive, implode(',', $aIds))) {
            foreach ($aIds as $id) {
                $this->call("$('.js_ad_config_active_" . $id . "').css('display','none').addClass('hide');");
                $this->call("$('.js_ad_config_not_active_" . $id . "').css('display','inline-block').removeClass('hide');");
            }
        }
    }

    public function checkExistedConfig()
    {
        $id = $this->get('id');
        if (!$id) {
            echo json_encode([
                'error' => '404'
            ]);
            return;
        }
        $oAdConfig = Phpfox::getService('mobile.ad-config');
        $aAdConfig = $oAdConfig->getAdConfigs($id);
        if (empty($aAdConfig)) {
            echo json_encode([
                'error' => '404'
            ]);
            return;
        }
        if (!empty($aAdConfig['screens'])) {
            $aDuplicate = $oAdConfig->getAdConfigByScreen($aAdConfig['screens'], $aAdConfig['type'], $aAdConfig['id']);
            if (count($aDuplicate)) {
                echo json_encode([
                    'ids' => implode(',', array_map(function ($config) {
                        return $config['id'];
                    }, $aDuplicate))
                ]);
                return;
            }
        }
        echo json_encode([
            'ids' => ''
        ]);
        return;
    }
}
