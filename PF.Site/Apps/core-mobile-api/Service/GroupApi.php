<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Group\GroupForm;
use Apps\Core_MobileApi\Api\Form\Group\GroupSearchForm;
use Apps\Core_MobileApi\Api\Resource\BlogResource;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\FeedResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\GroupAdminResource;
use Apps\Core_MobileApi\Api\Resource\GroupCategoryResource;
use Apps\Core_MobileApi\Api\Resource\GroupInfoResource;
use Apps\Core_MobileApi\Api\Resource\GroupInviteResource;
use Apps\Core_MobileApi\Api\Resource\GroupMemberResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Resource\GroupSectionResource;
use Apps\Core_MobileApi\Api\Resource\GroupTypeResource;
use Apps\Core_MobileApi\Api\Resource\GroupWidgetResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Resource\MusicAlbumResource;
use Apps\Core_MobileApi\Api\Resource\MusicSongResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\PhotoAlbumResource;
use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Resource\VideoResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\Core_MobileApi\Service\Helper\BrowseHelper;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\PHPfox_Groups\Service\Category;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Apps\PHPfox_Groups\Service\Type;
use Phpfox;
use Phpfox_Plugin;


class GroupApi extends AbstractResourceApi implements MobileAppSettingInterface, ActivityFeedInterface
{

    /**
     * @var Facade
     */
    protected $facadeService;

    /**
     * @var Groups
     */
    protected $groupService;
    /**
     * @var Process
     */
    protected $processService;
    /**
     * @var Category
     */
    protected $categoryService;
    /**
     * @var Type
     */
    protected $typeService;
    /**
     * @var \User_Service_User
     */
    protected $userService;

    /**
     * @var \Apps\Core_BetterAds\Service\Process
     */
    protected $adProcessService = null;

    /**
     * GroupApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('groups.facade');
        $this->groupService = Phpfox::getService('groups');
        $this->userService = Phpfox::getService('user');
        $this->typeService = Phpfox::getService('groups.type');
        $this->categoryService = Phpfox::getService('groups.category');
        $this->processService = Phpfox::getService('groups.process');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'groups/profile-menu'  => [
                'get' => 'getProfileMenus'
            ],
            'group-home'           => [
                'get' => 'getGroupHome'
            ],
            'groups/search-form'   => [
                'get' => 'searchForm'
            ],
            'groups/post-type/:id' => [
                'get'   => 'getPostTypes',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'groups/avatar/:id'    => [
                'post' => 'uploadAvatar'
            ],
            'groups/cover/:id'     => [
                'post'   => 'uploadCover',
                'delete' => 'removeCover'
            ],
            'groups/reassign-owner/form/:id' => [
                'get'   => 'getReassignOwnerForm',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'groups/reassign-owner/:id' => [
                'post'   => 'reassignOwner',
                'where' => [
                    'id' => '(\d+)'
                ]
            ]
        ];
    }

    function getGroupHome($params = [])
    {
        return $this->findAll($params, false);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */

