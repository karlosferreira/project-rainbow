<?php

namespace Apps\Core_MobileApi\Controller\Admin;

use Phpfox;
use Phpfox_Component;

class ManageInformationController extends Phpfox_Component
{
    public function process()
    {

        list($sLogo, $bIsDefault) = Phpfox::getService('mobile.admincp.setting')->getAppLogo();

        if ($aVals = $this->request()->getArray('val')) {
            //Update logo
            if (!empty($aVals['app_banner'])) {
                $oValid = Phpfox::getLib('validator')->set(array(
                        'sFormName' => 'core_js_blog_form',
                        'aParams' => [
                            'banner_title' => [
                                'def' => 'string:required',
                                'title' => _p('app_title_is_required')
                            ],
                            'banner_author' => [
                                'def' => 'string:required',
                                'title' => _p('app_author_is_required')
                            ],
                            'banner_apple_store_id' => [
                                'def' => 'string:required',
                                'title' => _p('app_apple_store_id_is_required')
                            ],
                            'banner_google_store_id' => [
                                'def' => 'string:required',
                                'title' => _p('app_google_store_id_is_required')
                            ],
                        ]
                    )
                );
                if ($oValid->isValid($aVals) && Phpfox::getService('mobile.admincp.setting')->updateAppBannerInfo($aVals)) {
                    $this->url()->send('admincp.mobile.manage-information', _p('mobile_app_banner_updated_successfully'));
                }
            } elseif (Phpfox::getService('mobile.admincp.setting')->updateLogo()) {
                $this->url()->send('admincp.mobile.manage-information', _p('logo_updated_successfully'));
            }
        }
        if ($this->request()->get('delete')) {
            if (Phpfox::getService('mobile.admincp.setting')->deleteLogo()) {
                $this->url()->send('admincp.mobile.manage-information', _p('logo_removed_successfully'));
            }
        }
        $this->template()
            ->setBreadCrumb(_p("Apps"), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p("Mobile Api"), $this->url()->makeUrl('admincp.app', ['id' => 'Core_MobileApi']))
            ->setBreadCrumb(_p('manage_information'), $this->url()->makeUrl('admincp.mobile.manage-information'))
            ->setTitle(_p('manage_information'))
            ->setHeader([
                'admin.css' => 'app_core-mobile-api'
            ])
            ->assign([
                'aForms'     => Phpfox::getService('mobile.admincp.setting')->getAppBannerInfo(),
                'sLogo'      => $sLogo,
                'bIsDefault' => $bIsDefault
            ]);
    }
}