<?php

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Service\UserApi;
use Phpfox;

class FriendResource extends ResourceBase
{
    const RESOURCE_NAME = "friend";
    const MINIMAL_FORMAT = "mini";

    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'friend';

    protected $idFieldName = 'friend_id';

    /**
     * @var string define form of data display in response
     */
    private $outputFormat = "";

    /**
     * @var bool this friend is set ad top friend
     */
    public $is_top_friend;


    public $friend_user_id;

    /**
     * @var array of mutual friends
     */
    public $mutual_friends;

    /**
     * @var array of lists that current friend was added in
     */
    public $lists;

    public $friendship;

    /**
     * @var UserResource
     */
    public $user;

    public $is_owner;

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getMutualFriends()
    {
        return $this->mutual_friends;
    }

    /**
     * @return mixed
     */
    public function getLists()
    {
        if (!empty($this->lists)) {
            $this->lists = array_map(function ($item) {
                return [
                    'id'   => $item['list_id'],
                    'name' => $this->parse->cleanOutput($item['name'])
                ];
            }, $this->lists);
        }
        return $this->lists;
    }

    public function toArray($displayFields = null)
    {
        if ($this->outputFormat == self::MINIMAL_FORMAT) {
            return [
                'id'   => $this->user->getId(),
                'name' => $this->user->full_name,
                'img'  => $this->user->getAvatar()
            ];
        }
        return parent::toArray($displayFields);
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

    /**
     * @param mixed $lists
     */
    public function setLists($lists)
    {
        $this->lists = $lists;
    }

    /**
     * @param string $outputFormat
     *
     * @return $this
     */
    public function setOutputFormat($outputFormat)
    {
        $this->outputFormat = $outputFormat;
        return $this;
    }

    public function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('friend_user_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_top_friend', ['type' => ResourceMetadata::BOOL]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();

        return self::createSettingForResource([
            'resource_name'   => $this->resource_name,
            'can_filter'      => false,
            'can_sort'        => false,
            'fab_buttons'     => false,
            'can_add'         => false,
            'list_view'       => [
                'item_view'       => 'friend',
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-friend'),
                    'label'     => $l->translate('no_friends_found'),
                    'sub_label' => $l->translate('start_adding_new_friends'),
                    'action'    => [
                        'routeName'   => 'module/home',
                        'module_name' => 'user',
                        'value'       => '@navigator/push',
                        'label'       => $l->translate('find_friends')
                    ]
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ]
            ],
            'mutual_friend'   => [
                'apiUrl'          => UrlUtility::makeApiUrl('friend/mutual'),
                'item_view'       => 'friend',
                'noItemMessage'   => [
                    'image' => $this->getAppImage('no-friend'),
                    'label' => $l->translate('no_mutual_friends_found'),
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ]
            ],
            'action_menu'     => [
                ['value' => 'friend/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1'],
                ['value' => 'friend/cancel_request', 'label' => $l->translate('cancel_request'), 'style' => 'danger', 'show' => 'friendship==3'],
                ['value' => 'friend/accept_request', 'label' => $l->translate('accept_friend_request'), 'style' => 'danger', 'show' => 'friendship==2'],
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
            ],
            'membership_menu' => [
                ['value' => 'friend/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1'],
                ['value' => 'friend/cancel_request', 'label' => $l->translate('cancel_request'), 'style' => 'danger', 'show' => 'friendship==3'],
                ['value' => 'friend/accept_request', 'label' => $l->translate('accept_friend_request'), 'style' => 'danger', 'show' => 'friendship==2'],
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
            ],
            'search_input'    => [
                'placeholder' => $l->translate('search_friends_dot_dot_dot'),
            ],
            'app_menu'        => [
                ['label' => $l->translate('all_friends')]
            ],
        ]);
    }

    public function getUser()
    {
        $resource = (new UserApi())->populateResource(UserResource::class, $this->rawData);
        $resource->setViewMode(ResourceBase::VIEW_LIST);
        return $resource->toArray(['id', 'full_name', 'resource_name', 'avatar', 'cover', 'summary', 'friendship', 'is_featured', 'user_name']);
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
        $friendUserId = $this->rawData['friend_user_id'];
        if (!$this->accessControl) {
            $this->setAccessControl((new UserApi())->getAccessControl());
        }
        $status = self::FRIENDSHIP_CAN_NOT_ADD_FRIEND;
        $isModule = Phpfox::isModule('friend');
        $userId = Phpfox::getUserId();
        //friend_user_id is user who sent request
        $iFriendRequestAwait = empty($this->rawData['is_friend_request']) ? Phpfox::getService('friend.request')->isRequested($friendUserId, $userId, false, true) : true;
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
        $this->is_owner = $this->friend_user_id == Phpfox::getUserId();
        return $this->is_owner;
    }
}