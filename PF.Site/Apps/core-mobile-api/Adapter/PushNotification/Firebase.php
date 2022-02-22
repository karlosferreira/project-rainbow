<?php

namespace Apps\Core_MobileApi\Adapter\PushNotification;

use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Service\FriendRequestApi;
use Apps\Core_MobileApi\Service\NotificationApi;
use Phpfox;

class Firebase implements PushNotificationInterface
{
    private $firebaseSetting;

    private static $HTTP_POST = 'POST';

    private static $HTTP_GET = 'GET';

    /**
     * @param $iUserId
     *
     * @return int
     */
    public function getUnseenTotal($iUserId)
    {
        $iCnt = Phpfox::getLib('database')->select('COUNT(*)')
            ->from(Phpfox::getT('notification'), 'n')
            ->where('n.user_id = ' . (int)$iUserId . ' AND n.is_seen = 0')
            ->execute('getSlaveField');

        return (int)$iCnt;
    }

    /**
     * @return array
     */
    private function getFirebaseSettings()
    {
        if (!$this->firebaseSetting) {
            $this->firebaseSetting = [
                'serverKey' => Phpfox::getParam('mobile.mobile_firebase_server_key'),
                'senderId'  => Phpfox::getParam('mobile.mobile_firebase_sender_id'),
            ];
        }

        return $this->firebaseSetting;
    }

    /**
     * @param $userId
     * @param $data
     *
     * @return bool|mixed
     */
    function pushNotification($userId, $data)
    {
        if (!is_array($data['tokens']) || !$data['tokens']) {
            return true;
        }

        $url = 'https://fcm.googleapis.com/fcm/send';
        $totalNotification = (int)(new NotificationApi())->getUnseenTotal($userId, true);
        $totalFriendRequest = (int)(new FriendRequestApi())->getUnseenTotal($userId);
        $fields = [
            'registration_ids' => $data['tokens'],
            'priority'         => 'high',
            'notification'     => [
                'title'        => isset($data['title']) ? Phpfox::getService(ParseInterface::class)->cleanOutput($data['title']) : '',
                'badge'        => (string)($totalNotification + $totalFriendRequest),
                'vibrate'      => true,
                'body'         => Phpfox::getService(ParseInterface::class)->cleanOutput($data['message']),
                'click_action' => '',
                'sound'        => 'default',
            ],
            'data'             => [
                'resource_link' => isset($data['resource_link']) ? $data['resource_link'] : '',
                'web_link'      => isset($data['web_link']) ? $data['web_link'] : ''
            ],
        ];

        $headers = [
            'Authorization: key=' . Phpfox::getParam('mobile.mobile_firebase_server_key'),
            'Content-Type: application/json',
        ];

        (($sPlugin = \Phpfox_Plugin::get('mobile.adapter_push_notification_start')) ? eval($sPlugin) : false);

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $curl_error = null;
        $result = curl_exec($ch);

        curl_close($ch);

        if (false === $result) {
            $curl_error = curl_error($ch);
        }

        $this->logInfo([
            'headers'    => $headers,
            'fields'     => $fields,
            'result'     => $result,
            'curl_error' => $curl_error,
        ]);

        $response = json_decode($result, true);

        (($sPlugin = \Phpfox_Plugin::get('mobile.adapter_push_notification_end')) ? eval($sPlugin) : false);

        if (isset($response['success']) && $response['success'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $error
     */
    private function logInfo($error)
    {
        if (defined('PHPFOX_DEBUG') && PHPFOX_DEBUG) {
            Phpfox::getLog('notification.log')->info($error);
        }
    }

    /**
     * @param $senderId
     * @param $receiverId
     * @param $data
     *
     * @return mixed|void
     */
    function addToQueue($senderId, $receiverId, $data)
    {
        Phpfox::getService('mobile.device')->addNotificationQueue($senderId, $receiverId, $data, 'firebase');
    }

    /**
     * @param $method
     * @param $url
     * @param $headers
     * @param $post
     *
     * @return mixed
     */
    private function jsonRequest($method, $url, $headers, $post)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method == static::$HTTP_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        }


        $curl_error = null;
        $result = curl_exec($ch);

        curl_close($ch);

        if (false === $result) {
            $curl_error = curl_error($ch);
        }

        $this->logInfo([
            'headers'    => $headers,
            'method'     => $method,
            'url'        => $url,
            'post'       => $post,
            'result'     => $result,
            'curl_error' => $curl_error,
        ]);

        return json_decode($result, true);
    }


    /**
     * Get notification groups.
     * https://firebase.google.com/docs/cloud-messaging/android/device-group
     *
     * @param string $userId
     * @param string $addToken
     * @param string $removeToken
     *
     * @return bool
     */
    function updateUserTokenDeviceGroup($userId, $addToken, $removeToken)
    {

        $firebaseSettings = $this->getFirebaseSettings();
        if (!$firebaseSettings['senderId'] || !$firebaseSettings['serverKey']) {
            return false;
        }

        $headers = [
            'Authorization: key=' . $firebaseSettings['serverKey'],
            'project_id: ' . $firebaseSettings['senderId'],
            'Content-Type: application/json',
        ];

        $notificationKeyName = "user-${userId}";

        $response = $this->jsonRequest(
            static::$HTTP_GET,
            "https://fcm.googleapis.com/fcm/notification?notification_key_name={$notificationKeyName}"
            , $headers, null);

        $notification_key = $response && array_key_exists('notification_key', $response) ?
            $response['notification_key'] : null;

        if ($notification_key && $removeToken) {
            // remove token
            $this->jsonRequest(
                static::$HTTP_POST,
                'https://fcm.googleapis.com/fcm/notification'
                , $headers, [
                'operation'             => 'remove',
                'notification_key_name' => $notificationKeyName,
                'notification_key'      => $notification_key,
                'registration_ids'      => [$removeToken],
            ]);
        } else if ($notification_key && $addToken) {
            // try to add
            $response = $this->jsonRequest(
                static::$HTTP_POST,
                'https://fcm.googleapis.com/fcm/notification'
                , $headers, [
                'operation'             => 'add',
                'notification_key'      => $notification_key,
                'notification_key_name' => $notificationKeyName,
                'registration_ids'      => [$addToken],
            ]);

            $this->logInfo($response);

            return $response && $response['notification_key'];
        } else if ($addToken) {
            // try to create
            $response = $this->jsonRequest(
                static::$HTTP_POST,
                'https://fcm.googleapis.com/fcm/notification'
                , $headers, [
                'operation'             => 'create',
                'notification_key_name' => $notificationKeyName,
                'registration_ids'      => [$addToken],
            ]);

            $this->logInfo($response);

            return $response && $response['notification_key'];
        }
        return true;
    }

    /**
     * @param      $userId
     * @param      $token
     * @param      $platform
     * @param null $deviceId
     *
     * @return mixed|void
     */
    function addToken($userId, $token, $platform, $deviceId = null)
    {
        Phpfox::getService('mobile.device')->addDeviceToken([
            'user_id'   => $userId,
            'token'     => $token,
            'platform'  => $platform,
            'device_id' => $deviceId,
            'source'    => 'firebase',
        ]);
    }

    /**
     * @param      $token
     * @param null $deviceId
     *
     * @return mixed|void
     */
    function removeToken($token, $deviceId = null)
    {
        Phpfox::getService('mobile.device')->removeDeviceToken($token, $deviceId, 'firebase');
    }
}
