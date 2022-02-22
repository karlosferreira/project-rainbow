<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class ForumResource extends ResourceBase
{
    const RESOURCE_NAME = "forum";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'forum';

    public $name;

    public $description;
    public $text;


    public $view_id;
    public $is_category;
    public $is_closed;
    public $parent_id;

    /**
     * @var ForumThreadResource
     */
    public $thread;

    /**
     * @var ForumPostResource
     */
    public $post;

    public $statistic;

    public $ordering;
    /**
     * @var ForumResource
     */
    public $sub_forums;

    public $subs;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getName()
    {
        return $this->parse->cleanOutput($this->getLocalization()->translate($this->name));
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('forum', $this->id, $this->name);
    }

    public function getStatistic()
    {
        return [
            'total_post'   => (int)$this->rawData['total_post'],
            'total_thread' => (int)$this->rawData['total_thread']
        ];
    }

    /**
     * Get sub forums
     * @return array
     */
    public function getSubForums()
    {
        if (!$this->sub_forums && !empty($this->rawData['sub_forum']) && empty($this->rawData['is_sub'])) {
            $subForums = [];
            foreach ($this->rawData['sub_forum'] as $forum) {
                //Get 2 level only
                $forum['is_sub'] = true;
                $subForums[] = ForumResource::populate($forum)->toArray();
            }
            $this->sub_forums = $subForums;
        }
        return $this->sub_forums;
    }

    public function getSubs()
    {
        $this->subs = $this->getSubForums();
        return $this->subs;
    }

    public function getDescription()
    {
        if ($this->description !== null) {
            $description = $this->getLocalization()->translate($this->description);
            $this->description = TextFilter::pureText($description, 255, true);
        }
        return $this->description;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_category', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_close', ['type' => ResourceMetadata::BOOL])
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('parent_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $appMenu = [
            ['label' => $l->translate('forums'), 'params' => ['initialQuery' => ['view' => '']]]
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile') { // for mobile version >= 1.4
            $appMenu = [];
        }
        return self::createSettingForResource([
            'acl'           => $permission,
            'schema'        => [
                'definition' => [
                    'sub_forums' => 'forum[]'
                ]
            ],
            'resource_name' => $this->getResourceName(),
            'can_filter'    => false,
            'can_sort'      => false,
            'fab_buttons'   => false,
            'can_search'    => false,
            'search_input'  => [
                'placeholder'   => $l->translate('search_discussions'),
                'search_form'   => true,
                'resource_name' => 'forum_thread',
            ],
            'list_view'     => [
                'item_view' => 'forum',
            ],
            'detail_view'   => [
                'component_name' => 'forum_detail'
            ],
            'app_menu'      => $appMenu,
            'action_menu'   => [
                ['value' => Screen::ACTION_ADD, 'label' => $l->translate('add_thread'), 'acl' => 'can_add_thread'],
            ],
            'queryKey'      => 'forum',
            'title'         => $this->getLocalization()->translate('forums')
        ]);
    }

    public function getUrlMapping($url, $queryArray)
    {
        preg_match('/\/(search|thread)\/*(\d+)?[\/|?]+/', $url, $match);
        if (Phpfox::isModule('forum') && isset($match[1])) {
            switch ($match[1]) {
                case 'thread':
                    return [
                        'routeName' => 'viewItemDetail',
                        'params'    => [
                            'module_name'   => 'forum',
                            'resource_name' => ForumThreadResource::populate([])->getResourceName(),
                            'id'            => isset($match[2]) ? (int)$match[2] : 0,
                        ]
                    ];
                case 'search':
                    //Search in forum
                    $view = isset($queryArray['view']) ? preg_replace('/(my|subscribed|pending|sponsor)?[\-]*(.+)?/', '$1', $queryArray['view']) : '';
                    return [
                        'routeName' => 'module/home',
                        'params'    => [
                            'module_name'   => $this->getResourceName(),
                            'resource_name' => $view == 'new' ?  ForumPostResource::populate([])->getResourceName() : ForumThreadResource::populate([])->getResourceName(),
                            'query' => [
                                'q' => isset($queryArray['search']['search']) ? $queryArray['search']['search'] : '',
                                'view' => $view,
                                'forum[0]' => isset($queryArray['forum_id']) ? (int)$queryArray['forum_id'] : 0
                            ]
                        ]
                    ];
            }
        }
        preg_match('/forum\/*(\d+)?[\/|?]+/', $url, $match);
        if (!empty($match[1])) {
            return [
                'routeName' => 'viewItemDetail',
                'params'    => [
                    'id'            => (int)$match[1],
                    'module_name'   => $this->getModuleName(),
                    'resource_name' => $this->getResourceName(),
                ]
            ];
        }
        return null;
    }
}