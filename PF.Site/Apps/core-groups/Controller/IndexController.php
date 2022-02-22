<?php

namespace Apps\PHPfox_Groups\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Core\Lib;
use Phpfox;
use Phpfox_Component;
use Phpfox_Locale;
use Phpfox_Module;
use Phpfox_Pager;
use Phpfox_Plugin;


class IndexController extends Phpfox_Component
{
    public function process()
    {
        $bIsUserProfile = $this->getParam('bIsProfile');
        $aUser = [];
        if ($bIsUserProfile) {
            $aUser = $this->getParam('aUser');
        }

        Phpfox::getUserParam('groups.pf_group_browse', true);

        $oGroupFacade = Phpfox::getService('groups.facade');
        $sView = $this->request()->get('view');

        if (defined('PHPFOX_IS_AJAX_CONTROLLER')) {
            $bIsProfile = true;
            $aUser = Phpfox::getService('user')->get($this->request()->get('profile_id'));
            $this->setParam('aUser', $aUser);
        } else {
            $bIsProfile = $this->getParam('bIsProfile');
            if ($bIsProfile === true) {
                $aUser = $this->getParam('aUser');
            }
        }

        $userId = 0;
        if ($bIsProfile) {
            $userId = $aUser['user_id'];
            $this->template()
                ->setTitle(_p('full_name_s_groups', ['full_name' => $aUser['full_name']]))
                ->setBreadCrumb(_p('Groups'), $this->url()->makeUrl($aUser['user_name'], ['groups']));
        } else {
            $this->template()
                ->setTitle(_p('Groups'))
                ->setBreadCrumb(_p('Groups'), $this->url()->makeUrl('groups'));
        }

        $aSearchTool = [
            'table_alias' => 'pages',
            'search' => [
                'action' => ($bIsProfile === true ? $this->url()->makeUrl($aUser['user_name'],
                    ['groups', 'view' => $this->request()->get('view')]) : $this->url()->makeUrl('groups',
                    ['view' => $this->request()->get('view')])),
                'default_value' => _p('Search groups'),
                'name' => 'search',
                'field' => 'pages.title',
            ],
            'sort' => [
                'latest' => ['pages.time_stamp', _p('Latest')],
                'most-liked' => ['pages.total_like', _p('Most Popular')],
            ],
            'show' => [10, 15, 20],
        ];

        $bInHomepage = $this->_checkIsInHomePage();
        if ($bInHomepage) {
            $aSearchTool['no_filters'] = [_p('sort'), _p('show'), _p('when')];
        }

        $this->search()->set([
                'type' => 'groups',
                'field' => 'pages.page_id',
                'search_tool' => $aSearchTool
            ]
        );

        $aBrowseParams = [
            'module_id' => 'groups',
            'alias' => 'pages',
            'field' => 'page_id',
            'table' => Phpfox::getT('pages'),
            'hide_view' => ['pending', 'my'],
            'select' => 'pages_type.name as type_name, '
        ];

        $sView = trim($sView, '/');
        $aModerations = [];

        $aGroupIds = Phpfox::getService('groups')->getAllGroupIdsOfMember($userId);
        if (count($aGroupIds)) {
            Phpfox::getService('groups.browse')->groupIds($aGroupIds);
        }

        switch ($sView) {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . Phpfox::getUserId());
                break;
            case 'joined':
            case 'all':
                Phpfox::isUser(true);
                $sGroupIds = '0';
                if (count($aGroupIds)) {
                    $sGroupIds = implode(',', $aGroupIds);
                }
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.page_id IN (' . $sGroupIds . ')');
                break;
            case 'pending':
                Phpfox::isUser(true);
                if (Phpfox::getService('groups.facade')->getUserParam('can_approve_pages')) {
                    $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 1');
                    $aModerations[] = [
                        'phrase' => _p('approve'),
                        'action' => 'approve'
                    ];
                } else {
                    \Phpfox_Url::instance()->send('groups');
                }
                break;
            default:
                if ($sView == 'friend') {
                    Phpfox::isUser(true);
                }
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0');
                break;
        }

        if ($oGroupFacade->getUserParam('can_delete_all_pages')) {
            $aModerations[] = [
                'phrase' => _p('delete'),
                'action' => 'delete'
            ];
        }

