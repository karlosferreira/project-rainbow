<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class SponsorSettingController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class SponsorSettingController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_sponsor_setting_process__start')) ? eval($sPlugin) : false);

        if ($this->request()->getArray('val')) {
            $this->_updateUserGroupSettings();

            return 'controller';
        }

        $iGroupId = $this->request()->getInt('group_id', 2);
        $sModuleId = $this->request()->get('module_id');
        $aSettings = Phpfox::getService('ad.sponsor')->getAllSponsorSettings($iGroupId, $sModuleId);
        $oPhraseLib = \Core\Lib::phrase();
        foreach ($aSettings as $key => $aSetting) {
            foreach ($aSetting as $settingKey => $aItems) {
                foreach ($aItems as $itemKey => $aItem) {
                    $aParts = explode('</title><info>',
                        $oPhraseLib->isPhrase("user_setting_{$aItem['module_id']}_{$aItem['name']}")
                            ? _p("user_setting_{$aItem['module_id']}_{$aItem['name']}") : ($oPhraseLib->isPhrase("user_setting_{$aItem['name']}") ? _p("user_setting_{$aItem['name']}") :  $aItem['name']));

                    $aItem['setting_name'] = strip_tags($aParts[0]);

                    if (isset($aParts[1])) {
                        $aItem['setting_info'] = strip_tags($aParts[1]);
                    } else {
                        $aItem['setting_info'] = '';
                    }

                    $aItem['setting_name'] = str_replace("\n", "<br />",
                        $aItem['setting_name']);

                    $aSettings[$key][$settingKey][$itemKey] = $aItem;
                }
            }
        }

        // get all items that have sponsor
        $this->template()
            ->setHeader('<script>var betterAdsApps = ' . json_encode(Phpfox::getService('ad.sponsor')->getAllAppsHaveSponsor(true)) . ';</script>')
            ->setTitle(_p('sponsor_settings'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(_p('sponsor_settings'), $this->url()->makeUrl('admincp.ad.sponsor-setting'))
            ->assign([
                'aSettings' => $aSettings,
                'aUserGroups' => Phpfox::getService('user.group')->get(),
                'iGroupId' => $iGroupId,
                'sModuleId' => $sModuleId,
                'sModuleName' => Phpfox::getService('ad.sponsor')->getAppName($sModuleId),
                'aForms' => Phpfox::getService('user.group')->getGroup($iGroupId),
                'aFilterApps' => Phpfox::getService('ad.sponsor')->getAllAppsHaveSponsor()
            ]);
        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();

        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_sponsor_setting_process__end')) ? eval($sPlugin) : false);

        return 'controller';
    }

    private function _updateUserGroupSettings()
    {
        Phpfox::getService('user.group.setting.process')->update($this->request()->get('id', 2),
            $this->request()->getArray('val'));
        $this->url()->send('current', [], _p('user_group_updated'));
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_sponsor_setting__clean')) ? eval($sPlugin) : false);
    }
}
