<?php

namespace Apps\Core_Activity_Points\Installation\Version;

class v475
{
    public function process()
    {
        // remove user group settings
        $aDeleteUserGroupSettings = [
            [
                'module_id' => 'invite',
                'name' => 'points_invite_inviteereceiveuponrequest'
            ],
        ];

        foreach ($aDeleteUserGroupSettings as $aDeleteUserGroupSetting) {
            db()->delete(':user_group_setting', [
                'module_id' => $aDeleteUserGroupSetting['module_id'],
                'name' => $aDeleteUserGroupSetting['name']
            ]);
        }

        // remove activity point settings
        $aDeletePointSettings = [
            [
                'module_id' => 'invite',
                'var_name' => 'points_invite_inviteereceiveuponrequest'
            ],
        ];

        foreach ($aDeletePointSettings as $aDeletePointSetting) {
            db()->delete(':activitypoint_setting', [
                'module_id' => $aDeletePointSetting['module_id'],
                'var_name' => $aDeletePointSetting['var_name']
            ]);
        }

    }

}
