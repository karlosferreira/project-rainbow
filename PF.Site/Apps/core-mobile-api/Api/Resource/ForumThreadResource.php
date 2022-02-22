<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\Core_MobileApi\Service\PollApi;
use Phpfox;

class ForumThreadResource extends ResourceBase
{
    const RESOURCE_NAME = "forum-thread";
    public $resource_name = self::RESOURCE_NAME;

    public $module_name = 'forum';
    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "thread_id";

    public $forum_id;
    public $forum_is_closed;
    public $group_id;
    public $item_id;
    public $poll_id;
    public $text;
    public $description;

    public $poll;
    public $view_id;
    public $start_id;

    public $is_announcement;
    public $is_closed;
    public $is_seen;
    public $is_subscribed;
    public $is_pending;

    public $title;
    public $title_url;

    public $order_id;

    public $posts;
    public $last_post;
    public $breadcrumbs;
    public $post_user_id;

    /**
     * @var UserResource
     */
    public $user;

    public $statistic;

    public $tags = [];

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    public $privacy;

    protected $canPurchaseSponsor = null;

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
        return Phpfox::permalink('forum.thread', $this->id, $this->title);
    }

    public function getStatistic()
    {
        return [
            'total_post' => isset($this->rawData['total_post']) ? (int)$this->rawData['total_post'] : 0,
            'total_view' => isset($this->rawData['total_view']) ? (int)$this->rawData['total_view'] : 0
        ];
    }

    public function getLastPost()
    {
        if (isset($this->rawData['last_post'])) {
            $this->rawData['last_post']['thread_id'] = $this->rawData['thread_id'];
            $this->rawData['last_post']['thread_title'] = $this->rawData['title'];
            return ForumPostResource::populate($this->rawData['last_post'])->toArray(['id', 'user', 'resource_name']);
        }
    }

    public function getPosts()
    {
        if (isset($this->rawData['posts'])) {
            $posts = [];
            foreach ($this->rawData['posts'] as $post) {
                $post['is_detail'] = true;
                $posts[] = ForumPostResource::populate($post)->loadFeedParam()->toArray();
            }
            return $posts;
        }
    }

    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('forum', $this->id);
        if (!empty($tag[$this->id])) {
            $tags = [];
            foreach ($tag[$this->id] as $tag) {
                $tags[] = TagResource::populate($tag)->displayShortFields()->toArray();
            }
            return $tags;
        }
        return null;
    }

    public function getPoll()
    {
        if (!empty($this->poll)) {
            $poll = $this->poll;
            $poll['is_detail'] = true;
            $resource = PollResource::populate($poll);
            return $resource->setExtra((new PollApi())->getAccessControl()->getPermissions($resource))->toArray();
        }
        return $this->poll;
    }

    public function getShortFields()
    {
        return [
            'id', 'resource_name', 'title', 'forum_id', 'is_announcement', 'is_closed', 'is_pending',
            'last_post', 'statistic', 'creation_date', 'user', 'extra', 'order_id', 'is_subscribed', 'poll_id', 'breadcrumbs'
        ];
    }

    public function getBreadcrumbs()
    {
        if ($this->rawData['forum_id']) {
            $this->getForum($this->rawData['forum_id']);
            return $this->breadcrumbs;
        }
        return null;
    }

    public function getForum($id)
    {
        $forum = NameResource::instance()->getApiServiceByResourceName(ForumResource::RESOURCE_NAME)->loadResourceById($id);
        if ($forum['parent_id']) {
            $this->getForum($forum['parent_id']);
        }
        $this->breadcrumbs[] = [
            'id'   => (int)$forum['forum_id'],
            'name' => $this->getLocalization()->translate($forum['name']),
            'href' => "forum/{$forum['forum_id']}"
        ];
    }

    public function getDescription()
    {
        if ($this->text === null) {
            $this->getText();
        }
        return TextFilter::pureText($this->text, null, true);
    }

    public function getItemId()
    {
        return $this->group_id;
    }

    public function getPostUserId()
    {
        return null;
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->start_id, 'forum');
        }
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('forum_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('forum_is_closed', ['type' => ResourceMetadata::INTEGER])
            ->mapField('group_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('start_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_announcement', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_subscribed', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_closed', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_seen', ['type' => ResourceMetadata::BOOL])
            ->mapField('order_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('poll_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('post_user_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('used', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $appMenu = [];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile') { // for mobile version >= 1.4
            $appMenu[] = ['label' => $l->translate('recent_discussions'), 'params' => ['initialQuery' => ['view' => 'recent_discussion']]];
        }
        $appMenu = array_merge($appMenu, [
            ['label' => $l->translate('my_threads'), 'params' => ['initialQuery' => ['view' => 'my']]],
            ['label' => $l->translate('subscribed_threads'), 'params' => ['initialQuery' => ['view' => 'subscribed']]],
            ['label' => $l->translate('pending_threads'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
        ]);
        $actionMenu = [
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('reply'), 'value' => '@forum/reply', 'acl' => 'can_reply'],
            ['label' => $l->translate('forum_add_poll'), 'value' => 'forum/add-poll', 'acl' => 'can_add_poll'],
            ['label' => $l->translate('remove_poll'), 'value' => 'forum/remove-poll', 'acl' => 'can_delete_poll'],
            ['label' => $l->translate('sponsor'), 'value' => 'forum/sponsor', 'show' => 'order_id!=2&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => 'forum/remove-sponsor', 'show' => 'order_id==2&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('close_thread'), 'value' => 'forum/close-thread', 'show' => '!is_closed&&!is_pending', 'acl' => 'can_close'],
            ['label' => $l->translate('open_thread'), 'value' => 'forum/open-thread', 'show' => 'is_closed&&!is_pending', 'acl' => 'can_close'],
            ['label' => $l->translate('stick_thread'), 'value' => 'forum/stick-thread', 'show' => 'order_id!=1&&!is_pending', 'acl' => 'can_stick'],
            ['label' => $l->translate('unstick_thread'), 'value' => 'forum/unstick-thread', 'show' => 'order_id==1&&!is_pending', 'acl' => 'can_stick'],
            ['label' => $l->translate('subscribe'), 'value' => 'forum/subscribe-thread', 'show' => '!is_subscribed'],
            ['label' => $l->translate('unsubscribe'), 'value' => 'forum/unsubscribe-thread', 'show' => 'is_subscribed'],
            ['label' => $l->translate('edit_thread'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('delete_thread'), 'value' => Screen::ACTION_DELETE_ITEM, 'acl' => 'can_delete', 'style' => 'danger'],
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 3, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'order_id!=2&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 4, 0, [['label' => $l->translate('remove_sponsor'), 'value' => 'forum/remove-sponsor', 'show' => 'order_id==2&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
        }
        return self::createSettingForResource([
            'resource_name'   => $this->getResourceName(),
            'acl'             => $permission,
            'schema'          => [
                'definition' => Phpfox::isAppActive('Core_Polls') ? [
                    'poll' => 'poll.poll'
                ] : null,
            ],
            'can_filter'      => false,
            'can_sort'        => false,
            'fab_buttons'     => false,
            'can_search'      => false,
            'forms'           => [
                'addItem'  => [
                    'headerTitle' => $l->translate('post_new_thread'),
                    'apiUrl'      => UrlUtility::makeApiUrl('forum-thread/form'),
                    'succeedAction' => "@forum/cache/UPDATE"
                ],
                'editItem' => [
                    'headerTitle' => $l->translate('editing_thread'),
                    'apiUrl'      => UrlUtility::makeApiUrl('forum-thread/form/:id'),
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'forum_thread'
                    ]
                ],
            ],
            'search_input'    => [
                'placeholder'   => $l->translate('search_threads'),
                'search_form'   => true,
                'resource_name' => 'forum_thread',
            ],
            'list_view'       => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_threads_found')
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'item_view'       => 'forum_thread',
            ],
            'detail_view'     => [
                'component_name' => 'forum_thread_detail'
            ],
            'app_menu'        => $appMenu,
            'action_menu'     => $actionMenu,
            'moderation_menu' => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'title', 'description', 'breadcrumbs']);
        return $this->toArray();
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['posts'])) {
            $posts = $this->rawData['posts'];
            if (isset($posts[0]['text'])) {
                $this->text = $posts[0]['text'];
            }
        }
        TextFilter::pureHtml($this->text, true);
        return $this->text;
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::getService('forum.thread')->canPurchaseSponsorItem($this->getId());
        }
        return $this->canPurchaseSponsor;
    }
}