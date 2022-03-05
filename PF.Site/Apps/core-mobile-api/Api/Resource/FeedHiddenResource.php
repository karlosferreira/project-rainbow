<?php

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Phpfox;

class FeedHiddenResource extends ResourceBase
{
    const RESOURCE_NAME = "feed-hidden";
    public $module_name = "feed";
    public $resource_name = self::RESOURCE_NAME;

    public $idFieldName = 'hide_id';
    public $user;

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'feed_hidden_resource'
            ],
            'urls.base'     => 'mobile/feed/manage-hidden',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => [
                [
                    'label'         => $l->translate('filter'),
                    'icon'          => 'filter',
                    'action'        => Screen::ACTION_FILTER_BY,
                    'resource_name' => $this->getResourceName(),
                    'queryKey'      => 'type',
                ],
            ],
            'list_view'     => [
                'noItemMessage'   => [
                    'image' => $this->getAppImage(),
                    'label' => $l->translate('no_items_found')
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ]
            ],
            'filter_menu'   => [
                'title'    => $l->translate('filter_by'),
                'queryKey' => 'type',
                'options'  => [
                    ['label' => $l->translate('friends'), 'value' => 'friend'],
                    ['label' => $l->translate('pages'), 'value' => 'page'],
                    ['label' => $l->translate('groups'), 'value' => 'group']
                ],
            ],
        ]);
    }

    public function getUser()
    {
        if ($this->rawData['profile_page_id']) {
            $type = Phpfox::getLib('pages.facade')->getPageItemType($this->rawData['profile_page_id']);
            $item = db()->select('is_featured, is_sponsor')->from(':pages')->where(['page_id' => (int)$this->rawData['profile_page_id']])->executeRow();
            $owner = [
                'resource_name' => $type == 'groups' ? GroupResource::RESOURCE_NAME : PageResource::RESOURCE_NAME,
                'module_name'   => $type == 'groups' ? GroupResource::RESOURCE_NAME : PageResource::RESOURCE_NAME,
                'full_name'     => $this->parse->cleanOutput($this->rawData['full_name']),
                'id'            => (int)$this->rawData['profile_page_id'],
                'is_featured'   => isset($item['is_featured']) ? (bool)$item['is_featured'] : false,
                'is_sponsor'    => isset($item['is_sponsor']) ? (bool)$item['is_sponsor'] : false
            ];
            if ($type && empty($this->rawData['user_image'])) {
                $owner['avatar'] = $type == 'pages' ? PageResource::populate([])->getDefaultImage() : GroupResource::populate([])->getDefaultImage();
            } else {
                $avatar = Image::createFrom([
                    'user' => $this->rawData,
                ], ["50_square"]);
                if (!empty($avatar)) {
                    $owner['avatar'] = $avatar->sizes['50_square'];
                }
            }
            return $owner;
        } else {
            return UserResource::populate($this->rawData)->toArray(['full_name', 'id', 'resource_name', 'avatar', 'is_featured', 'user_name']);
        }
    }
}