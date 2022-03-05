<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\Object\Privacy;
use Apps\Core_MobileApi\Api\Resource\Object\Statistic;
use Apps\Core_MobileApi\Service\EventApi;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class EventResource extends ResourceBase
{
    const RESOURCE_NAME = "event";
    const NO_INVITE = -1;
    const INVITED = 0;
    const ATTENDING = 1;
    const MAYBE_ATTEND = 2;
    const NOT_ATTEND = 3;

    public $resource_name = self::RESOURCE_NAME;

    public $title;

    public $description;
    public $text;

    public $module_id;
    public $item_id;

    public $view_id;
    public $is_sponsor;
    public $is_featured;
    public $is_friend;
    public $is_liked;
    public $is_pending;
    public $mass_email;
    public $post_types;
    public $profile_menus;

    public $image;

    public $full_address;
    public $location;
    public $country;
    public $province;
    public $postal_code;
    public $city;

    public $start_time;
    public $end_time;

    public $start_gmt_offset;
    public $end_gmt_offset;

    public $gmap;
    public $map_image;
    public $map_url;

    public $address;

    public $country_iso;
    public $country_child_id;

    public $rsvp;
    public $is_online;
    public $online_link;
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

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    public $coordinate;

    protected $coordinateMapping = [
        'latitude'  => 'location_lat',
        'longitude' => 'location_lng'
    ];

    protected $canPurchaseSponsor = null;
    protected $canSponsorInFeed = null;
    public $is_sponsored_feed = null;
    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('event', $this->id, $this->title);
    }

    public function getPostTypes()
    {
        return (new EventApi())->getPostTypes($this->getId());
    }

    public function getProfileMenus()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        if (empty($this->categories) || isset($this->rawData['categories'])) {
            $this->categories = NameResource::instance()
                ->getApiServiceByResourceName(EventCategoryResource::RESOURCE_NAME)
                ->getByEventId($this->id);
        }
        return $this->categories;
    }

    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('event', $this->id);
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

    /**
     * @return Image|String
     */
    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            $aSizes = Phpfox::getParam('event.thumbnail_sizes');
            return Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'event.url_image'
            ], $aSizes);
        }
        return $this->getDefaultImage();

    }

    public function getStartTime()
    {
        if ($this->start_time) {
            $this->start_time = $this->convertDatetime($this->start_time);
        }
        return $this->start_time;
    }

    public function getEndTime()
    {
        if ($this->end_time) {
            $this->end_time = $this->convertDatetime($this->end_time);
        }
        return $this->end_time;
    }

    public function getCountry()
    {
        if (empty($this->country) && !empty($this->rawData['country_iso'])) {
            $this->country = Phpfox::getService('core.country')->getCountry($this->rawData['country_iso']);
        }
        return html_entity_decode($this->country, ENT_QUOTES);
    }

    public function getProvince()
    {
        if (empty($this->province) && !empty($this->rawData['country_child_id'])) {
            $this->province = Phpfox::getService('core.country')->getChild($this->rawData['country_child_id']);
        }
        return html_entity_decode($this->province, ENT_QUOTES);
    }

    public function getMapImage()
    {

        $apiKey = Phpfox::getParam('core.google_api_key');

        if (empty($apiKey)) {
            return null;
        }

        if (empty($this->rawData['map_location'])) {
            return null;
        }

        $center = $this->rawData['map_location'];
        $extra = [
            'center'  => $center,
            'zoom'    => 16,
            'sensor'  => 'false',
            'size'    => '600x200',
            'maptype' => 'roadmap',
            'key'     => $apiKey,
            'scale'   => 2,
            'markers' => 'size:small|color:red|' . $center,
        ];
        return (PHPFOX_IS_HTTPS ? 'https' : 'http') . '://maps.googleapis.com/maps/api/staticmap?' . http_build_query($extra);
    }

    public function getMapUrl()
    {
        if (!empty($this->rawData['map_location'])) {
            return 'https://maps.google.com/?q=' . $this->rawData['map_location'];
        }
        return null;
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'event');
        }
        return null;
    }

    public function getShortFields()
    {
        return [
            'id', 'resource_name', 'title', 'start_time', 'end_time',
            'rsvp', 'location', 'statistic', 'is_sponsor', 'is_featured', 'is_liked',
            'image', 'user', 'creation_date', 'modification_date', 'extra', 'is_pending', 'coordinate',
            'is_online', 'online_link'
        ];
    }

    public function getFullAddress()
    {
        $this->full_address = $this->location;
        $hasFirst = false;
        if (!empty($this->address)) {
            $this->full_address .= ' - ' . $this->address;
            $hasFirst = true;
        }
        if (!empty($this->city)) {
            $this->full_address .= (!$hasFirst ? ' - ' : ', ') . $this->city;
            $hasFirst = true;
        }
        if (!empty($this->postal_code)) {
            $this->full_address .= (!$hasFirst ? ' - ' : ', ') . $this->postal_code;
            $hasFirst = true;
        }
        $country = $this->getCountry();
        if (!empty($country)) {
            $this->full_address .= (!$hasFirst ? ' - ' : ', ') . $country;
            $hasFirst = true;
        }
        $province = $this->getProvince();
        if (!empty($province)) {
            $this->full_address .= (!$hasFirst ? ' - ' : ', ') . $province;
        }
        return $this->full_address;
    }

    /**
     * @param ResourceMetadata|null $metadata
     *
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('mass_email', ['type' => ResourceMetadata::BOOL])
            ->mapField('postal_code', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_online', ['type' => ResourceMetadata::BOOL])
            ->mapField('country_child_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getRsvp()
    {
        if (empty($this->rsvp)) {
            $rsvp = NameResource::instance()->getApiServiceByResourceName(EventInviteResource::RESOURCE_NAME)->getUserInvite($this->id, Phpfox::getUserId());
            if (!empty($rsvp)) {
                switch ($rsvp['rsvp_id']) {
                    case 0:
                        $this->rsvp = self::INVITED;
                        break;
                    case 1:
                        $this->rsvp = self::ATTENDING;
                        break;
                    case 2:
                        $this->rsvp = self::MAYBE_ATTEND;
                        break;
                    case 3:
                        $this->rsvp = self::NOT_ATTEND;
                        break;
                }
            } else {
                $this->rsvp = self::NO_INVITE;
            }
        }
        return $this->rsvp;
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $actionMenu = [
            ['value' => '@event/invite', 'label' => $l->translate('invite_people_to_come'), 'acl' => 'can_invite'],
            ['value' => 'event/guest_list', 'label' => $l->translate('manage_guest_list'), 'acl' => 'can_edit'],
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['value' => Screen::ACTION_REPORT_ITEM, 'label' => $l->translate('report'), 'show' => '!is_owner', 'acl' => 'can_report'],
            ['value' => Screen::ACTION_EDIT_ITEM, 'label' => $l->translate('edit_event'), 'acl' => 'can_edit'],
            ['value' => Screen::ACTION_DELETE_ITEM, 'label' => $l->translate('delete_event'), 'style' => 'danger', 'acl' => 'can_delete'],
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 5, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 6, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 7, 0, [['label' => $l->translate('sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => '!is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
            array_splice($actionMenu, 8, 0, [['label' => $l->translate('remove_sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => 'is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
        }
        $defaultSortTime = Phpfox::getParam('event.event_default_sort_time');
        return self::createSettingForResource([
            'acl'              => $permission,
            'schema'           => [
                'definition' => [
                    'categories' => 'event_category[]'
                ]
            ],
            'resource_name'    => $this->getResourceName(),
            'search_input'     => [
                'placeholder' => $l->translate('search_events'),
            ],
            'detail_view'      => [
                'component_name' => 'event_detail',
            ],
            'list_view.tablet' => [
                'numColumns'      => 2,
                'layout'          => Screen::LAYOUT_GRID_VIEW,
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_events_found'),
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
                'item_view'       => 'event',
            ],
            'list_view'        => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_events_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action'    => [
                        'resource_name' => $this->getResourceName(),
                        'module_name'   => $this->getModuleName(),
                        'value'         => Screen::ACTION_ADD,
                        'label'         => $l->translate('add_new_item')
                    ]
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'item_view'       => 'event',
            ],
            'forms'            => [
                'addItem'  => [
                    'headerTitle' => $l->translate('create_new_event'),
                    'apiUrl'      => UrlUtility::makeApiUrl('event/form'),
                ],
                'editItem' => [
                    'headerTitle' => $l->translate('event_details'),
                    'apiUrl'      => UrlUtility::makeApiUrl('event/form/:id'),
                ],
                'invite'   => [
                    'headerTitle' => $l->translate('invited_events'),
                    'apiUrl'      => 'mobile/event-invite/form/:id',
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'event'
                    ]
                ],
                'sponsorInFeed' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'event',
                        'is_sponsor_feed' => 1
                    ]
                ],
            ],
            'membership_menu'  => [
                ['label' => $l->translate('attending'), 'value' => 'event/rsvp/attending'],
                ['label' => $l->translate('not_attending'), 'value' => 'event/rsvp/notAttending'],
            ],
            'action_menu'      => $actionMenu,
            'app_menu'         => [
                ['label' => $l->translate('all_events'), 'params' => ['initialQuery' => ['view' => '', 'when' => $defaultSortTime]]],
                ['label' => $l->translate('my_events'), 'params' => ['initialQuery' => ['view' => 'my']]],
                ['label' => $l->translate('friends_events'), 'params' => ['initialQuery' => ['view' => 'friend', 'when' => $defaultSortTime]]],
                ['label' => $l->translate('pending_events'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
                ['label' => $l->translate('events_i_m_attending'), 'params' => ['initialQuery' => ['view' => 'attending', 'when' => $defaultSortTime]]],
                ['label' => $l->translate('events_i_may_attend'), 'params' => ['initialQuery' => ['view' => 'may-attend', 'when' => $defaultSortTime]]],
                ['label' => $l->translate('invited_events'), 'params' => ['initialQuery' => ['view' => 'invites', 'when' => $defaultSortTime]]],
            ],
            'filter_menu'      => [
                'title'    => $l->translate('filter_by'),
                'queryKey' => 'when',
                'options'  => [
                    ['label' => $l->translate('all_time'), 'value' => 'all-time'],
                    ['label' => $l->translate('this_month'), 'value' => 'this-month'],
                    ['label' => $l->translate('this_week'), 'value' => 'this-week'],
                    ['label' => $l->translate('today'), 'value' => 'today'],
                    ['label' => $l->translate('upcoming'), 'value' => 'upcoming'],
                    ['label' => $l->translate('ongoing'), 'value' => 'ongoing'],
                ],
            ],
            'settings'         => [
                'time_format' => Phpfox::getParam('event.event_time_format'),
                'datetime_format' => Phpfox::getParam('event.event_basic_information_time')
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

    public function setStatistic($statistic)
    {
        if (Phpfox::isModule('event')) {
            $statistic->total_attending = isset($this->rawData['total_attending']) ? $this->rawData['total_attending'] : (int)Phpfox::getService('event')->getTotalRsvp($this->id, 1);
            $statistic->total_maybe_attending = (int)Phpfox::getService('event')->getTotalRsvp($this->id, 2);
            $statistic->total_awaiting_reply = (int)Phpfox::getService('event')->getTotalRsvp($this->id, 0);
        }
        $this->statistic = $statistic;
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'title', 'description', 'image', 'categories', 'privacy', 'start_time', 'end_time', 'location', 'statistic', 'is_online', 'online_link']);
        return $this->toArray();
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::isModule('event') && Phpfox::getService('event')->canPurchaseSponsorItem($this->getId());
        }
        return $this->canPurchaseSponsor;
    }

    public function getCanSponsorInFeed()
    {
        if ($this->canSponsorInFeed === null) {
            $this->canSponsorInFeed = Phpfox::isModule('feed') && Phpfox::getService('feed')->canSponsoredInFeed('event', $this->getId());
        }
        return $this->canSponsorInFeed;
    }

    public function getIsSponsoredFeed()
    {
        if ($this->is_sponsored_feed === null) {
            $this->is_sponsored_feed = Phpfox::isModule('feed') && is_numeric(Phpfox::getService('feed')->canSponsoredInFeed('event', $this->getId()));
        }
        return $this->is_sponsored_feed;
    }

    public function getUrlMapping($url, $queryArray)
    {
        preg_match('/\/category\/(\d+)?[\/|?]+/', $url, $match);
        if (Phpfox::isModule('event') && (isset($match[1]) || isset($queryArray['view']))) {
            $name = '';
            $categoryId = 0;
            $view = isset($queryArray['view']) ? $queryArray['view'] : '';
            if (!empty($match[1])) {
                $categoryId = $match[1];
                $category = Phpfox::getService('event.category')->getCategory($match[1]);
                $name = isset($category['name']) ? $this->getLocalization()->translate($category['name']) : '';
            }
            return [
                'routeName' => 'module/home',
                'params'    => [
                    'module_name'   => 'event',
                    'resource_name' => $this->resource_name,
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
        return null;
    }
}