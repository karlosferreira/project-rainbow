<?php
defined('PHPFOX') or exit('NO DICE!');

if ($aFeed['type_id'] == 'blog' && isset($aFeed['can_share_draft']) && $aFeed['can_share_draft'] == false) {
    if ($aActions['can_share']) {
        $aActions['can_share'] = false;
        $aActions['total_action']--;
    }
}

