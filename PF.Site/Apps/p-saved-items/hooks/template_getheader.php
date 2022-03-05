<?php
if (Phpfox::getLib('request')->getRequests() == [
        'req1' => 'admincp',
        'req2' => 'app',
        'id' => 'P_SavedItems'
    ]) {
    Phpfox::getLib('url')->send('admincp.user.group.add',
        ['setting' => 1, 'hide_app' => 1, 'module' => 'saveditems', 'group_id' => 2]);
}