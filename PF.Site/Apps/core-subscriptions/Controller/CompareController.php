<?php
namespace Apps\Core_Subscriptions\Controller;

use Phpfox;
use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

class CompareController extends Phpfox_Component
{
    public function process()
    {
        $aForCompare = Phpfox::getService('subscribe')->getPackagesForCompare();
        $aCurrentUsingPackage = Phpfox::getService('subscribe')->getCurrentUsingPackageForCompare(Phpfox::getUserId());

        $iCurrentUserGroupId = Phpfox::getUserBy('user_group_id');
        $aPackagePermissionIds = [];
        $iPackageCompareCount = 0;
        if(!empty($iCurrentUserGroupId))
        {
            $iPackageCompareCount = 0;
            foreach($aForCompare['packages'] as $aPackage)
            {
                $iPackageCompareCount++;
                if($aPackage['membership_permission'])
                {
                    $aPackagePermissionIds[] = $aPackage['package_id'];
                }
            }
        }
        if(!empty($aCurrentUsingPackage) && !empty($aPackagePermissionIds))
        {
            if(!empty($aForCompare['packages'][$aCurrentUsingPackage['package_id']]))
            {
                $aForCompare['packages'][$aCurrentUsingPackage['package_id']]['membership_permission'] = $aCurrentUsingPackage['membership_permission'];
                $aForCompare['packages'][$aCurrentUsingPackage['package_id']]['purchased_by_current_user'] = $aCurrentUsingPackage['purchased_by_current_user'];
            }
            else
            {
                $aForCompare['packages'][$aCurrentUsingPackage['package_id']] = $aCurrentUsingPackage;
                foreach($aForCompare['features'] as $iKey => $aFeature)
                {
                    if(empty($aFeature['data'][$aCurrentUsingPackage['package_id']]))
                    {
                        $aForCompare['features'][$iKey]['data'][$aCurrentUsingPackage['package_id']] = [
                            'option' => 2,
                            'text' => ''
                        ];
                    }
                }
            }
            $aPackagePermissionIds[] = $aCurrentUsingPackage['package_id'];

        }

        $this->template()->buildSectionMenu('subscribe', Phpfox::getService('subscribe')->getSectionMenu());

        $this->template()->setTitle(_p('plans_comparision'))
            ->setBreadCrumb(_p('plans_comparision'),$this->url()->makeUrl('subscribe.compare'))
            ->assign([
            'aComparePackages' => $aForCompare,
            'iPackageCompareCount' => $iPackageCompareCount,
            'sCheckImage' => Phpfox::getParam('core.path_actual').'PF.Base/theme/adminpanel/default/style/default/image/misc/accept.png',
            'sUncheckImage' => Phpfox::getParam('core.path_actual').'PF.Base/theme/adminpanel/default/style/default/image/misc/cross.png',
            'aPackagePermissionIds' => $aPackagePermissionIds
        ]);
    }
}