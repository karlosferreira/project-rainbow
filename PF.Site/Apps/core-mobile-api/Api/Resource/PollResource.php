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

class PollResource extends ResourceBase
{
    const RESOURCE_NAME = "poll";
    public $resource_name = self::RESOURCE_NAME;

    public $question;

    public $description;
    public $text;

    public $module_id;
    public $item_id;

    public $image;

    public $hide_vote;
    public $randomize;

    public $view_id;
    public $is_featured;
    public $is_sponsor;
    public $is_multiple;
    public $is_user_voted;
    public $is_liked;
    public $is_friend;
    public $is_closed;
    public $is_pending;
    public $user_voted_this_poll;
    public $enable_close;

    public $close_time;

    public $background;
    public $percentage;
    public $border;

    public $answers;
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


    public $tags;

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    /**
     * PollResource constructor.
     *
     * @param $data
     */
    protected $canPurchaseSponsor = null;
    protected $canSponsorInFeed = null;
    public $is_sponsored_feed = null;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getQuestion()
    {
        $question = $this->parse->cleanOutput($this->question);
        if (empty($this->rawData['is_edit'])) {
            $question = ($this->isClosed() ? '(' . $this->getLocalization()->translate('Closed') . ') ' : '') . $question;
        }
        return $question;
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('poll', $this->id, $this->question);
    }


    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('poll', $this->id);
        if (!empty($tag[$this->id])) {
            return $tag[$this->id];
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
     * @return Image|string
     */
    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            $sizes = Phpfox::getParam('poll.thumbnail_sizes');
            return Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'poll.url_image'
            ], $sizes, false);
        }
        return $this->getDefaultImage();
    }

    public function setStatistic($statistic)
    {
        $statistic->total_votes = isset($this->rawData['total_votes']) ? $this->rawData['total_votes'] : 0;
        $this->statistic = $statistic;
    }

    public function getCloseTime()
    {
        if (!empty($this->close_time) && empty($this->rawData['is_form'])) {
            return $this->convertDatetime($this->close_time);
        }
        return $this->close_time;
    }

    private function isClosed()
    {
        if (!isset($this->rawData['close_time']) || !$this->rawData['close_time'] || $this->rawData['close_time'] > PHPFOX_TIME) {
            return false;
        } else {
            return true;
        }
    }

    public function getAnswers()
    {
        if (isset($this->rawData['is_detail']) && !empty($this->rawData['answer'])) {
            $answers = [];
            foreach ($this->rawData['answer'] as $answer) {
                $answers[] = PollAnswerResource::populate($answer)->toArray();
            }
            return $answers;
        }
        return null;
    }

    public function getIsUserVoted()
    {
        return (isset($this->rawData['user_voted_this_poll']) && is_bool($this->rawData['user_voted_this_poll'])) ? $this->rawData['user_voted_this_poll'] : $this->getUserVotedThisPoll();
    }

    public function getUserVotedThisPoll()
    {
        if (empty($this->rawData['is_detail'])) {
            $this->user_voted_this_poll = !empty(Phpfox::getService('poll')->getVotedAnswersByUser(Phpfox::getUserId(), $this->id)) ? true : false;
        }
        return $this->user_voted_this_poll;
    }

    public function getShortFields()
    {
        return [
            'id', 'resource_name', 'question', 'description', 'item_id', 'module_id', 'view_id', 'is_featured', 'is_sponsor',
            'is_user_voted', 'is_liked', 'statistic', 'user', 'privacy', 'creation_date', 'image', 'extra', 'is_closed', 'is_pending'
        ];
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'poll');
        }
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('hide_vote', ['type' => ResourceMetadata::INTEGER])
            ->mapField('enable_close', ['type' => ResourceMetadata::INTEGER])
            ->mapField('randomize', ['type' => ResourceMetadata::INTEGER])
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_multiple', ['type' => ResourceMetadata::INTEGER])
            ->mapField('user_voted_this_poll', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_user_voted', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_pending', ['type' => ResourceMetadata::BOOL]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $actionMenu = [
            ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
            ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 3, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 4, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 5, 0, [['label' => $l->translate('sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => '!is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
            array_splice($actionMenu, 6, 0, [['label' => $l->translate('remove_sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => 'is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
        }
        return self::createSettingForResource([
            'resource_name'   => $this->getResourceName(),
            'acl'             => $permission,
            'search_input'    => [
                'placeholder' => $l->translate('search_polls'),
            ],
            'forms'           => [
                'addItem'  => [
                    'headerTitle' => $l->translate('adding_poll'),
                    'apiUrl'      => UrlUtility::makeApiUrl('poll/form'),
                ],
                'editItem' => [
                    'headerTitle' => $l->translate('editing_poll'),
                    'apiUrl'      => UrlUtility::makeApiUrl('poll/form/:id'),
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'poll'
                    ]
                ],
                'sponsorInFeed' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'poll',
                        'is_sponsor_feed' => 1
                    ]
                ],
            ],
            'list_view'       => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_polls_found'),
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
                'alignment'       => 'right'
            ],
            'detail_view'     => [
                'component_name' => 'poll_detail',
            ],
            'app_menu'        => [
                ['label' => $l->translate('all_polls'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_polls'), 'params' => ['initialQuery' => ['view' => 'my']]],
                ['label' => $l->translate('friends_polls'), 'params' => ['initialQuery' => ['view' => 'friend']]],
                ['label' => $l->translate('pending_polls'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
            ],
            'action_menu' => $actionMenu,
            'settings'        => [
                'highlight_answer_voted' => \Phpfox::getUserParam('poll.highlight_answer_voted_by_viewer')
            ],
            'moderation_menu' => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete']
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'question', 'description', 'image', 'privacy']);
        $embed = $this->toArray();
        $embed['total_vote'] = isset($this->rawData['total_votes']) ? $this->rawData['total_votes'] : (string)$this->getTotalVotes($this->id);
        return $embed;
    }

    private function getTotalVotes($id)
    {
        $aAnswers = db()->select('pa.*,pr.user_id as voted')
            ->from(Phpfox::getT('poll_answer'), 'pa')
            ->leftJoin(Phpfox::getT('poll_result'), 'pr',
                'pr.answer_id = pa.answer_id AND pr.user_id =' . Phpfox::getUserId())
            ->where('pa.poll_id = ' . (int)$id)
            ->order('pa.ordering ASC')
            ->execute('getSlaveRows');
        $iTotalVotes = 0;
        foreach ($aAnswers as $aAnswer) {
            $iTotalVotes += $aAnswer['total_votes'];
        }
        return $iTotalVotes;
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::getService('poll')->canPurchaseSponsorItem($this->getId());
        }
        return $this->canPurchaseSponsor;
    }

    public function getCanSponsorInFeed()
    {
        if ($this->canSponsorInFeed === null) {
            $this->canSponsorInFeed = Phpfox::isModule('feed') && Phpfox::getService('feed')->canSponsoredInFeed('poll', $this->getId());
        }
        return $this->canSponsorInFeed;
    }

    public function getIsSponsoredFeed()
    {
        if ($this->is_sponsored_feed === null) {
            $this->is_sponsored_feed = Phpfox::isModule('feed') && is_numeric(Phpfox::getService('feed')->canSponsoredInFeed('poll', $this->getId()));
        }
        return $this->is_sponsored_feed;
    }
}