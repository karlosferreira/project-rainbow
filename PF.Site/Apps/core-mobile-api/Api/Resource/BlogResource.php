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

class BlogResource extends ResourceBase
{
    const RESOURCE_NAME = "blog";
    const TAG_CATEGORY = 'blog';
    public $resource_name = self::RESOURCE_NAME;

    public $title;

    public $description;

    public $module_id;
    public $item_id;

    public $is_approved;
    public $is_sponsor;
    public $is_featured;
    public $is_liked;
    public $is_friend;
    public $is_pending;
    public $is_draft;

    public $post_status;

    public $text;

    public $image;

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
     * @var BlogCategoryResource[]
     */
    public $categories = [];

    /**
     * @var TagResource[]
     */
    public $tags = [];

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    protected $canPurchaseSponsor = null;
    protected $canSponsorInFeed = null;
    public $is_sponsored_feed = null;

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
        return Phpfox::permalink('blog', $this->id, $this->title);
    }

    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            return Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'blog.url_photo',
                'suffix'    => '_1024'
            ]);
        }
        return $this->getDefaultImage();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCategories()
    {
        return $this->categories;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['text'])) {
            $this->text = $this->rawData['text'];
        }
        TextFilter::pureHtml($this->text, true);
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        if ($this->description === null && isset($this->rawData['text'])) {
            $this->description = $this->rawData['text'];
        }
        TextFilter::pureText($this->description, 255, true);
        return $this->description;
    }

    public function getShortFields()
    {
        $default = parent::getShortFields();
        if (($key = array_search('text', $default)) !== false) {
            unset($default[$key]);
        }
        return $default;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('title', ['type' => ResourceMetadata::STRING])
            ->mapField('description', ['type' => ResourceMetadata::STRING])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('module_id', ['type' => ResourceMetadata::STRING])
            ->mapField('is_approved', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_draft', ['type' => ResourceMetadata::BOOL])
            ->mapField('post_status', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $actionMenu = [
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('publish'), 'value' => 'blog/publish', 'show' => 'is_draft', 'acl' => 'can_publish'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending&&!is_draft', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending&&!is_draft', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending&&!is_draft', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending&&!is_draft', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
            ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete']
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 4, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending&&!is_draft', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 5, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending&&!is_draft', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 6, 0, [['label' => $l->translate('sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => '!is_sponsored_feed&&!is_pending&&!is_draft', 'acl' => 'can_sponsor_in_feed']]);
            array_splice($actionMenu, 7, 0, [['label' => $l->translate('remove_sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => 'is_sponsored_feed&&!is_pending&&!is_draft', 'acl' => 'can_sponsor_in_feed']]);
        }

        return self::createSettingForResource([
            'schema'          => [
                'definition' => [
                    'categories' => 'blog.blog_category[]',
                ],
            ],
            'acl'             => $permission,
            'resource_name'   => $this->getResourceName(),
            'search_input'    => [
                'placeholder' => $l->translate('search_blogs_dot'),
            ],
            'forms'           => [
                'addItem'  => [
                    'apiUrl'      => UrlUtility::makeApiUrl('blog/form'),
                    'headerTitle' => $l->translate('add_a_new_blog'),
                ],
                'editItem' => [
                    'apiUrl'      => UrlUtility::makeApiUrl('blog/form/:id'),
                    'headerTitle' => $l->translate('editing_blog'),
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'blog'
                    ]
                ],
                'sponsorInFeed' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'blog',
                        'is_sponsor_feed' => 1
                    ]
                ],
            ],
            'list_view'       => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_blogs_found'),
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
                'alignment'       => 'right',
            ],
            'detail_view'     => [
                'component_name' => 'blog_detail'
            ],
            'app_menu'        => [
                ['label' => $l->translate('all_blogs'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_blogs'), 'params' => ['initialQuery' => ['view' => 'my']]],
                ['label' => $l->translate('my_draft_blog'), 'params' => ['initialQuery' => ['view' => 'draft']]],
                ['label' => $l->translate('friends_blogs'), 'params' => ['initialQuery' => ['view' => 'friend']]],
                ['label' => $l->translate('pending_blogs'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
            ],
            'action_menu'     => $actionMenu,
            'moderation_menu' => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending&&view!=draft', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending&&view!=draft', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'title', 'description', 'image', 'categories', 'privacy', 'creation_date', 'statistic']);
        return $this->toArray();
    }

    public function getIsPending()
    {
        $this->is_pending = !$this->is_approved;
        return $this->is_pending;
    }

    public function getIsDraft()
    {
        $this->is_draft = $this->post_status == 2;
        return $this->is_draft;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::getService('blog.permission')->canPurchaseSponsor($this->rawData);
        }
        return $this->canPurchaseSponsor;
    }

    public function getCanSponsorInFeed()
    {
        if ($this->canSponsorInFeed === null) {
            $this->canSponsorInFeed = Phpfox::getService('blog.permission')->canSponsorInFeed($this->rawData);
        }
        return $this->canSponsorInFeed;
    }

    public function getIsSponsoredFeed()
    {
        if ($this->is_sponsored_feed === null) {
            $this->is_sponsored_feed = Phpfox::isModule('feed') && Phpfox::getService('feed')->canSponsoredInFeed('blog', $this->getId()) !== true;
        }
        return $this->is_sponsored_feed;
    }

    public function getUrlMapping($url, $queryArray)
    {
        preg_match('/\/category\/(\d+)?[\/|?]+/', $url, $match);
        if (Phpfox::isModule('blog') && (isset($match[1]) || isset($queryArray['view']))) {
            $name = '';
            $categoryId = 0;
            if (!empty($match[1])) {
                $categoryId = $match[1];
                $category = Phpfox::getService('blog.category')->getCategory($categoryId);
                $name = isset($category['name']) ? $this->getLocalization()->translate($category['name']) : '';
            }
            return [
                'routeName' => 'module/home',
                'params'    => [
                    'module_name'   => 'blog',
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