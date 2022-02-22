<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Statistic;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class ForumPostResource extends ResourceBase
{
    const RESOURCE_NAME = "forum-post";
    public $resource_name = self::RESOURCE_NAME;

    public $module_name = 'forum';
    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "post_id";

    public $thread;
    public $thread_id;
    public $view_id;

    public $text;
    public $description;

    public $title;

    public $order_id;

    public $count;

    public $is_liked;

    public $thank_id;
    /**
     * @var UserResource
     */
    public $user;

    /**
     * @var Statistic
     */
    public $statistic;

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    public $privacy;

    public $tags;

    public $is_pending;

    private $isForm;

    public $forum_is_closed;

    protected $module_id;

    protected $item_id;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getThread()
    {
        return [
            'id'            => (int)$this->rawData['thread_id'],
            'resource_name' => ForumThreadResource::populate([])->getResourceName(),
            'module_name'   => ForumThreadResource::populate([])->getModuleName(),
            'title'         => isset($this->rawData['thread_title']) ? $this->parse->cleanOutput($this->rawData['thread_title']) : ''
        ];
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('forum.thread', $this->rawData['thread_id']) . '?view=' . $this->id;
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['text'])) {
            $this->text = $this->rawData['text'];
        }
        if (!$this->isForm) {
            TextFilter::pureHtml($this->text, true);
        }
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
        TextFilter::pureText($this->description, null, true);
        return $this->description;
    }

    public function getCount()
    {
        if (!$this->count && isset($this->rawData['thread_id']) && isset($this->rawData['post_id'])) {
            $this->count = Phpfox::getService('forum.post')->getPostCount($this->rawData['thread_id'],
                    $this->rawData['post_id']) - 1;
        }
        return $this->count;
    }

    public function getShortFields()
    {
        return ['id', 'title', 'resource_name', 'description', 'user', 'extra', 'is_pending'];
    }

    public function setStatistic($statistic)
    {
        if (Phpfox::getParam('forum.enable_thanks_on_posts')) {
            $statistic->total_thank = isset($this->rawData['thanks_count']) ? (int)$this->rawData['thank_count'] : (int)Phpfox::getService('forum.post')->getThanksCount($this->id);
        }
        if (!isset($this->rawData['total_like'])) {
            $statistic->total_like = (int)\Phpfox_Database::instance()->select('total_like')->from(':forum_post')->where(['post_id' => (int)$this->getId()])->executeField();
        }
        $this->statistic = $statistic;
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'forum');
        }
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('order_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_liked', ['type' => ResourceMetadata::INTEGER])
            ->mapField('thread_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('count', ['type' => ResourceMetadata::INTEGER])
            ->mapField('thank_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'acl'             => $permission,
            'resource_name'   => $this->getResourceName(),
            'schema'          => [
                'definition' => ['thread' => 'forum_thread'],
            ],
            'can_filter'      => false,
            'can_sort'        => false,
            'fab_buttons'     => false,
            'can_search'      => false,
            'forms'           => [
                'addItem'  => [
                    'headerTitle' => $l->translate('post_a_reply'),
                    'apiUrl'      => UrlUtility::makeApiUrl('forum-post/form'),
                ],
                'editItem' => [
                    'headerTitle' => $l->translate('editing_post'),
                    'apiUrl'      => UrlUtility::makeApiUrl('forum-post/form/:id'),
                ],
            ],
            'search_input'    => [
                'placeholder'   => $l->translate('search_posts'),
                'search_form'   => true,
                'resource_name' => 'forum_post',
            ],
            'detail_view'     => [
                'component_name' => 'forum_post_detail',
            ],
            'list_view'       => [
                'noItemMessage'   => [
                    'image' => $this->getAppImage(),
                    'label' => $l->translate('no_posts_found'),
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'item_view'       => 'forum_post',
                'layout'          => Screen::LAYOUT_LIST_CARD_VIEW,
            ],
            'action_menu'     => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
                ['label' => $l->translate('edit_post'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
                ['label' => $l->translate('delete_post'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
            ],
            'post0_menu'      => [
                ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
            ],
            'app_menu'        => [
                ['label' => $l->translate('new_posts'), 'params' => ['initialQuery' => ['view' => 'new']]],
                ['label' => $l->translate('pending_posts'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
            ],
            'settings'        => [
                'enable_thank' => Phpfox::getParam('forum.enable_thanks_on_posts')
            ],
            'moderation_menu' => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $data = ['id', 'resource_name', 'title', 'description'];
        if ($this->getViewMode() == ResourceBase::VIEW_DETAIL) {
            $data[] = 'text';
        }
        $this->setDisplayFields($data);
        return $this->toArray();
    }

    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        //Get tags for first post
        if ($this->getCount() == 0 && !empty($this->rawData['thread_id'])) {
            $threadId = $this->rawData['thread_id'];
            $tag = Phpfox::getService('tag')->getTagsById('forum', $threadId);
            if (!empty($tag[$threadId])) {
                $tags = [];
                foreach ($tag[$threadId] as $tag) {
                    $tags[] = TagResource::populate($tag)->displayShortFields()->toArray();
                }
                return $tags;
            }
        }
        return null;
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getThankId()
    {
        if (!isset($this->rawData['thank_id']) && $this->thank_id === null) {
            $this->thank_id = \Phpfox_Database::instance()->select('thank_id')->from(':forum_thank')->where(['post_id' => (int)$this->getId(), 'user_id' => Phpfox::getUserId()])->execute('getField');
        }

        return $this->thank_id;
    }

    /**
     * @param mixed $isForm
     */
    public function setIsForm($isForm)
    {
        $this->isForm = $isForm;
    }

    public function getIsLiked()
    {
        if (!isset($this->rawData['is_liked']) && $this->is_liked == null) {
            $this->is_liked = Phpfox::getService('like')->didILike('forum_post', $this->getId());
        }
        return $this->is_liked;
    }

    public function getModuleId()
    {
        $itemId = $this->getItemId();
        if (!empty($itemId) && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups'))) {
            $pageType = \Phpfox_Database::instance()->select('item_type')->from(':pages')->where(['page_id' => $itemId])->execute('getField');
            $this->module_id = $pageType ? 'groups' : 'pages';
        }
        return $this->module_id;
    }

    public function getItemId()
    {
        $this->item_id = isset($this->rawData['group_id']) ? $this->rawData['group_id'] : null;
        return $this->item_id;
    }
}