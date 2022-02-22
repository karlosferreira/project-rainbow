<?php

if (Phpfox::isAppActive('P_StatusBg')) {
    $typeId = $feed['type_id'];
    $itemId = $feed['item_id'];

    $statusBg = \Phpfox::getService('pstatusbg')->getFeedStatusBackground($feed['item_id'], $feed['type_id'], $feed['user_id'], true);
    if (!empty($statusBg)) {
        $item['item']['status_background_id'] = (int)$statusBg['background_id'];
    }
}
