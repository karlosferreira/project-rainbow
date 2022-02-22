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
use Apps\Core_MobileApi\Api\Form\Page\PageClaimForm;
use Apps\Core_MobileApi\Api\Form\Page\PageForm;
use Apps\Core_MobileApi\Api\Form\Page\PageSearchForm;
use Apps\Core_MobileApi\Api\Resource\BlogResource;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\FeedResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Resource\MusicAlbumResource;
use Apps\Core_MobileApi\Api\Resource\MusicSongResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\PageAdminResource;
use Apps\Core_MobileApi\Api\Resource\PageCategoryResource;
use Apps\Core_MobileApi\Api\Resource\PageInfoResource;
use Apps\Core_MobileApi\Api\Resource\PageInviteResource;
use Apps\Core_MobileApi\Api\Resource\PageMemberResource;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Resource\PageSectionResource;
use Apps\Core_MobileApi\Api\Resource\PageTypeResource;
use Apps\Core_MobileApi\Api\Resource\PageWidgetResource;
use Apps\Core_MobileApi\Api\Resource\PhotoAlbumResource;
use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Resource\VideoResource;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\Core_MobileApi\Service\Helper\BrowseHelper;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Pages\Service\Category;
use Apps\Core_Pages\Service\Facade;
use Apps\Core_Pages\Service\Pages;
use Apps\Core_Pages\Service\Process;
use Apps\Core_Pages\Service\Type;
use Phpfox;
use Phpfox_Plugin;
use Phpfox_Request;


class PageApi extends AbstractResourceApi implements MobileAppSettingInterface, ActivityFeedInterface
{

    /**
     * @var Facade
     */
    protected $facadeService;

