<?php

if (Phpfox::isAppActive('P_StatusBg') && !empty($values['parent_user_id'])) {
    if (!empty($id) && !empty($background)) {
        $statusId = $this->database()->select('item_id')->from(':feed')->where('feed_id = ' . (int)$id)->execute('getField');
        \Phpfox::getService('pstatusbg.process')->addBackgroundForStatus('feed_comment', $statusId, $background, $this->getUser()->getId(), 'feed');
    }
}
