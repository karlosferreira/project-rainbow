<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 10/5/18
 * Time: 9:20 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\UserApi;

class FriendRequestResource extends ResourceBase
{
    const RESOURCE_NAME = "friend-request";
    public $resource_name = self::RESOURCE_NAME;

    public $is_seen;
    public $is_ignore;
    public $message;
    public $user;
    public $mutual_friends;
    public $relation_data_id;
    public $relation_title;
    public $friendship = UserResource::FRIENDSHIP_CONFIRM_AWAIT;

    public $accept_action;
    public $deny_action;

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
        if (!empty($this->mutual_friends['friends'])) {
            $this->mutual_friends['friends'] = array_map(function ($item) {
                return UserResource::populate($item)->displayShortFields()->toArray();
            }, $this->mutual_friends['friends']);
        }
        return $this->mutual_friends;
    }

    /**
     * @param mixed $mutual_friends
     */
    public function setMutualFriends($mutual_friends)
    {
        $this->mutual_friends = $mutual_friends;
    }


    public function getFriendship()
    {
        return !empty($this->rawData['friend_user_id'])
            ? UserResource::FRIENDSHIP_CONFIRM_AWAIT :
            UserResource::FRIENDSHIP_REQUEST_SENT;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdFieldName()
    {
        return "request_id";
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();

        return self::createSettingForResource([
            'resource_name' => $this->resource_name,
            'urls.base'     => 'mobile/friend/request',
            'can_filter'    => false,
            'can_sort'      => false,
            'fab_buttons'   => false,
            'can_search'    => false,
            'search_input'  => [
                'can_search' => false,
            ],
            'actions'       => [
                'delete' => ['label' => $l->translate('delete'),],
                'cancel' => ['label' => $l->translate('cancel'),],
            ],
            'list_view'     => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-friend-request'),
                    'label'     => $l->translate('no_new_requests'),
                    'sub_label' => $l->translate('you_don_have_any_new_friend_request')
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'item_view'     => 'friend_request',
                'apiUrl'        => 'mobile/friend/request',
            ],
            'app_menu'      => [
                ['label' => $l->translate('incoming_requests'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('pending_requests'), 'params' => ['initialQuery' => ['view' => 'pending']]],
            ],
        ]);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('is_seen', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_ignore', ['type' => ResourceMetadata::BOOL])
            ->mapField('relation_data_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getRelationTitle()
    {
        if (!empty($this->relation_data_id)) {
            $relationData = \Phpfox::getService('custom.relation')->getDataById($this->relation_data_id);
            $relationship = '';
            if (!empty($relationData['relation_id'])) {
                $relationship = \Phpfox::getService('custom.relation')->getRelationName($relationData['relation_id']);
            }
            $this->relation_title = $this->getLocalization()->translate('relationship_request_for') . ' "' . $relationship . '"';
        }
        return $this->relation_title;
    }

    public function getAcceptAction()
    {
        $this->accept_action = 'friend/accept_request';
        if (!empty($this->relation_data_id)) {
            $this->accept_action = 'friend/accept_relationship';
        }
        return $this->accept_action;
    }

    public function getDenyAction()
    {
        $this->deny_action = 'friend/deny_request';
        if (!empty($this->relation_data_id)) {
            $this->deny_action = 'friend/deny_relationship';
        }
        return $this->deny_action;
    }

    public function getUser()
    {
        $resource = (new UserApi())->populateResource(UserResource::class, $this->rawData);
        $resource->setViewMode(ResourceBase::VIEW_DETAIL);
        return $resource->toArray(['id', 'full_name', 'resource_name', 'avatar', 'is_featured', 'friendship', 'user_name']);
    }
}