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

class MarketplaceResource extends ResourceBase
{
    const RESOURCE_NAME = "marketplace";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "listing_id";

    public $title;

    public $description;
    public $short_description;
    public $text;

    public $view_id;
    public $is_sponsor;
    public $is_featured;
    public $is_friend;
    public $is_liked;
    public $is_sell;
    public $allow_point_payment;
    public $is_closed;
    public $is_expired;
    public $is_notified;
    public $is_pending;
    public $auto_sell;
    public $currency_id;
    public $price;

    public $image;
    public $images;

    public $group_id;

    public $country;
    public $province;
    public $postal_code;
    public $city;

    public $country_iso;
    public $country_child_id;
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


    public $categories = [];

    public $tags = [];

    public $buy_now_link;

    public $time_stamp;

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    public $location;
    public $coordinate;

    protected $coordinateMapping = [
        'latitude'  => 'location_lat',
        'longitude' => 'location_lng'
    ];

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
        return Phpfox::permalink('marketplace', $this->id, $this->title);
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        if (empty($this->categories) || isset($this->rawData['categories'])) {
            $this->categories = NameResource::instance()
                ->getApiServiceByResourceName(MarketplaceCategoryResource::RESOURCE_NAME)
                ->getByListingId($this->id);
        }
        return $this->categories;
    }

    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('marketplace', $this->id);
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

    public function getShortDescription()
    {
        return TextFilter::pureHtml($this->rawData['mini_description'], true);
    }

    /**
     * @return Image|array|string
     */
    public function getImage()
    {
        $sizes = Phpfox::getParam('marketplace.thumbnail_sizes');

        if (!empty($this->rawData['image_path'])) {
            return Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'marketplace.url_image'
            ], $sizes);
        } else {
            return $this->getDefaultImage();
        }

    }

    public function getImages()
    {
        if (isset($this->rawData['images_list']) && count($this->rawData['images_list'])) {
            $images = [];
            $sizes = Phpfox::getParam('marketplace.thumbnail_sizes');
            foreach ($this->rawData['images_list'] as $image) {
                $images[] = Image::createFrom([
                    'file'      => $image['image_path'],
                    'server_id' => $image['server_id'],
                    'path'      => 'marketplace.url_image'
                ], $sizes)->toArray();
            }
            $this->images = $images;
        }
        return $this->images;
    }

    public function getCountry()
    {
        $this->country = '';
        if (!empty($this->rawData['country_iso'])) {
            $this->country = Phpfox::getService('core.country')->getCountry($this->rawData['country_iso']);
        }
        return html_entity_decode($this->country, ENT_QUOTES);
    }

    public function getProvince()
    {
        $this->province = '';
        if (!empty($this->rawData['country_child_id'])) {
            $this->province = Phpfox::getService('core.country')->getChild($this->rawData['country_child_id']);
        }
        return html_entity_decode($this->province, ENT_QUOTES);
    }

    public function getPrice()
    {
        if (!empty($this->rawData['is_edit'])) {
            return $this->price;
        } else {
            if (isset($this->price) && isset($this->rawData['currency_id'])) {
                if ($this->price == '0.00') {
                    return $this->getLocalization()->translate('free');
                } else {
                    return html_entity_decode(Phpfox::getService('core.currency')->getCurrency($this->price, $this->rawData['currency_id']), ENT_QUOTES);
                }
            }
        }
        return null;
    }

    public function getIsSell()
    {
        if (!empty($this->rawData['allow_point_payment'])) {
            return (int)($this->is_sell || $this->rawData['allow_point_payment']);
        }
        return $this->is_sell;
    }

    public function getShortFields()
    {
        return [
            'id', 'title', 'images', 'resource_name', 'city', 'province', 'country', 'image', 'price', 'statistic', 'description', 'is_pending', 'is_expired',
            'is_sponsor', 'is_featured', 'is_liked', 'currency_id', 'privacy', 'user', 'creation_date', 'short_description', 'view_id', 'is_sell', 'is_closed', 'extra', 'coordinate'
        ];
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && (!empty($this->rawData['is_detail']) || !empty($this->rawData['is_edit']))) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'marketplace');
        }
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_closed', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_notified', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_pending', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_sell', ['type' => ResourceMetadata::INTEGER])
            ->mapField('allow_point_payment', ['type' => ResourceMetadata::INTEGER])
            ->mapField('auto_sell', ['type' => ResourceMetadata::INTEGER])
            ->mapField('price', ['type' => ResourceMetadata::FLOAT])
            ->mapField('group_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('country_child_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $actionMenu = [
            ['value' => 'marketplace/photos', 'label' => $l->translate('manage_photos'), 'acl' => 'can_manage_photo'],
            ['value' => '@marketplace/invite', 'label' => $l->translate('send_invitations'), 'acl' => 'can_invite'],
            ['value' => 'marketplace/manage-invite', 'label' => $l->translate('manage_invites'), 'acl' => 'can_invite'],
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('reopen'), 'value' => '@marketplace/reopen', 'show' => 'is_expired', 'acl' => 'can_reopen'],
            ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report'],
            ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 5, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 6, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 7, 0, [['label' => $l->translate('sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => '!is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
            array_splice($actionMenu, 8, 0, [['label' => $l->translate('remove_sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => 'is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
        }
        return self::createSettingForResource([
            'acl'              => $permission,
            'resource_name'    => $this->getResourceName(),
            'schema'           => [
                'definition' => [
                    'categories' => 'marketplace_category[]'
                ],
            ],
            'search_input'     => [
                'placeholder' => $l->translate('search_listings'),
            ],
            'detail_view'      => [
                'component_name' => 'marketplace_detail',
            ],
            'list_view.tablet' => [
                'numColumns' => 3,
            ],
            'list_view'        => [
                'item_view'       => 'marketplace',
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_marketplace_listings_found'),
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
                'alignment'       => 'left',
                'numColumns'      => 2,
                'layout'          => Screen::LAYOUT_GRID_VIEW,
            ],
            'forms'            => [
                'addItem'  => [
                    'headerTitle' => $l->translate('create_a_listing'),
                    'apiUrl'      => UrlUtility::makeApiUrl('marketplace/form'),
                ],
                'editItem' => [
                    'headerTitle' => $l->translate('edit_listing'),
                    'apiUrl'      => UrlUtility::makeApiUrl('marketplace/form/:id'),
                ],
                'photos'   => [
                    'headerTitle' => $l->translate('manage_photos'),
                    'apiUrl'      => 'mobile/marketplace-photo/form/:id'
                ],
                'invite'   => [
                    'headerTitle' => $l->translate('invite_friends'),
                    'apiUrl'      => 'mobile/marketplace-invite/form/:id',
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'marketplace'
                    ]
                ],
                'sponsorInFeed' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'marketplace',
                        'is_sponsor_feed' => 1
                    ]
                ],
            ],
            'action_menu'      => $actionMenu,
            'app_menu'         => [
                ['label' => $l->translate('all_listings'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_listings'), 'params' => ['initialQuery' => ['view' => 'my']]],
                ['label' => $l->translate('listing_invites'), 'params' => ['initialQuery' => ['view' => 'invites']]],
                ['label' => $l->translate('expired'), 'params' => ['initialQuery' => ['view' => 'expired']], 'acl' => 'can_view_expired'],
                ['label' => $l->translate('friends_listings'), 'params' => ['initialQuery' => ['view' => 'friend']]],
                ['label' => $l->translate('pending_listings'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
            ],
            'payment'          => [
                'buy_now' => [
                    'apiUrl' => 'mobile/marketplace/buy-now/:id',
                    'method' => 'get'
                ]
            ],
            'enable_map'       => true,
            'moderation_menu'  => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'title', 'short_description', 'price', 'image', 'categories', 'privacy', 'country', 'province', 'city']);
        return $this->toArray();
    }

    public function getBuyNowLink()
    {
        return Phpfox::getLib('url')->makeUrl('marketplace.purchase', ['id' => $this->getId()]);
    }

    public function getTitle()
    {
        $title = parent::getTitle();

        if ($this->view_id == 2 && empty($this->rawData['is_edit'])) {
            $title = '(' . $this->getLocalization()->translate('sold') . ') ' . $title;
        }
        return $title;
    }

    public function getIsExpired()
    {
        $this->is_expired = false;
        $expireTime = Phpfox::getParam('marketplace.days_to_expire_listing');
        if ($expireTime > 0 && isset($this->rawData['time_stamp'])) {
            $this->is_expired = $this->rawData['time_stamp'] < (PHPFOX_TIME - $expireTime * 86400);
        }
        return $this->is_expired;
    }

    public function getIsPending()
    {
        $this->is_pending = $this->view_id == 1;
        return $this->is_pending;
    }

    public function getIsClosed()
    {
        $this->is_closed = $this->view_id == 2;
        return $this->is_closed;
    }

    public function getUrlMapping($url, $queryArray)
    {
        preg_match('/\/category\/(\d+)?[\/|?]+/', $url, $match);
        if (Phpfox::isModule('marketplace') && (isset($match[1]) || isset($queryArray['view']))) {
            $name = '';
            $categoryId = 0;
            if (!empty($match[1])) {
                $categoryId = $match[1];
                $category = Phpfox::getService('marketplace.category')->getCategory($categoryId);
                $name = isset($category['name']) ? $this->getLocalization()->translate($category['name']) : '';
            }
            return [
                'routeName' => 'module/home',
                'params'    => [
                    'module_name'   => 'marketplace',
                    'resource_name' => $this->resource_name,
                    'header_title'  => $name,
                    'filter_title'  => $name,
                    'query'         => [
                        'category' => (int)$categoryId,
                        'q'        => isset($queryArray['search']['search']) ? $queryArray['search']['search'] : '',
                        'view'      => isset($queryArray['view']) ? $queryArray['view'] : ''
                    ]
                ]
            ];
        }
        return null;
    }
}