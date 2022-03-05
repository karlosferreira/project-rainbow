<?php

use Apps\Core_MobileApi\Adapter\PushNotification\PushNotificationInterface;

if ($aInsert) {
    Phpfox::getService(PushNotificationInterface::class)->addToQueue(($iSenderUserId === null ? Phpfox::getUserId() : $iSenderUserId), $iOwnerUserId, [
        'notification_id'   => $iId,
        'notification_type' => $sType
    ]);
}