<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\Object\Privacy;
use Apps\Core_MobileApi\Api\Resource\Object\Statistic;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;
use Phpfox_File;

class PhotoResource extends ResourceBase
{
    const RESOURCE_NAME = "photo";
    public $resource_name = self::RESOURCE_NAME;

    public $title;

    public $description;
    public $text;

    public $module_id;
    public $group_id;
    public $album_id;
    public $item_id;
    public $type_id;

    public $view_id;
    public $is_sponsor;
    public $is_featured;
    public $is_cover;
    public $is_profile_photo;
    public $is_cover_photo;
    public $is_temp;
    public $is_friend;
    public $is_liked;
    public $is_pending;
    public $width;
    public $height;

    public $file_size;
    public $resolution;
    public $mature;
    public $allow_download;

    public $user_tags;

    public $image;

    /**
     * @var PhotoAlbumResource
     */
    public $album;

    /**
     * @var Statistic
     */
    public $statistic;

    /**
     * @var Privacy
     */
    public $privacy;

    /**
     * @var UserResource
     */
    public $user;

    /**
     * @var UserResource
     */
    public $parent_user;


    public $categories = [];

    public $tags = [];

    protected $canPurchaseSponsor = null;
    protected $canSponsorInFeed = null;
    public $is_sponsored_feed = null;

    /**
     * PhotoResource constructor.
     *
     * @param $data
     *
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('photo', $this->id, $this->title);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCategories()
    {
        if (empty($this->categories)) {
            $this->categories = NameResource::instance()
                ->getApiServiceByResourceName(PhotoCategoryResource::RESOURCE_NAME)
                ->getByPhotoId($this->id);
        } else {
            $this->categories = array_map(function ($category) {
                return PhotoCategoryResource::populate([
                    'category_id' => $category['category_id'],
                    'name' => $category[0]
                ])->displayShortFields()->toArray();
            }, $this->categories);
        }
        return $this->categories;
    }

    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('photo', $this->id);
        if (!empty($tag[$this->id])) {
            $tags = [];
            foreach ($tag[$this->id] as $tag) {
                $tags[] = TagResource::populate($tag)->displayShortFields()->toArray();
            }
            return $tags;
        }
        return null;
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['description'])) {
            $this->text = TextFilter::pureHtml($this->rawData['description'], empty($this->rawData['is_form']));
        }
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        if ($this->description === null && isset($this->rawData['description'])) {
            $this->description = $this->rawData['description'];
        }
        TextFilter::pureText($this->description, null, true);
        return $this->description;
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        if (($this->mature == 0 || (($this->mature == 1 || $this->mature == 2)
                    && Phpfox::getUserId()
                    && Phpfox::getUserParam('photo.photo_mature_age_limit') <= UserResource::populate(Phpfox::getUserBy())->getAge()))
            || $this->rawData['user_id'] == Phpfox::getUserId() || !empty($this->rawData['is_detail'])) {

            $aSizes = Phpfox::getService('photo')->getPhotoPicSizes();
            return Image::createFrom([
                'file' => $this->rawData['destination'],
                'server_id' => $this->rawData['server_id'],
                'path' => 'photo.url_photo'
            ], $aSizes, false);
        } else {
            return Image::createFrom([
                'theme' => 'misc/mature.jpg',
            ]);
        }
    }

    /**
     * @return PhotoAlbumResource
     * @throws \Exception
     */
    public function getAlbum()
    {
        if (empty($this->album) && empty($this->rawData['album_detail'])) {
            $this->album = NameResource::instance()
                ->getApiServiceByResourceName(PhotoAlbumResource::RESOURCE_NAME)
                ->getById($this->rawData['album_id']);
        }
        return $this->album;
    }


    /**
     * @return string
     */
    public function getFileSize()
    {
        if (empty($this->file_size)) {
            $this->file_size = Phpfox_File::instance()->filesize($this->rawData['file_size']);
        }
        return $this->file_size;
    }

