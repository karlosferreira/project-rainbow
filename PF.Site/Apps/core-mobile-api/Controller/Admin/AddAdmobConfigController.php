<?php

namespace Apps\Core_MobileApi\Controller\Admin;

use Phpfox;
use Phpfox_Component;

class AddAdmobConfigController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isAdmin(true);
        $oAdConfig = Phpfox::getService('mobile.ad-config');
        $bIsEdit = false;
        if ($iId = $this->request()->get('id')) {
            $aRow = $oAdConfig->getAdConfigs($iId, true);
            $bIsEdit = true;
            $this->template()->assign([
                'aForms' => $aRow,
            ]);
        }
        $aVals = $this->request()->getArray('val');
        if (!empty($aVals)) {
            if (!$bIsEdit) {
                if ($oAdConfig->addAdConfigs($aVals)) {
                    $this->url()->send('admincp.mobile.manage-ads-config', [], _p('ad_config_added_successfully'));
                }
            } else {
                $aVals['id'] = $iId;
                if ($oAdConfig->addAdConfigs($aVals, true)) {
                    $this->url()->send('admincp.mobile.manage-ads-config', [], _p('ad_config_updated_successfully'));
                }
            }
            $this->template()->assign([
                'aForms' => $aVals,
            ]);
        }
        $sTitle = $bIsEdit ? _p('edit_ad_config') : _p('add_new_ad_config');
        $this->template()
            ->setBreadCrumb(_p("Apps"), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p("Mobile Api"), $this->url()->makeUrl('admincp.app', ['id' => 'Core_MobileApi']))
            ->setBreadCrumb($sTitle, $this->url()->makeUrl('admincp.mobile.add-ad-config'))
            ->setTitle($sTitle)
            ->setHeader([
                'jscript/admin.js' => 'app_core-mobile-api',
            ])
            ->assign([
                'aUserGroups'       => Phpfox::getService('user.group')->get(),
                'aAdTypes'          => $oAdConfig->getAllAdType(),
                'aFrequencyCapping' => $oAdConfig->getAllFrequencyCapping(),
                'aLocation'         => $oAdConfig->getAllLocation(),
                'aMobilePages'      => $oAdConfig->getAllPageOnMobile(),
                'aAccess'           => (empty($aRow['disallow_access']) ? null : $aRow['disallow_access']),
                'bIsEdit'           => $bIsEdit
            ]);
    }
}