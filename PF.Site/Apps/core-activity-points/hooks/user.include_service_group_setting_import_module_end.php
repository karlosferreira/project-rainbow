<?php
$sSettingMainModule =  ($sModule === null ? $aRow['module_id'] : $sModule);
if(Phpfox::isAppActive('Core_Activity_Points') && preg_match('/points_/',$aRow['value']) && $sSettingMainModule !== 'activitypoint')
{
    $iCnt = db()->select('COUNT(*)')
        ->from(Phpfox::getT('activitypoint_setting'))
        ->where(['module_id' => $sSettingMainModule, 'var_name' => $aRow['value']])
        ->execute('getSlaveField');
    if(!$iCnt)
    {
        $aInsert = [
            'var_name' => $aRow['value'],
            'phrase_var_name' => 'user_setting_'.$aRow['value'],
            'module_id' => $sSettingMainModule
        ];
        db()->insert(Phpfox::getT('activitypoint_setting'), $aInsert);
        Phpfox::getLib('cache')->removeGroup('activitypoint_setting_actions');
    }

    $iTransactionCnt = db()->select('COUNT(*)')
        ->from(Phpfox::getT('activitypoint_transaction'))
        ->where('module_id = "'. $sSettingMainModule .'" AND is_hidden = 1')
        ->execute('getSlaveField');
    if($iTransactionCnt)
    {
        db()->update(Phpfox::getT('activitypoint_transaction'),['is_hidden' => 0], 'module_id = "'. $sSettingMainModule. '"');
    }
}