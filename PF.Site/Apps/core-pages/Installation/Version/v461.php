<?php

namespace Apps\Core_Pages\Installation\Version;


use Phpfox;

class v461
{
    public function process()
    {
        $this->_updateUserGroupSettingVarname();
    }

    private function _updateUserGroupSettingVarname()
    {
        // check if setting was not overrided
        $bIsExist = db()->select('count(*)')->from(':user_group_setting')->where([
            'module_id' => 'pages',
            'name' => 'flood_control'
        ])->executeField();

        if (!$bIsExist) {
            return;
        }

        $iNewUserGroupSettingId = db()->select('setting_id')->from(':user_group_setting')->where([
            'module_id' => 'pages',
            'name' => 'flood_control'
        ])->executeField();
        $aUserGroupIds = db()->select('user_group_id')->from(':user_group')->executeRows();

        foreach (array_column($aUserGroupIds, 'user_group_id') as $iUserGroupId) {
            // get old setting value
            $iOldValue = Phpfox::getService('user.group.setting')->getGroupParam($iUserGroupId, 'pages.flood_control');

            if (!is_null($iOldValue)) {
                // update value
                $aConds = [
                    'user_group_id' => $iUserGroupId,
                    'setting_id' => $iNewUserGroupSettingId
                ];
                // check to update or insert
                $bValueExist = db()->select('count(*)')->from(':user_setting')->where($aConds)->executeField();

                if ($bValueExist) {
                    db()->update(':user_setting', ['value_actual' => $iOldValue], $aConds);
                } else {
                    db()->insert(':user_setting', array_merge($aConds, ['value_actual' => $iOldValue]));
                }
            }
        }

        // remove old setting
        Phpfox::getService('user.group.setting.process')->deleteOldSetting('flood_control', 'pages');
    }
}
