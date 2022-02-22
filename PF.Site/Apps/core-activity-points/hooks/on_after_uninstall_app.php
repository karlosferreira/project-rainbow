<?php

if(Phpfox::isAppActive('Core_Activity_Points'))
{
    db()->delete(Phpfox::getT('activitypoint_setting'),'module_id = "'. $this->alias .'"');
    db()->update(Phpfox::getT('activitypoint_transaction'),['is_hidden' => 1] ,'module_id = "' . $this->alias .'"');
}