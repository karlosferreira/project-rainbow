<?php
if(!empty($iId))
{
    db()->insert(Phpfox::getT('activitypoint_statistics'),['user_id' => $iId]);
}