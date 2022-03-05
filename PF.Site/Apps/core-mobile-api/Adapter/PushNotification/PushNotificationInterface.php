<?php

namespace Apps\Core_MobileApi\Adapter\PushNotification;


interface PushNotificationInterface
{
    /**
     * @param $userId
     * @param $data
     *
     * @return mixed
     */
    function pushNotification($userId, $data);

    /**
     * @param $senderId
     * @param $receiverId
     * @param $data
     *
     * @return mixed
     */
    function addToQueue($senderId, $receiverId, $data);

    /**
     * @param $userId
     * @param $token
     * @param $platform
     * @param $deviceId
     *
     * @return mixed
     */
    function addToken($userId, $token, $platform, $deviceId = null);

    /**
     * @param      $token
     * @param null $deviceId
     *
     * @return mixed
     */
    function removeToken($token, $deviceId = null);

    /**
     * @param $userId
     * @param $addToken
     * @param $removeToken
     *
     * @return mixed
     */
    function updateUserTokenDeviceGroup($userId, $addToken, $removeToken);
}