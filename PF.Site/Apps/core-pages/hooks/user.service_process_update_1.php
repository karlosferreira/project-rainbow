<?php
if(Phpfox::isAppActive('Core_Pages') && !empty($aInsert['full_name']) && !empty($aInsert['prev_full_name']) && ($aInsert['full_name'] !== $aInsert['prev_full_name']))
{
    $admins = db()->select('p.page_id AS page_id_owner, pa.page_id AS page_id_admin')
        ->from(Phpfox::getT('user'), 'u')
        ->leftJoin(Phpfox::getT('pages'), 'p', 'p.user_id = u.user_id')
        ->leftJoin(Phpfox::getT('pages_admin'), 'pa', 'pa.user_id = u.user_id')
        ->where('u.user_id = '. (int)$iUserId)
        ->execute('getSlaveRows');
    foreach ($admins as $admin)
    {
        $targetId = !empty($admin['page_id_owner']) ? $admin['page_id_owner'] : (!empty($admin['page_id_admin']) ? $admin['page_id_admin'] : 0);
        if(!empty($targetId))
        {
            $this->cache()->remove('pages_' . $targetId . '_admins');
        }
    }
}