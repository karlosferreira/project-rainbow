<?php

namespace Apps\Core_Pages\Controller;

use Core\Lib;
use Phpfox;
use Phpfox_Locale;
use Phpfox_Module;
use Phpfox_Pager;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class IndexController extends \Phpfox_Component
{
    /**
     * Controller
     * @throws \Exception
     */
    public function process()
    {
        $bIsUserProfile = $this->getParam('bIsProfile');
        $aUser = [];
        if ($bIsUserProfile) {
            $aUser = $this->getParam('aUser');
        }
        Phpfox::getUserParam('pages.can_view_browse_pages', true);

        if ($this->request()->getInt('req2') > 0) {
            return Phpfox_Module::instance()->setController('pages.view');
        }

        if (($iDeleteId = $this->request()->getInt('delete')) && Phpfox::getService('pages.process')->delete($iDeleteId)) {
            // clear cache if page's event is featured or sponsored
            if ($iProfileId = $this->request()->getInt('profile')) {
                $aUser = Phpfox::getService('user')->getUser($iProfileId);
                $this->url()->send($aUser['user_name'] . '.pages', [], _p('page_successfully_deleted'));
            } else {
                $this->url()->send('pages', [], _p('page_successfully_deleted'));
            }
        }

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
                ->setTitle(_p('full_name_s_pages', ['full_name' => $aUser['full_name']]))
                ->setBreadCrumb(_p('pages'), $this->url()->makeUrl($aUser['user_name'], ['pages']));
        } else {
            $this->template()
                ->setTitle(_p('pages'))
                ->setBreadCrumb(_p('pages'), $this->url()->makeUrl('pages'));
        }

        $aSearchTool = [
            'table_alias' => 'pages',
            'search' => [
                'action' => ($bIsProfile === true ? $this->url()->makeUrl($aUser['user_name'],
                    ['pages', 'view' => $this->request()->get('view')]) : $this->url()->makeUrl('pages',
                    ['view' => $this->request()->get('view')])),
                'default_value' => _p('search_pages'),
                'name' => 'search',
                'field' => 'pages.title'
            ],
            'sort' => [
                'latest' => ['pages.time_stamp', _p('latest')],
                'most-liked' => ['pages.total_like', _p('most_liked')]
            ],
            'show' => [10, 15, 20]
        ];
        $bInHomepage = $this->_checkIsInHomePage();
        if ($bInHomepage) {
            $aSearchTool['no_filters'] = [_p('sort'), _p('show'), _p('when')];
        }

        $this->search()->set([
                'type' => 'pages',
                'field' => 'pages.page_id',
                'search_tool' => $aSearchTool
            ]
        );

        $aBrowseParams = [
            'module_id' => 'pages',
            'alias' => 'pages',
            'field' => 'page_id',
            'table' => Phpfox::getT('pages'),
            'hide_view' => ['pending', 'my']
        ];

        $sView = trim($sView, '/');
        $aModerations = [];
        // check if user can delete all pages
        if (Phpfox::getUserParam('pages.can_delete_all_pages')) {
            $aModerations[] = [
                'phrase' => _p('delete'),
                'action' => 'delete',
                'message' => _p('pages_are_you_sure_you_want_to_delete_selected_pages_permanently'),
            ];
        }

        $aPageIds = Phpfox::getService('pages')->getAllPageIdsOfMember($userId);
        if(count($aPageIds)) {
            Phpfox::getService('pages.browse')->pageIds($aPageIds);
        }

        switch ($sView) {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . Phpfox::getUserId());
                break;
            case 'liked':
            case 'all':
                Phpfox::isUser(true);
                $sPageIds = '0';
                if (count($aPageIds)) {
                    $sPageIds = implode(',', $aPageIds);
                }
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.page_id IN (' . $sPageIds . ')');
                break;
            case 'pending':
                Phpfox::isUser(true);
                if (Phpfox::getUserParam('pages.can_approve_pages')) {
                    $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 1');
                } else {
                    \Phpfox_Url::instance()->send('pages');
                }
                $aModerations[] = [
                    'phrase' => _p('approve'),
                    'action' => 'approve'
                ];
                break;
            default:
                if ($sView == 'friend') {
                    Phpfox::isUser(true);
                }
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0');
                break;
        }

        $aFilterMenu = Phpfox::getService('pages')->getSectionMenu();
        $this->template()->buildSectionMenu('pages', $aFilterMenu);
        $bIsValidCategory = false;

        if ($this->request()->get('req2') == 'category' && ($iCategoryId = $this->request()->getInt('req3')) && ($aType = Phpfox::getService('pages.type')->getById($iCategoryId))) {
            $bIsValidCategory = true;
            $this->setParam('iParentCategoryId', $aType['type_id']);

            $sType = (Lib::phrase()->isPhrase($aType['name'])) ? _p($aType['name']) : Phpfox_Locale::instance()->convert($aType['name']);
            $this->template()->setBreadCrumb($sType, Phpfox::permalink('pages.category', $aType['type_id'],
                    $sType) . ($sView ? 'view_' . $sView . '/' . '' : ''), true);
            $this->template()->assign('aType', $aType);

            $this->search()->setFormUrl($this->url()->permalink([
                'pages.category',
                'view' => $sView
            ], $iCategoryId, $sType));
        }

        if ($this->request()->get('req2') == 'sub-category' && ($iSubCategoryId = $this->request()->getInt('req3')) &&
            ($aCategory = Phpfox::getService('pages.category')->getById($iSubCategoryId))
        ) {
            $bIsValidCategory = true;
            $this->setParam('sCurrentCategory', $iSubCategoryId);
            $this->setParam('iParentCategoryId', $aCategory['type_id']);
            $sTypeName = (Lib::phrase()->isPhrase($aCategory['type_name'])) ? _p($aCategory['type_name']) : Phpfox_Locale::instance()->convert($aCategory['type_name']);
            $this->template()->setBreadCrumb($sTypeName, Phpfox::permalink('pages.category', $aCategory['type_id'],
                    $sTypeName) . ($sView ? 'view_' . $sView . '/' . '' : ''));
            $sCategoryName = (Lib::phrase()->isPhrase($aCategory['name'])) ? _p($aCategory['name']) : Phpfox_Locale::instance()->convert($aCategory['name']);
            $this->template()->setBreadCrumb($sCategoryName,
                Phpfox::permalink('pages.sub-category', $aCategory['category_id'],
                    $sCategoryName) . ($sView ? 'view_' . $sView . '/' . '' : ''), true);

            $this->search()->setFormUrl($this->url()->permalink([
                'pages.sub-category',
                'view' => $sView
            ], $iSubCategoryId, $sCategoryName));
        }

        if (isset($aType) && isset($aType['type_id'])) {
            $this->search()->setCondition('AND pages.type_id = ' . (int)$aType['type_id']);
        }

        if (isset($aType) && isset($aType['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aType['category_id']);
        } elseif (isset($aCategory) && isset($aCategory['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aCategory['category_id']);
        }

        if ($bIsUserProfile && $sView != 'all') {
            $this->search()->setCondition('AND pages.user_id = ' . (int)$aUser['user_id']);
        }

        $aPages = [];
        $aCategories = [];
        $bShowCategories = false;
        if ($this->search()->isSearch() || defined('PHPFOX_IS_USER_PROFILE')) {
            $bIsValidCategory = true;
        }

        if ($bIsValidCategory) {
            $this->search()->browse()->params($aBrowseParams)->execute(function (\Phpfox_Search_Browse $browse) {
                $browse->database()->select('pages_type.name as type_name, ')->join(':pages_type', 'pages_type',
                    'pages_type.type_id = pages.type_id AND pages_type.item_type = 0');
            });
            $aPages = $this->search()->browse()->getRows();

            $this->search()->browse()->setPagingMode(Phpfox::getParam('pages.pagination_at_search_page', 'loadmore'));
            Phpfox_Pager::instance()->set([
                'page' => $this->search()->getPage(),
                'size' => $this->search()->getDisplay(),
                'count' => $this->search()->browse()->getCount(),
                'paging_mode' => $this->search()->browse()->getPagingMode()
            ]);
        } else {
            $bShowCategories = true;
            $iPagesLimitPerCategory = Phpfox::getParam('pages.pages_limit_per_category', 0);
            $aCategories = Phpfox::getService('pages.category')->getForBrowse(true,
                ($sView == 'my' ? Phpfox::getUserId() : ($bIsProfile ? $aUser['user_id'] : null)),
                $iPagesLimitPerCategory, $sView);
        }

        $iCountPage = 0;
        if (count($aCategories)) {
            foreach ($aCategories as &$aCategory) {
                if (isset($aCategory['pages']) && is_array($aCategory['pages'])) {
                    $iCountPage += count($aCategory['pages']);
                    // count number of pages that not show
                    if (isset($iPagesLimitPerCategory) && $iPagesLimitPerCategory && ($aCategory['total_pages'] - $iPagesLimitPerCategory > 0)) {
                        $aCategory['remain_pages'] = $aCategory['total_pages'] - count($aCategory['pages']);
                    }
                }
            }
        }

        if ($sView == 'pending' && (!$bIsValidCategory && !$iCountPage)) {
            \Phpfox_Url::instance()->send('pages');
        }

        // Only admin and moderator have mass actions permissions
        if (!empty($aModerations)) {
            $this->setParam('global_moderation', [
                    'name' => 'pages',
                    'ajax' => 'pages.pageModeration',
                    'menu' => $aModerations
                ]
            );
        }

        if (Phpfox::getService('pages')->canUserCreateNewPage(Phpfox::getUserId(), false) && (!defined('PHPFOX_CURRENT_TIMELINE_PROFILE') || PHPFOX_CURRENT_TIMELINE_PROFILE == Phpfox::getUserId())) {
            sectionMenu(_p('add_new_page'), 'pages.add');
        }

        $this->template()->assign([
                'sView' => $sView,
                'aPages' => $aPages,
                'aCategories' => $aCategories,
                'bShowCategories' => $bShowCategories,
                'is_group' => 0,
                'iCountPage' => $iCountPage,
                'bIsModerator' => !empty($aModerations)
            ]
        )->setMeta([
            'keywords' => _p('seo_pages_meta_keywords'),
            'description' => _p('seo_pages_meta_description')
        ]);

        if (defined('PHPFOX_CURRENT_TIMELINE_PROFILE') && PHPFOX_CURRENT_TIMELINE_PROFILE) {
            $this->template()->assign('iCurrentProfileId', PHPFOX_CURRENT_TIMELINE_PROFILE);
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
        (($sPlugin = Phpfox_Plugin::get('pages.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}
