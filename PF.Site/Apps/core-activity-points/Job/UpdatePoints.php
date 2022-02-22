<?php

namespace Apps\Core_Activity_Points\Job;

use Core\Queue\JobAbstract;
use Phpfox;
use Phpfox_Plugin;

class UpdatePoints extends JobAbstract
{
    public function perform()
    {
        $aParams = $this->getParams();
        $iUserId = (int)$aParams['iUserId'];
        $iCnt = (int)$aParams['iCnt'];
        $sSettingName = $aParams['sSettingName'];
        $sModule = $aParams['sModule'];
        $sMethod = $aParams['sMethod'];
        $iTimestamp = (int)$aParams['iTimestamp'];
        $iPoints = (int)$aParams['iPoints'];
        $iUserGroupId = (int)$aParams['iUserGroupId'];

        (($sPlugin = Phpfox_Plugin::get('activitypoint.job_update_points_perform_start')) ? eval($sPlugin) : false);

        if (Phpfox::isAppActive('Core_Activity_Points')) {
            list($iMaxEarned, $iPeriod, $iIsActive, $sSettingName) = Phpfox::getService('activitypoint')->getPointSettingValue($sModule, $sSettingName, $iUserGroupId);

            if ((int)$iIsActive == 0 || empty($iIsActive)) {
                $this->delete();
                return false;
            }

            if ($iCnt) {
                $iPoints = ($iPoints * $iCnt);
            }

            if ((int)$iMaxEarned > 0 && $sMethod == '+') {
                if ((int)$iPeriod > 0) {
                    $iTotalPoints = db()->select('SUM(points) AS total_points')
                        ->from(Phpfox::getT('activitypoint_transaction'))
                        ->where('type = "Earned" AND time_stamp >= ' . ((int)$iTimestamp - ((int)$iPeriod * 86400)) . ' AND action = "' . $sSettingName . '" AND module_id = "' . $sModule . '" AND user_id = ' . (int)$iUserId)
                        ->execute('getSlaveField');
                    if (((int)$iTotalPoints >= (int)$iMaxEarned) || (((int)$iTotalPoints + (int)$iPoints) > (int)$iMaxEarned)) {
                        $this->delete();
                        return false;
                    }
                } elseif ((int)$iPeriod == 0) {
                    $iTotalPoints = db()->select('SUM(points) AS total_points')
                        ->from(Phpfox::getT('activitypoint_transaction'))
                        ->where('type = "Earned" AND action = "' . $sSettingName . '" AND module_id = "' . $sModule . '" AND user_id = ' . (int)$iUserId)
                        ->execute('getSlaveField');
                    if (((int)$iTotalPoints >= (int)$iMaxEarned) || (((int)$iTotalPoints + (int)$iPoints) > (int)$iMaxEarned)) {
                        $this->delete();
                        return false;
                    }
                }
            }

            if ($sMethod == '+') {
                db()->update(Phpfox::getT('user_activity'), ['activity_points' => ['= activity_points +', $iPoints]], 'user_id = ' . $iUserId);
                db()->update(Phpfox::getT('activitypoint_statistics'), ['total_earned' => ['= total_earned +', $iPoints]], 'user_id = ' . $iUserId);
            } else {
                db()->query('UPDATE ' . Phpfox::getT('user_activity') . ' SET activity_points = IF(activity_points <= ' . $iPoints . ', 0, activity_points - ' . $iPoints . ') WHERE user_id = ' . $iUserId);
                db()->update(Phpfox::getT('activitypoint_statistics'), ['total_retrieved' => ['= total_retrieved +', $iPoints]], 'user_id = ' . $iUserId);
            }

            db()->insert(Phpfox::getT('activitypoint_transaction'), [
                'user_id' => (int)$iUserId,
                'module_id' => $sModule,
                'type' => ($sMethod == '+') ? 'Earned' : 'Retrieved',
                'action' => ($sMethod == '+') ? $sSettingName : 'activitypoint_subtract_points_when_delete_item',
                'points' => $iPoints,
                'time_stamp' => $iTimestamp
            ]);
        }
        $this->delete();

        (($sPlugin = Phpfox_Plugin::get('activitypoint.job_update_points_perform_end')) ? eval($sPlugin) : false);
    }
}