        // moderations mass actions
        if (!empty($aModerations)) {
            $this->setParam('global_moderation', [
                    'name' => 'groups',
                    'ajax' => 'groups.pageModeration',
                    'menu' => $aModerations
                ]
            );
            $this->template()->assign('bShowModeration', true);
        } else {
            $this->template()->assign('bShowModeration', false);
        }

        $aFilterMenu = Phpfox::getService('groups')->getSectionMenu();
        $this->template()->buildSectionMenu('groups', $aFilterMenu);

        // add button to add new group
        if (Phpfox::getService('groups')->canUserCreateNewGroup(Phpfox::getUserId(), false) &&
            (!defined('PHPFOX_CURRENT_TIMELINE_PROFILE') || PHPFOX_CURRENT_TIMELINE_PROFILE == Phpfox::getUserId())
        ) {
            sectionMenu(_p('Add a Group'), url('/groups/add'));
        }

        $bIsValidCategory = false;

        if ($this->request()->get('req2') == 'category' && ($iCategoryId = $this->request()->getInt('req3')) && ($aType = Phpfox::getService('groups.type')->getById($iCategoryId))) {
            $bIsValidCategory = true;
            $this->setParam('iParentCategoryId', $aType['type_id']);

            $sType = (Lib::phrase()->isPhrase($aType['name'])) ? _p($aType['name']) : Phpfox_Locale::instance()->convert($aType['name']);
            $this->template()->setBreadCrumb($sType, Phpfox::permalink('groups.category', $aType['type_id'],
                    $sType) . ($sView ? 'view_' . $sView . '/' . '' : ''), true);
            $this->template()->assign('aType', $aType);

            $this->search()->setFormUrl($this->url()->permalink([
                'groups.category',
                'view' => $sView
            ], $iCategoryId, $sType));
        }

        if ($this->request()->get('req2') == 'sub-category' && ($iSubCategoryId = $this->request()->getInt('req3')) && ($aCategory = Phpfox::getService('groups.category')->getById($iSubCategoryId))) {
            $bIsValidCategory = true;
            $this->setParam('sCurrentCategory', $iSubCategoryId);
            $this->setParam('iParentCategoryId', $aCategory['type_id']);
            $sTypeName = (Lib::phrase()->isPhrase($aCategory['type_name'])) ? _p($aCategory['type_name']) : Phpfox_Locale::instance()->convert($aCategory['type_name']);
            $this->template()->setBreadCrumb($sTypeName, Phpfox::permalink('groups.category', $aCategory['type_id'],
                    $sTypeName) . ($sView ? 'view_' . $sView . '/' . '' : ''));
            $sCategoryName = (Lib::phrase()->isPhrase($aCategory['name'])) ? _p($aCategory['name']) : Phpfox_Locale::instance()->convert($aCategory['name']);
            $this->template()->setBreadCrumb($sCategoryName,
                Phpfox::permalink('groups.sub-category', $aCategory['category_id'],
                    $sCategoryName) . ($sView ? 'view_' . $sView . '/' . '' : ''), true);

            // set search condition
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aCategory['category_id']);

