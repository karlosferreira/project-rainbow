<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Phpfox;

class PageMemberResource extends UserResource
{
    const RESOURCE_NAME = "page-member";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "user_id";


    public $full_name;
    public $avatar;
    public $is_featured;
    public $page_id;
    public $is_admin;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getFullName()
    {
        $this->full_name = isset($this->rawData['full_name']) ? $this->parse->cleanOutput($this->rawData['full_name']) : '';
        return $this->full_name;
    }

    public function getAvatar()
    {
        $image = Image::createFrom([
            'user' => $this->rawData,
        ], ["50_square"]);

        if ($image == null) {
            return $this->getDefaultImage(false, parent::RESOURCE_NAME);
        }
        return (!$this->isDetailView() ? (!empty($image->sizes['50_square']) ? $image->sizes['50_square'] : $this->getDefaultImage(false, parent::RESOURCE_NAME)) : $image->image_url);

    }

    public function getShortFields()
    {
        return ['resource_name', 'id', 'full_name', 'avatar', 'is_featured', 'statistic', 'is_owner', 'friendship', 'friend_id', 'extra', 'page_id', 'is_admin'];
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7.1', '>')) {
            $actionMenu = [
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
                ['value' => 'user/add_friend_request', 'label' => $l->translate('add_friend'), 'show' => 'friendship==0'],
                ['value' => 'user/accept_friend_request', 'label' => $l->translate('accept_friend_request'), 'show' => 'friendship==2'],
                ['value' => 'user/cancel_friend_request', 'label' => $l->translate('cancel_request'), 'show' => 'friendship==3', 'style' => 'danger'],
                ['value' => 'user/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1', 'acl' => 'can_view_remove_friend_link'],
                ['value' => 'pages/delete-member', 'style' => 'danger', 'label' => $l->translate('delete_member'), 'acl' => 'pages.can_delete_member'],
                ['value' => 'pages/delete-admin', 'style' => 'danger', 'label' => $l->translate('delete_admin'), 'show' => 'is_admin', 'acl' => 'pages.can_delete_admin'],
            ];
            $membershipMenu = null;
        } else {
            $actionMenu = [
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
                ['value' => 'user/add_friend_request', 'label' => $l->translate('add_friend'), 'show' => 'friendship==0'],
                ['value' => 'user/accept_friend_request', 'label' => $l->translate('accept_friend_request'), 'show' => 'friendship==2'],
                ['value' => 'user/cancel_friend_request', 'label' => $l->translate('cancel_request'), 'show' => 'friendship==3', 'style' => 'danger'],
                ['value' => 'user/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1', 'acl' => 'can_view_remove_friend_link'],
            ];
            $membershipMenu = [
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
                ['value' => 'user/add_friend_request', 'label' => $l->translate('add_friend'), 'show' => 'friendship==0'],
                ['value' => 'user/accept_friend_request', 'label' => $l->translate('accept_friend_request'), 'show' => 'friendship==2'],
                ['value' => 'user/cancel_friend_request', 'label' => $l->translate('cancel_request'), 'show' => 'friendship==3', 'style' => 'danger'],
                ['value' => 'user/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1', 'acl' => 'can_view_remove_friend_link'],
            ];
        }
        return self::createSettingForResource([
            'schema'          => [
                'ref' => 'item_member',
            ],
            'resource_name'   => $this->getResourceName(),
            'urls.base'       => 'mobile/page-member',
            'search_input'    => false,
            'list_view'       => [
                'item_view' => 'page_member',
            ],
            'fab_buttons'     => false,
            'can_add'         => false,
            'membership_menu' => $membershipMenu,
            'action_menu'     => $actionMenu,
        ]);
    }

    public function getIsFeatured()
    {
        if ($this->is_featured === null) {
            $this->is_featured = \Phpfox::getService('user')->isFeatured($this->getId());
        }
        return (bool)$this->is_featured;
    }

    public function getIsAdmin()
    {
        if (isset($this->rawData['is_admin'])) {
            $this->is_admin = $this->rawData['is_admin'];
        } else {
            $this->is_admin = Phpfox::getService('pages')->isAdmin($this->page_id, $this->getId());
        }
        return $this->is_admin;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('page_id', ['type' => ResourceMetadata::INTEGER]);
    }
}