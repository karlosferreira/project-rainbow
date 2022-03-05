<?php

use Apps\Core_MobileApi\Adapter\PushNotification\PushNotificationInterface;

if (!empty($iFriendId)) {
    Phpfox::getService(PushNotificationInterface::class)->addToQueue(Phpfox::getUserId(), $iFriendId, [
        'notification_type' => 'friend',
        'notification_id'   => $iId
    ]);
}