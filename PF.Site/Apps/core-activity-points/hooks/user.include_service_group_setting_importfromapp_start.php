<?php
if(isset($sVarName, $sAlias) && Phpfox::isAppActive('Core_Activity_Points') && preg_match('/points_/',$sVarName) && $sAlias !== 'activitypoint')
{

    $iCnt = db()->select('COUNT(*)')
        ->from(Phpfox::getT('activitypoint_setting'))
        ->where(['module_id' => $sAlias, 'var_name' => $sVarName])
        ->execute('getSlaveField');
    if(!$iCnt)
    {
        $aInsert = [
            'var_name' => $sVarName,
            'phrase_var_name' => 'user_setting_'.$sVarName,
            'module_id' => $sAlias
        ];
        db()->insert(Phpfox::getT('activitypoint_setting'), $aInsert);
        Phpfox::getLib('cache')->removeGroup('activitypoint_setting_actions');
    }

    $iTransactionCnt = db()->select('COUNT(*)')
                    ->from(Phpfox::getT('activitypoint_transaction'))
                    ->where('module_id = "'. $sAlias .'" AND is_hidden = 1')
                    ->execute('getSlaveField');
    if($iTransactionCnt)
    {
        db()->update(Phpfox::getT('activitypoint_transaction'),['is_hidden' => 0], 'module_id = "'. $sAlias . '"');
    }
}