    /**
     * @return string
     */
    public function getResolution()
    {
        if (empty($this->resolution) && !empty($this->rawData['width'])) {
            $this->resolution = $this->rawData['width'] . '×' . $this->rawData['height'];
        }
        return $this->resolution;
    }

    public function getItemId()
    {
        return $this->group_id;
    }

    public function setItemId()
    {
        $this->item_id = $this->group_id;
    }

    public function getUserTags()
    {
        if (!empty($this->rawData['user_tags'])) {
            $userTags = [];
            foreach ($this->rawData['user_tags'] as $key => $tag) {
                $userTags[] = UserResource::populate($tag)->displayShortFields()->toArray();
            }
            return $userTags;
        } else {
            return null;
        }
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('group_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_cover', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_profile_photo', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_cover_photo', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_temp', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_like', ['type' => ResourceMetadata::BOOL])
            ->mapField('file_size', ['type' => ResourceMetadata::INTEGER])
            ->mapField('mature', ['type' => ResourceMetadata::INTEGER])
            ->mapField('width', ['type' => ResourceMetadata::INTEGER])
            ->mapField('height', ['type' => ResourceMetadata::INTEGER])
            ->mapField('allow_download', ['type' => ResourceMetadata::BOOL]);
    }

    public function getFeedDisplay()
    {
        $result = $this->toArray(['resource_name', 'id', 'module_id', 'item_id']);
        $result['total_photo'] = 1;
        $result['remain_photo'] = 0;
        $result['photos'][] = [
            'id' => $this->getId(),
            'href' => "photo/{$this->getId()}",
            'module_name' => 'photo',
            'resource_name' => 'photo',
            'mature' => $this->mature,
            'user' => isset($this->rawData['user_id']) ? ['id' => (int)$this->rawData['user_id'], 'resource_name' => 'user'] : null,
            'image' => $this->getImage()->sizes['1024']
        ];
        return $result;
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()
            ->getApiServiceByResourceName($this->resource_name)
            ->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $actionMenu = [
            ['label' => $l->translate('download'), 'value' => '@photo/download', 'acl' => 'can_download'],
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('mobile_set_as_album_cover'), 'value' => 'photo/set_album_cover', 'acl' => 'can_set_album_cover'],
            ['label' => $l->translate('make_profile_picture'), 'value' => 'photo/set_profile_avatar', 'acl' => 'can_set_profile_avatar'],
            ['label' => $l->translate('make_cover_photo'), 'value' => 'photo/set_profile_cover', 'acl' => 'can_set_profile_cover'],
            ['label' => $l->translate('set_as_page_s_cover_photo'), 'value' => 'photo/set_parent_cover', 'show' => 'module_id==pages', 'acl' => 'can_set_parent_cover'],
            ['label' => $l->translate('Set as Group\'s Cover Photo'), 'value' => 'photo/set_parent_cover', 'show' => 'module_id==groups', 'acl' => 'can_set_parent_cover'],
            ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
            ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 3, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 4, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 5, 0, [['label' => $l->translate('sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => '!is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
            array_splice($actionMenu, 6, 0, [['label' => $l->translate('remove_sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => 'is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
        }
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'acl' => $permission,
            'schema' => [
                'definition' => [
                    'categories' => 'photo_category[]',
                ]
            ],
            'search_input' => [
                'placeholder' => $l->translate('search_photos'),
                'can_search' => isset($permission['can_search']) ? !!$permission['can_search'] : true
            ],
            'detail_view' => [
                'component_name' => 'photo_detail',
            ],
            'list_view' => [
                'noItemMessage' => [
                    'image' => $this->getAppImage(),
                    'label' => $l->translate('no_photos_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action' => !empty($permission['can_add']) ? [
                        'resource_name' => $this->getResourceName(),
                        'module_name' => $this->getModuleName(),
                        'value' => Screen::ACTION_ADD,
                        'label' => $l->translate('add_new_item')
                    ] : null
                ],
                'noResultMessage' => [
                    'image' => $this->getAppImage('no-result'),
                    'label' => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'numColumns' => 3,
                'gutter' => 1,
                'spacing' => 0,
                'limit' => 40,
                'item_view' => 'photo',
                'layout' => Screen::LAYOUT_GRID_CARD_VIEW,
            ],
            'forms' => [
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'photo'
                    ]
                ],
                'sponsorInFeed' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'photo',
                        'is_sponsor_feed' => 1
                    ]
                ],
            ],
            'action_menu' => $actionMenu,
            'app_menu' => [
                ['label' => $l->translate('all_photos'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_photos'), 'params' => ['initialQuery' => ['view' => 'my']]],
                ['label' => $l->translate('friends_photos'), 'params' => ['initialQuery' => ['view' => 'friend']]],
                ['label' => $l->translate('pending_photos'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
            ],
            'moderation_menu' => [
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete']
            ]
        ]);
    }

    public function getShortFields()
    {
        return [
            'title', 'resource_name', 'id', 'group_id', 'item_id', 'view_id', 'is_sponsor', 'is_featured',
            'mature', 'image', 'statistic', 'privacy', 'creation_date', 'user', 'extra', 'is_pending'
        ];
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::getService('photo')->canPurchaseSponsorItem($this->getId(), 'photo', 'photo', 'photo_id');
        }
        return $this->canPurchaseSponsor;
    }

    public function getCanSponsorInFeed()
    {
        if ($this->canSponsorInFeed === null) {
            $this->canSponsorInFeed = Phpfox::isModule('feed') && Phpfox::getService('feed')->canSponsoredInFeed('photo', $this->getId());
        }
        return $this->canSponsorInFeed;
    }

    public function getIsSponsoredFeed()
    {
        if ($this->is_sponsored_feed === null) {
            $this->is_sponsored_feed = Phpfox::isModule('feed') && is_numeric(Phpfox::getService('feed')->canSponsoredInFeed('photo', $this->getId()));
        }
        return $this->is_sponsored_feed;
    }

    public function getUrlMapping($url, $queryArray)
    {
        preg_match('/\/category\/(\d+)?[\/|?]+/', $url, $match);
        if (Phpfox::isModule('photo') && (isset($match[1]) || isset($queryArray['view']))) {
            $name = '';
            $categoryId = 0;
            $view = isset($queryArray['view']) ? $queryArray['view'] : '';
            $resourceName = $this->getResourceName();
            if ($view == 'myalbums') {
                $view = 'my';
                $resourceName = PhotoAlbumResource::populate([])->getResourceName();
            }
            if (!empty($match[1])) {
                $categoryId = $match[1];
                $category = Phpfox::getService('photo.category')->getCategory($match[1]);
                $name = isset($category['name']) ? $this->getLocalization()->translate($category['name']) : '';
            }
            return [
                'routeName' => 'module/home',
                'params'    => [
                    'module_name'   => 'photo',
                    'resource_name' => $resourceName,
                    'header_title'  => $name,
                    'filter_title'  => $name,
                    'query'         => [
                        'category' => (int)$categoryId,
                        'q'        => isset($queryArray['search']['search']) ? $queryArray['search']['search'] : '',
                        'view'     => $view
                    ]
                ]
            ];
        }
        preg_match('/\/album\/(profile|cover)\/(\d+)?[\/|?]+/', $url, $match);
        if (Phpfox::isModule('photo') && isset($match[1])) {
            $aAlbum = [];
            switch ($match[1]) {
                case 'profile':
                    $aAlbum = Phpfox::getService('photo.album')->getForProfileView((int)$match[2]);
                    break;
                case 'cover':
                    $aAlbum = Phpfox::getService('photo.album')->getForCoverView((int)$match[2]);
                    break;
            }
            if (!empty($aAlbum['album_id'])) {
                return [
                    'routeName' => 'viewItemDetail',
                    'params'    => [
                        'id'            => (int)$aAlbum['album_id'],
                        'module_name'   => 'photo',
                        'resource_name' => PhotoAlbumResource::populate([])->getResourceName(),
                    ],
                ];
            }
        }
        return null;
    }
}