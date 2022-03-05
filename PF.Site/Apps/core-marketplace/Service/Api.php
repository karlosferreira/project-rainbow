<?php
namespace Apps\Core_Marketplace\Service;

use Core\Api\ApiServiceBase;
use Phpfox;

class Api extends ApiServiceBase
{
    public function __construct()
    {
        $this->setPublicFields([
            'listing_id',
            'module_id',
            'item_id',
            'user_id',
            'view_id',
            'title',
            'category_id',
            'category_name',
            'currency_id',
            'privacy',
            'price',
            'country_iso',
            'country_child_id',
            'postal_code',
            'city',
            'location',
            'location_lat',
            'location_lng',
            'image_path',
            'time_stamp',
            'total_comment',
            'total_like',
            'total_attachment',
            'total_view',
            'is_featured',
            'is_sponsor',
            'is_sell',
            'allow_point_payment',
            'auto_sell',
            'mini_description',
            'description',
            'is_notified',
        ]);
    }

    /**
     * @description: update an item
     * @param $params
     *
     * @return array|bool
     */
    public function put($params)
    {
        $this->isUser();

        $aVals = $this->request()->get('val');
        $sModule = !empty($aVals['module_id']) ? $aVals['module_id'] : null;
        $iItemId = !empty($aVals['item_id']) ? $aVals['item_id'] : null;

        if (!empty($aListing = Phpfox::getService('marketplace')->getForEdit($params['id']))) {
            if (!empty($aListing['module_id']) && !empty($aListing['item_id'])) {
                if (isset($aListing['module_id'])
                    && Phpfox::isModule($aListing['module_id'])
                    && Phpfox::hasCallback($aListing['module_id'], 'checkPermission')) {
                    if (!Phpfox::callback($aListing['module_id'] . '.checkPermission', $aListing['item_id'], 'marketplace.view_browse_marketplace_listings')) {
                        return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
                    }
                }
                $sModule = $aListing['module_id'];
                $iItemId = $aListing['item_id'];
            }
        }

        $aValidation = [
            'title' => _p('provide_a_name_for_this_listing'),
            'location' => _p('provide_a_location_for_this_listing'),
            'price' => [
                'def' => 'money',
                'title' => _p('please_type_valid_price')
            ]
        ];

        $oValidator = \Phpfox_Validator::instance()->set([
                'sFormName' => 'js_marketplace_form',
                'aParams' => $aValidation
            ]
        );

        $aCallback = null;
        if (!empty($sModule) && Phpfox::hasCallback($sModule, 'getItem')) {
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItemId);
            if ($aCallback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        } else {
            if (!empty($sModule) && !empty($iItemId) && $sModule != 'marketplace' && $aCallback === null) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        if ($oValidator->isValid($aVals) && Phpfox::getService('marketplace.process')->update($aListing['listing_id'], $aVals)) {
            return $this->get(['id' => $params['id']], [_p('{{ item }} successfully updated.', ['item' => _p('Marketplace')])]);
        }

        return $this->error();
    }

    /**
     * @description: get info of an item
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function get($params, $messages = [])
    {
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace')
            || empty($aListing = Phpfox::getService('marketplace')->getListing($params['id']))
            || (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('marketplace', $aListing['listing_id'], $aListing['user_id'],
                $aListing['privacy'], $aListing['is_friend'], true))) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                ['action' => _p('view__l'), 'item' => _p('Marketplace')]));
        }

        if (!empty($aListing['module_id']) && !empty($aListing['item_id'])) {
            if (!Phpfox::isModule($aListing['module_id'])) {
                return $this->error(_p('Cannot find the parent item.'));
            } elseif (Phpfox::hasCallback($aListing['module_id'], 'checkPermission')
                && !Phpfox::callback($aListing['module_id'] . '.checkPermission', $aListing['item_id'], 'marketplace.view_browse_marketplace_listings')) {
                return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
            } elseif (Phpfox::hasCallback($aListing['module_id'], 'getItem') && empty(Phpfox::callback($aListing['module_id'] . '.getItem', $aListing['item_id']))) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $aListing['user_id'])) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                ['action' => _p('view__l'), 'item' => _p('Marketplace')]));
        }

        Phpfox::getService('marketplace')->getPermissions($aListing);

        if (Phpfox::isModule('notification') && $aListing['user_id'] == Phpfox::getUserId()) {
            Phpfox::getService('notification.process')->delete('marketplace_approved', $aListing['listing_id'],
                Phpfox::getUserId());
        }

        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$aListing['is_viewed']) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('marketplace', $aListing['listing_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('marketplace', $aListing['listing_id']);
                } else {
                    Phpfox::getService('track.process')->update('marketplace', $aListing['listing_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }

        if ($bUpdateCounter) {
            Phpfox::getService('marketplace.process')->updateView($aListing['listing_id']);
            $aListing['total_view'] += 1;
        }

        if ($aListing['image_path']) {
            $aListing['image_path'] = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aListing['listing_id'],
                    'path' => 'marketplace.url_image',
                    'file' => $aListing['image_path'],
                    'suffix' => '_400_square',
                    'return_url' => true
                ]
            );
        } else {
            $aListing['image_path'] = Phpfox::getParam('marketplace.marketplace_default_photo');
        }

        if (!empty($aListing['category_name']) && \Core\Lib::phrase()->isPhrase($aListing['category_name'])) {
            $aListing['category_name'] = _p($aListing['category_name']);
        }



        return $this->success($this->getItem($aListing, 'public'), $messages);
    }

    /**
     * @description: delete an item
     * @param $params
     *
     * @return array|bool
     */
    public function delete($params)
    {
        $this->isUser();

        if (Phpfox::getService('marketplace.process')->delete($params['id'])) {
            return $this->success([], [_p('{{ item }} successfully deleted.', ['item' => _p('Marketplace')])]);
        }

        return $this->error();
    }

    /**
     * @description: add new item
     * @return array|bool
     */
    public function post()
    {
        $this->isUser();

        if (!Phpfox::getUserParam('marketplace.can_create_listing')) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('Marketplace')]));
        } elseif (!Phpfox::getService('marketplace')->checkLimitation()) {
            return $this->error(_p('marketplace_you_have_reached_your_limit_to_create_new_listings'));
        }

        $aVals = $this->request()->get('val');
        $sModule = !empty($aVals['module_id']) ? $aVals['module_id'] : null;
        $iItemId = !empty($aVals['item_id']) ? $aVals['item_id'] : null;

        $aValidation = [
            'title' => _p('provide_a_name_for_this_listing'),
            'location' => _p('provide_a_location_for_this_listing'),
            'price' => [
                'def' => 'money',
                'title' => _p('please_type_valid_price')
            ]
        ];

        $oValidator = \Phpfox_Validator::instance()->set([
                'sFormName' => 'js_marketplace_form',
                'aParams' => $aValidation
            ]
        );

        $aCallback = null;
        if (!empty($sModule) && !empty($iItemId) && Phpfox::hasCallback($sModule, 'getItem')) {
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItemId);
            if ($aCallback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }

            if (Phpfox::hasCallback($sModule, 'checkPermission')
                && !Phpfox::callback($sModule . '.checkPermission', $iItemId, 'marketplace.share_marketplace_listings')) {
                return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
            }
        } else {
            if (!empty($sModule) && !empty($iItemId) && $sModule != 'marketplace' && $aCallback === null) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        if ($oValidator->isValid($aVals)) {
            if (($iFlood = Phpfox::getUserParam('marketplace.flood_control_marketplace')) !== 0) {
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field' => 'time_stamp', // The time stamp field
                        'table' => Phpfox::getT('marketplace'), // Database table we plan to check
                        'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];

                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error(_p('you_are_creating_a_listing_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }

            if (\Phpfox_Error::isPassed() && !empty($iId = Phpfox::getService('marketplace.process')->add($aVals))) {
                return $this->get(['id' => $iId], [_p('{{ item }} successfully added.', ['item' => _p('Marketplace')])]);
            }
        }

        return $this->error();
    }

    /**
     * @description: get items
     * @return array|bool
     */
    public function gets()
    {
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('Marketplace')]));
        }

        $userId = $this->request()->get('user_id');
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $view = $this->request()->get('view');
        $categoryId = $this->request()->get('category_id');
        $aUser = !empty($userId) ? Phpfox::getService('user')->get($userId) : [];
        $bIsProfile = !empty($aUser['user_id']);
        $oServiceMarketplaceBrowse = Phpfox::getService('marketplace.browse');

        $this->initSearchParams();

        $aSearchFields = [
            'type'           => 'marketplace',
            'field'          => 'l.listing_id',
            'ignore_blocked' => true,
            'search_tool'    => [
                'table_alias' => 'l',
                'search'      => [
                    'name'          => 'search',
                    'field'         => ['l.title', 'mt.description_parsed']
                ],
                'sort'        => [
                    'latest'      => ['l.time_stamp', _p('latest')],
                    'most-liked'  => ['l.is_sponsor DESC, l.total_like', _p('most_liked')],
                    'most-talked' => ['l.is_sponsor DESC, l.total_comment', _p('most_discussed')]
                ],
                'show'        => [$this->getSearchParam('limit')]
            ]
        ];

        if (empty($aUser)) {
            $aCountriesValue = [];
            $aCountries = Phpfox::getService('core.country')->get();
            foreach ($aCountries as $sKey => $sValue) {
                $aCountriesValue[] = [
                    'link'   => $sKey,
                    'phrase' => $sValue
                ];
            }
            $aSearchFields['search_tool']['custom_filters'] = [
                _p('location') => [
                    'param'          => 'location',
                    'data'           => $aCountriesValue,
                ]
            ];
        }

        $this->search()->set($aSearchFields);

        $aBrowseParams = [
            'module_id' => 'marketplace',
            'alias'     => 'l',
            'field'     => 'listing_id',
            'table'     => Phpfox::getT('marketplace'),
            'hide_view' => ['pending', 'my']
        ];

        switch ($view) {
            case 'sold':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND l.user_id = ' . Phpfox::getUserId());
                $this->search()->setCondition('AND (l.is_sell = 1 OR l.allow_point_payment = 1)');

                break;
            case 'featured':
                $this->search()->setCondition('AND l.is_featured = 1');
                break;
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND l.user_id = ' . Phpfox::getUserId());
                break;
            case 'pending':
                if (Phpfox::getUserParam('marketplace.can_approve_listings')) {
                    $this->search()->setCondition('AND l.view_id = 1');
                } else {
                    if ($bIsProfile) {
                        $this->search()->setCondition("AND l.view_id IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND l.user_id = " . $aUser['user_id'] . "");
                    } else {
                        $this->search()->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%)');
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
                if ($bIsProfile === true) {
                    $this->search()->setCondition("AND l.item_id = 0 AND l.view_id = 0 AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND l.user_id = " . $aUser['user_id'] . "");
                } else if (!empty($moduleId) && !empty($itemId)) {
                    $this->search()->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%) AND l.module_id = \'' . db()->escape($moduleId) . '\' AND l.item_id = ' . (int)$itemId . '');
                } else {
                    if ($view == 'invites') {
                        Phpfox::isUser(true);
                        $oServiceMarketplaceBrowse->seen();
                    }
                    $this->search()->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%)');
                }
                break;
        }

        if (empty($moduleId) && empty($itemId) && !in_array($view, ['my', 'sold', 'pending', 'invites', 'featured'])) {
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

        if (($sLocation = $this->request()->get('location'))) {
            $this->search()->setCondition('AND l.country_iso = \'' . db()->escape($sLocation) . '\'');
        }

        if (!empty($categoryId)) {
            $this->search()->setCondition('AND mcd.category_id = ' . $categoryId);
        }

        if (!empty($moduleId) && !empty($itemId) && in_array($moduleId, ['pages', 'groups'])) {
            $sService = $moduleId == 'pages' ? 'pages' : 'groups';
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $itemId, 'marketplace.view_browse_marketplace_listings')) {
                return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('Marketplace')]));
            }
        }

        $oServiceMarketplaceBrowse->category($categoryId);
        $oServiceMarketplaceBrowse->isApi(true);

        if ($this->search()->isSearch()) {
            $oServiceMarketplaceBrowse->search();
        }

        if (Phpfox::getParam('marketplace.days_to_expire_listing') > 0 && !in_array($view, ['my', 'expired', 'invites'])) {
            $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400));
            $this->search()->setCondition(' AND l.time_stamp >=' . $iExpireTime);
        }

        // if its a user trying to buy sponsor space he should get only his own listings
        if ($this->request()->get('sponsor') == 'help') {
            $this->search()->setCondition('AND l.user_id = ' . Phpfox::getUserId() . ' AND is_sponsor != 1');
        }

        $this->search()->setContinueSearch(true);

        $this->search()->browse()
            ->params($aBrowseParams)
            ->execute();

        $items = $this->search()->browse()->getRows();
        $parsedItems = [];

        foreach ($items as $item) {
            if (!empty($item['category_name']) && \Core\Lib::phrase()->isPhrase($item['category_name'])) {
                $item['category_name'] = _p($item['category_name']);
            }
            if ($item['image_path']) {
                $item['image_path'] = Phpfox::getLib('image.helper')->display([
                    'server_id' => $item['listing_id'],
                    'path' => 'marketplace.url_image',
                    'file' => $item['image_path'],
                    'suffix' => '_400_square',
                    'return_url' => true,
                ]);
            } else {
                $item['image_path'] = Phpfox::getParam('marketplace.marketplace_default_photo');
            }
            $parsedItems[] = $this->getItem($item);
        }

        return $this->success($parsedItems);
    }
}