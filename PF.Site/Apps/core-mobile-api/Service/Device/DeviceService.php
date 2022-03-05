<?php

namespace Apps\Core_MobileApi\Service\Device;

defined('PHPFOX') or exit('NO DICE!');

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\PushNotification\PushNotificationInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Resource\NotificationResource;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\Core_MobileApi\Version1_6\Service\NotificationApi;
use Phpfox;
use Phpfox_Service;

class DeviceService extends Phpfox_Service
{

    const TOKEN_SOURCE = 'firebase';

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mobile_api_device_token');
    }

    /**
     * @return LocalizationInterface|object
     */
    protected function getLocalization()
    {
        return Phpfox::getService(LocalizationInterface::class);
    }

    /**
     * @param $aData
     *
     * @return bool|int
     */
    public function addDeviceToken($aData)
    {
        if (empty($aData['user_id']) || empty($aData['token'])) {
            return false;
        }
        //De-active all tokens on this device
        $this->removeDeviceToken(null, $aData['device_id'], null, isset($aData['source']) ? $aData['source'] : self::TOKEN_SOURCE, true);
        $aToken = db()->select('*')
            ->from($this->_sTable)
            ->where([
                'token'        => $aData['token'],
                'token_source' => isset($aData['source']) ? $aData['source'] : self::TOKEN_SOURCE,
                'device_id'    => $aData['device_id']
            ])
            ->execute('getRow');
        if (!empty($aToken)) {
            $id = (int)$aToken['id'];
            db()->update($this->_sTable, [
                'user_id'    => $aData['user_id'],
                'platform'   => !empty($aData['platform']) ? $aData['platform'] : 'ios',
                'time_stamp' => PHPFOX_TIME,
                'is_active'  => 1
            ], 'id = ' . $id);
        } else {
            $aInsert = [
                'user_id'      => $aData['user_id'],
                'time_stamp'   => PHPFOX_TIME,
                'token'        => $aData['token'],
                'device_id'    => isset($aData['device_id']) ? $aData['device_id'] : '',
                'platform'     => !empty($aData['platform']) ? $aData['platform'] : 'ios',
                'token_source' => isset($aData['source']) ? $aData['source'] : self::TOKEN_SOURCE,
                'is_active'    => 1
            ];

            $id = db()->insert($this->_sTable, $aInsert);
        }
        return $id;
    }

    /**
     * @param null   $token
     * @param null   $deviceId
     * @param null   $deviceUID
     * @param string $source
     * @param bool   $deActive
     *
     * @return bool
     */
    public function removeDeviceToken($token = null, $deviceId = null, $deviceUID = null, $source = self::TOKEN_SOURCE, $deActive = true)
    {
        if (!$token && !$deviceId && !$deviceUID) {
            return false;
        }
        $where = 'token_source = \'' . $source . '\'';
        $where .= ' AND ( ';
        $extra = [];
        if ($token) {
            $extra[] = 'token = \'' . $token . '\'';
        }
        if ($deviceId) {
            $extra[] = 'device_id = \'' . $deviceId . '\'';
        }
        if ($deviceUID) {
            $extra[] = 'device_id = \'' . $deviceUID . '\'';
        }
        $where .= implode(' OR ', $extra) . ' )';

        if ($deActive) {
            return db()->update($this->_sTable, ['is_active' => 0], $where);
        }
        return db()->delete($this->_sTable, $where);
    }

    /**
     * @param        $userId
     * @param bool   $active
     * @param bool   $tokenOnly
     * @param string $source
     *
     * @return array|bool|int|string
     */
    public function getDeviceToken($userId, $active = true, $tokenOnly = true, $source = self::TOKEN_SOURCE)
    {
        if (!$userId) {
            return false;
        }

        $tokens = db()->select('dt.*')
            ->from($this->_sTable, 'dt')
            ->join(':user', 'u', 'dt.user_id = u.user_id')
            ->where('dt.user_id = ' . (int)$userId . ($active ? ' AND dt.is_active = 1' : '') . ' AND dt.token_source = \'' . $source . '\'')
            ->execute('getSlaveRows');


        if ($tokenOnly && count($tokens)) {
            $tokens = array_map(function ($arr) {
                return $arr['token'];
            }, $tokens);
        }
        return $tokens;
    }

    /**
     * @param        $senderId
     * @param        $receiverId
     * @param        $data
     * @param string $source
     *
     * @return bool
     */
    public function addNotificationQueue($senderId, $receiverId, $data, $source = self::TOKEN_SOURCE)
    {
        if (empty($senderId) || empty($receiverId) || empty($data)) {
            return false;
        }

        \Phpfox_Queue::instance()->addJob('mobile_push_notification', [
            'sender_id'         => $senderId,
            'receiver_id'       => $receiverId,
            'token_source'      => $source,
            'notification_id'   => isset($data['notification_id']) ? $data['notification_id'] : 0,
            'notification_type' => isset($data['notification_type']) ? $data['notification_type'] : ''
        ]);
    }

    /**
     * @param null $queue
     *
     * @return bool
     */
    public function executePushNotification($queue = null)
    {
        //Doesn't config firebase
        if (empty(Phpfox::getParam('mobile.mobile_firebase_server_key')) || empty($queue) || empty($queue['notification_type'])) {
            return false;
        }
        $parts = explode('_', $queue['notification_type']);
        $module = !empty($parts) ? $parts[0] : '';

        $supportModules = NameResource::instance()->getSupportModules();
        if (!Phpfox::isModule($module) || !in_array($module, $supportModules)) {
            return false;
        }

        $ended = end($parts);
        if (!empty($ended) && in_array($ended, ['like'])) {
            $module = $ended;
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.mobile_service_device_execute_push_notification_start')) ? eval($sPlugin) : false);

        $pushNotificationCacheId = 'user_push_notification_' . $queue['receiver_id'];
        if (($data = $this->cache()->get($pushNotificationCacheId)) === false) {
            $disabledModule = db()->select('push_notification_id, module_id')->from(':mobile_api_push_notification_setting')->where(['user_id' => $queue['receiver_id'],])->executeRows();
            $data = array_map(function ($item) {
                return $item['module_id'];
            }, $disabledModule);
            $this->cache()->save($pushNotificationCacheId, $data);
        }
        if (is_array($data) && in_array($module, $data)) {
            //Don't push notification
            return false;
        }

        $tokens = $this->getDeviceToken($queue['receiver_id'], true, true, $queue['token_source']);

        if (!empty($tokens)) {
            $result = $this->getNotificationDetail($queue);

            (($sPlugin = \Phpfox_Plugin::get('mobile.mobile_service_device_execute_push_notification')) ? eval($sPlugin) : false);

            if (!empty($result) && Phpfox::getService(PushNotificationInterface::class)->pushNotification($queue['receiver_id'], array_merge($result, ['tokens' => $tokens]))) {
                //Sent Success
                return true;
            } else {
                //Sent Fail
                return false;
            }
        }
        return false;
    }

    /**
     * @param $data
     *
     * @return array|bool
     */
    private function getNotificationDetail($data)
    {
        //Fetch info send to app
        $user = Phpfox::getService('user')->getUser($data['sender_id']);
        $receiver = Phpfox::getService('user')->getUser($data['receiver_id']);
        if (empty($user) || empty($receiver)) {
            return false;
        }
        $result = [];
        switch ($data['notification_type']) {
            case 'friend':
                $result = [
                    'title'         => $this->getLocalization()->translate('new_friend_request', [], $receiver['language_id']),
                    'message'       => $this->getLocalization()->translate('full_name_added_you_as_a_friend', ['full_name' => $user['full_name']], $receiver['language_id']),
                    'resource_link' => "friend/request/{$data['notification_id']}",
                    'web_link'      => "tab/friend"
                ];
                break;
            case 'mail':
                $message = db()->select('text')
                    ->from(':mail_thread_text')
                    ->where('message_id = ' . (int)$data['notification_id'])
                    ->execute('getSlaveField');
                $message = Phpfox::getLib('parse.input')->clean(strip_tags(Phpfox::getLib('parse.bbcode')->cleanCode(str_replace([
                    '&lt;',
                    '&gt;'
                ], ['<', '>'], $message))));
                $result = [
                    'title'         => $this->getLocalization()->translate('full_name_sent_you_a_message', ['full_name' => $user['full_name']], $receiver['language_id']),
                    'message'       => $message,
                    'resource_link' => ""
                ];
                break;
            default:
                $notification = $this->database()->select('n.*, n.user_id as item_user_id, ' . Phpfox::getUserField())
                    ->from(':notification', 'n')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                    ->where('n.notification_id = ' . (int)$data['notification_id'])
                    ->execute('getRow');
                Phpfox::getService('user.auth')->setUserId($notification['item_user_id'], $receiver);
                //Call callback to get message / link
                $callback = Phpfox::callback($notification['type_id'] . '.getNotification', $notification);

                if (!empty($callback)) {
                    /** @var NotificationResource $notificationResource */
                    $notificationResource = (new NotificationApi())->processRow(array_merge($notification, $callback));
                    Phpfox::getService('user.auth')->setUserId(null, ['user_id' => 0]);
                    $resource_link = UrlUtility::convertWebLinkToApi($callback['link']);
                    $result = [
                        'title'         => '',
                        'message'       => $notificationResource ? $notificationResource->getMessage(false) : (isset($callback['message']) ? $callback['message'] : ''),
                        'resource_link' => !empty($resource_link) ? $resource_link : '',
                        'web_link'      => $notificationResource ? $notificationResource->getLink() : (isset($callback['link']) ? $callback['link'] : '')
                    ];
                }
                break;
        }
        return $result;
    }

    /**
     * @param        $fieldName
     * @param bool   $bNotIn Get $fieldName IN or NOT IN supported modules
     * @param string $prefix AND | OR
     *
     * @return string
     * Get extra query condition, only get $fieldName match with supported modules on Mobile App
     */
    public function getExtraConditions($fieldName, $bNotIn = false, $prefix = 'AND')
    {
        $regexType = $bNotIn ? ' NOT REGEXP ' : ' REGEXP ';
        $inType = $bNotIn ? ' NOT IN ' : ' IN ';
        $supportModules = NameResource::instance()->getSupportModules();
        if (!empty($supportModules)) {
            $mobileAppCondition1 = " $fieldName $regexType '^" . implode('_|^', $supportModules) . "'";
            $mobileAppCondition2 = ($bNotIn ? ' AND ' : ' OR ') . ($fieldName . $inType . ' ("' . implode('","', $supportModules) . '")');
            return " $prefix ($mobileAppCondition1 $mobileAppCondition2)";
        }
        return " $prefix 1=1 ";
    }
}