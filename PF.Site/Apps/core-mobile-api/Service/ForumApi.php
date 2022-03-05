<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Forums\Service\Forum;
use Apps\Core_Forums\Service\Process;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Forum\ForumForm;
use Apps\Core_MobileApi\Api\Resource\ForumAnnouncementResource;
use Apps\Core_MobileApi\Api\Resource\ForumModeratorResource;
use Apps\Core_MobileApi\Api\Resource\ForumPostResource;
use Apps\Core_MobileApi\Api\Resource\ForumResource;
use Apps\Core_MobileApi\Api\Resource\ForumThankResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\Forum\ForumAccessControl;
use Phpfox;

class ForumApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    const ERROR_FORUM_NOT_FOUND = "Forum not found";

    /**
     * @var Forum
     */
    private $forumService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->forumService = Phpfox::getService('forum');
        $this->processService = Phpfox::getService('forum.process');
        $this->userService = Phpfox::getService('user');
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }

        if (!empty($params['forum']) && $params['forum'] > 0) {
            $this->forumService->id($params['forum']);
        }

        $items = $this->forumService->live()->getForums();
        $results = [];
        foreach ($items as $item) {
            $results[] = $item;
        }
        $this->processRows($results);
        return $this->success($results);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $item = $this->database()->select('*')
            ->from(':forum')
            ->where('forum_id = ' . (int)$id)
            ->execute('getSlaveRow');
        if (!$item) {
            return $this->notFoundError();
        }
        $item['sub_forum'] = $this->forumService->live()->id($id)->getForums();

        /** @var ForumResource $resource */
        $resource = $this->populateResource(ForumResource::class, $item);
        $this->denyAccessUnlessGranted(ForumAccessControl::VIEW, $resource);

        $resource->setExtra($this->getAccessControl()->getPermissions($resource));
        $this->setHyperlinks($resource, true);

        //Get Sub forum of this forum
        return $this->success($resource->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $this->denyAccessUnlessGranted(ForumAccessControl::ADD);

        $forumId = $this->resolver->resolveSingle($params, 'id');
        /** @var ForumForm $form */
        $form = $this->createForm(ForumForm::class, [
            'title'  => $forumId ? 'editing_forum' : 'create_new_forum',
            'method' => $forumId ? 'PUT' : 'POST'
        ]);
        $forum = $this->loadResourceById($forumId, false, true);
        if ($forumId && empty($forum)) {
            return $this->notFoundError();
        }
        $form->setForums($this->getForums($forumId));

        if ($forum) {
            $form->setEditing(true);
            $this->denyAccessUnlessGranted(ForumAccessControl::EDIT, ForumResource::populate($forum));
            $form->assignValues($forum);
        }

        return $this->success($form->getFormStructure());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(ForumAccessControl::ADD);
        /** @var ForumForm $form */
        $form = $this->createForm(ForumForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumResource::populate([])->getResourceName()
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        $values = Phpfox::getService('language')->validateInput($values, 'description', false, false);
        return $this->processService->add($values);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var ForumForm $form */
        $form = $this->createForm(ForumForm::class);
        $forum = $this->loadResourceById($id);
        if (empty($forum)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(ForumAccessControl::EDIT, ForumResource::populate($forum));

        $form->setEditing(true);
        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumResource::populate([])->getResourceName()
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        //TODO Check parent is valid
        $values['edit_id'] = $id;
        $values = Phpfox::getService('language')->validateInput($values, 'description', false, false);
        return $this->processService->update($values);
    }

    private function getForums($editId)
    {
        $forums = $this->forumService->getForums();
        if ($editId) {
            return $this->checkEditingForum($editId, $forums);
        }
        return $forums;
    }

    private function checkEditingForum($editId, $forums)
    {
        foreach ($forums as $key => $forum) {
            if ($forum['forum_id'] == $editId) {
                unset($forums[$key]);
                continue;
            }
            if (!empty($forums[$key]['sub_forum'])) {
                $forums[$key]['sub_forum'] = $this->checkEditingForum($editId, $forums[$key]['sub_forum']);
            }
        }
        return $forums;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $params = $this->resolver
            ->setDefined([
                'delete_type', 'forum'
            ])
            ->setAllowedValues('delete_type', ['1', '2'])
            ->setAllowedTypes('forum', 'int', ['min' => 0])
            ->setRequired(['id'])
            ->resolve(array_merge(['delete_type' => 0], $params))
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('forum.can_delete_forum')) {
            return $this->permissionError();
        }
        $itemId = $params['id'];
        $item = $this->loadResourceById($itemId);
        if (!$itemId || !$item) {
            return $this->notFoundError();
        }
        //delete type
        $aVals = [
            'delete_type'  => $params['delete_type'],
            'new_forum_id' => $params['forum']
        ];
        if ($this->processService->deleteForum($params['id'], $aVals)) {
            return $this->success([], [], $this->getLocalization()->translate('forum_successfully_deleted'));
        }
        return $this->permissionError();
    }

    /**
     * @param $id
     * @param $isForm
     *
     * @return mixed
     *
     * @param $returnResource boolean
     */
    function loadResourceById($id, $returnResource = false, $isForm = false)
    {
        $forum = $this->forumService->getForEdit($id);
        if (empty($forum['forum_id'])) {
            return null;
        }
        if ($isForm) {
            $languages = Phpfox::getService('language')->getAll();
            foreach ($languages as $language) {
                $forum['name_' . $language['language_id']] = $this->getLocalization()->translate($forum['name'], [], $language['language_id']);
                $forum['description_' . $language['language_id']] = $this->getLocalization()->translate($forum['description'], [],
                    $language['language_id']);
            }
        }
        if ($returnResource) {
            return ForumResource::populate($forum);
        }
        return $forum;
    }

    public function processRow($item)
    {
        /** @var ForumResource $resource */
        $resource = $this->populateResource(ForumResource::class, $item);
        $this->setHyperlinks($resource);
        return $resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->displayShortFields()->toArray();
    }

    /**
     * Get for display on activity feed
     *
     * @param array $feed
     * @param array $item detail data from database
     *
     * @return array
     */
    function getFeedDisplay($feed, $item)
    {
        if (empty($item) && !$item = $this->loadResourceById($feed['item_id'])) {
            return null;
        }
        $item['is_in_feed'] = true;
        $resource = $this->populateResource(ForumThreadResource::class, $item);

        return $resource->getFeedDisplay();

    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl = new ForumAccessControl($this->getSetting(), $this->getUser());
    }

    private function setHyperlinks(ForumResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            ForumAccessControl::VIEW   => $this->createHyperMediaLink(ForumAccessControl::VIEW, $resource,
                HyperLink::GET, 'forum/:id', ['id' => $resource->getId()]),
            ForumAccessControl::EDIT   => $this->createHyperMediaLink(ForumAccessControl::EDIT, $resource,
                HyperLink::GET, 'forum/form/:id', ['id' => $resource->getId()]),
            ForumAccessControl::DELETE => $this->createHyperMediaLink(ForumAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'forum/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'threads'      => $this->createHyperMediaLink(ForumAccessControl::VIEW, $resource,
                    HyperLink::GET, 'forum-thread', ['forum' => $resource->getId()]),
                'announcement' => $this->createHyperMediaLink(ForumAccessControl::VIEW, $resource,
                    HyperLink::GET, 'forum-announcement', ['forum' => $resource->getId()]),
                'post'         => $this->createHyperMediaLink(ForumAccessControl::VIEW, $resource,
                    HyperLink::GET, 'forum-post', ['forum' => $resource->getId()]),
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', ForumResource::RESOURCE_NAME);
        $module = 'forum';
        return [
            [
                'path'      => 'forum/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'forum(/*)',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        if (isset($param['api_version_name']) && $param['api_version_name'] != 'mobile') { // for mobile version >= 1.4
            $forumThread = (new ForumThreadResource([]))->getResourceName();
            $forum = (new ForumResource([]))->getResourceName();
            $app = new MobileApp('forum', [
                'title'             => $l->translate('forums'),
                'home_view'         => 'menu',
                'main_resource'     => new ForumThreadResource([]),
                'other_resources'   => [
                    new ForumAnnouncementResource([]),
                    new ForumPostResource([]),
                    new ForumModeratorResource([]),
                    new ForumThankResource([])
                ],
                'category_resource' => [
                    $forum       => new ForumResource([]),
                    $forumThread => new ForumResource([])
                ]
            ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
            $headerButtons[$forumThread] = [
                [
                    'icon'   => 'list-bullet-o',
                    'action' => '@forum/FILTER_BY_FORUM',
                ],
            ];
            $headerButtons[$forum] = [
                [
                    'icon'   => 'list-bullet-o',
                    'action' => '@forum/FILTER_BY_FORUM',
                ],
            ];
            $app->addSetting('home.header_buttons', $headerButtons);
        } else { // for mobile version < 1.4
            $app = new MobileApp('forum', [
                'title'           => $l->translate('forums'),
                'home_view'       => 'menu',
                'main_resource'   => new ForumResource([]),
                'other_resources' => [
                    new ForumAnnouncementResource([]),
                    new ForumThreadResource([]),
                    new ForumPostResource([]),
                    new ForumModeratorResource([]),
                    new ForumThankResource([])
                ],
            ]);
        }

        return $app;
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }

    public function getActions()
    {
        $l = $this->getLocalization();
        return [
            'forum/add-poll'           => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'poll',
                    'resource_name' => 'poll',
                    'formType'      => 'addItem',
                    'query'         => [
                        'in_thread' => 1
                    ]
                ]
            ],
            'forum/remove-poll'        => [
                'method'          => 'delete',
                'url'             => 'mobile/poll/:poll_id',
                'data'            => 'poll_id=:poll',
                'new_state'       => 'poll=null,poll_id=0,can_add_poll=true,can_delete_poll=false',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'forum/sponsor'            => [
                'method'          => 'put',
                'url'             => 'mobile/forum-thread/sponsor/:id',
                'data'            => 'sponsor=1,id=:id',
                'new_state'       => 'order_id=2',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'forum/remove-sponsor'     => [
                'method'          => 'put',
                'url'             => 'mobile/forum-thread/sponsor/:id',
                'data'            => 'sponsor=0,id=:id',
                'new_state'       => 'order_id=0',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'forum/close-thread'       => [
                'method'          => 'put',
                'url'             => 'mobile/forum-thread/close/:id',
                'data'            => 'close=1,id=:id',
                'new_state'       => 'is_closed=1',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'forum/open-thread'        => [
                'method'          => 'put',
                'url'             => 'mobile/forum-thread/close/:id',
                'data'            => 'close=0,id=:id',
                'new_state'       => 'is_closed=0',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'forum/stick-thread'       => [
                'method'          => 'put',
                'url'             => 'mobile/forum-thread/stick/:id',
                'data'            => 'stick=1,id=:id',
                'new_state'       => 'order_id=1',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'forum/unstick-thread'     => [
                'method'          => 'put',
                'url'             => 'mobile/forum-thread/stick/:id',
                'data'            => 'stick=0,id=:id',
                'new_state'       => 'order_id=0',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'forum/subscribe-thread'   => [
                'method'    => 'put',
                'url'       => 'mobile/forum-thread/subscribe/:id',
                'data'      => 'subscribe=1,id=:id',
                'new_state' => 'is_subscribed=1'
            ],
            'forum/unsubscribe-thread' => [
                'method'    => 'put',
                'url'       => 'mobile/forum-thread/subscribe/:id',
                'data'      => 'subscribe=0,id=:id',
                'new_state' => 'is_subscribed=0'
            ],
        ];
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('forum', []);
        $resourceNameForum = ForumResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceNameForum, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceNameForum, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceNameForum, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'forum_detail_header',
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_RESOURCE_SECTION,
                'listEmptyComponent' => [
                    'component' => ScreenSetting::LIST_EMPTY,
                    'label' => $l->translate('no_threads_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action' => [
                        'resource_name' => ForumThreadResource::populate([])->getResourceName(),
                        'module_name'   => 'forum',
                        'value'         => Screen::ACTION_ADD,
                        'label'         => $l->translate('add_new_item'),
                        'use_query' => [
                            'forum_id' => ':forum'
                        ]
                    ]
                ],
                'sections'  => [
                    [
                        'module_name'   => 'forum',
                        'resource_name' => 'forum',
                        'header'        => [
                            'title' => 'sub_forums',
                            'stats' => 'sub_forum'
                        ],
                        'use_query'     => ['forum' => ':id']
                    ],
                    [
                        'module_name'   => 'forum',
                        'resource_name' => ForumAnnouncementResource::populate([])->getResourceName(),
                        'header'        => [
                            'title' => 'announcements',
                            'stats' => 'thread'
                        ],
                        'use_query'     => [
                            'forum'     => ':id',
                            'sort_type' => 'desc'
                        ]
                    ],
                    [
                        'module_name'   => 'forum',
                        'resource_name' => ForumThreadResource::populate([])->getResourceName(),
                        'header'        => [
                            'title' => 'threads',
                            'stats' => 'thread'
                        ],
                        'load_more'     => true,
                        'use_query'     => [
                            'forum'     => ':id',
                            'sort_type' => 'desc'
                        ]
                    ]
                ]
            ],
            'screen_title'                 => $l->translate('forum') . ' > ' . $l->translate('forum') . ' - ' . $l->translate('mobile_detail_page')
        ]);

        $resourceNameThread = ForumThreadResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceNameThread, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceNameThread, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceNameThread, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header', 'title' => 'view_thread'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => ScreenSetting::SMART_RESOURCE_LIST,
                'module_name'     => 'forum',
                'resource_name'   => ForumPostResource::populate([])->getResourceName(),
                'use_query'       => [
                    'thread'          => ':id',
                    'sort_type'       => 'desc',
                    'skip_start_post' => 1
                ],
                'embedComponents' => ['forum_thread_detail_header'],
                'item_props'      => [
                    'show_post_in_thread' => false
                ],
                'showEmptyMessage' => false
            ],
            'screen_title'                 => $l->translate('forum') . ' > ' . $l->translate('forum_thread') . ' - ' . $l->translate('mobile_detail_page')
        ]);

        $resourceNameAnnounce = ForumAnnouncementResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceNameAnnounce, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceNameAnnounce, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceNameAnnounce, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header'],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => 'forum_thread_detail_header'
            ],
            'screen_title'                 => $l->translate('forum') . ' > ' . $l->translate('forum_announcement') . ' - ' . $l->translate('mobile_detail_page')
        ]);

        $screenSetting->addBlock($resourceNameForum, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [

            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_threads'),
                'resource_name' => $resourceNameThread,
                'module_name'   => 'forum',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);
        $resourceNamePost = ForumPostResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceNamePost, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'item_header',
                'title'     => 'view_post'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => ['forum_post_detail']
            ],
            'screen_title'                 => $l->translate('forum') . ' > ' . $l->translate('forum_post') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'forum.index',
            ScreenSetting::MODULE_LISTING => 'forum.index',
            ScreenSetting::MODULE_DETAIL  => 'forum.forum'
        ];
    }
}