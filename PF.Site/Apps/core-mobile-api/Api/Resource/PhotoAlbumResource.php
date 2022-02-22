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
use Apps\Core_MobileApi\Service\PhotoApi;
use Phpfox;

class PhotoAlbumResource extends ResourceBase
{
    const RESOURCE_NAME = "photo-album";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'photo';

    protected $idFieldName = 'album_id';

    public $name;

    public $description;
    public $text;

    public $module_id;
    public $group_id;
    public $item_id;
    public $is_friend;
    public $is_liked;
    public $view_id;
    public $is_pending;
    public $is_featured;
    public $is_sponsor;
    public $mature;

    public $profile_id;
    public $timeline_id;
    public $cover_id;

    public $image;

    /**
     * @var Statistic
     */
    public $statistic;
    protected $canPurchaseSponsor = null;
    /**
     * @var Privacy
     */
    public $privacy;

    /**
     * @var UserResource
     */
    public $user;

    /**
     * @var PhotoResource
     */
    public $photos;

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
        return Phpfox::permalink('photo.album', $this->id, $this->name);
    }

    public function getImage()
    {
        if (empty($this->rawData['destination'])) {
            return $this->getDefaultImage();
        }
        if (($this->mature == 0 || (($this->mature == 1 || $this->mature == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= UserResource::populate(Phpfox::getUserBy())->getAge())) || $this->rawData['user_id'] == Phpfox::getUserId()) {
            $aSizes = Phpfox::getService('photo')->getPhotoPicSizes();
            return Image::createFrom([
                'file'      => $this->rawData['destination'],
                'server_id' => isset($this->rawData['server_id']) ? $this->rawData['server_id'] : 0,
                'path'      => 'photo.url_photo'
            ], $aSizes, false);
        } else {
            return Image::createFrom([
                'theme' => 'misc/mature.jpg',
            ]);
        }
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['description'])) {
            $this->text = TextFilter::pureHtml($this->rawData['description'], true);
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
     * Get photo of albums
     * @return array
     */
    public function getPhotos()
    {
        if (isset($this->rawData['photos'])) {
            $photos = [];
            foreach ($this->rawData['photos'] as $photo) {
                $photo['album_detail'] = true;
                $resource = PhotoResource::populate($photo);
                $photos[] = $resource->setExtra((new PhotoApi())->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray();
            }
            return $photos;
        }
        return null;
    }

    public function getItemId()
    {
        return $this->group_id;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('group_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('mature', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()
            ->getApiServiceByResourceName($this->resource_name)
            ->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $actionMenu = [
            ['label' => $l->translate('upload_photos'), 'value' => 'photo-album/upload', 'style' => 'primary', 'acl' => 'can_upload'],
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
            ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 3, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 4, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
        }
        return self::createSettingForResource([
            'resource_name'   => $this->getResourceName(),
            'acl'             => $permission,
            'search_input'    => [
                'placeholder' => $l->translate('search_photo_albums'),
                'can_search'  => isset($permission['can_search']) ? !!$permission['can_search'] : true
            ],
            'schema'          => [
                'definition' => [
                    'photos' => 'photo[]',
                ]
            ],
            'detail_view'     => [
                'component_name' => 'photo_album_detail',
            ],
            'forms'           => [
                'uploadPhotos' => [
                    'apiUrl'      => 'mobile/photo/album-upload/:id',
                    'headerTitle' => $l->translate('upload_photos'),
                    'succeedAction' => '@photo/album/refresh'
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'photo_album'
                    ]
                ],
            ],
            'list_view'       => [
                'numColumns'      => 2,
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_albums_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action'    => !empty($permission['can_add']) ? [
                        'resource_name' => $this->getResourceName(),
                        'module_name'   => $this->getModuleName(),
                        'value'         => Screen::ACTION_ADD,
                        'label'         => $l->translate('add_new_item')
                    ] : null
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'layout'          => Screen::LAYOUT_GRID_VIEW,
                'item_view'       => 'photo_album',
                'limit'           => 30,
            ],
            'sort_menu'       => [
                'title'    => $l->translate('sort_by'),
                'queryKey' => 'sort',
                'options'  => [
                    ['label' => $l->translate('latest'), 'value' => 'latest'],
                    ['label' => $l->translate('most_liked'), 'value' => 'most_liked'],
                    ['label' => $l->translate('most_discussed'), 'value' => 'most_discussed'],
                ]
            ],
            'action_menu'     => $actionMenu,
            'app_menu'        => [
                ['label' => $l->translate('all_albums'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_albums'), 'params' => ['initialQuery' => ['view' => 'my']]],
            ],
            'moderation_menu' => [
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete']
            ]
        ]);
    }

    public function setStatistic($statistic)
    {
        $statistic->total_photo = intval(isset($this->rawData['total_photo']) ? $this->rawData['total_photo'] : 0);
        $this->statistic = $statistic;
    }

    public function getName()
    {
        if (!empty($this->rawData['is_edit'])) {
            if ($this->profile_id) {
                return $this->name = $this->getLocalization()->translate('profile_pictures');
            } else if ($this->cover_id) {
                return $this->name = $this->getLocalization()->translate('cover_photo');
            } else if ($this->timeline_id) {
                return $this->name = $this->getLocalization()->translate('timeline_photos');
            }
        }
        return $this->name = parent::getName();
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getSponsoredDisplay()
    {
        $this->setDisplayFields([
            'resource_name', 'name', 'image', 'statistic', 'id', 'sponsor_id'
        ]);
        return $this->toArray();
    }

    public function getFeaturedDisplay()
    {
        $this->setDisplayFields([
            'resource_name', 'name', 'image', 'statistic', 'id'
        ]);
        return $this->toArray();
    }


    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::getService('photo')->canPurchaseSponsorItem($this->getId(), 'photo_album', 'photo_album', 'album_id');
        }
        return $this->canPurchaseSponsor;
    }
}