    /**
     * @var Pages
     */
    protected $pageService;
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
     * PageApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('pages.facade');
        $this->pageService = Phpfox::getService('pages');
        $this->userService = Phpfox::getService('user');
        $this->typeService = Phpfox::getService('pages.type');
        $this->categoryService = Phpfox::getService('pages.category');
        $this->processService = Phpfox::getService('pages.process');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'pages/profile-menu'  => [
                'get' => 'getProfileMenus'
            ],
            'page-home'           => [
                'get' => 'getPageHome'
            ],
            'pages/search-form'   => [
                'get' => 'searchForm'
            ],
            'pages/post-type/:id' => [
                'get'   => 'getPostTypes',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'pages/avatar/:id'    => [
                'post' => 'uploadAvatar'
            ],
            'pages/cover/:id'     => [
                'post'   => 'uploadCover',
                'delete' => 'removeCover'
            ],
            'page-claim/:id'      => [
                'post'  => 'claimPage',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'page-claim/form/:id' => [
                'get'   => 'getClaimForm',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'pages/location/:id'  => [
                'post'  => 'updateLocation',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'pages/related/:id'   => [
                'get'   => 'getRelatedPage',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'pages/reassign-owner/form/:id' => [
                'get'   => 'getReassignOwnerForm',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'pages/reassign-owner/:id' => [
                'post'   => 'reassignOwner',
                'where' => [
                    'id' => '(\d+)'
                ]
            ]
        ];
    }

    function getPageHome($params = [])
    {
        return $this->findAll($params, false);
    }

    /**
     * @param array   $params
     * @param boolean $isSearch
     *
     * @return mixed
     */

    function findAll($params = [], $isSearch = true)
    {

        $params = $this->resolver->setDefined([
            'view', 'category', 'type', 'q', 'sort', 'limit', 'page', 'profile_id', 'when'
        ])
            ->setAllowedValues('sort', ['latest', 'most_liked'])
            ->setAllowedValues('view', ['all', 'my', 'pending', 'friend', 'related', 'sponsor', 'feature', 'liked'])
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
        if (!Phpfox::getUserParam('pages.can_view_browse_pages')) {
            return $this->permissionError();
        }
        $params['is_search'] = $isSearch;
        $sort = $params['sort'];
        $view = $params['view'];

        if (in_array($view, ['feature', 'sponsor'])) {
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
            'module_id' => 'pages',
            'alias'     => 'pages',
            'field'     => 'page_id',
            'table'     => Phpfox::getT('pages'),
            'hide_view' => ['pending', 'my'],
            'select'    => 'pages_type.name as type_name , \'' . $view . '\' as current_view',
            'service'   => 'pages.browse',
        ];
        $userPageIds = null;
        if ($this->getSetting()->getAppSetting('core.friends_only_community')) {
            if($view != 'friend') {
                $userPageIds = $this->pageService->getAllPageIdsOfMember($this->getUser()->getId());
                if ($userPageIds && count($userPageIds)) {
                    Phpfox::getService('pages.browse')->pageIds($userPageIds);
                }
            }
        }
        if ($isSearch) {
            $this->search()->setSearchTool([
                'table_alias' => 'pages'
            ]);
            switch ($view) {
                case 'related':
                case 'my':
                    if (Phpfox::isUser()) {
                        $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . Phpfox::getUserId());
                    }
                    break;
                case 'pending':
                    $isValidCategory = true;
                    if (Phpfox::isUser()) {
                        if (Phpfox::getUserParam('pages.can_approve_pages')) {
                            $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 1');
                        } else {
                            return $this->permissionError();
                        }
                    }
                    break;
                case 'liked':
                    if (Phpfox::isUser()) {
                        $pageIds = '0';
                        if ($userPageIds === null) {
                            $userPageIds = $this->pageService->getAllPageIdsOfMember(isset($user['user_id']) ? $user['user_id'] : $this->getUser()->getId());
                            if ($userPageIds && count($userPageIds)) {
                                Phpfox::getService('pages.browse')->pageIds($userPageIds);
                            }
                        }
                        if ($userPageIds && count($userPageIds)) {
                            $pageIds = implode(',', $userPageIds);
                        }
                        $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.page_id IN (' . $pageIds . ')');
                    } else {
                        return $this->permissionError();
                    }
                    break;
                case 'all':
                    $this->search()->setCondition('AND pages.view_id = 0');
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
            } else if (isset($type) && isset($category) && isset($category['category_id'])) {
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
            $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);
            $this->browse()->params($browseParams)->execute(function (BrowseHelper $browse) {
                $browse->database()->select('pages_type.name as type_name, ')->join(':pages_type', 'pages_type',
                    'pages_type.type_id = pages.type_id AND pages_type.item_type = 0');
            });
            $items = $this->browse()->getRows();
            $this->processRows($items);
        } else {
            $limit = Phpfox::getParam('pages.pages_limit_per_category', 0);
            $items = $this->getForBrowse(($view == 'my' ? Phpfox::getUserId() : ($isProfile ? $user['user_id'] : null)), $limit);
            NameResource::instance()->getApiServiceByResourceName(PageTypeResource::RESOURCE_NAME)->processRows($items);
        }

        if (!$isSearch) {
            $data = [];
            if ($params['page'] > 1) {
                return $this->success([]);
            }
            foreach ($items as $index => $item) {
                $children = isset($item['items']) ? $item['items'] : [];
                if (!isset($children) || !count($children))
                    continue;

                unset($item['items']);
                $data[] = [
                    'id'            => $index,
                    'resource_name' => 'pages_section',
                    'module_name'   => 'pages',
                    'main'          => $item,
                    'items'         => $children,
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
        $id = $this->resolver->resolveId($params);
        if (!Phpfox::getUserParam('pages.can_view_browse_pages')) {
            return $this->permissionError();
        }
        $item = $this->pageService->getForView($id);
        if (!$item || $item['view_id'] == '2') {
            return $this->notFoundError();
        }
        if ($item['view_id'] != '0' && !(Phpfox::getUserParam('pages.can_approve_pages') || Phpfox::getUserParam('pages.can_edit_all_pages') ||
                Phpfox::getUserParam('pages.can_delete_all_pages') || $item['is_admin'])
        ) {
            return $this->permissionError();
        }
        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('pages', $item['page_id'], $item['user_id'],
                $item['privacy'], (isset($item['is_friend']) ? $item['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        $item['is_detail'] = true;
        $resource = $this->populateResource(PageResource::class, $item);
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
        /** @var PageForm $form */
        $form = $this->createForm(PageForm::class, [
            'title'  => 'add_new_page',
            'action' => UrlUtility::makeApiUrl('pages'),
            'method' => 'POST'
        ]);
        $form->setCategories($this->getCategories());
        $page = $this->loadResourceById($editId, true);
        if ($editId && empty($page)) {
            return $this->notFoundError();
        }

        if ($page) {
            $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $page);
            $form->setTitle('editing_page')
                ->setAction(UrlUtility::makeApiUrl('pages/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($page);
        } else {
            $this->denyAccessUnlessGranted(PageAccessControl::ADD);
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
        $this->denyAccessUnlessGranted(PageAccessControl::ADD);
        /** @var PageForm $form */
        $form = $this->createForm(PageForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => PageResource::populate([])->getResourceName()
                ], [], $this->localization->translate('page_successfully_created'));
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
                } else if (strpos($val, 'category_') > -1) {
                    $values['category'] = str_replace('category_', '', $val);
                }
            }
        }
        if (!empty($values['type'])) {
            $type = NameResource::instance()->getApiServiceByResourceName(PageTypeResource::RESOURCE_NAME)->loadResourceById($values['type']);
            if (empty($type)) {
                //Not valid group type
                return $this->notFoundError($this->getLocalization()->translate('page_type_is_not_found'));
            }
            $values['type_id'] = $values['type'];
        } else {
            return $this->error($this->getLocalization()->translate('page_type_is_required'));
        }
        if (!empty($values['category'])) {
            $category = NameResource::instance()->getApiServiceByResourceName(PageCategoryResource::RESOURCE_NAME)->loadResourceById($values['category'], false, $values['type']);
            if (empty($category)) {
                //Not valid group category
                return $this->notFoundError($this->getLocalization()->translate('page_category_does_not_exist_or_does_not_belonging_to_type_name', ['name' => isset($type['name']) ? $type['name'] : '']));
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
        $itemId = $this->resolver->resolveId($params);
        $item = $this->loadResourceById($itemId);
        if ($itemId < 1 || !$item) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('pages.can_view_browse_pages') && ($item['user_id'] == Phpfox::getUserId() || Phpfox::getUserParam('pages.can_delete_all_pages')) && $this->processService->delete($itemId)) {
            return $this->success([], [], $this->getLocalization()->translate('page_successfully_deleted'));
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
            return PageResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        //Get Permission of page
        $this->pageService->getActionsPermission($item, isset($item['current_view']) ? $item['current_view'] : '');
        /** @var PageResource $resource */
        $resource = $this->populateResource(PageResource::class, $item);
        $this->setHyperLinks($resource);

        $view = $this->request()->get('view');
        $shortFields = [];

        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'summary', 'statistic', 'covers', 'image', 'id', 'is_sponsor', 'is_featured'
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
            new PageAccessControl($this->getSetting(), $this->getUser());
    }

    public function getProfileMenus($id)
    {
        defined('PHPFOX_IS_PAGES_VIEW') or define('PHPFOX_IS_PAGES_VIEW', true);
        $page = $this->loadResourceById($id);
        if (empty($page['page_id'])) {
            return [];
        }
        $query = ['item_id' => $id, 'module_id' => 'pages'];

        $defaultMenu = $this->defaultProfileMenu();

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_pageapi_getprofilemenu_start')) ? eval($sPlugin) : false);

        $result = [];
        $local = $this->getLocalization();
        if ($aIntegrate = storage()->get($this->facadeService->getItemType() . '_integrate')) {
            $aIntegrate = (array)$aIntegrate->value;
        } else {
            $aIntegrate = [];
        }
        $cacheId = $this->cache()->set('pages_' . $page['page_id'] . '_menus');
        if (($pageMenus = $this->cache()->get($cacheId)) === false) {
            $pageMenus = $this->pageService->getPageMenu($page['page_id']);
            $this->cache()->save($cacheId, $pageMenus);
        }
        $pageMenuName = array_column($pageMenus, 'menu_name');
        $nextOrdering = count($defaultMenu) + 1;
        foreach ($defaultMenu as $menu) {
            if (array_key_exists($menu['module_id'], $aIntegrate) && !$aIntegrate[$menu['module_id']]) {
                continue;
            }
            if (!empty($menu['perm']) && !$this->getAccessControl()->isGrantedSetting($menu['perm'])) {
                continue;
            }
            if (!empty($menu['page_perm']) && !$this->pageService->hasPerm($id, $menu['page_perm'])) {
                continue;
            }
            $label = $local->translate($menu['label']);
            $actionButtons = [];
            $moduleId = isset($menu['app_module_id']) ? $menu['app_module_id'] : $menu['module_id'];
            $index = array_search($moduleId, $pageMenuName);
            if ($index !== false) {
                if (!$pageMenus[$index]['is_active']) {
                    continue;
                }
                $ordering = $pageMenus[$index]['ordering'];
            } else {
                $ordering = $nextOrdering;
                $nextOrdering++;
            }

            if (empty($menu['disable_add']) && Phpfox::hasCallback($menu['module_id'], 'getPageSubMenu')) {
                $callback = Phpfox::callback($menu['module_id'] . '.getPageSubMenu', $page);
                if ($callback !== null && count($callback)) {
                    $actionButtons[] = [
                        'icon'   => 'plus',
                        'action' => Screen::ACTION_ADD,
                        'params' => [
                            'resource_name' => str_replace('-', '_', $menu['resource_name']),
                            'module_name'   => isset($menu['app_module_id']) ? $menu['app_module_id'] : $menu['module_id'],
                            'query'         => [
                                'item_id'   => $page['page_id'],
                                'module_id' => 'pages'
                            ]
                        ],
                    ];
                }
            }
            $result[] = [
                'label'    => $label,
                'ordering' => (int)$ordering,
                'path'     => "{$menu['resource_name']}/list-item",
                'params'   => [
                    'headerTitle'        => $local->translate('full_name_s_item', ['full_name' => $page['title'], 'item' => $label]),
                    'headerRightButtons' => $actionButtons,
                    'query'              => $query
                ]
            ];
        }
        /** @var PageWidgetApi $widgetApi */
        $widgetApi = (new ApiVersionResolver())->getApiServiceWithVersion(PageWidgetResource::RESOURCE_NAME, [
            'api_version_name' => Phpfox::getLib('request')->get('req2')
        ]);
        /** @var array $pageWidgets */
        $pageWidgets = $widgetApi->getWidgets($id, PageWidgetApi::TYPE_MENU);
        if (count($pageWidgets)) {
            $widgetApi->processRows($pageWidgets);
            foreach ($pageWidgets as $key => $widget) {
                $index = array_search($widget['url_title'], $pageMenuName);
                if ($index !== false) {
                    if (!$pageMenus[$index]['is_active']) {
                        continue;
                    }
                    $ordering = $pageMenus[$index]['ordering'];
                } else {
                    $ordering = $nextOrdering;
                    $nextOrdering++;
                }
                $result[] = [
                    'label'    => $widget['menu_title'],
                    'ordering' => $ordering,
                    'path'     => "{$widget['module_name']}/{$widget['resource_name']}/{$widget['id']}",
                    'params'   => [
                        'headerTitle' => $widget['title'],
                        'query'       => $query
                    ]
                ];
            }
        }
        uasort($result, function ($item1, $item2) {
            if ($item1['ordering'] == $item2['ordering']) {
                return 0;
            }
            return $item1['ordering'] > $item2['ordering'] ? 1 : -1;
        });

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_pageapi_getprofilemenu_end')) ? eval($sPlugin) : false);

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

        if (!$userId
            || !$this->pageService->hasPerm($id, 'pages.share_updates')
            || !$this->pageService->hasPerm($id, 'pages.view_browse_updates')
            || Phpfox::getService('user.block')->isBlocked($page['user_id'], $this->getUser()->getId())) {
            return [];
        }
        $postOptions[] = (new CoreApi())->getPostOption('status');

        if (Phpfox::isAppActive('Core_Photos') && $this->pageService->hasPerm($id, 'photo.share_photos') && $this->getSetting()->getUserSetting('photo.can_upload_photos')) {
            $postOptions[] = (new CoreApi())->getPostOption('photo');
        }
        if (Phpfox::isAppActive('PHPfox_Videos') && $this->pageService->hasPerm($id, 'pf_video.share_videos')
            && $this->getSetting()->getUserSetting('v.pf_video_share') && $this->getSetting()->getUserSetting('v.pf_video_view')) {
            $postOptions[] = (new CoreApi())->getPostOption('video');
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_pageapi_getposttype_end')) ? eval($sPlugin) : false);

        return $postOptions;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(PageAccessControl::VIEW);
        /** @var PageSearchForm $form */
        $form = $this->createForm(PageSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('page')
        ]);

        return $this->success($form->getFormStructure());
    }

    public function setHyperLinks(PageResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            PageAccessControl::VIEW   => $this->createHyperMediaLink(PageAccessControl::VIEW, $resource,
                HyperLink::GET, 'page/:id', ['id' => $resource->getId()]),
            PageAccessControl::EDIT   => $this->createHyperMediaLink(PageAccessControl::EDIT, $resource,
                HyperLink::PUT, 'page-profile/:id', ['id' => $resource->getId()]),
            PageAccessControl::DELETE => $this->createHyperMediaLink(PageAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'page/:id', ['id' => $resource->getId()]),
        ]);
        if ($includeLinks) {
            $resource->setLinks([
                'photos'  => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'photo', ['item_id' => $resource->getId(), 'module_id' => 'pages', 'limit' => 12]),
                'admins'  => $this->createHyperMediaLink(PageAccessControl::VIEW, $resource, HyperLink::GET, 'page-admin', ['page_id' => $resource->getId(), 'limit' => 12]),
                'members' => $this->createHyperMediaLink(PageAccessControl::VIEW, $resource, HyperLink::GET, 'page-member', ['page_id' => $resource->getId(), 'view' => 'all', 'limit' => 12])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', PageResource::RESOURCE_NAME);
        $module = 'pages';
        return [
            [
                'path'      => 'pages/:id',
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
            'pages/member/add'    => [
                'method'    => 'post',
                'url'       => 'mobile/page-member',
                'data'      => 'page_id=:id',
                'new_state' => 'membership=1',
            ],
            'pages/member/remove' => [
                'method'    => 'delete',
                'url'       => 'mobile/page-member',
                'data'      => 'page_id=:id',
                'new_state' => 'membership=0',
            ],
            'pages/delete-member' => [
                'method'          => 'delete',
                'url'             => 'mobile/page-member',
                'data'            => 'page_id=:page_id, user_id=:id',
                'new_state'       => 'is_deleted=1',
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'pages/delete-admin'  => [
                'method'          => 'delete',
                'url'             => 'mobile/page-admin',
                'data'            => 'page_id=:page_id, user_id=:id',
                'new_state'       => 'is_admin=0',
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'pages/manage'        => [
                'routeName' => 'pages/edit',
            ],
            'pages/claim'         => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'pages',
                    'resource_name' => 'pages',
                    'formType'      => 'claimThisPage',
                ]
            ],
            'pages/remove_cover'  => [
                'method'          => 'delete',
                'url'             => 'mobile/pages/cover/:id',
                'data'            => 'id=:id',
                'new_state'       => 'can_remove_cover=false,cover_photo_id=0,covers=' . (new PageResource([]))->getDefaultImage(true),
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'pages/reassign-owner'         => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'pages',
                    'resource_name' => 'pages',
                    'formType'      => 'reassignOwner',
                ]
            ],
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $resourceName = (new PageResource([]))->getResourceName();
        $sectionResourceName = (new PageSectionResource([]))->getResourceName();
        $config = [
            'title'         => $l->translate('pages'),
            'home_view'     => 'menu',
            'home_resource' => new PageSectionResource([]),
            'main_resource' => new PageResource([]),

            'other_resources' => [
                new FeedResource([]),
                new PageCategoryResource([]),
                new PageInviteResource([]),
                new PageMemberResource([]),
                new PageAdminResource([]),
                new PageInfoResource([]),
                new PageWidgetResource([])
            ],
        ];
        if (isset($param['api_version_name']) && $param['api_version_name'] != 'mobile') {
            $config['category_resource'] = [
                $resourceName        => new PageTypeResource([]),
                $sectionResourceName => new PageTypeResource([])
            ];
        } else {
            $config['category_resource'] = new PageTypeResource([]);
        }

        $app = new MobileApp('pages', $config, isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $headerButtons[$resourceName] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ]
        ];
        if ($this->getAccessControl()->isGranted(PageAccessControl::ADD)) {
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
        $pageResource = PageResource::populate($page);
        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $pageResource);

        if (!$this->getSetting()->getUserSetting('photo.can_upload_photos')) {
            return $this->permissionError();
        }

        $userId = $this->pageService->getUserId($page['page_id']);
        $sTempPath = PHPFOX_DIR_CACHE . md5('pages_avatar' . Phpfox::getUserId()) . '.png';
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
                $maxSize = $this->getSetting()->getUserSetting('pages.max_upload_size_pages');
                if ($maxSize != 0 && $size > $maxSize) {
                    return $this->error($this->getLocalization()->translate('upload_failed_your_file_size_is_larger_then_our_limit_file_size',
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
                foreach (Phpfox::getService('pages')->getPhotoPicSizes() as $iSize) {
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

                $serverId = Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');

                if (!isset($aImage['pending_photo'])) {
                    if (!empty($page['image_path'])) {
                        $this->processService->deleteImage($page);
                    }

                    db()->update(':pages', [
                        'image_path'      => $sFileName,
                        'image_server_id' => $serverId
                    ], ['page_id' => $page['page_id']]);

                    // add feed after updating page's profile image
                    if (Phpfox::isModule('feed') && $oProfileImage = storage()->get('user/avatar/' . $userId)) {
                        Phpfox::getService('feed.process')->callback([
                            'table_prefix'     => 'pages_',
                            'module'           => 'pages',
                            'add_to_main_feed' => true,
                            'has_content'      => true
                        ])->add('pages_photo', $oProfileImage->value, 0, 0, $page['page_id'], $userId);
                    }

                    $page['image_path'] = $sFileName;
                    $page['image_server_id'] = $serverId;
                    $sMessage = 'pages_photo_successfully_updated';
                } else {
                    $cacheKey = 'pages_profile_photo_pending_' . $page['page_id'];
                    storage()->del($cacheKey);
                    storage()->set($cacheKey, [
                        'image_path'      => $sFileName,
                        'image_server_id' => $serverId
                    ]);

                    $sMessage = 'the_profile_photo_is_pending_please_waiting_until_the_approval_process_is_done';
                }

                // Update user space usage
                Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'pages', $iFileSizes);

                @unlink($sTempPath);

                return $this->success(PageResource::populate($page)->toArray(), [], $this->getLocalization()->translate($sMessage));
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
        $pageResource = PageResource::populate($page);

        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $pageResource);
        $this->denyAccessUnlessGranted(PageAccessControl::ADD_COVER, $pageResource);

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

        $userId = $this->pageService->getUserId($page['page_id']);
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
                    'callback_module'  => 'pages',
                    'callback_item_id' => $id,
                    'group_id'         => $id
                ];
                if ($iId = Phpfox::getService('photo.process')->add($userId, array_merge($aVals, $aImage))) {
                    $sFileName = $oFile->upload('image', $uploadDir, $iId, true);
                    $sFile = $uploadDir . sprintf($sFileName, '');
                    $iFileSizes = filesize($sFile);
                    // Get the current image width/height
                    $aSize = getimagesize($sFile);
                    $iServerId = Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');
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

    public function getClaimForm($params)
    {
        $editId = $this->resolver->resolveId($params);
        /** @var PageClaimForm $form */
        $form = $this->createForm(PageClaimForm::class, [
            'title'  => 'claim_page',
            'action' => UrlUtility::makeApiUrl('page-claim/:id', $editId),
            'method' => 'POST'
        ]);
        $page = $this->pageService->getForView($editId);
        if (empty($page)) {
            return $this->notFoundError();
        }
        if (!$this->getSetting()->getAppSetting('pages.admin_in_charge_of_page_claims')) {
            return $this->error($this->getLocalization()->translate('no_admin_has_been_set_to_handle_this_type_of_issues'));
        }

        if (!$this->getSetting()->getUserSetting('pages.can_claim_page') || !empty($page['is_admin']) || !empty($page['claim_id'])) {
            return $this->permissionError();
        }

        $form->assignValues([
            'message' => $this->getLocalization()->translate('page_claim_message', [
                'title' => $page['title'],
                'url'   => ($page['vanity_url'] ? \Phpfox_Url::instance()->makeUrl($page['vanity_url']) : Phpfox::permalink('pages', $page['page_id'], $page['title']))
            ])
        ]);

        return $this->success($form->getFormStructure());
    }

    public function claimPage($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var PageClaimForm $form */
        $form = $this->createForm(PageClaimForm::class);

        $page = $this->pageService->getForView($id);
        if (empty($page)) {
            return $this->notFoundError();
        }
        if (!($iUserId = $this->getSetting()->getAppSetting('pages.admin_in_charge_of_page_claims'))) {
            return $this->error($this->getLocalization()->translate('no_admin_has_been_set_to_handle_this_type_of_issues'));
        }

        if (!$this->getSetting()->getUserSetting('pages.can_claim_page') || !empty($page['is_admin']) || !empty($page['claim_id'])) {
            return $this->permissionError();
        }

        if ($form->isValid() && ($values = $form->getValues())) {
            $values['to'][] = $iUserId;
            $values['claim_page'] = true;
            if (Phpfox::isModule('mail')) {
                $success = Phpfox::getService('mail.process')->add($values, true);
            } else {
                //If Messages is disabled, don't send message
                $success = true;
            }
            if ($success) {
                \Phpfox_Database::instance()->insert(':pages_claim', ['status_id' => '1', 'page_id' => ((int)$page['page_id']), 'user_id' => $this->getUser()->getId(), 'time_stamp' => PHPFOX_TIME]);
                return $this->success([
                    'id' => $id
                ], [], $this->getLocalization()->translate('your_claim_request_sent_successfully'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    public function getFeedDisplay($param, $item)
    {
        if (empty($item)) {
            return null;
        }

        $resource = $this->populateResource(PageResource::class, $item);
        return $resource->getFeedDisplay();
    }

    public function searchFriendFilter($id, $friends)
    {
        $aInvites = $this->pageService->getCurrentInvites($id);
        list(, $aMembers) = $this->pageService->getMembers($id);
        foreach ($friends as $iKey => $friend) {
            if (is_array($aInvites) && isset($aInvites[$friend['user_id']])) {
                $friends[$iKey]['is_active'] = $this->getLocalization()->translate('invited');
                continue;
            }
            if (is_array($aMembers) && in_array($friend['user_id'], array_column($aMembers, 'user_id'))) {
                $friends[$iKey]['is_active'] = $this->getLocalization()->translate('liked');
            }
        }
        return $friends;
    }

    public function getLatestPages($iId, $userId = null, $iPagesLimit = 8)
    {
        $extra_conditions = 'pages.type_id = ' . (int)$iId . ($userId ? ' AND pages.user_id = ' . (int)$userId : '') . ' AND pages.app_id = 0 AND pages.view_id = 0';
        $iUserLogin = Phpfox::getUserId();

        if (Phpfox::getParam('core.friends_only_community')) {
            $sFriendsList = '0';
            if ($iUserLogin && Phpfox::isModule('friend')) {
                $aFriends = Phpfox::getService('friend')->getFromCache();
                $aFriendIds = array_column($aFriends, 'user_id');
                $aFriendIds[] = $iUserLogin;
                if (!empty($aFriendIds)) {
                    $sFriendsList = implode(',', $aFriendIds);
                }
            }
            $extra_conditions .= ' AND (pages.user_id IN (' . $sFriendsList . ') ';
            if (Phpfox::getParam('core.friends_only_community')) {
                $userPageIds = $this->pageService->getAllPageIdsOfMember();
                if (count($userPageIds)) {
                    $extra_conditions .= " OR pages.page_id IN (" . implode(',', $userPageIds) . ")";
                }
            }
            $extra_conditions .= ') ';
        }
        $sOrder = 'pages.time_stamp DESC';

        (($sPlugin = Phpfox_Plugin::get('mobile.service_page_api_getlatestpages_start')) ? eval($sPlugin) : false);

        $this->database()->select('pages.*')
            ->from(Phpfox::getT('pages'), 'pages')
            ->where($extra_conditions)
            ->order($sOrder)
            ->limit($iPagesLimit)
            ->union()
            ->unionFrom('pages');

        $this->database()->select('pages.is_featured, pages.is_sponsor, pages.type_id, pages.category_id, pages.title, pages.cover_photo_id, pages.page_id, pages.image_path, pages.image_server_id, pages.user_id, pages.view_id, pages.privacy, pages.total_like, pages.total_comment, pages.time_stamp, l.like_id AS is_liked')
            ->leftJoin(':like', 'l', 'l.type_id = \'pages\' AND l.item_id = pages.page_id AND l.user_id = ' . $iUserLogin);

        $aPages = $this->database()
            ->group('pages.page_id')
            ->order($sOrder)
            ->limit($iPagesLimit)
            ->execute('getSlaveRows');

        (($sPlugin = Phpfox_Plugin::get('mobile.service_page_api_getlatestpages_end')) ? eval($sPlugin) : false);

        return $aPages;
    }

    public function getForBrowse($userId = null, $iPagesLimit = null
    )
    {
        $aTypes = $this->database()->select('pt.*')
            ->from(Phpfox::getT('pages_type'), 'pt')
            ->where('pt.is_active = 1 AND pt.item_type = 0')
            ->order('pt.ordering ASC')
            ->execute('getSlaveRows');
        foreach ($aTypes as $iKey => $aType) {
            $aTypes[$iKey]['pages'] = $this->getLatestPages($aType['type_id'], $userId, $iPagesLimit);
        }

        return $aTypes;
    }

    public function updateLocation($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->setDefined(['location'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $page = $this->loadResourceById($params['id'], true);
        if (empty($page)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $page);
        if (!$this->setting->getAppSetting('core.google_api_key')) {
            return $this->error();
        }
        if (isset($params['location']['name'])) {
            $aUpdate['location_name'] = $this->preParse()->clean($params['location']['name']);
        } else {
            $aUpdate['location_name'] = null;
        }
        if (isset($params['location']['lat']) && $params['location']['lat'] != '-43.132123') {
            $aUpdate['location_latitude'] = $params['location']['lat'];
        } else {
            $aUpdate['location_latitude'] = null;
        }
        if (isset($params['location']['lng']) && $params['location']['lng'] != '9.140625') {
            $aUpdate['location_longitude'] = $params['location']['lng'];
        } else {
            $aUpdate['location_longitude'] = null;
        }
        if (!empty($aUpdate)) {
            if ($this->database()->update(':pages', $aUpdate, 'page_id = ' . (int)$params['id'])) {
                return $this->success([
                    'id' => (int)$params['id']
                ], [], $this->getLocalization()->translate('page_successfully_updated'));
            }
        }
        return $this->error($this->getErrorMessage());
    }

    public function getRelatedPage($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->setDefined(['limit'])
            ->setAllowedTypes('limit', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE
            ])->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $page = $this->loadResourceById($params['id']);
        if (!$page) {
            return $this->notFoundError();
        }
        $items = $this->pageService->getSameCategoryPages($params['id'], $params['limit']);
        $this->processRows($items);
        return $this->success($items);
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var PageResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('page_has_been_approved'));
        }
        return $this->permissionError();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('page_successfully_featured') : $this->getLocalization()->translate('page_successfully_un_featured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        /** @var PageResource $item */
        $item = $this->loadResourceById($id, true);
        if (empty($item)) {
            return $this->notFoundError();
        }

        if (!$this->getAccessControl()->isGranted(PageAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(PageAccessControl::PURCHASE_SPONSOR, $item)) {
            return $this->permissionError();
        }
        if ($this->processService->sponsor($id, $sponsor)) {
            if ($sponsor == 1) {
                $sModule = $this->getLocalization()->translate('pages');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'pages',
                    'item_id' => $id,
                    'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                ], false);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('pages', $id);
            }
            return $this->success([
                'is_sponsor' => !!$sponsor
            ], [], $sponsor ? $this->getLocalization()->translate('page_successfully_sponsored') : $this->getLocalization()->translate('page_successfully_un_sponsored'));
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

        $sponsoredItems = $this->pageService->getSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * Update view count for sponsored item
     *
     * @param $sponsorItems
     */
    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'pages');
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

        $featuredItems = $this->pageService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('pages', []);
        $resourceName = PageResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING, $screenSetting->getDefaultModuleListing(false, $l->translate('pages') . ' > ' . $l->translate('page') . ' - ' . $l->translate('view_all_pages_by_type')));
        $embedComponents = [
            'stream_pages_header_info',
            'stream_profile_menus',
            'stream_profile_is_pending',
            'stream_profile_information',
            'stream_pages_members',
            'stream_pages_also_likes'
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
            'screen_title'                 => $l->translate('pages') . ' > ' . $l->translate('page') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addSetting($resourceName, 'pages/edit', [
            ScreenSetting::LOCATION_TOP  => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => $l->translate('edit_page')
            ],
            ScreenSetting::LOCATION_MAIN => ['component' => 'edit_pages'],
            'no_ads'                     => true,
            'screen_title'               => $l->translate('pages') . ' > ' . $l->translate('edit_page_menu')
        ]);
        $screenSetting->addSetting($resourceName, 'smartPagesMember', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'members'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_TABS,
                'tabs'      => [
                    [
                        'label'         => 'all_members',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'pages',
                        'resource_name' => PageMemberResource::populate([])->getResourceName(),
                        'item_view'     => 'page_member',
                        'search'        => true,
                        'use_query'     => ['page_id' => ':id']
                    ],
                    [
                        'label'         => 'page_admins',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'pages',
                        'resource_name' => PageMemberResource::populate([])->getResourceName(),
                        'item_view'     => 'page_member',
                        'search'        => true,
                        'use_query'     => ['page_id' => ':id', 'view' => 'admin']
                    ]
                ],
            ],
            'screen_title'              => $l->translate('pages') . ' > ' . $l->translate('page_members'),
        ]);
        $screenSetting->addSetting($resourceName, 'detailPagesFeed', [
            ScreenSetting::LOCATION_HEADER => ['component' => 'feed_header'],
            ScreenSetting::LOCATION_MAIN   => ['component' => 'feed_detail'],
            'no_ads'                       => true
        ]);
        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_pages'),
                'resource_name' => $resourceName,
                'module_name'   => 'pages',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_pages'),
                'resource_name' => $resourceName,
                'module_name'   => 'pages',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);
        $resourceWidgetName = PageWidgetResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceWidgetName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'simple_header'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    'item_title',
                    'item_html_content',
                ]
            ],
            'screen_title'                 => $l->translate('pages') . ' > ' . $l->translate('menus') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'pages.index',
            ScreenSetting::MODULE_LISTING => 'pages.index',
            ScreenSetting::MODULE_DETAIL  => 'pages.view'
        ];
    }

    public function removeCover($params)
    {
        $id = $this->resolver->setRequired(['id'])->resolveId($params);

        $page = $this->loadResourceById($id);
        if (!$page) {
            return $this->notFoundError();
        }
        $pageResource = PageResource::populate($page);

        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $pageResource);
        $this->denyAccessUnlessGranted(PageAccessControl::REMOVE_COVER, $pageResource);
        if ($this->processService->removeLogo($id)) {
            return $this->success([], [], $this->getLocalization()->translate('page_cover_removed_successfully'));
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

        $data = [];
        $sMessage = '';
        switch ($action) {
            case Screen::ACTION_APPROVE_ITEMS:
                $this->denyAccessUnlessGranted(PageAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->approve($id)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_pending' => false];
                $sMessage = $this->getLocalization()->translate('pages_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(PageAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('page_s_successfully_featured') : $this->getLocalization()->translate('page_s_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(PageAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('pages_s_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}