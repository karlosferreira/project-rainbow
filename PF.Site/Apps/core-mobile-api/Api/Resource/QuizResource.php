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
use Apps\Core_MobileApi\Service\QuizResultApi;
use Phpfox;

class QuizResource extends ResourceBase
{
    const RESOURCE_NAME = "quiz";
    public $resource_name = self::RESOURCE_NAME;

    public $title;

    public $description;
    public $text;

    public $image;

    public $module_id;
    public $item_id;

    public $view_id;
    public $is_featured;
    public $is_sponsor;
    public $is_user_played;
    public $is_liked;
    public $is_friend;
    public $is_pending;
    public $is_owner;

    public $questions;
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

    public $results;

    public $member_results = [];

    protected $canPurchaseSponsor = null;
    protected $canSponsorInFeed = null;
    public $is_sponsored_feed = null;

    /**
     * PollResource constructor.
     *
     * @param $data
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
        return Phpfox::permalink('quiz', $this->id, $this->title);
    }


    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('quiz', $this->id);
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
     * @return Image|array|string
     */
    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            $sizes = Phpfox::getParam('quiz.thumbnail_sizes');
            return Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'quiz.url_image'
            ], $sizes);
        }

        return $this->getDefaultImage();
    }

    public function setStatistic($statistic)
    {
        $statistic->total_play = isset($this->rawData['total_play']) ? $this->rawData['total_play'] : 0;
        $this->statistic = $statistic;
    }

    public function getQuestions()
    {
        if (isset($this->rawData['is_detail']) && !empty($this->rawData['question'])) {
            $questions = [];
            foreach ($this->rawData['question'] as $key => $question) {
                $quest = [
                    'question_id' => isset($question['question_id']) ? (int)$question['question_id'] : (int)$key,
                    'question'    => $this->parse->cleanOutput($question['question'])
                ];
                if (!empty($question['answer'])) {
                    foreach ($question['answer'] as $key2 => $answer) {
                        $quest['answers'][] = [
                            'answer_id' => (int)$key2,
                            'answer'    => $this->parse->cleanOutput($answer)
                        ];
                    }
                } else if (!empty($question['answers'])) {
                    foreach ($question['answers'] as $key2 => $answer) {
                        $quest['answers'][] = [
                            'answer_id'  => (int)$answer['answer_id'],
                            'answer'     => $this->parse->cleanOutput($answer['answer']),
                            'is_correct' => (int)$answer['is_correct']
                        ];
                    }
                }
                $questions[] = $quest;
            }
            return $questions;
        }
        return null;
    }

    public function getShortFields()
    {
        return ['id', 'title', 'user', 'resource_name', 'statistic', 'description', 'is_featured', 'is_sponsor',
            'is_liked', 'privacy', 'creation_date', 'view_id', 'image', 'extra', 'is_pending', 'item_id', 'module_id'];
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'quiz');
        }
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_user_played', ['type' => ResourceMetadata::BOOL])
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
            'resource_name'   => $this->resource_name,
            'acl'             => $permission,
            'schema'          => [
                'definition' => [
                    'member_results' => 'quiz_result[]',
                ]
            ],
            'detail_view'     => [
                'component_name' => 'quiz_detail',
            ],
            'forms'           => [
                'addItem'  => [
                    'apiUrl'      => UrlUtility::makeApiUrl('quiz/form'),
                    'headerTitle' => $l->translate('add_new_quiz'),
                ],
                'editItem' => [
                    'apiUrl'      => UrlUtility::makeApiUrl('quiz/form/:id'),
                    'headerTitle' => $l->translate('editing_quiz'),
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'quiz'
                    ]
                ],
                'sponsorInFeed' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'quiz',
                        'is_sponsor_feed' => 1
                    ]
                ],
            ],
            'search_input'    => [
                'placeholder' => $l->translate('search_quizzes'),
            ],
            'list_view'       => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_quizzes_found'),
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
            'action_menu' => $actionMenu,
            'app_menu'        => [
                ['label' => $l->translate('all_quizzes'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_quizzes'), 'params' => ['initialQuery' => ['view' => 'my']]],
                ['label' => $l->translate('friends_quizzes'), 'params' => ['initialQuery' => ['view' => 'friend']]],
                ['label' => $l->translate('pending_quizzes'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
            ],
            'moderation_menu' => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'title', 'description', 'image', 'privacy']);
        $embed = $this->toArray();
        $embed['total_play'] = isset($this->rawData['total_play']) ? $this->rawData['total_play'] : 0;

        return $embed;
    }

    public function getMemberResults()
    {
        return (new QuizResultApi())->getMemberResults($this->getId(), 0, 5);
    }

    public function getResults()
    {
        if (!empty($this->rawData['results'])) {
            $data = $this->rawData['results'];
            $results = [];
            $results['total_correct'] = $data['total_correct'];
            $results['total_question'] = $data['total_question'];
            $results['percent_correct'] = $data['percent_correct'];
            $results['user_result'] = [];
            foreach ($data['user_results'] as $user_result) {
                $results['user_result'][] = [
                    'question'            => $user_result['questionText'],
                    'question_id'         => $user_result['questionId'],
                    'user_answer_text'    => $user_result['userAnswerText'],
                    'user_answer_id'      => ResourceMetadata::convertValue($user_result['userAnswer'], ['type' => ResourceMetadata::INTEGER]),
                    'correct_answer_text' => $user_result['correctAnswerText'],
                    'correct_answer_id'   => ResourceMetadata::convertValue($user_result['correctAnswer'], ['type' => ResourceMetadata::INTEGER]),
                    'user_answer_date'    => $this->convertDatetime($user_result['time_stamp'])
                ];
            }
            return $results;
        }
        return null;
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getIsOwner()
    {
        $this->is_owner = $this->user->getId() == \Phpfox::getUserId();

        return $this->is_owner;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::getService('quiz')->canPurchaseSponsorItem($this->getId());
        }
        return $this->canPurchaseSponsor;
    }

    public function getCanSponsorInFeed()
    {
        if ($this->canSponsorInFeed === null) {
            $this->canSponsorInFeed = Phpfox::isModule('feed') && Phpfox::getService('feed')->canSponsoredInFeed('quiz', $this->getId());
        }
        return $this->canSponsorInFeed;
    }

    public function getIsSponsoredFeed()
    {
        if ($this->is_sponsored_feed === null) {
            $this->is_sponsored_feed = Phpfox::isModule('feed') && is_numeric(Phpfox::getService('feed')->canSponsoredInFeed('quiz', $this->getId()));
        }
        return $this->is_sponsored_feed;
    }
}