<?php
if (Phpfox::getService('pages')->isLoginAsPage() && $aActions['can_share']) {
    $aActions['can_share'] = false;
    $aActions['total_action']--;
}
