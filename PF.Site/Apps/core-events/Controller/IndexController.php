<?php

namespace Apps\Core_Events\Controller;

use Phpfox;
use Phpfox_Database;
use Phpfox_Error;
use Phpfox_Module;
use Phpfox_Pager;
use Phpfox_Plugin;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class IndexController
 * @package Apps\Core_Events\Controller
 */
class IndexController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::getService('event.process')->convertOldLocation([]);
        Phpfox::getUserParam('event.can_access_event', true);

        $aParentModule = $this->getParam('aParentModule');

        if ($aParentModule === null && $this->request()->getInt('req2') > 0) {
            return Phpfox_Module::instance()->setController('event.view');
        }

        if (($iRedirectId = $this->request()->getInt('redirect'))
            && ($aEvent = Phpfox::getService('event')->getEvent($iRedirectId, true))
            && $aEvent['module_id'] != 'event'
            && Phpfox::hasCallback($aEvent['module_id'], 'getEventRedirect')
        ) {
            if (($sForward = Phpfox::callback($aEvent['module_id'] . '.getEventRedirect', $aEvent['event_id']))) {
                Phpfox::getService('notification.process')->delete('event_invite', $aEvent['event_id'],
                    Phpfox::getUserId());

                $this->url()->forward($sForward);
            }
        }

        if (($iDeleteId = $this->request()->getInt('delete'))) {
            if (($mDeleteReturn = Phpfox::getService('event.process')->delete($iDeleteId))) {
                if (is_bool($mDeleteReturn)) {
                    $this->url()->send('event', null, _p('event_successfully_deleted'));
                } else {
                    $this->url()->forward($mDeleteReturn, _p('event_successfully_deleted'));
                }
            }
        }

        if (($iRedirectId = $this->request()->getInt('redirect')) && ($aEvent = Phpfox::getService('event')->getEvent($iRedirectId,
                true))
        ) {
            Phpfox::getService('notification.process')->delete('event_invite', $aEvent['event_id'],
                Phpfox::getUserId());

            $this->url()->permalink('event', $aEvent['event_id'], $aEvent['title']);
        }

        $aUser = [];
        $bIsUserProfile = false;
        $sDefaultSort = Phpfox::getParam('event.event_default_sort_time', 'ongoing');
        if (defined('PHPFOX_IS_AJAX_CONTROLLER')) {
            $bIsUserProfile = true;
            $sDefaultSort = 'all-time';
            $aUser = Phpfox::getService('user')->get($this->request()->get('profile_id'));
            $this->setParam('aUser', $aUser);
        }
        if (defined('PHPFOX_IS_USER_PROFILE')) {
            $bIsUserProfile = true;
            $sDefaultSort = 'all-time';
            $aUser = $this->getParam('aUser');
        }
        if ($aParentModule === null && (!defined('PHPFOX_IS_USER_PROFILE') || (isset($aUser) && $aUser['user_id'] == Phpfox::getUserId()))) {
            if (Phpfox::getUserParam('event.can_create_event')) {
                sectionMenu(_p('add_new_event'), url('/event/add'));
            }
        }
        $oServiceEventBrowse = Phpfox::getService('event.browse');
        $sCategory = null;
        $sView = $this->request()->get('view', false);
        $aCallback = $this->getParam('aCallback', false);
        $aCountriesValue = [];
        $aCountries = Phpfox::getService('core.country')->get();
        foreach ($aCountries as $sKey => $sValue) {
            $aCountriesValue[] = [
                'link' => $sKey,
                'phrase' => $sValue
            ];
        }

        $aSearchFields = [
            'type' => 'event',
            'field' => 'm.event_id',
            'ignore_blocked' => true,
            'search_tool' => [
                'default_when' => (in_array($sView, ['pending', 'my']) ? 'all-time' : $sDefaultSort),
                'when_field' => 'start_time',
                'when_end_field' => 'end_time',
                'when_upcoming' => true,
                'when_ongoing' => true,
                'table_alias' => 'm',
                'search' => [
                    'action' => ($aParentModule === null ? ($bIsUserProfile === true ? $this->url()->makeUrl($aUser['user_name'],
                        ['event', 'view' => $this->request()->get('view')]) : $this->url()->makeUrl('event',
                        ['view' => $this->request()->get('view')])) : $aParentModule['url'] . 'event/view_' . $this->request()->get('view') . '/'),
                    'default_value' => _p('search_events'),
                    'name' => 'search',
                    'field' => 'm.title'
                ],
                'sort' => [
                    'latest' => ['m.start_time', _p('latest'), 'ASC'],
                    'most-liked' => ['m.total_like', _p('most_liked')],
                    'most-talked' => ['m.total_comment', _p('most_discussed')]
                ],
                'show' => [12, 15, 18, 21],
            ],

        ];
        if (!$bIsUserProfile) {
            $aSearchFields['search_tool']['custom_filters'] = [
                _p('location') => [
                    'param' => 'location',
                    'default_phrase' => _p('anywhere'),
                    'data' => $aCountriesValue,
                    'height' => '300px',
                    'width' => '150px'
                ]
            ];
        }
        $this->search()->set($aSearchFields);
        $aBrowseParams = [
            'module_id' => 'event',
            'alias' => 'm',
            'field' => 'event_id',
            'table' => Phpfox::getT('event'),
            'hide_view' => ['pending', 'my'],
            'no_union_from' => true,
        ];

        switch ($sView) {
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('event.can_approve_events', true);
                $this->search()->setCondition('AND m.view_id = 1');
                break;
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                break;
            default:
                if ($bIsUserProfile) {
                    $this->search()->setCondition('AND m.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND m.module_id = \'event\' AND m.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND m.user_id = ' . (int)$aUser['user_id']);
                } elseif ($aParentModule !== null) {
                    $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.module_id = \'' . Phpfox_Database::instance()->escape($aParentModule['module_id']) . '\' AND m.item_id = ' . (int)$aParentModule['item_id'] . '');
                } else {
                    switch ($sView) {
                        case 'attending':
                            $oServiceEventBrowse->attending(1);
                            break;
                        case 'may-attend':
                            $oServiceEventBrowse->attending(2);
                            break;
                        case 'not-attending':
                            $oServiceEventBrowse->attending(3);
                            break;
                        case 'invites':
                            $oServiceEventBrowse->attending(0);
                            break;
                    }

                    if ($sView == 'attending' || $sView === 'invites' || $sView == 'may-attend') {
                        Phpfox::isUser(true);
                        $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)');
                    } else {
                        if ($aCallback !== false) {
                            $this->search()->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.item_id = ' . $aCallback['item'] . '');
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

                    if ($this->request()->getInt('user') && ($aUserSearch = Phpfox::getService('user')->getUser($this->request()->getInt('user')))) {
                        $this->search()->setCondition('AND m.user_id = ' . (int)$aUserSearch['user_id']);
                        $this->template()->setBreadCrumb($aUserSearch['full_name'] . '\'s Events',
                            $this->url()->makeUrl('event', ['user' => $aUserSearch['user_id']]), true);
                    }

                }
                break;
        }

        $this->template()->setBreadCrumb(_p('all_events'),
            ($aCallback !== false ? ($aCallback['url_home'] . '/event') : ($bIsUserProfile ? $this->url()->makeUrl($aUser['user_name'],
                'event') : $this->url()->makeUrl('event'))));

        if (($sLocation = $this->request()->get('location'))) {
            $this->search()->setCondition('AND m.country_iso = \'' . Phpfox_Database::instance()->escape($sLocation) . '\'');
        }

        if ($this->request()->getInt('sponsor') == 1) {
            $this->search()->setCondition('AND m.is_sponsor != 1');
            Phpfox::addMessage(_p('sponsor_help'));
        }

        if ($this->request()->get('req2') == 'category') {
            $sCategory = $this->request()->getInt('req3');
            $iCategory = (int)$sCategory;
            $this->search()->setCondition('AND mcd.category_id = ' . $iCategory);

            $aEventCategory = Phpfox::getService('event.category')->getCategory($iCategory);
            if ($aEventCategory) {
                $this->search()->setFormUrl($this->url()->permalink([
                    'event.category',
                    'view' => $sView
                ], $iCategory, $aEventCategory['name']));
            }
        }

        if ($sCategory !== null) {
            $aCategories = Phpfox::getService('event.category')->getParentBreadcrumb($sCategory);
            $this->setParam('sCurrentCategory', $sCategory);
            $this->setParam('iParentCategoryId', Phpfox::getService('event.category')->getParentCategoryId($sCategory));
            $iCnt = 0;
            foreach ($aCategories as $aCategory) {
                $iCnt++;

                $this->template()->setTitle(_p($aCategory[0]));

                if ($aCallback !== false) {
                    $sHomeUrl = '/' . Phpfox_Url::instance()->doRewrite($aCallback['url_home'][0]) . '/' . implode('/',
                            $aCallback['url_home'][1]) . '/' . Phpfox_Url::instance()->doRewrite('event') . '/';
                    $aCategory[1] = preg_replace('/^http:\/\/(.*?)\/' . Phpfox_Url::instance()->doRewrite('event') . '\/(.*?)$/i',
                        'http://\\1' . $sHomeUrl . '\\2', $aCategory[1]);
                }

                $this->template()->setBreadCrumb($aCategory[0], $aCategory[1], (empty($sView) ? true : false));
            }
        }

        if ($sView == 'featured') {
            $this->search()->setCondition('AND m.is_featured = 1');
        }

        $oServiceEventBrowse->callback($aCallback)->category($sCategory);

        // PARENT MODULE: PRIVACY AND BREADCRUMB
        $bIsAdmin = false;
        if (!empty($aParentModule['module_id']) && Phpfox::hasCallback($aParentModule['module_id'], 'isAdmin')) {
            $bIsAdmin = Phpfox::callback($aParentModule['module_id'] . '.isAdmin', $aParentModule['item_id']);
        }
        if (defined('PHPFOX_IS_PAGES_VIEW') && PHPFOX_IS_PAGES_VIEW && defined('PHPFOX_PAGES_ITEM_TYPE') && $aParentModule) {
            $sService = PHPFOX_PAGES_ITEM_TYPE ? PHPFOX_PAGES_ITEM_TYPE : 'pages';
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $aParentModule['item_id'], 'event.view_browse_events')
            ) {
                $this->template()->assign(['aSearchTool' => []]);
                return Phpfox_Error::display(_p('Cannot display this section due to privacy.'));
            }

            if (Phpfox::getService($sService)->isAdmin($aParentModule['item_id'])) {
                $bIsAdmin = true;
                $this->request()->set('view', 'pages_admin');
            } elseif (Phpfox::getService($sService)->isMember($aParentModule['item_id'])) {
                $this->request()->set('view', 'pages_member');
            }

            $sTitle = Phpfox::getService($sService)->getTitle($aParentModule['item_id']);
            $this->template()
                ->clearBreadCrumb()
                ->setBreadCrumb($sTitle, $aParentModule['url'])
                ->setBreadCrumb(_p('all_events'), $aParentModule['url'] . 'event/')
                ->setTitle(_p('events') . ' &raquo; ' . $sTitle, true);
        } else {
            $this->template()->setTitle(($bIsUserProfile ? _p('full_name_s_events', ['full_name' => $aUser['full_name']]) : _p('events')));
        }

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('event.event_paging_mode', 'loadmore'))
            ->execute();

        $bSetFilterMenu = (!defined('PHPFOX_IS_USER_PROFILE') && !defined('PHPFOX_IS_PAGES_VIEW'));
        if ($sPlugin = Phpfox_Plugin::get('event.component_controller_index_set_filter_menu_1')) {
            eval($sPlugin);
            if (isset($mReturnFromPlugin)) {
                return $mReturnFromPlugin;
            }
        }

        if ($bSetFilterMenu) {
            Phpfox::getService('event')->buildSectionMenu();
        }

        $aEvents = $this->search()->browse()->getRows();

        $sSort = $this->search()->getSort();
        $bIsGroupByDate = preg_match('/m.total_like/', $sSort) || preg_match('/m.total_comment/', $sSort) ? false : true;

        if ($sView == 'invites') {
            if ($bIsGroupByDate) {
                foreach ($aEvents as $sDate => $aItems) {
                    foreach ($aItems as $iKey => $aItem) {
                        $aEvents[$sDate][$iKey]['is_invited'] = Phpfox::getService('event')->isInvitedByOwner($aItem['event_id'], $aItem['user_id'], Phpfox::getUserId());
                    }
                }
            } else {
                foreach ($aEvents as $iKey => $aItem) {
                    $aEvents[$iKey]['is_invited'] = Phpfox::getService('event')->isInvitedByOwner($aItem['event_id'], $aItem['user_id'], Phpfox::getUserId());
                }
            }

        }

        $this->template()
            ->setHeader('cache', [
                    'country.js' => 'module_core',
                ]
            )
            ->setMeta('keywords', Phpfox::getParam('event.event_meta_keywords'))
            ->setMeta('description', Phpfox::getParam('event.event_meta_description'))
            ->assign([
                    'aEvents' => $aEvents,
                    'sView' => $sView,
                    'aCallback' => $aCallback,
                    'sParentLink' => ($aCallback !== false ? $aCallback['url_home'] . '.event' : 'event'),
                    'sApproveLink' => $this->url()->makeUrl('event', ['view' => 'pending']),
                    'bIsAdmin' => $bIsAdmin,
                    'bIsGroupByDate' => $bIsGroupByDate
                ]
            );

        if ($aCallback !== false) {
            $this->template()->rebuildMenu('event.index', $aCallback['url_home']);
        }

        Phpfox_Pager::instance()->set([
            'page' => $this->search()->getPage(),
            'size' => $this->search()->getDisplay(),
            'count' => $this->search()->browse()->getCount(),
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ]);

        $aModerationMenu = [];
        $bShowModerator = $bIsAdmin;
        if ($sView == 'pending') {
            if (Phpfox::getUserParam('event.can_approve_events')) {
                $aModerationMenu[] = [
                    'phrase' => _p('approve'),
                    'action' => 'approve'
                ];
            }
        } elseif (Phpfox::getUserParam('event.can_feature_events')) {
            $aModerationMenu[] = [
                'phrase' => _p('feature'),
                'action' => 'feature'
            ];
            $aModerationMenu[] = [
                'phrase' => _p('un_feature'),
                'action' => 'un-feature'
            ];
        }
        if (Phpfox::getUserParam('event.can_delete_other_event') || $bIsAdmin) {
            $aModerationMenu[] = [
                'phrase' => _p('delete'),
                'action' => 'delete',
                'message' => _p('are_you_sure_you_want_to_delete_selected_events_permanently'),
            ];
        }
        if (count($aModerationMenu)) {
            $this->setParam('global_moderation', [
                    'name' => 'event',
                    'ajax' => 'event.moderation',
                    'menu' => $aModerationMenu
                ]
            );
            $bShowModerator = true;
        }
        $this->template()->assign(['bShowModerator' => $bShowModerator]);

        if (defined('PHPFOX_IS_PAGES_VIEW') || defined('PHPFOX_IS_USER_PROFILE')) {
            $this->setParam('bHideBirthday', true);
        }
        $this->setParam('aGmapView', [
            'type' => 'event',
            'url' => $this->url()->makeUrl('event.map', ['type' => 'event', 'view' => $sView])
        ]);
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('event.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}