    function findAll($params = [], $isSearch = true)
    {
        $params = $this->resolver->setDefined([
            'view', 'category', 'type', 'q', 'sort', 'limit', 'page', 'profile_id', 'when'
        ])
            ->setAllowedValues('sort', ['latest', 'most_liked'])
            ->setAllowedValues('view', ['my', 'pending', 'friend', 'sponsor', 'feature', 'joined'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('pf_group_browse')) {
            return $this->permissionError();
        }
        $params['is_search'] = $isSearch;
        $sort = $params['sort'];
        $view = $params['view'];

        if (in_array($view, ['sponsor', 'feature'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }

        //When search sub category
        if ($params['type'] && !is_numeric($params['type'])) {
            if (preg_match('/category_/', $params['type'])) {
                $isSubCategory = true;
                $params['category'] = str_replace('category_', '', $params['type']);
            } else {
                $isSubCategory = false;
                $params['type'] = str_replace('type_', '', $params['type']);
            }
        } else {
            $isSubCategory = empty($params['type']) && !empty($params['category']);
        }
        if ($params['category'] && !is_numeric($params['category'])) {
            $params['category'] = str_replace('category_', '', $params['category']);
        }
        $isValidCategory = false;
        $isProfile = $params['profile_id'];
        $user = null;
        if ($isProfile) {
            $isValidCategory = true;
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
        }
        $browseParams = [
            'module_id' => 'groups',
            'alias'     => 'pages',
            'field'     => 'page_id',
            'table'     => Phpfox::getT('pages'),
            'hide_view' => ['pending', 'my'],
            'select'    => 'pages_type.name as type_name , \'' . $view . '\' as current_view',
            'service'   => 'groups.browse',
        ];
        $userGroupIds = null;
        if ($this->getSetting()->getAppSetting('core.friends_only_community')) {
            if($view != 'friend') {
                $userGroupIds = Phpfox::getService('groups')->getAllGroupIdsOfMember($this->getUser()->getId());
                if ($userGroupIds && count($userGroupIds)) {
                    Phpfox::getService('groups.browse')->groupIds($userGroupIds);
                }
            }
        }
        if ($isSearch) {
            $this->search()->setSearchTool([
                'table_alias' => 'pages'
            ]);
            switch ($view) {
                case 'my':
                    if (Phpfox::isUser()) {
                        $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . Phpfox::getUserId());
                    }
                    break;
                case 'joined':
                    if (Phpfox::isUser()) {
                        $groupIds = '0';
                        if ($userGroupIds === null) {
                            $userGroupIds = $this->groupService->getAllGroupIdsOfMember(isset($user['user_id']) ? $user['user_id'] : $this->getUser()->getId());
                            if ($userGroupIds && count($userGroupIds)) {
                                Phpfox::getService('groups.browse')->groupIds($userGroupIds);
                            }
                        }
                        if ($userGroupIds && count($userGroupIds)) {
                            $groupIds = implode(',', $userGroupIds);
                        }
                        $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.page_id IN (' . $groupIds . ')');
                    } else {
                        return $this->permissionError();
                    }
                    break;
                case 'pending':
                    $isValidCategory = true;
                    if (Phpfox::isUser()) {
                        if ($this->facadeService->getUserParam('can_approve_pages')) {
                            $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 1');
                        } else {
                            return $this->permissionError();
                        }
                    }
                    break;
                default:
                    if (Phpfox::getUserParam('privacy.can_view_all_items')) {
                        $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0');
                    } else {
                        $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.privacy IN(%PRIVACY%)');
                    }
                    break;
            }
            if ($isSubCategory) {
                $isValidCategory = true;
                $category = $this->categoryService->getById($params['category']);
                if ($category) {
                    $this->search()->setCondition('AND pages.category_id = ' . (int)$category['category_id']);
                }
            }
            if (!empty($params['type'])) {
                $isValidCategory = true;
                $type = $this->typeService->getById($params['type']);
            }
            if (isset($type) && isset($type['type_id'])) {
                $this->search()->setCondition('AND pages.type_id = ' . (int)$type['type_id']);
            }

            if (isset($type) && isset($type['category_id'])) {
                $this->search()->setCondition('AND pages.category_id = ' . (int)$type['category_id']);
            } elseif (isset($type) && isset($category) && isset($category['category_id'])) {
                $this->search()->setCondition('AND pages.category_id = ' . (int)$category['category_id']);
            }

            if ($isProfile) {
                if ($view != 'all') {
                    $this->search()->setCondition('AND pages.user_id = ' . (int)$user['user_id']);
                }
                if ($user['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('core.can_view_private_items')) {
                    $this->search()->setCondition('AND pages.reg_method <> 2');
                }
            }
            // search
            if (!empty($params['q'])) {
                $isValidCategory = true;
                $this->search()->setCondition('AND pages.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
            }

            // sort
            switch ($sort) {
                case 'most_liked':
                    $sort = 'pages.total_like DESC';
                    break;
                default:
                    $sort = 'pages.time_stamp DESC';
                    break;
            }
        }
        if (!empty($params['is_search'])) {
            $isValidCategory = true;
        }
        if ($isValidCategory) {
            if ($view != 'pending') {
                $this->search()->setCondition(Phpfox::callback('groups.getExtraBrowseConditions', 'pages'));
            }
            $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);
            $this->browse()->params($browseParams)->execute(function (BrowseHelper $browse) {
                $browse->database()->select('pages_type.name as type_name, ')->join(':pages_type', 'pages_type',
                    'pages_type.type_id = pages.type_id AND pages_type.item_type = 1');
            });
            $items = $this->browse()->getRows();
            $this->processRows($items);
        } else {
            $limit = Phpfox::getParam('groups.groups_limit_per_category', 0);
            $items = $this->getForBrowse(($view == 'my' ? $this->getUser()->getId() : ($isProfile ? $user['user_id'] : null)), $limit);

            NameResource::instance()->getApiServiceByResourceName(GroupTypeResource::RESOURCE_NAME)->processRows($items);
        }

        if (!$isSearch) {
            $data = [];
            foreach ($items as $index => $item) {
                $children = isset($item['items']) ? $item['items'] : [];
                if (!isset($children) || !count($children))
                    continue;

                unset($item['items']);
                $data[] = [
                    'id'    => $index,
                    'main'  => $item,
                    'items' => $children,
                ];
            }
            return $this->success($data);
        }
        return $this->success($items);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        if (!Phpfox::getUserParam('pf_group_browse')) {
            return $this->permissionError();
        }
        $item = $this->groupService->getForView($params['id']);
        if (!$item || $item['view_id'] == '2' || ($item['view_id'] != '0'
                && !$this->groupService->canModerate() && ($this->getUser()->getId() != $item['user_id']))) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('groups', $item['page_id'], $item['user_id'],
                $item['privacy'], (isset($item['is_friend']) ? $item['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        if ($item['reg_method'] == 2 && !$this->groupService->isMember($item['page_id'])
            && !Phpfox::isAdmin() && !$this->groupService->isInvited($item['page_id']) && $this->getUser()->getId() != $item['user_id']) {
            return $this->permissionError();
        }
        $item['is_detail'] = true;
        /** @var GroupResource $resource */
        $resource = $this->populateResource(GroupResource::class, $item);
        $this->setHyperLinks($resource, true);
        return $this->success(
            $resource
                ->setExtra($this->getAccessControl()->getPermissions($resource))
                ->lazyLoad(['user'])
                ->toArray()
        );
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var GroupForm $form */
        $form = $this->createForm(GroupForm::class, [
            'title'  => 'add_new_group',
            'action' => UrlUtility::makeApiUrl('groups'),
            'method' => 'POST'
        ]);
        $form->setCategories($this->getCategories());
        /** @var GroupResource $group */

        $group = $editId ? $this->loadResourceById($editId, true) : null;
        if ($editId && empty($group)) {
            return $this->notFoundError();
        }
        if ($group) {
            $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $group);
            $form->setTitle('editing_group')
                ->setAction(UrlUtility::makeApiUrl('groups/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($group);
        } else {
            $this->denyAccessUnlessGranted(GroupAccessControl::ADD);
        }

        return $this->success($form->getFormStructure());
    }

    public function getCategories()
    {
        $allTypeCategories = $this->typeService->get();
        return $this->convertTypeToForm($allTypeCategories);
    }

    private function convertTypeToForm($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $id = isset($value['item_type']) ? "type_{$value['type_id']}" : "category_{$value['category_id']}";
            $result[$id] = [
                'category_id' => $id,
                'name'        => $value['name']
            ];
            if (!empty($value['categories'])) {
                $result[$id]['categories'] = $this->convertTypeToForm($value['sub']);
            }
        }
        return $result;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(GroupAccessControl::ADD);
        /** @var GroupForm $form */
        $form = $this->createForm(GroupForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => GroupResource::populate([])->getResourceName()
                ], [], $this->localization->translate('group_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        $type = null;
        if (!empty($values['type_category'])) {
            foreach ($values['type_category'] as $val) {
                if (strpos($val, 'type_') > -1) {
                    $values['type'] = str_replace('type_', '', $val);
                } elseif (strpos($val, 'category_') > -1) {
                    $values['category'] = str_replace('category_', '', $val);
                }
            }
        }
        if (!empty($values['type'])) {
            $type = NameResource::instance()->getApiServiceByResourceName(GroupTypeResource::RESOURCE_NAME)->loadResourceById($values['type']);
            if (empty($type)) {
                //Not valid group type
                return $this->notFoundError($this->getLocalization()->translate('group_type_not_found'));
            }
            $values['type_id'] = $values['type'];
        } else {
            return $this->error($this->getLocalization()->translate('group_type_is_required'));
        }
        if (!empty($values['category'])) {
            $category = NameResource::instance()->getApiServiceByResourceName(GroupCategoryResource::RESOURCE_NAME)->loadResourceById($values['category'], false, $values['type']);
            if (empty($category)) {
                //Not valid group category
                return $this->notFoundError($this->getLocalization()->translate('group_category_does_not_exist_or_does_not_belonging_to_type_name', ['name' => isset($type['name']) ? $type['name'] : '']));
            }
            $values['category_id'] = $values['category'];
        }
        return $this->processService->add($values);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        // TODO: Implement update() method.
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
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $itemId = $params['id'];
        $item = $this->loadResourceById($itemId);
        if ($itemId < 1 || !$item) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('pf_group_browse') && ($item['user_id'] == Phpfox::getUserId() || Phpfox::getUserParam('groups.can_delete_all_groups')) && $this->processService->delete($params['id'])) {
            return $this->success([], [], $this->getLocalization()->translate('group_successfully_deleted'));
        }
        return $this->permissionError();
    }

    /**
     * @param $id
     * @param $returnResource
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->database()->select('p.*, pu.vanity_url, ' . Phpfox::getUserField())
            ->from(':pages', 'p')
            ->join(':user', 'u', 'p.user_id = u.user_id')
            ->leftJoin(':pages_url', 'pu', 'pu.page_id = p.page_id')
            ->where('p.item_type = ' . $this->facadeService->getItemTypeId() . ' AND p.page_id = ' . (int)$id)
            ->execute('getSlaveRow');
        if (empty($item['page_id'])) {
            return null;
        }
        if ($returnResource) {
            $item['is_form'] = true;
            return GroupResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        // Get Permission of group. Get sponsor/feature doesn't have current_view.
        $this->groupService->getActionsPermission($item, isset($item['current_view']) ? $item['current_view'] : '');
        /** @var GroupResource $resource */
        $resource = $this->populateResource(GroupResource::class, $item);
        $this->setHyperLinks($resource);

        $shortFields = [];
        $view = $this->request()->get('view');

        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'image', 'covers', 'statistic', 'type_name', 'user', 'id', 'is_sponsor', 'is_featured'
            ];

            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }

        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new GroupAccessControl($this->getSetting(), $this->getUser());
    }

    public function getProfileMenus($id)
    {
        defined('PHPFOX_IS_PAGES_VIEW') or define('PHPFOX_IS_PAGES_VIEW', true);

        $page = $this->loadResourceById($id);
        if (empty($page['page_id'])) {
            return [];
        }
        $query = ['item_id' => $id, 'module_id' => 'groups'];

        $defaultMenu = $this->defaultProfileMenu();

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_groupapi_getprofilemenu_start')) ? eval($sPlugin) : false);

        $result = [];
        $local = $this->getLocalization();
        if ($aIntegrate = storage()->get($this->facadeService->getItemType() . '_integrate')) {
            $aIntegrate = (array)$aIntegrate->value;
        } else {
            $aIntegrate = [];
        }
        $cacheId = $this->cache()->set('groups_' . $page['page_id'] . '_menus');
        if (($groupMenus = $this->cache()->get($cacheId)) === false) {
            $groupMenus = $this->groupService->getGroupMenu($page['page_id']);
            $this->cache()->save($cacheId, $groupMenus);
        }
        $groupMenuName = array_column($groupMenus, 'menu_name');
        $nextOrdering = count($defaultMenu) + 1;
        foreach ($defaultMenu as $menu) {
            if (array_key_exists($menu['module_id'], $aIntegrate) && !$aIntegrate[$menu['module_id']]) {
                continue;
            }
            if (!empty($menu['perm']) && !$this->getAccessControl()->isGrantedSetting($menu['perm'])) {
                continue;
            }
            if (!empty($menu['page_perm']) && !$this->groupService->hasPerm($id, $menu['page_perm'])) {
                continue;
            }
            $label = $local->translate($menu['label']);
            $actionButtons = [];
            $moduleId = isset($menu['app_module_id']) ? $menu['app_module_id'] : $menu['module_id'];
            $index = array_search($moduleId, $groupMenuName);
            if ($index !== false) {
                if (!$groupMenus[$index]['is_active']) {
                    continue;
                }
                $ordering = $groupMenus[$index]['ordering'];
            } else {
                $ordering = $nextOrdering;
                $nextOrdering++;
            }
            if (empty($menu['disable_add']) && Phpfox::hasCallback($menu['module_id'], 'getGroupSubMenu')) {
                $callback = Phpfox::callback($menu['module_id'] . '.getGroupSubMenu', $page);
                if ($callback !== null && count($callback)) {
                    $actionButtons[] = [
                        'icon'   => 'plus',
                        'action' => Screen::ACTION_ADD,
                        'params' => [
                            'resource_name' => str_replace('-', '_', $menu['resource_name']),
                            'module_name'   => isset($menu['app_module_id']) ? $menu['app_module_id'] : $menu['module_id'],
                            'query'         => [
                                'item_id'   => $page['page_id'],
                                'module_id' => 'groups'
                            ]
                        ],
                    ];
                }
            }
            $result[] = [
                'label'  => $label,
                'ordering' => (int)$ordering,
                'path'   => "{$menu['resource_name']}/list-item",
                'params' => [
                    'headerTitle'        => $local->translate('full_name_s_item', ['full_name' => $page['title'], 'item' => $label]),
                    'headerRightButtons' => $actionButtons,
                    'query'              => $query
                ]
            ];
        }
        /** @var GroupWidgetApi $widgetApi */
        $widgetApi = (new ApiVersionResolver())->getApiServiceWithVersion(GroupWidgetResource::RESOURCE_NAME, [
            'api_version_name' => Phpfox::getLib('request')->get('req2')
        ]);
        /** @var array $groupWidgets */
        $groupWidgets = $widgetApi->getWidgets($id, GroupWidgetApi::TYPE_MENU);
        if (count($groupWidgets)) {
            $widgetApi->processRows($groupWidgets);
            foreach($groupWidgets as $key => $widget) {
                $index = array_search($widget['url_title'], $groupMenuName);
                if ($index !== false) {
                    if (!$groupMenus[$index]['is_active']) {
                        continue;
                    }
                    $ordering = $groupMenus[$index]['ordering'];
                } else {
                    $ordering = $nextOrdering;
                    $nextOrdering++;
                }
                $result[] = [
                    'label'  => $widget['menu_title'],
                    'ordering' => $ordering,
                    'path'   => "{$widget['module_name']}/{$widget['resource_name']}/{$widget['id']}",
                    'params' => [
                        'headerTitle'        => $widget['title'],
                        'query'              => $query
                    ]
                ];
            }
        }
        uasort($result, function($item1, $item2) {
            if ($item1['ordering'] == $item2['ordering']) {
                return 0;
            }
            return $item1['ordering'] > $item2['ordering'] ? 1 : -1;
        });

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_groupapi_getprofilemenu_end')) ? eval($sPlugin) : false);

        return array_values($result);
    }

    public function defaultProfileMenu()
    {
        return [
            [
                'resource_name' => PhotoResource::RESOURCE_NAME,
                'perm'          => 'photo.can_view_photos',
                'page_perm'     => 'photo.view_browse_photos',
                'label'         => 'photos',
                'module_id'     => 'photo'
            ],
            [
                'resource_name' => PhotoAlbumResource::RESOURCE_NAME,
                'perm'          => ['photo.can_view_photos', 'photo.can_view_photo_albums'],
                'page_perm'     => 'photo.view_browse_photos',
                'label'         => 'photo_albums',
                'module_id'     => 'photo'
            ],
            [
                'resource_name' => VideoResource::RESOURCE_NAME,
                'perm'          => 'v.pf_video_view',
                'page_perm'     => 'pf_video.view_browse_videos',
                'label'         => 'Videos',
                'module_id'     => 'v',
                'app_module_id' => 'video'
            ],
            [
                'resource_name' => BlogResource::RESOURCE_NAME,
                'perm'          => 'blog.view_blogs',
                'page_perm'     => 'blog.view_browse_blogs',
                'label'         => 'blogs',
                'module_id'     => 'blog'
            ],
            [
                'resource_name' => EventResource::RESOURCE_NAME,
                'perm'          => 'event.can_access_event',
                'page_perm'     => 'event.view_browse_events',
                'label'         => 'events',
                'module_id'     => 'event'
            ],
            [
                'resource_name' => ForumThreadResource::RESOURCE_NAME,
                'perm'          => 'forum.can_view_forum',
                'page_perm'     => 'forum.view_browse_forum',
                'label'         => 'discussions',
                'module_id'     => 'forum'
            ],
            [
                'resource_name' => MusicSongResource::RESOURCE_NAME,
                'perm'          => 'music.can_access_music',
                'page_perm'     => 'music.view_browse_music',
                'label'         => 'music_songs',
                'module_id'     => 'music',
                'disable_add'   => true
            ],
            [
                'resource_name' => MusicAlbumResource::RESOURCE_NAME,
                'perm'          => 'music.can_access_music',
                'page_perm'     => 'music.view_browse_music',
                'label'         => 'music_albums',
                'module_id'     => 'music',
                'disable_add'   => true
            ],
            [
                'resource_name' => MarketplaceResource::RESOURCE_NAME,
                'perm'          => 'marketplace.can_access_marketplace',
                'page_perm'     => 'marketplace.view_browse_marketplace_listings',
                'label'         => 'listing',
                'module_id'     => 'marketplace'
            ],
            [
                'resource_name' => PollResource::RESOURCE_NAME,
                'perm'          => 'poll.can_access_polls',
                'page_perm'     => 'poll.view_browse_polls',
                'label'         => 'polls',
                'module_id'     => 'poll'
            ],
            [
                'resource_name' => QuizResource::RESOURCE_NAME,
                'perm'          => 'quiz.can_access_quiz',
                'page_perm'     => 'quiz.view_browse_quizzes',
                'label'         => 'quizzes',
                'module_id'     => 'quiz'
            ]
        ];
    }

    public function getPostTypes($id)
    {
        $page = $this->loadResourceById($id);
        if (empty($page) || !Phpfox::isModule('feed')) {
            return [];
        }
        $postOptions = [];
        $userId = $this->getUser()->getId();

        if (!$userId || !$this->groupService->hasPerm($id, 'groups.share_updates')
            || Phpfox::getService('user.block')->isBlocked($page['user_id'], $this->getUser()->getId())) {
            return [];
        }
        $postOptions[] = (new CoreApi())->getPostOption('status');

        if (Phpfox::isAppActive('Core_Photos') && $this->groupService->hasPerm($id, 'photo.share_photos') && $this->getSetting()->getUserSetting('photo.can_upload_photos')) {
            $postOptions[] = (new CoreApi())->getPostOption('photo');
        }
        if (Phpfox::isAppActive('PHPfox_Videos') && $this->groupService->hasPerm($id, 'pf_video.share_videos')
            && $this->getSetting()->getUserSetting('v.pf_video_share') && $this->getSetting()->getUserSetting('v.pf_video_view')) {
            $postOptions[] = (new CoreApi())->getPostOption('video');
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_groupapi_getposttype_end')) ? eval($sPlugin) : false);

        return $postOptions;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(GroupAccessControl::VIEW);
        /** @var GroupSearchForm $form */
        $form = $this->createForm(GroupSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('group')
        ]);

        return $this->success($form->getFormStructure());
    }

    public function setHyperLinks(GroupResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            GroupAccessControl::VIEW   => $this->createHyperMediaLink(GroupAccessControl::VIEW, $resource,
                HyperLink::GET, 'group/:id', ['id' => $resource->getId()]),
            GroupAccessControl::EDIT   => $this->createHyperMediaLink(GroupAccessControl::EDIT, $resource,
                HyperLink::GET, 'group-profile/form/:id', ['id' => $resource->getId()]),
            GroupAccessControl::DELETE => $this->createHyperMediaLink(GroupAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'group/:id', ['id' => $resource->getId()]),
        ]);
        if ($includeLinks) {
            $resource->setLinks([
                'photos'  => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'photo', ['item_id' => $resource->getId(), 'module_id' => 'groups']),
                'admins'  => $this->createHyperMediaLink(GroupAccessControl::VIEW, $resource, HyperLink::GET, 'group-admin', ['group_id' => $resource->getId()]),
                'members' => $this->createHyperMediaLink(GroupAccessControl::VIEW, $resource, HyperLink::GET, 'group-member', ['group_id' => $resource->getId(), 'view' => 'all'])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', GroupResource::RESOURCE_NAME);
        $module = 'group';
        return [
            [
                'path'      => 'groups/:id',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function getActions()
    {
        $l = $this->getLocalization();
        return [
            'group/member/join'             => [
                'method'    => 'post',
                'url'       => 'mobile/group-member',
                'data'      => 'group_id=:id',
                'new_state' => 'membership=2',
            ],
            'group/member/leave'            => [
                'method'          => 'delete',
                'url'             => 'mobile/group-member',
                'data'            => 'group_id=:id',
                'new_state'       => 'membership=0',
                'confirm_message' => $l->translate('are_you_sure_you_want_to_leave_this_group'),
            ],
            'group/delete-member'           => [
                'method'          => 'delete',
                'url'             => 'mobile/group-member',
                'data'            => 'group_id=:group_id, user_id=:id',
                'new_state'       => 'is_deleted=1',
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'group/delete-admin'            => [
                'method'          => 'delete',
                'url'             => 'mobile/group-admin',
                'data'            => 'group_id=:group_id, user_id=:id',
                'new_state'       => 'is_admin=0',
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'groups/manage'                 => [
                'routeName' => 'groups/edit',
            ],
            'groups/remove_cover'           => [
                'method'          => 'delete',
                'url'             => 'mobile/groups/cover/:id',
                'data'            => 'id=:id',
                'new_state'       => 'can_remove_cover=false,cover_photo_id=0,covers=' . (new GroupResource([]))->getDefaultImage(true),
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'groups/member/approve-request' => [
                'method'    => 'put',
                'url'       => 'mobile/group-member/request',
                'data'      => 'group_id=:page_id,user_id=:id',
                'new_state' => 'is_pending=false'
            ],
            'groups/member/delete-request'  => [
                'method'          => 'delete',
                'url'             => 'mobile/group-member/request',
                'data'            => 'group_id=:page_id,user_id=:id',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure_you_want_to_delete_this_user_from_the_group'),
                'new_state'       => 'is_pending=false'
            ],
            'groups/member/cancel-request'  => [
                'method'          => 'delete',
                'url'             => 'mobile/group-member/request',
                'data'            => 'group_id=:id',
                'new_state'       => 'membership=0'
            ],
            'groups/reassign-owner'         => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'groups',
                    'resource_name' => 'groups',
                    'formType'      => 'reassignOwner',
                ]
            ],
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $resourceName = (new GroupResource([]))->getResourceName();
        $sectionResourceName = (new GroupSectionResource([]))->getResourceName();
        $config = [
            'title'           => $l->translate('groups'),
            'home_view'       => 'menu',
            'home_resource'   => new GroupSectionResource([]),
            'main_resource'   => new GroupResource([]),
            'other_resources' => [
                new FeedResource([]),
                new GroupCategoryResource([]),
                new GroupInviteResource([]),
                new GroupInfoResource([]),
                new GroupMemberResource([]),
                new GroupAdminResource([]),
                new GroupWidgetResource([])
            ],
        ];

        if (isset($param['api_version_name']) && $param['api_version_name'] != 'mobile') {
            $config['category_resource'] = [
                $resourceName        => new GroupTypeResource([]),
                $sectionResourceName => new GroupTypeResource([]),
            ];
        } else {
            $config['category_resource'] = new GroupTypeResource([]);
        }

        $app = new MobileApp('groups', $config, isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $headerButtons[$resourceName] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ]
        ];
        if ($this->getAccessControl()->isGranted(GroupAccessControl::ADD)) {
            $headerButtons[$resourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => $resourceName]
            ];
        }
        $headerButtons[$sectionResourceName] = $headerButtons[$resourceName];
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    public function uploadAvatar($params)
    {
        $params = $this->resolver
            ->setDefined(['id', 'image'])
            ->setRequired(['image'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $page = $this->loadResourceById($params['id']);
        if (!$page) {
            return $this->notFoundError();
        }
        $pageResource = GroupResource::populate($page);
        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $pageResource);

        if (!$this->getSetting()->getUserSetting('photo.can_upload_photos')) {
            return $this->permissionError();
        }
        $userId = $this->groupService->getUserId($page['page_id']);
        $sTempPath = PHPFOX_DIR_CACHE . md5('groups_avatar' . Phpfox::getUserId()) . '.png';
        list($header, $data) = explode(';', $params['image']);

        $aImageData = explode(',', $data);
        if (isset($aImageData[1])) {
            $data = base64_decode($aImageData[1]);
            if (!empty($data)) {
                //Check file type
                $imageExt = str_replace('data:image/', '', $header);
                $accept = ['jpg', 'gif', 'png', 'jpeg'];
                if (!in_array($imageExt, $accept)) {
                    return $this->error(_p('not_a_valid_image_we_only_accept_the_following_file_extensions_support',
                        ['support' => implode(', ', $accept)]));
                }

                //Check file size
                $length = strlen($data);
                $size = round($length / 1024, 2);
                $maxSize = $this->getSetting()->getUserSetting('groups.pf_group_max_upload_size');
                if ($maxSize != 0 && $size > $maxSize) {
                    return $this->error(_p('upload_failed_your_file_size_is_larger_then_our_limit_file_size',
                        [
                            'size'      => $size . 'kb',
                            'file_size' => $maxSize . 'kb'
                        ]));
                }
                file_put_contents($sTempPath, $data);

                if (!Phpfox::getService('user.space')->isAllowedToUpload($this->getUser()->getId(), filesize($sTempPath))) {
                    return $this->error();
                }

                $oFile = \Phpfox_File::instance();
                $oImage = \Phpfox_Image::instance();
                $imageDir = Phpfox::getParam('pages.dir_image');
                $sFileName = $oFile->upload($sTempPath, $imageDir, $page['page_id']);
                $iFileSizes = filesize(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''));
                foreach (Phpfox::getService('groups')->getPhotoPicSizes() as $iSize) {
                    if (Phpfox::getParam('core.keep_non_square_images')) {
                        $oImage->createThumbnail($imageDir . sprintf($sFileName, ''),
                            $imageDir . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
                    }
                    $oImage->createThumbnail($imageDir . sprintf($sFileName, ''),
                        $imageDir . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);
                }
                //Crop max width
                if (Phpfox::isAppActive('Core_Photos')) {
                    Phpfox::getService('photo')->cropMaxWidth(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''));
                }
                $aImage = Phpfox::getService('user.process')->uploadImage($userId, true,
                    $imageDir . sprintf($sFileName, ''));

                $serverId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');

                if (!isset($aImage['pending_photo'])) {
                    if (!empty($page['image_path'])) {
                        $this->processService->deleteImage($page);
                    }

                    db()->update(':pages', [
                        'image_path'      => $sFileName,
                        'image_server_id' => $serverId
                    ], ['page_id' => $page['page_id']]);

                    // Update user space usage
                    Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'groups', $iFileSizes);

                    // add feed after updating page's profile image
                    if (Phpfox::isModule('feed') && $oProfileImage = storage()->get('user/avatar/' . $userId)) {
                        Phpfox::getService('feed.process')->callback([
                            'table_prefix'     => 'pages_',
                            'module'           => 'pages',
                            'add_to_main_feed' => true,
                            'has_content'      => true
                        ])->add('groups_photo', $oProfileImage->value, 0, 0, $page['page_id'], $userId);
                    }

                    $page['image_path'] = $sFileName;
                    $page['image_server_id'] = $serverId;
                    $sMessage = 'groups_photo_successfully_updated';
                } else {
                    $cacheKey = 'groups_profile_photo_pending_' . $page['page_id'];
                    storage()->del($cacheKey);
                    storage()->set($cacheKey, [
                        'image_path'      => $sFileName,
                        'image_server_id' => $serverId
                    ]);

                    $sMessage = 'the_profile_photo_is_pending_please_waiting_until_the_approval_process_is_done';
                }

                @unlink($sTempPath);

                return $this->success(GroupResource::populate($page)->toArray(), [], $this->getLocalization()->translate($sMessage));
            }
        }

        return $this->error();
    }

    public function uploadCover($params)
    {
        $id = $this->resolver->setRequired(['id'])->resolveId($params);

        $page = $this->loadResourceById($id);
        if (!$page) {
            return $this->notFoundError();
        }
        $pageResource = GroupResource::populate($page);
        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $pageResource);
        $this->denyAccessUnlessGranted(GroupAccessControl::ADD_COVER, $pageResource);
        if (!$this->getSetting()->getUserSetting('photo.can_upload_photos')) {
            return $this->permissionError();
        }

        $resource = $pageResource->toArray();
        //Check permission when update cover of other
        if (isset($_FILES['Filedata']) && !isset($_FILES['image'])) // photo.enable_mass_uploader == true
        {
            $_FILES['image'] = [];
            $_FILES['image']['error'] = UPLOAD_ERR_OK;
            $_FILES['image']['name'] = $_FILES['Filedata']['name'];
            $_FILES['image']['type'] = $_FILES['Filedata']['type'];
            $_FILES['image']['tmp_name'] = $_FILES['Filedata']['tmp_name'];
            $_FILES['image']['size'] = $_FILES['Filedata']['size'];
        }

        if (empty($_FILES['image'])) {
            return $this->validationParamsError(['image']);
        }

        $userId = $this->groupService->getUserId($page['page_id']);
        $oFile = \Phpfox_File::instance();
        $oImage = \Phpfox_Image::instance();
        $maxSize = $this->getSetting()->getUserSetting('photo.photo_max_upload_size');
        $uploadDir = $this->getSetting()->getAppSetting('photo.dir_photo');
        if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
            if ($aImage = $oFile->load('image', ['jpg', 'gif', 'png'],
                ($maxSize == 0 ? null : ($maxSize / 1024)))) {
                $aVals = [
                    'type_id'          => 0,
                    'is_cover_photo'   => 1,
                    'callback_module'  => 'groups',
                    'callback_item_id' => $id,
                    'group_id'         => $id
                ];
                if ($iId = Phpfox::getService('photo.process')->add($userId, array_merge($aVals, $aImage))) {
                    $sFileName = $oFile->upload('image', $uploadDir, $iId, true);
                    $sFile = $uploadDir . sprintf($sFileName, '');
                    $iFileSizes = filesize($sFile);
                    // Get the current image width/height
                    $aSize = getimagesize($sFile);
                    $iServerId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');
                    // Update the image with the full path to where it is located.
                    $aUpdate = [
                        'destination'    => $sFileName,
                        'width'          => $aSize[0],
                        'height'         => $aSize[1],
                        'server_id'      => $iServerId,
                        'allow_rate'     => 1,
                        'description'    => null,
                        'allow_download' => 1
                    ];
                    Phpfox::getService('photo.process')->update($userId, $iId, $aUpdate);
                    $picSizes = Phpfox::getService('photo')->getPhotoPicSizes();
                    if (file_exists($sFile)
                        && !$this->getSetting()->getAppSetting('core.keep_files_in_server')
                    ) {
                        if ($iServerId > 0) {
                            $sActualFile = Phpfox::getLib('image.helper')->display([
                                    'server_id'  => $iServerId,
                                    'path'       => 'photo.url_photo',
                                    'file'       => $sFileName,
                                    'suffix'     => '',
                                    'return_url' => true
                                ]
                            );

                            $aExts = preg_split("/[\/\\.]/", $sActualFile);
                            $iCnt = count($aExts) - 1;
                            $sExt = strtolower($aExts[$iCnt]);

                            $aParts = explode('/', $sFileName);
                            $sFile = $uploadDir . $aParts[0] . '/' . $aParts[1] . '/' . md5($sFileName) . '.' . $sExt;

                            // Create a temp copy of the original file in local server
                            if (filter_var($sActualFile, FILTER_VALIDATE_URL) !== false) {
                                file_put_contents($sFile, fox_get_contents($sActualFile));
                            } else {
                                copy($sActualFile, $sFile);
                            }
                            //Delete file in local server
                            register_shutdown_function(function () use ($sFile) {
                                @unlink($sFile);
                            });
                        }
                    }
                    list($width, $height, ,) = getimagesize($sFile);
                    foreach ($picSizes as $iSize) {
                        // Create the thumbnail
                        if ($oImage->createThumbnail($sFile,
                                $uploadDir . sprintf($sFileName, '_' . $iSize), $iSize,
                                $height, true,
                                false) === false
                        ) {
                            continue;
                        }

                        if (defined('PHPFOX_IS_HOSTED_SCRIPT')) {
                            unlink($uploadDir . sprintf($sFileName, '_' . $iSize));
                        }
                    }
                    //Crop original image
                    $iWidth = (int)$this->getSetting()->getUserSetting('photo.maximum_image_width_keeps_in_server');
                    if ($iWidth < $width) {
                        $bIsCropped = $oImage->createThumbnail($sFile, $sFile, $iWidth, $height,
                            true,
                            false);
                        if ($bIsCropped !== false) {
                            //Rename file
                            if (defined('PHPFOX_IS_HOSTED_SCRIPT')) {
                                unlink($sFile);
                            }
                        }
                    }

                    Phpfox::getService('user.space')->update($this->getUser()->getId(), 'photo', $iFileSizes);
                    $this->processService->setCoverPhoto($id, $iId, true);
                    $this->processService->updateCoverPhoto($iId, $id);
                    $directlyPublic = $this->getSetting()->getUserSetting('photo.photo_must_be_approved');

                    if (!$directlyPublic) {
                        $resource['covers'] = Image::createFrom([
                            'file'      => $sFileName,
                            'server_id' => $iServerId,
                            'path'      => 'photo.url_photo'
                        ], $picSizes)->toArray();
                    }

                    return $this->success($resource, [], $this->getLocalization()->translate(!$directlyPublic
                        ? 'cover_photo_successfully_updated' : 'the_cover_photo_is_pending_please_waiting_until_the_approval_process_is_done'));
                }
            }
        }
        return $this->error();
    }

    public function getFeedDisplay($param, $item)
    {
        if (empty($item)) {
            return null;
        }
        $resource = $this->populateResource(GroupResource::class, $item);

        return $resource->getFeedDisplay();
    }

    public function searchFriendFilter($id, $friends)
    {
        $aInvites = $this->groupService->getCurrentInvitesForGroup($id);
        list(, $aMembers) = $this->groupService->getMembers($id);
        foreach ($friends as $iKey => $friend) {
            if (is_array($aInvites) && isset($aInvites[$friend['user_id']])) {
                $friends[$iKey]['is_active'] = $this->getLocalization()->translate('invited');
                continue;
            }
            if (is_array($aMembers) && in_array($friend['user_id'], array_column($aMembers, 'user_id'))) {
                $friends[$iKey]['is_active'] = $this->getLocalization()->translate('joined');
            }
        }
        return $friends;
    }

    public function getLatestPages($iId, $userId = null, $iPagesLimit = 8)
    {
        $extra_conditions = 'pages.type_id = ' . (int)$iId . ($userId ? ' AND pages.user_id = ' . (int)$userId : '') . ' AND pages.app_id = 0 AND pages.view_id = 0';
        if (!empty($userId)) {
            $extra_conditions .= ' AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . (int)$userId . ' ';
        }

        $iUserLogin = Phpfox::getUserId();
        if (Phpfox::getParam('core.friends_only_community')) {
            $sFriendsList = '0';
            if (Phpfox::getUserId() && Phpfox::isModule('friend')) {
                $aFriends = Phpfox::getService('friend')->getFromCache();
                $aFriendIds = array_column($aFriends, 'user_id');
                $aFriendIds[] = $iUserLogin;

                if (!empty($aFriendIds)) {
                    $sFriendsList = implode(',', $aFriendIds);
                }
            }
            $extra_conditions .= ' AND (pages.user_id IN (' . $sFriendsList . ') ';
            if (Phpfox::getParam('core.friends_only_community')) {
                $groupIds = $this->groupService->getAllGroupIdsOfMember();
                if ($groupIds && count($groupIds)) {
                    $extra_conditions .= " OR pages.page_id IN (" . implode(',', $groupIds) . ")";
                }
            }
            $extra_conditions .= ') ';
        }
        if (($userId != $iUserLogin || $userId === null) && Phpfox::hasCallback($this->facadeService->getItemType(),
                'getExtraBrowseConditions')
        ) {
            $extra_conditions .= Phpfox::callback($this->facadeService->getItemType() . '.getExtraBrowseConditions', 'pages');
        }

        $sOrder = 'pages.time_stamp DESC';

        (($sPlugin = Phpfox_Plugin::get('mobile.service_group_api_getlatestpages_start')) ? eval($sPlugin) : false);

        $this->database()->select('pages.*')
            ->from(Phpfox::getT('pages'), 'pages')
            ->where($extra_conditions)
            ->order($sOrder)
            ->limit($iPagesLimit)
            ->union()
            ->unionFrom('pages');

        $this->database()->select('pages.is_featured, pages.is_sponsor, pages.reg_method, pages.type_id, pages.category_id, pages.title, pages.cover_photo_id, pages.image_path, pages.image_server_id, pages.page_id, pages.user_id, pages.view_id, pages.privacy, pages.total_like, pages.total_comment, pages.time_stamp, l.like_id AS is_liked')
            ->leftJoin(':like', 'l', 'l.type_id = \'groups\' AND l.item_id = pages.page_id AND l.user_id = ' . $iUserLogin);

        $aGroups = $this->database()
            ->group('pages.page_id')
            ->order($sOrder)
            ->limit($iPagesLimit)
            ->execute('getSlaveRows');

        (($sPlugin = Phpfox_Plugin::get('mobile.service_group_api_getlatestpages_end')) ? eval($sPlugin) : false);

        return $aGroups;
    }

    public function getForBrowse($userId = null, $iPagesLimit = null
    )
    {
        $aTypes = $this->database()->select('pt.*')
            ->from(Phpfox::getT('pages_type'), 'pt')
            ->where('pt.is_active = 1 AND pt.item_type = 1')
            ->order('pt.ordering ASC')
            ->execute('getSlaveRows');
        foreach ($aTypes as $iKey => $aType) {
            $aTypes[$iKey]['pages'] = $this->getLatestPages($aType['type_id'], $userId, $iPagesLimit);
        }

        return $aTypes;
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var GroupResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(GroupAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('Group has been approved.'));
        }
        return $this->permissionError();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(GroupAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('group_successfully_featured') : $this->getLocalization()->translate('group_successfully_un_featured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        /** @var GroupResource $item */
        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if (!$this->getAccessControl()->isGranted(GroupAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(GroupAccessControl::PURCHASE_SPONSOR, $item)) {
            return $this->permissionError();
        }

        if ($this->processService->sponsor($id, $sponsor)) {
            if ($sponsor == 1) {
                $sModule = $this->getLocalization()->translate('groups');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'groups',
                    'item_id' => $id,
                    'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                ], false);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('groups', $id);
            }
            return $this->success([
                'is_sponsor' => !!$sponsor
            ], [], $sponsor ? $this->getLocalization()->translate('group_successfully_sponsored') : $this->getLocalization()->translate('group_successfully_un_sponsored'));
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->groupService->getSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * Update view count for sponsored items
     *
     * @param $sponsorItems
     */
    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'groups');
            }
        }
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    protected function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $featuredItems = $this->groupService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('groups', []);
        $resourceName = GroupResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING, $screenSetting->getDefaultModuleListing(false, $l->translate('groups') . ' > ' . $l->translate('group') . ' - ' . $l->translate('view_all_groups_by_type')));
        $embedComponents = [
            'stream_groups_header_info',
            'stream_profile_menus',
            'stream_profile_is_pending',
            'stream_profile_information',
            'stream_groups_pending_members',
            'stream_groups_members'
        ];
        if (Phpfox::isModule('feed')) {
            $embedComponents[] = 'stream_composer';
        }
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => ScreenSetting::STREAM_PROFILE_FEEDS,
                'embedComponents' => $embedComponents
            ],
            'screen_title'                 => $l->translate('groups') . ' > ' . $l->translate('group') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addSetting($resourceName, 'groups/edit', [
            ScreenSetting::LOCATION_TOP  => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => $l->translate('edit_group')
            ],
            ScreenSetting::LOCATION_MAIN => ['component' => 'edit_groups'],
            'no_ads'                     => true,
            'screen_title'               => $l->translate('groups') . ' > ' . $l->translate('edit_group_menu')
        ]);
        $screenSetting->addSetting($resourceName, 'detailGroupsFeed', [
            ScreenSetting::LOCATION_HEADER => ['component' => 'feed_header'],
            ScreenSetting::LOCATION_MAIN   => ['component' => 'feed_detail'],
            'no_ads'                       => true
        ]);
        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_groups'),
                'resource_name' => $resourceName,
                'module_name'   => 'groups',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_groups'),
                'resource_name' => $resourceName,
                'module_name'   => 'groups',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);
        $allMembers = [
            'label'         => 'all_members',
            'component'     => ScreenSetting::SMART_RESOURCE_LIST,
            'module_name'   => 'groups',
            'resource_name' => GroupMemberResource::populate([])->getResourceName(),
            'item_view'     => 'group_member',
            'search'        => true,
            'use_query'     => ['group_id' => ':id']
        ];
        $pendingRequest = [

            'label'         => 'pending_requests',
            'component'     => ScreenSetting::SMART_RESOURCE_LIST,
            'module_name'   => 'groups',
            'resource_name' => GroupMemberResource::populate([])->getResourceName(),
            'item_view'     => 'group_member',
            'search'        => true,
            'use_query'     => ['group_id' => ':id', 'view' => 'pending'],
            'noItemMessage' => $this->localization->translate('there_are_no_pending_requests'),
        ];
        $allAdmins = [
            'label'         => 'group_admins',
            'component'     => ScreenSetting::SMART_RESOURCE_LIST,
            'module_name'   => 'groups',
            'resource_name' => GroupMemberResource::populate([])->getResourceName(),
            'item_view'     => 'group_member',
            'search'        => true,
            'use_query'     => ['group_id' => ':id', 'view' => 'admin']
        ];
        $screenSetting->addSetting($resourceName, 'smartGroupsMember', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'members'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_TABS,
                'tabs'      => [$allMembers, $allAdmins],
            ],
            'screen_title'                 => $l->translate('groups') . ' > ' . $l->translate('group_members') . ' - ' . $l->translate('for_members'),
        ]);

        $screenSetting->addSetting($resourceName, 'smartGroupsMemberAdmin', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'members'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_TABS,
                'tabs'      => [$allMembers, $pendingRequest, $allAdmins],
            ],
            'screen_title'                 => $l->translate('groups') . ' > ' . $l->translate('group_members') . ' - ' . $l->translate('for_admins'),
        ]);
        $resourceWidgetName = GroupWidgetResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceWidgetName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component'   => 'simple_header'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    'item_title',
                    'item_html_content',
                ]
            ],
            'screen_title'                 => $l->translate('groups') . ' > ' . $l->translate('menus') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'groups.index',
            ScreenSetting::MODULE_LISTING => 'groups.index',
            ScreenSetting::MODULE_DETAIL  => 'groups.view'
        ];
    }

    public function removeCover($params)
    {
        $id = $this->resolver->setRequired(['id'])->resolveId($params);

        $page = $this->loadResourceById($id);
        if (!$page) {
            return $this->notFoundError();
        }
        $pageResource = GroupResource::populate($page);

        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $pageResource);
        $this->denyAccessUnlessGranted(GroupAccessControl::REMOVE_COVER, $pageResource);
        if ($this->processService->removeLogo($id)) {
            return $this->success([], [], $this->getLocalization()->translate('group_cover_removed_successfully'));
        }
        return $this->error();
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
                $this->denyAccessUnlessGranted(GroupAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    $this->processService->approve($id);
                }
                $data = array_merge($data, ['is_pending' => false]);
                $sMessage = $this->getLocalization()->translate('Group(s) successfully approved.');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(GroupAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    $this->processService->feature($id, $value);
                }
                $data = array_merge($data, ['is_featured' => !!$value]);
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('group_s_successfully_featured') : $this->getLocalization()->translate('group_s_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(GroupAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    $this->processService->delete($id);
                }
                $sMessage = $this->getLocalization()->translate('Group(s) successfully deleted.');
                break;
        }
        return $this->success($data, [], $sMessage);
    }
}