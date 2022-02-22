<?php

if ($sTemplate == 'notification.controller.panel') {
    array_walk($this->_aVars['aNotifications'], function (&$aNotifcation) {
        if (in_array($aNotifcation['type_id'], ['feed_mini_like'])) {
            $aNotifcation['message'] = comment_parse_emojis($aNotifcation['message']);
        }
    });

} else if ($sTemplate == 'notification.controller.index') {
    array_walk($this->_aVars['aNotifications'], function (&$aNotifcationOfDay) {
        array_walk($aNotifcationOfDay, function (&$aNotifcation) {
            if (in_array($aNotifcation['type_id'], ['feed_mini_like'])) {
                $aNotifcation['message'] = comment_parse_emojis($aNotifcation['message']);
            }
        });
    });
}
