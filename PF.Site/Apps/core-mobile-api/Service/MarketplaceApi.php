<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Marketplace\Service\Browse;
use Apps\Core_Marketplace\Service\Category\Category;
use Apps\Core_Marketplace\Service\Marketplace;
use Apps\Core_Marketplace\Service\Process;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Marketplace\MarketplaceForm;
use Apps\Core_MobileApi\Api\Form\Marketplace\MarketplaceSearchForm;
use Apps\Core_MobileApi\Api\Resource\MarketplaceCategoryResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceInviteResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceInvoiceResource;
use Apps\Core_MobileApi\Api\Resource\MarketplacePhotoResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\Marketplace\MarketplaceAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Phpfox_Database;

class MarketplaceApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    /**
     * @var Marketplace
     */
    protected $marketplaceService;

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

    /**
     * @var \Apps\Core_BetterAds\Service\Process
     */
    protected $adProcessService = null;

    /**
     * MarketplaceApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->marketplaceService = Phpfox::getService('marketplace');
        $this->categoryService = Phpfox::getService('marketplace.category');
        $this->processService = Phpfox::getService('marketplace.process');
        $this->browserService = Phpfox::getService('marketplace.browse');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'marketplace/search-form' => [
                'get' => 'searchForm'
            ],
            'marketplace/reopen/:id'  => [
                'put' => 'reopenListing'
            ],
            'marketplace/buy-now/:id' => [
                'get' => 'buyNowListing'
            ]
        ];
    }


    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'category', 'q', 'sort', 'profile_id', 'limit', 'page', 'when', 'location', 'bounds'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'pending', 'friend', 'invites', 'expired', 'sold', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('category', 'int')
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('bounds', 'array')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return $this->permissionError();
        }
        $sort = $params['sort'];
        $view = $params['view'];
        $isProfile = $params['profile_id'];
        $user = [];

        if (in_array($view, ['feature', 'sponsor'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }

        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
        }
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'marketplace',
            'alias'     => 'l',
            'field'     => 'listing_id',
            'table'     => Phpfox::getT('marketplace'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'marketplace.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'l',
            'location_field' => [
                'latitude_field' => 'location_lat',
                'longitude_field' => 'location_lng'
            ]
        ]);
        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'l.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'l.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'l.total_comment DESC';
                break;
            default:
                $sort = 'l.time_stamp DESC';
                break;
        }

        switch ($view) {
            case 'sold':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND l.user_id = ' . Phpfox::getUserId());
                    $this->search()->setCondition('AND l.is_sell = 1');
                } else {
                    return $this->permissionError();
                }
                break;
            case 'my':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND l.user_id = ' . Phpfox::getUserId());
                } else {
                    return $this->permissionError();
                }
                break;
            case 'pending':
                if (Phpfox::getUserParam('marketplace.can_approve_listings')) {
                    $this->search()->setCondition('AND l.view_id = 1');
                } else {
                    if ($isProfile) {
                        $this->search()->setCondition("AND l.view_id IN(" . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ") AND l.user_id = " . $user['user_id'] . "");
                    } else {
                        return $this->permissionError();
                    }
                }
                break;
            case 'expired':
                if (Phpfox::getParam('marketplace.days_to_expire_listing') > 0 && Phpfox::getUserParam('marketplace.can_view_expired')) {
                    $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400));
                    $this->search()->setCondition('AND l.time_stamp < ' . $iExpireTime);
                    break;
                } else {
                    $this->search()->setCondition('AND l.time_stamp < 0');
                }
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition("AND l.view_id IN(" . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ") AND l.user_id = " . $user['user_id'] . "");
                } else {
                    switch ($view) {
                        case 'invites':
                            Phpfox::isUser(true);
                            $this->browserService->seen();
                            break;
                    }

                    $this->search()->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%)');
                }
                break;
        }
        if ($this->getSetting()->getAppSetting('marketplace.days_to_expire_listing') > 0 && !in_array($view, ['my', 'expired', 'invites'])) {
            $iExpireTime = (PHPFOX_TIME - ($this->getSetting()->getAppSetting('marketplace.days_to_expire_listing') * 86400));
            $this->search()->setCondition(' AND l.time_stamp >=' . $iExpireTime);
        }
        //search on map
        $this->search()->setABounds($params['bounds']);

        //location
        if ($params['location']) {
            $this->search()->setCondition('AND l.country_iso = \'' . Phpfox_Database::instance()->escape($params['location']) . '\'');
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND l.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }
        //category
        if ($params['category']) {
            $this->browserService->category($params['category']);
            $this->search()->setCondition('AND mcd.category_id = ' . (int)$params['category']);
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);

        $this->browse()->params($browseParams)->execute();

        $items = $this->browse()->getRows();

        $this->processRows($items);
        return $this->success($items);
    }

    function findOne($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return $this->permissionError();
        }
        $item = $this->marketplaceService->getListing($params['id']);
        if (!$item) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(MarketplaceAccessControl::VIEW, MarketplaceResource::populate($item));

        if (Phpfox::isUser() && $item['invite_id'] && !$item['visited_id'] && $item['user_id'] != Phpfox::getUserId()) {
            Phpfox::getService('marketplace.process')->setVisit($item['listing_id'], Phpfox::getUserId());
        }
        // Increment the view counter
        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$item['is_viewed']) {
                $updateCounter = true;
                Phpfox::getService('track.process')->add('marketplace', $item['listing_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('marketplace', $item['listing_id']);
                } else {
                    Phpfox::getService('track.process')->update('marketplace', $item['listing_id']);
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->processService->updateView($item['listing_id']);
            $item['total_view'] += 1;
        }
        $item['images_list'] = $this->marketplaceService->getImages($item['listing_id']);
        $item['is_detail'] = true;
        $resource = $this->populateResource(MarketplaceResource::class, $item);
        $this->setHyperlinks($resource, true);
        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->loadFeedParam()
            ->toArray());
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var MarketplaceForm $form */
        $form = $this->createForm(MarketplaceForm::class, [
            'title'  => 'create_a_listing',
            'action' => UrlUtility::makeApiUrl('marketplace'),
            'method' => 'POST'
        ]);
        $form->setCategories($this->getCategories());
        $form->setCurrencies($this->getCurrencies());
        $listing = $this->loadResourceById($editId, true);
        if ($editId && empty($listing)) {
            return $this->notFoundError();
        }

        if ($listing) {
            $this->denyAccessUnlessGranted(MarketplaceAccessControl::EDIT, $listing);
            $form->setEditing(true);
            $form->setTitle('edit_listing')
                ->setAction(UrlUtility::makeApiUrl('marketplace/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($listing);
        } else {
            $this->denyAccessUnlessGranted(MarketplaceAccessControl::ADD);
            if (($iFlood = $this->getSetting()->getUserSetting('marketplace.flood_control_marketplace')) !== 0) {
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field'      => 'time_stamp', // The time stamp field
                        'table'      => Phpfox::getT('marketplace'), // Database table we plan to check
                        'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];

                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error($this->getLocalization()->translate('you_are_creating_a_listing_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }
        }

        return $this->success($form->getFormStructure());
    }

    protected function getCategories()
    {
        return $this->categoryService->getForBrowse();
    }

    protected function getCurrencies()
    {
        return $this->getLocalization()->getAllCurrencies();
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::ADD);
        /** @var MarketplaceForm $form */
        $form = $this->createForm(MarketplaceForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MarketplaceResource::populate([])->getResourceName(),
                    'editing'       => true
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    protected function processCreate($values)
    {
        if (($iFlood = $this->getSetting()->getUserSetting('marketplace.flood_control_marketplace')) !== 0) {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp', // The time stamp field
                    'table'      => Phpfox::getT('marketplace'), // Database table we plan to check
                    'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->error($this->getLocalization()->translate('you_are_creating_a_listing_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        if (isset($values['short_description'])) {
            $values['mini_description'] = $values['short_description'];
        }
        if (isset($values['text'])) {
            $values['description'] = $values['text'];
        }
        if (isset($values['categories'])) {
            $values['category'] = $values['categories'];
        }
        return $this->processService->add($values);
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var MarketplaceForm $form */
        $form = $this->createForm(MarketplaceForm::class);
        $form->setEditing(true);
        $listing = $this->loadResourceById($id, true);
        if (empty($listing)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::EDIT, $listing);
        if ($form->isValid() && ($values = $form->getValues())) {
            $values['view_id'] = $listing->view_id;
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MarketplaceResource::populate([])->getResourceName()
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    protected function processUpdate($id, $values)
    {
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        if (isset($values['short_description'])) {
            $values['mini_description'] = $values['short_description'];
        }
        if (isset($values['text'])) {
            $values['description'] = $values['text'];
        }
        if (isset($values['categories'])) {
            $values['category'] = $values['categories'];
        }
        if (empty($values['tag_list'])) {
            $values['tag_list'] = '';
        }
        if (!empty($values['is_closed'])) {
            $values['view_id'] = 2;
        } else if (isset($values['view_id']) && $values['view_id'] == 2) {
            $values['view_id'] = 0;
        }
        return $this->processService->update($id, $values);
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

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
        if (!$itemId || !$item) {
            return $this->notFoundError();
        }

        if (Phpfox::getUserParam('marketplace.can_access_marketplace') && $this->processService->delete($itemId)) {
            return $this->success([], [], $this->getLocalization()->translate('successfully_deleted_listing'));
        }

        return $this->permissionError();
    }

    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->marketplaceService->getForEdit($id, true);
        if (empty($item['listing_id'])) {
            return false;
        }
        if ($returnResource) {
            $item['is_edit'] = true;
            return MarketplaceResource::populate($item);
        }

        return $item;
    }

    public function processRow($item)
    {
        /** @var MarketplaceResource $resource */
        $resource = $this->populateResource(MarketplaceResource::class, $item);
        $this->setHyperlinks($resource);

        $view = $this->request()->get('view');
        $shortFields = [];

        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'statistic', 'image', 'id', 'price'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
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
        $resource = $this->populateResource(MarketplaceResource::class, $item);

        return $resource->getFeedDisplay();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MarketplaceAccessControl($this->getSetting(), $this->getUser());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::VIEW);
        /** @var MarketplaceSearchForm $form */
        $form = $this->createForm(MarketplaceSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('marketplace')
        ]);

        return $this->success($form->getFormStructure());
    }

    protected function setHyperlinks(MarketplaceResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            MarketplaceAccessControl::VIEW         => $this->createHyperMediaLink(MarketplaceAccessControl::VIEW, $resource,
                HyperLink::GET, 'marketplace/:id', ['id' => $resource->getId()]),
            MarketplaceAccessControl::EDIT         => $this->createHyperMediaLink(MarketplaceAccessControl::EDIT, $resource,
                HyperLink::GET, 'marketplace/form/:id', ['id' => $resource->getId()]),
            MarketplaceAccessControl::MANAGE_PHOTO => $this->createHyperMediaLink(MarketplaceAccessControl::MANAGE_PHOTO, $resource,
                HyperLink::GET, 'marketplace-photo/form/:id', ['id' => $resource->getId()]),
            MarketplaceAccessControl::DELETE       => $this->createHyperMediaLink(MarketplaceAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'marketplace/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(MarketplaceAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'marketplace']),
                'comments' => $this->createHyperMediaLink(MarketplaceAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'marketplace'])
            ]);
        }


    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('marketplace', [
            'title'             => $l->translate('marketplace'),
            'home_view'         => 'menu',
            'main_resource'     => new MarketplaceResource([]),
            'category_resource' => new MarketplaceCategoryResource([]),
            'other_resources'   => [
                new MarketplacePhotoResource([]),
                new MarketplaceInviteResource([]),
                new MarketplaceInvoiceResource([])
            ],
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $resourceName = (new MarketplaceResource([]))->getResourceName();
        $headerButtons[$resourceName] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ],
        ];
        if ($this->getAccessControl()->isGranted(MarketplaceAccessControl::ADD)) {
            $headerButtons[$resourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => $resourceName]
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    public function getActions()
    {
        return [
            'marketplace/manage-invite' => [
                'routeName' => 'marketplace/manage-invite'
            ],
            'marketplace/photos'        => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'marketplace',
                    'resource_name' => 'marketplace',
                    'formType'      => 'photos'
                ]
            ]
        ];
    }


    public function searchFriendFilter($id, $friends)
    {
        $aInviteCache = Phpfox::getService('marketplace')->isAlreadyInvited($id, $friends);
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

        /** @var MarketplaceResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('listing_has_been_approved'));
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
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('listing_successfully_featured') : $this->getLocalization()->translate('listing_successfully_un_featured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::SPONSOR, $item);

        if ($this->processService->sponsor($id, $sponsor)) {
            if ($sponsor == 1) {
                $sModule = $this->getLocalization()->translate('marketplace');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'marketplace',
                    'item_id' => $id,
                    'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                ], false);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('marketplace', $id);
            }
            return $this->success([
                'is_sponsor' => !!$sponsor
            ], [], $sponsor ? $this->getLocalization()->translate('listing_successfully_sponsored') : $this->getLocalization()->translate('listing_successfully_un_sponsored'));
        }
        return $this->error();
    }

    public function reopenListing($params)
    {
        $id = $this->resolver->resolveId($params);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::REOPEN, $item);
        if ((PHPFOX_TIME - ($this->getSetting()->getAppSetting('marketplace.days_to_expire_listing') * 86400)) <= $item->time_stamp) {
            return $this->error($this->getLocalization()->translate('marketplace_not_expired'));
        }
        if ($this->processService->reopenListing($id)) {
            return $this->success([
                'is_expired' => false,
            ], [], $this->getLocalization()->translate('listing_reopened_successfully'));
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

        $sponsoredItems = $this->marketplaceService->getSponsorListings($limit, $cacheTime);

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
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'marketplace');
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

        $featuredItems = $this->marketplaceService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }

        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('marketplace', []);
        $resourceName = MarketplaceResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header'],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    [
                        'component'       => 'item_image',
                        'imageResizeMode' => 'contain',
                        'aspectRatio'     => 1
                    ],
                    'item_title',
                    'item_pricing',
                    'item_author',
                    'item_stats',
                    'item_like_phrase',
                    [
                        'component' => 'item_pending',
                        'message'   => 'listing_is_pending_approval'
                    ],
                    [
                        'component'   => 'item_pending',
                        'message'     => 'listing_expired_and_not_available_main_section',
                        'check_field' => 'is_expired'
                    ],
                    'item_description',
                    'item_html_content',
                    'item_category',
                    'item_tags',
                    'item_user_tags',
                    'item_location',
                    'marketplace_bottom_action'
                ],
            ],
            'screen_title'                 => $l->translate('marketplace') . ' > ' . $l->translate('marketplace') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addSetting($resourceName, 'marketplace/manage-invite', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => $l->translate('invites')
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_TABS,
                'tabs'      => [
                    [
                        'label'         => $l->translate('visited'),
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module'        => 'marketplace',
                        'resource_name' => MarketplaceInviteResource::populate([])->getResourceName(),
                        'item_view'     => 'marketplace_invite',
                        'use_query'     => ['visited' => '1', 'listing_id' => ':id']
                    ],
                    [
                        'label'         => $l->translate('invited'),
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module'        => 'marketplace',
                        'resource_name' => MarketplaceInviteResource::populate([])->getResourceName(),
                        'item_view'     => 'marketplace_invite',
                        'use_query'     => ['visited' => '0', 'listing_id' => ':id']
                    ],

                ]
            ],
            'screen_title'                 => $l->translate('marketplace') . ' > ' . $l->translate('invites') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_listings'),
                'resource_name' => $resourceName,
                'module_name'   => 'marketplace',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_listings'),
                'resource_name' => $resourceName,
                'module_name'   => 'marketplace',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ]
        ]);
        $resourceInvoiceName = MarketplaceInvoiceResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceInvoiceName, ScreenSetting::MODULE_LISTING);

        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'marketplace.index',
            ScreenSetting::MODULE_LISTING => 'marketplace.index',
            ScreenSetting::MODULE_DETAIL  => 'marketplace.view'
        ];
    }

    public function buyNowListing($params)
    {
        $id = $this->resolver->resolveId($params);

        $item = $this->loadResourceById($id);

        if (!$item) {
            return $this->notFoundError();
        }
        $resource = MarketplaceResource::populate($item);
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::BUY_NOW, $resource);

        if ($invoiceId = $this->processService->addInvoice($item['listing_id'],
            $item['currency_id'], $item['price'])) {
            $invoice = $this->marketplaceService->getInvoice($invoiceId);
            $image = $resource->getImage();
            return $this->success([
                'pending_purchase' => [
                    'title'         => $resource->getTitle(),
                    'description'   => $resource->getShortDescription(),
                    'price_text'    => $resource->getPrice(),
                    'seller_id'     => $item['user_id'],
                    'image'         => isset($image->sizes['400']) ? $image->sizes['400'] : $image,
                    'item_number'   => 'marketplace|' . $invoiceId,
                    'currency_id'   => $invoice['currency_id'],
                    'price'         => $invoice['price'],
                    'allow_point'   => $item['allow_point_payment'],
                    'allow_gateway' => $item['is_sell']
                ]
            ]);
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
                $this->denyAccessUnlessGranted(MarketplaceAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    $this->processService->approve($id);
                }
                $data = array_merge($data, ['is_pending' => false]);
                $sMessage = $this->getLocalization()->translate('listing_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(MarketplaceAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    $this->processService->feature($id, $value);
                }
                $data = array_merge($data, ['is_featured' => !!$value]);
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('listing_s_successfully_featured') : $this->getLocalization()->translate('listing_s_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(MarketplaceAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    $this->processService->delete($id);
                }
                $sMessage = $this->getLocalization()->translate('listing_s_successfully_deleted');
                break;
        }
        return $this->success($data, [], $sMessage);
    }
}