<?php

namespace Apps\Core_MobileApi\Controller\Admin;

use Phpfox;
use Phpfox_Component;

class ManageAdmobConfigController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isAdmin(true);
        $oAdConfig = Phpfox::getService('mobile.ad-config');
        if ($iDelete = $this->request()->getInt('delete')) {
            if ($oAdConfig->deleteAdConfig($iDelete)) {
                $this->url()->send('admincp.mobile.manage-ads-config', null, _p('deleted_ad_config_successfully'));
            }
        }
        $iPage = $this->request()->get('page', 1);
        $iPageSize = 15;
        $aAdsConfig = $oAdConfig->getAdsConfigs([], $iPage, $iPageSize, $iCnt);
        $this->template()
            ->setBreadCrumb(_p("Apps"), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p("Mobile Api"), $this->url()->makeUrl('admincp.app', ['id' => 'Core_MobileApi']))
            ->setBreadCrumb(_p('manage_ads_config'), $this->url()->makeUrl('admincp.mobile.manage-ads-config'))
            ->setTitle(_p('manage_ads_config'))
            ->setHeader([
                'jscript/admin.js' => 'app_core-mobile-api',
            ])
            ->setPhrase([
                'mobile_enable_ad_config_warning'
            ])
            ->assign([
                'aAdsConfig'        => $aAdsConfig,
                'aAdTypes'          => $oAdConfig->getAllAdType(),
                'aFrequencyCapping' => $oAdConfig->getAllFrequencyCapping(),
            ]);
        \Phpfox_Pager::instance()->set([
            'page'  => $iPage,
            'size'  => $iPageSize,
            'count' => $iCnt
        ]);
    }
}