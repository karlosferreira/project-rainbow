<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Events\Service\Browse;
use Apps\Core_Events\Service\Category\Category;
use Apps\Core_Events\Service\Event;
use Apps\Core_Events\Service\Process;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Event\EventForm;
use Apps\Core_MobileApi\Api\Form\Event\EventSearchForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\EventCategoryResource;
use Apps\Core_MobileApi\Api\Resource\EventInviteResource;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\FeedResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Event\EventAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Phpfox_Database;


class EventApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    const ERROR_EVENT_NOT_FOUND = "Event not found";

    /**
     * @var Event
     */
    protected $eventService;

    /**
     * @var Category
     */
    protected $categoryService;

    /**
     * @var Process
     */
    protected $processService;
    /**
     * @var Browse
     */
    protected $browserService;
    /**
     * @var \User_Service_User
     */
    protected $userService;

    protected $adProcessService = null;

    public function __construct()
    {
        parent::__construct();
        $this->eventService = Phpfox::getService('event');
        $this->categoryService = Phpfox::getService('event.category');
        $this->processService = Phpfox::getService('event.process');
        $this->browserService = Phpfox::getService('mobile.event_browse_helper');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'event/search-form' => [
                'get' => 'searchForm'
            ],
        ];
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(EventAccessControl::VIEW);

        $params = $this->resolver->setDefined([
            'view',
            'module_id',
            'item_id',
            'category',
            'q',
            'sort',
            'profile_id',
            'limit',
            'page',
            'when',
            'location',
            'bounds'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view',
                ['my', 'pending', 'friend', 'attending', 'may-attend', 'not-attending', 'invites', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month', 'upcoming', 'ongoing'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('category', 'int')
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('album_id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->setAllowedTypes('bounds', 'array')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $sort = $params['sort'];
        $view = $params['view'];

        if (in_array($view, ['sponsor', 'feature'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }

        $parentModule = null;
        if (!empty($params['module_id']) && !empty($params['item_id'])) {
            $parentModule = [
                'module_id' => $params['module_id'],
                'item_id'   => $params['item_id'],
            ];
        }
        $isProfile = $params['profile_id'];
        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                $this->notFoundError();
            }
            $this->search()->setCondition('AND m.user_id = ' . $user['user_id']);
        }
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'event',
            'alias'     => 'm',
            'field'     => 'event_id',
            'table'     => Phpfox::getT('event'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'mobile.event_browse_helper',
            'no_union_from' => true
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'm',
            'when_field' => 'start_time',
            'when_end_field' => 'end_time',
            'location_field' => [
                'latitude_field' => 'location_lat',
                'longitude_field' => 'location_lng'
            ]
        ]);
        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'm.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'm.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'm.total_comment DESC';
                break;
            default:
                $sort = 'm.start_time ASC';
                break;
        }
        switch ($view) {
            case 'pending':
                if (Phpfox::getUserParam('event.can_approve_events')) {
                    $this->search()->setCondition('AND m.view_id = 1');
                } else {
                    $this->permissionError();
                }
                break;
            case 'my':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                } else {
                    $this->permissionError();
                }
                break;
            default:
                if ($parentModule !== null) {
                    $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.module_id = \'' . Phpfox_Database::instance()->escape($parentModule['module_id']) . '\' AND m.item_id = ' . (int)$parentModule['item_id'] . '');
                } else {
                    switch ($view) {
                        case 'attending':
                            $this->browserService->attending(1);
                            break;
                        case 'may-attend':
                            $this->browserService->attending(2);
                            break;
                        case 'not-attending':
                            $this->browserService->attending(3);
                            break;
                        case 'invites':
                            $this->browserService->attending(0);
                            break;
                    }

                    if ($view == 'attending' || $view === 'invites' || $view == 'may-attend') {
                        $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)');
                    } else {
                        if ((Phpfox::getParam('event.event_display_event_created_in_page') || Phpfox::getParam('event.event_display_event_created_in_group'))) {
                            $aModules = [];
                            if (Phpfox::getParam('event.event_display_event_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                                $aModules[] = 'groups';
                            }
                            if (Phpfox::getParam('event.event_display_event_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                                $aModules[] = 'pages';
                            }
                            if (count($aModules)) {
                                $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND (m.module_id IN ("' . implode('","',
                                        $aModules) . '") OR m.module_id = \'event\')');
                            } else {
                                $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.module_id = \'event\'');
                            }
                        } else {
                            $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.item_id = 0');
                        }
                    }
                }
                break;
        }
        //search on map
        $this->search()->setABounds($params['bounds']);
        $this->browserService->callback($parentModule);
        //location
        if ($params['location']) {
            $this->search()->setCondition('AND m.country_iso = \'' . Phpfox_Database::instance()->escape($params['location']) . '\'');
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND m.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }
        //category
        if ($params['category']) {
            $this->browserService->category($params['category']);
            $this->search()->setCondition('AND mcd.category_id = ' . (int)$params['category']);
        }
        $this->browserService->conditions($this->search()->getConditions());
        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);
        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $items = $this->browse()->getRows();
        $items = $this->combineRows($items);

        $this->processRows($items);
        return $this->success($items);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);


        $item = $this->eventService->getEvent($id);
        if (!$item) {
            $this->notFoundError();
        }
        $item['is_detail'] = true;
        $resource = $this->populateResource(EventResource::class, $item);
        $this->denyAccessUnlessGranted(EventAccessControl::VIEW, $resource);

        $this->setHyperlinks($resource, true);

        // Increment the view counter
        $updateCounter = false;

        if (Phpfox::isModule('track')) {
            if (!Phpfox::getUserBy('is_invisible')) {
                if (!$item['is_viewed']) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('event', $item['event_id']);
                } else {
                    if (!setting('track.unique_viewers_counter')) {
                        $updateCounter = true;
                        Phpfox::getService('track.process')->add('event', $item['event_id']);
                    } else {
                        Phpfox::getService('track.process')->update('event', $item['event_id']);
                    }
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->processService->updateCounter($item['event_id'], 'total_view');
        }

        $resource->setExtra($this->getAccessControl()->getPermissions($resource));

        return $this->success($resource->lazyLoad(['user'])->loadFeedParam()->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var EventForm $form */
        $form = $this->createForm(EventForm::class, [
            'title'  => 'create_new_event',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('event')
        ]);
        $form->setCategories($this->getCategories());
        $event = $this->loadResourceById($editId, true);
        if ($editId && empty($event)) {
            return $this->notFoundError();
        }

        if ($event) {
            $this->denyAccessUnlessGranted(EventAccessControl::EDIT, $event);
            $form->setTitle('edit_event')
                ->setAction(UrlUtility::makeApiUrl('event/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($event);
        } else {
            $this->denyAccessUnlessGranted(EventAccessControl::ADD);
            if (($iFlood = $this->getSetting()->getUserSetting('event.flood_control_events')) !== 0) {
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field'      => 'time_stamp', // The time stamp field
                        'table'      => Phpfox::getT('event'), // Database table we plan to check
                        'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];

                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error($this->getLocalization()->translate('you_are_creating_an_event_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }
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
        $this->denyAccessUnlessGranted(EventAccessControl::ADD);
        /** @var EventForm $form */
        $form = $this->createForm(EventForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => EventResource::populate([])->getResourceName()
                ], [], $this->localization->translate('event_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    /**
     * @param $values
     *
     * @return int
     */
    protected function processCreate($values)
    {
        if (($iFlood = $this->getSetting()->getUserSetting('event.flood_control_events')) !== 0) {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp', // The time stamp field
                    'table'      => Phpfox::getT('event'), // Database table we plan to check
                    'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->error($this->getLocalization()->translate('you_are_creating_an_event_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }

        $this->convertSubmitForm($values);
        return $this->processService->add($values, $values['module_id'], $values['item_id']);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var EventForm $form */
        $form = $this->createForm(EventForm::class);
        $event = $this->loadResourceById($id, true);
        if (empty($event)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(EventAccessControl::EDIT, $event);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => EventResource::populate([])->getResourceName()
                ], [], $this->localization->translate('event_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    /**
     * @param $id
     * @param $values
     *
     * @return bool
     */
    protected function processUpdate($id, $values)
    {
        $this->convertSubmitForm($values, true);
        $values['event_id'] = $id;
        return $this->processService->update($id, $values);
    }

    protected function convertSubmitForm(&$vals, $edit = false)
    {
        if (isset($vals['categories'])) {
            $vals['category'] = $vals['categories'];
        }
        if (!isset($vals['isrepeat'])) {
            $vals['isrepeat'] = '-1';
        }
        $inValid = [];
        $startTime = (new \DateTime($vals['start_time']));
        if (empty($startTime)) {
            $inValid[] = 'start_time';
        }

        $endTime = (new \DateTime($vals['end_time']));
        if (empty($endTime)) {
            $inValid[] = 'end_time';
        }
        if (!empty($inValid)) {
            return $this->validationParamsError($inValid);
        }
        $vals['start_month'] = $startTime->format('m');
        $vals['start_day'] = $startTime->format('d');
        $vals['start_year'] = $startTime->format('Y');
        $vals['start_hour'] = $startTime->format('H');
        $vals['start_minute'] = $startTime->format('i');

        $vals['end_month'] = $endTime->format('m');
        $vals['end_day'] = $endTime->format('d');
        $vals['end_year'] = $endTime->format('Y');
        $vals['end_hour'] = $endTime->format('H');
        $vals['end_minute'] = $endTime->format('i');
        if (!empty($vals['text'])) {
            $vals['description'] = $vals['text'];
        } else {
            $vals['description'] = '';
        }
        if (!empty($vals['file'])) {
            if (!$edit) {
                if (!empty($vals['file']['temp_file'])) {
                    $vals['temp_file'] = $vals['file']['temp_file'];
                }
            } else {
                if ($vals['file']['status'] == FileType::NEW_UPLOAD || $vals['file']['status'] == FileType::CHANGE) {
                    $vals['temp_file'] = $vals['file']['temp_file'];
                } else if ($vals['file']['status'] == FileType::REMOVE) {
                    $vals['remove_photo'] = 1;
                }
            }
        }
        if (!$edit) {
            if (empty($vals['module_id'])) {
                $vals['module_id'] = 'event';
            }
            if (empty($vals['item_id'])) {
                $vals['item_id'] = 0;
            }
        }
        if (!empty($vals['attachment'])) {
            $vals['attachment'] = implode(",", $vals['attachment']);
        }
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $itemId = $this->resolver->resolveId($params);
        $item = $this->loadResourceById($itemId);
        if (!$itemId || !$item) {
            return $this->notFoundError();
        }

        if (Phpfox::getUserParam('event.can_access_event') && $this->processService->delete($itemId)) {
            return $this->success([], [], $this->getLocalization()->translate('successfully_deleted_event'));
        }
        return $this->permissionError();

    }


    protected function getCategories()
    {
        return $this->categoryService->getForBrowse();
    }

    /**
     * @param      $id
     * @param bool $returnResource
     *
     * @return array|int|string
     */
    function loadResourceById($id, $returnResource = false)
    {
        $event = $this->eventService->getEvent($id);
        if (empty($event['event_id'])) {
            return null;
        }
        if ($returnResource) {
            $event['is_detail'] = true;
            return EventResource::populate($event);
        }
        return $event;
    }

    public function processRow($item)
    {
        /** @var EventResource $resource */
        $resource = $this->populateResource(EventResource::class, $item);
        $this->setHyperlinks($resource);

        $shortFields = [];
        $view = $this->request()->get('view');
        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'description', 'image', 'statistic', 'user', 'id', 'start_time', 'end_time', 'location', 'link', 'is_sponsor', 'is_featured'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }

        return $resource
            ->setViewMode(ResourceBase::VIEW_LIST)
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->displayShortFields()
            ->toArray($shortFields);
    }

    /**
     * @param $items
     *
     * @return array
     */
    public function combineRows($items)
    {
        $results = [];
        foreach ($items as $key => $item) {
            $results = array_merge($results, $item);
        }
        return $results;
    }

    public static function checkPermission($item)
    {
        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $item['user_id'])) {
            return false;
        }
        if ($item['item_id'] && Phpfox::hasCallback($item['module_id'], 'viewEvent')) {
            if (isset($item['module_id']) && Phpfox::isModule($item['module_id']) && Phpfox::hasCallback($item['module_id'],
                    'checkPermission')
            ) {
                if (!Phpfox::callback($item['module_id'] . '.checkPermission', $item['item_id'],
                    'event.view_browse_events')
                ) {
                    return false;
                }
            }
        }
        if (Phpfox::isModule('privacy')) {
            if (!Phpfox::getService('privacy')->check('event', $item['event_id'], $item['user_id'], $item['privacy'],
                $item['is_friend'], true)
            ) {
                return false;
            }
        }
        return true;
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
        $event = EventResource::populate($item)->getFeedDisplay();
        $event['time_format'] = Phpfox::getParam('event.event_time_format');
        return $event;
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new EventAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get("module_id");
        $itemId = $this->request()->get("item_id");

        if ($moduleId) {
            $context = AppContextFactory::create($moduleId, $itemId);
            if ($context === null) {
                return $this->notFoundError();
            }
            $this->accessControl->setAppContext($context);
        }
        return true;
    }

    public function getPostTypes($id)
    {

        if (empty($this->loadResourceById($id)) || !Phpfox::isModule('feed')) {
            return [];
        }
        $postOptions = [];
        $userId = $this->getUser()->getId();

        if (!$userId || !$this->getSetting()->getUserSetting('event.can_post_comment_on_event')) {
            return [];
        }
        $postOptions[] = (new CoreApi())->getPostOption('status');

        if (Phpfox::isAppActive('Core_Photos') && $this->getSetting()->getUserSetting('photo.can_upload_photos')) {
            $postOptions[] = (new CoreApi())->getPostOption('photo');
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_eventapi_getposttype_end')) ? eval($sPlugin) : false);

        return $postOptions;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(EventAccessControl::VIEW);
        /** @var EventSearchForm $form */
        $form = $this->createForm(EventSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('event')
        ]);

        return $this->success($form->getFormStructure());
    }

    /**
     * @param EventResource $resource
     * @param bool          $includeLinks
     */
    private function setHyperlinks(EventResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            EventAccessControl::VIEW   => $this->createHyperMediaLink(EventAccessControl::VIEW, $resource,
                HyperLink::GET, 'event/:id', ['id' => $resource->getId()]),
            EventAccessControl::DELETE => $this->createHyperMediaLink(EventAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'event/:id', ['id' => $resource->getId()]),
            EventAccessControl::EDIT   => $this->createHyperMediaLink(EventAccessControl::EDIT, $resource,
                HyperLink::GET, 'event/form/:id', ['id' => $resource->getId()]),
        ]);
        if ($includeLinks) {
            $resource->setLinks([
//                'likes' => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'like',['item_id' => $resource->getId(),'item_type' => 'event'])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', EventResource::RESOURCE_NAME);
        $module = 'event';
        return [
            [
                'path'      => 'event/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'event/category/:category(/*), event/tag/:tag',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'event/add',
                'routeName' => ROUTE_MODULE_ADD,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'event(/*)',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function getActions()
    {
        return [
            'event/rsvp/attending'    => [
                'method'    => 'put',
                'url'       => 'mobile/event/rsvp/:id',
                'data'      => 'id,rsvp=1',
                'new_state' => 'rsvp=1, tracker=1',
            ],
            'event/rsvp/maybe'        => [
                'method'    => 'put',
                'url'       => 'mobile/event/rsvp/:id',
                'data'      => 'id,rsvp=2',
                'new_state' => 'rsvp=2, tracker=2',
            ],
            'event/rsvp/notAttending' => [
                'method'    => 'put',
                'url'       => 'mobile/event/rsvp/:id',
                'data'      => 'id,rsvp=3',
                'new_state' => 'rsvp=3, tracker=3',
            ],
            'event/guest_list'        => [
                'routeName' => 'event/guest'
            ]
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('event', [
            'title'             => $l->translate('events'),
            'home_view'         => 'menu',
            'main_resource'     => new EventResource([]),
            'category_resource' => new EventCategoryResource([]),
            'other_resources'   => [
                new FeedResource([]),
                new EventInviteResource([])
            ]
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $eventResourceName = (new EventResource([]))->getResourceName();
        $headerButtons[$eventResourceName] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ],
        ];
        if ($this->getAccessControl()->isGranted(EventAccessControl::ADD)) {
            $headerButtons[$eventResourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => $eventResourceName]
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    public function searchFriendFilter($id, $friends)
    {
        $aInviteCache = Phpfox::getService('event')->isAlreadyInvited($id, $friends);
        if (is_array($aInviteCache)) {
            foreach ($friends as $iKey => $friend) {
                if (isset($aInviteCache[$friend['user_id']])) {
                    $friends[$iKey]['is_active'] = $aInviteCache[$friend['user_id']];
                }
            }
        }
        return $friends;
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var EventResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(EventAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('event_has_been_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(EventAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('event_successfully_featured') : $this->getLocalization()->translate('event_successfully_unfeatured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $isSponsorFeed = $this->resolver->resolveSingle($params, 'is_sponsor_feed', null, [], 0);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        /** @var EventResource $item */
        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($isSponsorFeed) {
            //Support un-sponsor in feed
            $this->denyAccessUnlessGranted(EventAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('event', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(EventAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(EventAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }

            if ($this->processService->sponsor($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('event');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module'  => 'event',
                        'item_id' => $id,
                        'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('event', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('event_successfully_sponsored') : $this->getLocalization()->translate('event_successfully_un_sponsored'));
            }
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array|bool
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->eventService->getRandomSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * @param $sponsorItems
     */
    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'event');
            }
        }
    }

    /**
     * Get featured items
     *
     * @param $params
     *
     * @return mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        list(, $featuredItems) = $this->eventService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('event', []);
        $resourceName = EventResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME, [
            'header'       => [
                'component' => 'module_header'
            ],
            'content'      => [
                'component' => ScreenSetting::SMART_RESOURCE_LIST,
                'initialQuery' => [
                    'when' => $this->getSetting()->getAppSetting('event.event_default_sort_time')
                ]
            ],
            'mainBottom'   => [
                'component' => ScreenSetting::SORT_FILTER_FAB
            ],
            'no_ads'       => false,
            'screen_title' => $l->translate('events') . ' > ' . $l->translate('event') . ' - ' . $l->translate('mobile_home_page'),
            'footer'       => [
                'component' => 'mass_action'
            ]
        ]);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);
        $embedComponents = [
            'stream_event_header_info',
            'stream_profile_description'
        ];
        if (Phpfox::isModule('feed')) {
            $embedComponents[] = 'stream_composer';
        }
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component'  => 'item_header',
                'transition' => 'transparent'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => ScreenSetting::STREAM_PROFILE_FEEDS,
                'embedComponents' => $embedComponents
            ],
            ScreenSetting::LOCATION_RIGHT  => [
                'component'     => 'simple_list_block',
                'module_name'   => 'event',
                'resource_name' => $resourceName,
                'title'         => 'events',
                'query'         => ['sort' => 'upcoming']
            ],
            'screen_title'                 => $l->translate('events') . ' > ' . $l->translate('event') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addSetting($resourceName, 'event/guest', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'guests'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_TABS,
                'tabs'      => [
                    [
                        'label'         => 'attending',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'event',
                        'resource_name' => EventInviteResource::populate([])->getResourceName(),
                        'item_view'     => 'event_invite',
                        'search'        => true,
                        'use_query'     => ['rsvp_id' => 1, 'event_id' => ':id']
                    ],
                    [

                        'label'         => 'maybe',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'event',
                        'resource_name' => EventInviteResource::populate([])->getResourceName(),
                        'item_view'     => 'event_invite',
                        'search'        => true,
                        'use_query'     => ['rsvp_id' => 2, 'event_id' => ':id']
                    ],
                    [

                        'label'         => 'awaiting',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'event',
                        'resource_name' => EventInviteResource::populate([])->getResourceName(),
                        'item_view'     => 'event_invite',
                        'search'        => true,
                        'use_query'     => ['rsvp_id' => 0, 'event_id' => ':id']
                    ],
                    [

                        'label'         => 'not_attending',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'event',
                        'resource_name' => EventInviteResource::populate([])->getResourceName(),
                        'item_view'     => 'event_invite',
                        'search'        => true,
                        'use_query'     => ['rsvp_id' => 3, 'event_id' => ':id']
                    ]
                ],
            ],
            'screen_title'                 => $l->translate('events') . ' > ' . $l->translate('guests'),
        ]);

        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_events'),
                'resource_name' => $resourceName,
                'module_name'   => 'event',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_event'),
                'resource_name' => $resourceName,
                'module_name'   => 'event',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'event.index',
            ScreenSetting::MODULE_LISTING => 'event.index',
            ScreenSetting::MODULE_DETAIL  => 'event.view'
        ];
    }

    /**
     * Moderation items
     *
     * @param $params
     *
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    public function moderation($params)
    {
        $this->resolver
            ->setAllowedValues('action', [Screen::ACTION_APPROVE_ITEMS, Screen::ACTION_DELETE_ITEMS, Screen::ACTION_FEATURE_ITEMS, Screen::ACTION_REMOVE_FEATURE_ITEMS]);
        $action = $this->resolver->resolveSingle($params, 'action', 'string', [], '');
        $ids = $this->resolver->resolveSingle($params, 'ids', 'array', [], []);
        if (!count($ids)) {
            return $this->missingParamsError(['ids']);
        }

        $data = ['ids' => $ids];
        $sMessage = '';
        switch ($action) {
            case Screen::ACTION_APPROVE_ITEMS:
                $this->denyAccessUnlessGranted(EventAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->approve($id)) {
                        unset($ids[$key]);
                    }
                }
                $data = array_merge($data, ['is_pending' => false]);
                $sMessage = $this->getLocalization()->translate('event_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(EventAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = array_merge($data, ['is_featured' => !!$value]);
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('event_s_successfully_featured') : $this->getLocalization()->translate('event_s_successfully_unfeatured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(EventAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('event_s_successfully_deleted');
                break;
        }
        return $this->success($data, [], $sMessage);
    }
}