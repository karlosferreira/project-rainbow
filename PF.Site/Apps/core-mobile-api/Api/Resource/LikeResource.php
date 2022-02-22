<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Phpfox;

class LikeResource extends ResourceBase
{
    const RESOURCE_NAME = "like";
    public $resource_name = self::RESOURCE_NAME;

    public $item_type;
    public $item_id;

    public $is_owner;
    /**
     * @var array of mutual friends
     */
    public $mutual_friends;

    public $friendship;

    /**
     * Who like this post
     * @var UserResource
     */
    public $user;


    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    public function getItemType()
    {
        return $this->rawData['type_id'];
    }

    public function getItemId()
    {
        return $this->item_id;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('item_type', [
                'type'       => ResourceMetadata::STRING,
                'data_field' => 'type_id'
            ]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'urls.base'       => 'mobile/like',
            'resource_name'   => $this->getResourceName(),
            'fab_buttons'     => false,
            'list_view'       => [
                'item_view' => 'like',
            ],
            'action_menu'     => [
                ['value' => 'friend/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1'],
                ['value' => 'friend/cancel_request', 'label' => $l->translate('cancel_request'), 'style' => 'danger', 'show' => 'friendship==3'],
                ['value' => 'friend/accept_request', 'label' => $l->translate('accept_friend_request'), 'show' => 'friendship==2'],
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
            ],
            'membership_menu' => [
                ['value' => 'friend/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1'],
                ['value' => 'friend/cancel_request', 'label' => $l->translate('cancel_request'), 'style' => 'danger', 'show' => 'friendship==3'],
                ['value' => 'friend/accept_request', 'label' => $l->translate('accept_friend_request'), 'show' => 'friendship==2'],
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
            ],
        ]);
    }

    /**
     * @return mixed
     */
    public function getMutualFriends()
    {
        return $this->mutual_friends;
    }

    /**
     * @param mixed $mutual_friends
     */
    public function setMutualFriends($mutual_friends)
    {
        if (!empty($mutual_friends['friends'])) {
            foreach ($mutual_friends['friends'] as $key => $data) {
                $mutual_friends['friends'][$key] = UserResource::populate($data)->displayShortFields()->toArray();
            }
        }
        $this->mutual_friends = $mutual_friends;
    }

    const FRIENDSHIP_CAN_ADD_FRIEND = 0;
    const FRIENDSHIP_IS_FRIEND = 1;
    const FRIENDSHIP_CONFIRM_AWAIT = 2;
    const FRIENDSHIP_REQUEST_SENT = 3;
    const FRIENDSHIP_CAN_NOT_ADD_FRIEND = 4;
    const FRIENDSHIP_IS_OWNER = 5;
    const FRIENDSHIP_IS_UNKNOWN = 6;
    const FRIENDSHIP_IS_DENY_REQUEST = 7;

    public function getFriendship()
    {
        return ($this->friendship !== null) ? $this->friendship : ($this->friendship = $this->_getFriendship());
    }

    public function _getFriendship()
    {
        if ($this->getIsOwner()) {
            return self::FRIENDSHIP_IS_OWNER;
        }
        $friendUserId = $this->getAuthor()->getId();
        if (!$this->accessControl || !$friendUserId) { // todo @ApiDev check return value.
            return self::FRIENDSHIP_CAN_ADD_FRIEND;
        }
        $status = self::FRIENDSHIP_CAN_NOT_ADD_FRIEND;
        $isModule = Phpfox::isModule('friend');
        $userId = Phpfox::getUserId();
        $iFriendRequestAwait = empty($this->rawData['is_friend_request']) && $isModule ? Phpfox::getService('friend.request')->isRequested($friendUserId, $userId, false, true) : true;
        $iFriendRequestSent = $iFriendRequestAwait ? false : Phpfox::getService('friend.request')->isRequested($userId, $friendUserId, false, true);
        $isFriend = $isModule ? Phpfox::getService('friend')->isFriend($userId, $friendUserId) : false;

        if (!$userId) {
            $status = self::FRIENDSHIP_IS_UNKNOWN;
        } else if (!$isModule) {
            $status = self::FRIENDSHIP_IS_UNKNOWN;
        } else if (!empty($isFriend)) {
            $status = self::FRIENDSHIP_IS_FRIEND;
        } else if (Phpfox::getService('friend.request')->isDenied($userId, $friendUserId)) {
            $status = self::FRIENDSHIP_IS_DENY_REQUEST;
        } else if (Phpfox::getService('friend.request')->isDenied($friendUserId, $userId) && $this->accessControl->isGranted(UserAccessControl::ADD_FRIEND, UserResource::populate(['user_id' => $friendUserId])) && $userId != $friendUserId) {
            $status = self::FRIENDSHIP_CAN_ADD_FRIEND;
        } else if ($iFriendRequestAwait) {
            $status = self::FRIENDSHIP_CONFIRM_AWAIT;
        } else if ($iFriendRequestSent) {
            $status = self::FRIENDSHIP_REQUEST_SENT;
        } else if ($this->accessControl->isGranted(UserAccessControl::ADD_FRIEND, UserResource::populate(['user_id' => $friendUserId])) && $userId != $friendUserId) {
            $status = self::FRIENDSHIP_CAN_ADD_FRIEND;
        }
        return $status;
    }

    public function getIsOwner()
    {
        $this->is_owner = $this->getAuthor()->getId() == Phpfox::getUserId();
        return $this->is_owner;
    }
}