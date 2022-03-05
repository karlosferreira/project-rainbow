<?php

namespace Apps\Core_Activity_Points\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class IndexController
 * @package Apps\Core_Activity_Points\Controller\Admin
 */
class IndexController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::getUserParam('admincp.has_admin_access', true);
        $iGroupId = !empty($this->request()->getInt('group_id')) ? $this->request()->getInt('group_id') : 2;
        $aSettingTitle = Phpfox::getService('activitypoint')->getSettingActions();
        $aSettingTitle = array_combine(array_column($aSettingTitle, 'var_name'), array_column($aSettingTitle, 'text'));
        if ($aVals = $this->request()->get('val')) {
            foreach ($aVals as $sModule => $aModule) {
                foreach ($aModule['settings'] as $sVarName => $aSetting) {
                    if ((!is_numeric($aSetting['value'])) || (is_numeric($aSetting['value']) && (int)$aSetting['value'] < 0)) {
                        Phpfox_Error::set(_p('activitypoint_validate_setting_message', [
                            'field' => _p('activitypoint_admincp_index_earned'),
                            'title' => $aSettingTitle[$sVarName]
                        ]));
                    }
                    if ((!is_numeric($aSetting['max_earned'])) || (is_numeric($aSetting['max_earned']) && (int)$aSetting['max_earned'] < 0)) {
                        Phpfox_Error::set(_p('activitypoint_validate_setting_message', [
                            'field' => _p('activitypoint_admincp_index_max_earned'),
                            'title' => $aSettingTitle[$sVarName]
                        ]));
                    }
                    if ((!is_numeric($aSetting['period'])) || (is_numeric($aSetting['period']) && (int)$aSetting['period'] < 0)) {
                        Phpfox_Error::set(_p('activitypoint_validate_setting_message', [
                            'field' => _p('activitypoint_admincp_index_period'),
                            'title' => $aSettingTitle[$sVarName]
                        ]));
                    }
                    if (is_numeric($aSetting['value']) && is_numeric($aSetting['max_earned']) && ((int)$aSetting['max_earned'] > 0) && ((int)$aSetting['value'] > (int)$aSetting['max_earned'])) {
                        Phpfox_Error::set(_p('activitypoint_validate_comparision', [
                            'field_one' => _p('activitypoint_admincp_index_earned'),
                            'field_two' => _p('activitypoint_admincp_index_max_earned'),
                            'title' => $aSettingTitle[$sVarName]
                        ]));
                    }
                }
            }
            if (Phpfox_Error::isPassed()) {
                if (Phpfox::getService('activitypoint.process')->updatePointSettings($aVals, $iGroupId)) {
                    $this->url()->send('admincp.activitypoint', ['group_id' => $iGroupId], _p('activitypoint_update_point_setting_successfully'));
                }
            }
        }
        $aUserGroups = Phpfox::getService('user.group')->getAll();
        $aModules = Phpfox::getService('activitypoint')->getPointSettings($iGroupId, true);
        if (!empty($aModules)) {
            $iFlag = 0;
            foreach ($aModules as $sKey => $aModule) {
                if ($iFlag == 1) {
                    continue;
                }
                $aModules[$sKey]['active'] = 1;
                $iFlag++;
            }
        }
        $this->template()->setTitle(_p('activitypoint_point_settings_title'))
            ->setBreadCrumb(_p('activitypoint_point_settings_title'))
            ->assign([
                'aUserGroups' => $aUserGroups,
                'aModules' => $aModules,
                'iGroupId' => $iGroupId,
                'aForms' => ['group_id' => $iGroupId]
            ]);
    }
}