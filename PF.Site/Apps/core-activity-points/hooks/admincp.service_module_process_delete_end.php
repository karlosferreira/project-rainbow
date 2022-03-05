<?php

if(Phpfox::isAppActive('Core_Activity_Points') && isset($iId))
{
    db()->delete(Phpfox::getT('activitypoint_setting'),'module_id = "'. $iId .'"');
    db()->update(Phpfox::getT('activitypoint_transaction'),['is_hidden' => 1] ,'module_id = "' . $iId .'"');
}