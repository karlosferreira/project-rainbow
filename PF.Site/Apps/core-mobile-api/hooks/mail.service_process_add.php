<?php

use Apps\Core_MobileApi\Adapter\PushNotification\PushNotificationInterface;

//Implement push notification
if (!$bIsThreadReply && Phpfox::getParam('mail.threaded_mail_conversation')) {
    $iUserId = $aVals['to'];
} else {
    foreach ($aThreadUsers as $aThreadUser) {
        if ($aThreadUser['user_id'] == Phpfox::getUserId()) {
            continue;
        }
        $iUserId = $aThreadUser['user_id'];
    }
}


Phpfox::getService(PushNotificationInterface::class)->addToQueue(Phpfox::getUserId(), $iUserId, [
    'message'           => $aVals['message'],
    'notification_type' => 'mail',
    'notification_id'   => isset($iTextId) ? $iTextId : 0
]);