            $this->search()->setFormUrl($this->url()->permalink([
                'groups.sub-category',
                'view' => $sView
            ], $iSubCategoryId, $sCategoryName));
        }

        if (isset($aType) && isset($aType['type_id'])) {
            $this->search()->setCondition('AND pages.type_id = ' . (int)$aType['type_id']);
        }

        if (isset($aType) && isset($aType['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aType['category_id']);
        } elseif (isset($aType) && isset($aCategory) && isset($aCategory['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aCategory['category_id']);
        }

        if ($bIsUserProfile) {
            if ($sView != 'all') {
                $this->search()->setCondition('AND pages.user_id = ' . (int)$aUser['user_id']);
            }
            if ($aUser['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('core.can_view_private_items')) {
                $this->search()->setCondition('AND pages.reg_method <> 2');
            }
        }

        $aPages = [];
        $aCategories = [];
        $bShowCategories = false;
        if ($this->search()->isSearch() || defined('PHPFOX_IS_USER_PROFILE')) {
            $bIsValidCategory = true;
        }

        if ($bIsValidCategory) {
            if ($sView != 'pending') {
                $this->search()->setCondition(Phpfox::callback('groups.getExtraBrowseConditions', 'pages'));
            }
            $this->search()->browse()->params($aBrowseParams)->execute(function (\Phpfox_Search_Browse $browse) {
                $browse->database()->join(':pages_type', 'pages_type',
                    'pages_type.type_id = pages.type_id AND pages_type.item_type = 1');
            });
            $aPages = $this->search()->browse()->getRows();

            foreach ($aPages as $iKey => $aPage) {
                $aPages[$iKey]['joinRequested'] = Phpfox::getService('groups')->joinGroupRequested($aPage['page_id']);
            }

            $this->search()->browse()->setPagingMode(Phpfox::getParam('groups.pagination_at_search_groups', 'loadmore'));
            Phpfox_Pager::instance()->set([
                'page' => $this->search()->getPage(),
                'size' => $this->search()->getDisplay(),
                'count' => $this->search()->browse()->getCount(),
                'paging_mode' => $this->search()->browse()->getPagingMode()
            ]);
        } else {
            $bShowCategories = true;
            $iGroupsLimitPerCategory = Phpfox::getParam('groups.groups_limit_per_category', 0);
            $aCategories = Phpfox::getService('groups.category')->getForBrowse(true,
                ($sView == 'my' ? Phpfox::getUserId() : ($bIsProfile ? $aUser['user_id'] : null)),
                $iGroupsLimitPerCategory, $sView);
        }

        $iCountPage = 0;
        if (count($aCategories)) {
            foreach ($aCategories as &$aCategory) {
                if (isset($aCategory['pages']) && is_array($aCategory['pages'])) {
                    $iCountPage += count($aCategory['pages']);
                    // count number of pages that not show
                    if (isset($iGroupsLimitPerCategory) && $iGroupsLimitPerCategory && ($aCategory['total_pages'] - $iGroupsLimitPerCategory > 0)) {
                        $aCategory['remain_pages'] = $aCategory['total_pages'] - count($aCategory['pages']);
                    }
                }
            }
        }

        // no pending items in pending view => redirect to all groups
        if ($sView == 'pending' && (!$bIsValidCategory && !$iCountPage)) {
            \Phpfox_Url::instance()->send('groups');
        }

        $this->template()->assign([
            'sView' => $sView,
            'aPages' => $aPages,
            'aCategories' => $aCategories,
            'bShowCategories' => $bShowCategories,
            'iCountPage' => $iCountPage,
            'bIsSearch' => $this->search()->isSearch()
        ])->setMeta([
            'keywords' => _p('seo_groups_meta_keywords'),
            'description' => _p('seo_groups_meta_description')
        ]);

        $iStartCheck = 0;
        if ($bIsValidCategory == true) {
            $iStartCheck = 5;
        }
        $aRediAllow = ['category'];
        if (defined('PHPFOX_IS_USER_PROFILE') && PHPFOX_IS_USER_PROFILE) {
            $aRediAllow[] = 'groups';
        }
        $aCheckParams = [
            'url' => $this->url()->makeUrl('groups'),
            'start' => $iStartCheck,
            'reqs' => [
                '2' => $aRediAllow,
            ],
        ];

        if (defined('PHPFOX_CURRENT_TIMELINE_PROFILE') && PHPFOX_CURRENT_TIMELINE_PROFILE) {
            $this->template()->assign('iCurrentProfileId', PHPFOX_CURRENT_TIMELINE_PROFILE);
        }

        if (Phpfox::getParam('core.force_404_check') && !Phpfox::getService('core.redirect')->check404($aCheckParams)) {
            return Phpfox_Module::instance()->setController('error.404');
        }

        return null;
    }

    private function _checkIsInHomePage()
    {
        $bIsInHomePage = false;
        $aParentModule = $this->getParam('aParentModule');
        $sTempSearch = $this->request()->get('s', 0);
        if (!$sTempSearch
            && !isset($aParentModule['module_id'])
            && !$this->request()->get('sort')
            && !$this->request()->get('when')
            && !$this->request()->get('show')
            && $this->request()->get('req2') == ''
            && !defined('PHPFOX_IS_USER_PROFILE')) {
            $bIsInHomePage = true;
        }
        return $bIsInHomePage;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('groups.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}
