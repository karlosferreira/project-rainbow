<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_6\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_MobileApi\Version1_6\Api\Form\Marketplace\MarketplaceForm;
use Apps\Core_MobileApi\Version1_6\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Version1_6\Api\Security\Marketplace\MarketplaceAccessControl;
use Phpfox;
use Phpfox_Database;

class MarketplaceApi extends \Apps\Core_MobileApi\Service\MarketplaceApi
{
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
                ], [], $this->localization->translate('listing_successfully_added'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
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
                ], [], $this->localization->translate('listing_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    public function findOne($params)
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
    
    public function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::VIEW);
        $params = $this->resolver->setDefined([
            'view', 'category', 'q', 'sort', 'profile_id', 'limit', 'page', 'when', 'location', 'bounds', 'module_id', 'item_id'
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
            ->setAllowedTypes('item_id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $sort = $params['sort'];
        $view = $params['view'];
        $isProfile = $params['profile_id'];
        $user = [];

        if (in_array($view, ['feature', 'sponsor'])) {
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
                }else if ($parentModule !== null) {
                    $this->search()->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%) AND l.module_id = \'' . Phpfox_Database::instance()->escape($parentModule['module_id']) . '\' AND l.item_id = ' . (int)$parentModule['item_id'] . '');
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
        if ($parentModule === null && !in_array($view, ['my', 'sold', 'pending', 'invites', 'featured'])) {
            if ((Phpfox::getParam('marketplace.display_marketplace_created_in_page') || Phpfox::getParam('marketplace.display_marketplace_created_in_group'))) {
                $aModules = [];
                if (Phpfox::getParam('marketplace.display_marketplace_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                    $aModules[] = 'groups';
                }
                if (Phpfox::getParam('marketplace.display_marketplace_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                    $aModules[] = 'pages';
                }
                if (count($aModules)) {
                    $this->search()->setCondition('AND (l.module_id IN ("' . implode('","', $aModules) . '") OR l.module_id = \'marketplace\')');
                } else {
                    $this->search()->setCondition('AND l.module_id = \'marketplace\'');
                }
            } else {
                $this->search()->setCondition('AND l.item_id = 0');
            }
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
        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $items = $this->browse()->getRows();

        $this->processRows($items);
        return $this->success($items);
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MarketplaceAccessControl($this->getSetting(), $this->getUser());
        $moduleId = $this->request()->get('module_id');
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

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $isSponsorFeed = $this->resolver->resolveSingle($params, 'is_sponsor_feed', null, [], 0);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($isSponsorFeed) {
            //Support un-sponsor in feed
            $this->denyAccessUnlessGranted(MarketplaceAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('marketplace', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(MarketplaceAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(MarketplaceAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }
            if ($this->processService->sponsor($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('marketplace');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module' => 'marketplace',
                        'item_id' => $id,
                        'name' => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('marketplace', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('listing_successfully_sponsored') : $this->getLocalization()->translate('listing_successfully_un_sponsored'));
            }
        }
        return $this->error();
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
}