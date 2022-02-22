<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Quizzes\Controller;

use Phpfox;
use Phpfox_Error;
use Phpfox_Module;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author        phpFox
 * @package        Quiz
 * @version        4.5.3
 */
class IndexController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (defined('PHPFOX_IS_USER_PROFILE') && ($sLegacyTitle = $this->request()->get('req3')) && !empty($sLegacyTitle)) {
            Phpfox::getService('core')->getLegacyItem([
                    'field' => ['quiz_id', 'title'],
                    'table' => 'quiz',
                    'redirect' => 'quiz',
                    'title' => $sLegacyTitle
                ]
            );
        }

        Phpfox::getUserParam('quiz.can_access_quiz', true);

        if (($iRedirect = $this->request()->getInt('redirect')) && ($sUrl = Phpfox::getService('quiz.callback')->getFeedRedirect($iRedirect))) {
            $this->url()->forward($sUrl);
        }

        $bIsProfile = false;
        if (defined('PHPFOX_IS_AJAX_CONTROLLER')) {
            $aUser = Phpfox::getService('user')->get($this->request()->get('profile_id'));
            $this->setParam('aUser', $aUser);
        } else {
            $bIsProfile = $this->getParam('bIsProfile');
            if ($bIsProfile === true) {
                $aUser = $this->getParam('aUser');
            } else {
                $aUser = [];
            }
        }
        if (defined('PHPFOX_IS_USER_PROFILE')) {
            $bIsProfile = true;
            $aUser = $this->getParam('aUser');
        }

        $aParentModule = $this->getParam('aParentModule');

        if ($aParentModule === null && $this->request()->getInt('req2') > 0) {
            return Phpfox_Module::instance()->setController('quiz.view');
        }

        if ($aParentModule === null && (!$bIsProfile || (Phpfox::getUserId() == $aUser['user_id']))) {
            if (Phpfox::getUserParam('quiz.can_create_quiz') && Phpfox::getService('quiz')->checkLimitation()) {
                sectionMenu(_p('add_new_quiz'), url('/quiz/add'));
            }
        }

        $sView = $this->request()->get('view');
        $aCallback = $this->getParam('aCallback', false);

        $this->search()->set([
                'type' => 'quiz',
                'field' => 'q.quiz_id',
                'ignore_blocked' => true,
                'search_tool' => [
                    'table_alias' => 'q',
                    'search' => [
                        'action' => ($aParentModule === null ? ($bIsProfile ? $this->url()->makeUrl($aUser['user_name'],
                            ['quiz', 'view' => $sView]) : $this->url()->makeUrl('quiz',
                            ['view' => $sView])) : $aParentModule['url'] . 'quiz/view_' . $sView . '/'),
                        'default_value' => _p('search_quizzes'),
                        'name' => 'search',
                        'field' => 'q.title'
                    ],
                    'sort' => [
                        'latest' => ['q.time_stamp', _p('latest')],
                        'most-viewed' => ['q.total_view', _p('most_viewed')],
                        'most-liked' => ['q.total_like', _p('most_liked')],
                        'most-talked' => ['q.total_comment', _p('most_discussed')]
                    ],
                    'show' => [10, 20, 30]
                ]
            ]
        );
        $aModerationMenu = [];
        switch ($sView) {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND q.user_id = ' . (int)Phpfox::getUserId());
                break;
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('quiz.can_approve_quizzes', true);
                $this->search()->setCondition('AND q.view_id = 1');
                if (Phpfox::getUserParam('quiz.can_approve_quizzes')) {
                    $aModerationMenu[] = [
                        'phrase' => _p('approve'),
                        'action' => 'approve'
                    ];
                }
                break;
            default:
                if ($bIsProfile === true) {
                    $this->search()->setCondition('AND q.view_id IN(' . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ') AND q.user_id = ' . (int)$aUser['user_id'] . ' AND  q.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ')');
                } elseif ($aParentModule !== null) {
                    $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND q.module_id = \'' . \Phpfox_Database::instance()->escape($aParentModule['module_id']) . '\' AND q.item_id = ' . (int)$aParentModule['item_id'] . '');
                } else {
                    if ($aCallback !== false) {
                        $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND q.item_id = ' . $aCallback['item'] . '');
                    } else {
                        if ((Phpfox::getParam('quiz.display_quizzes_created_in_page') || Phpfox::getParam('quiz.display_quizzes_created_in_group'))) {
                            $aModules = [];
                            if (Phpfox::getParam('quiz.display_quizzes_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                                $aModules[] = 'groups';
                            }
                            if (Phpfox::getParam('quiz.display_quizzes_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                                $aModules[] = 'pages';
                            }
                            if (count($aModules)) {
                                $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND (q.module_id IN ("' . implode('","', $aModules) . '") OR q.module_id = \'quiz\')');
                            } else {
                                $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND q.module_id = \'quiz\'');
                            }
                        } else {
                            $this->search()->setCondition('AND q.view_id = 0 AND q.item_id = 0 AND q.privacy IN(%PRIVACY%)');
                        }
                    }

                }
                break;
        }

        $aBrowseParams = [
            'module_id' => 'quiz',
            'alias' => 'q',
            'field' => 'quiz_id',
            'table' => Phpfox::getT('quiz'),
            'hide_view' => ['pending', 'my']
        ];

        // PARENT MODULE: PRIVACY AND BREADCRUMB
        $bIsAdmin = false;
        if (!empty($aParentModule['module_id']) && Phpfox::hasCallback($aParentModule['module_id'], 'isAdmin')) {
            $bIsAdmin = Phpfox::callback($aParentModule['module_id'] . '.isAdmin', $aParentModule['item_id']);
        }
        if (defined('PHPFOX_IS_PAGES_VIEW') && PHPFOX_IS_PAGES_VIEW && defined('PHPFOX_PAGES_ITEM_TYPE') && $aParentModule) {
            $sService = PHPFOX_PAGES_ITEM_TYPE ? PHPFOX_PAGES_ITEM_TYPE : 'pages';
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $aParentModule['item_id'], 'quiz.view_browse_quizzes')
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
                ->setBreadCrumb(_p('quizzes'), $aParentModule['url'] . 'quiz/')
                ->setTitle(_p('quizzes') . ' &raquo; ' . $sTitle, true);
        }
        else {
            $this->template()
                ->setTitle(($bIsProfile ? _p('full_name_s_quizzes', ['full_name' => $aUser['full_name']]) : _p('quizzes')))
                ->setBreadCrumb(_p('quizzes'), ($bIsProfile ? $this->url()->makeUrl($aUser['user_name'], 'quiz') : $this->url()->makeUrl('quiz')));
        }

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('quiz.quiz_paging_mode', 'loadmore'))
            ->execute();

        $iCnt = $this->search()->browse()->getCount();
        $aQuizzes = $this->search()->browse()->getRows();

        foreach ($aQuizzes as $aQuiz) {
            $this->template()->setMeta('keywords', $this->template()->getKeywords($aQuiz['title']));
        }

        Phpfox::getLib('pager')->set([
            'page' => $this->search()->getPage(),
            'size' => $this->search()->getDisplay(),
            'count' => $iCnt,
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ]);

        if (empty($aParentModule['module_id'])) {
            Phpfox::getService('quiz')->buildSectionMenu();
        }

        if (Phpfox::getUserParam('quiz.can_feature_quiz') && $sView != 'pending') {
            $aModerationMenu[] = [
                'phrase' => _p('feature'),
                'action' => 'feature'
            ];
            $aModerationMenu[] = [
                'phrase' => _p('un_feature'),
                'action' => 'un-feature'
            ];
        }

        if (Phpfox::getUserParam('quiz.can_delete_others_quizzes') || $bIsAdmin) {
            $aModerationMenu[] = [
                'phrase' => _p('delete'),
                'action' => 'delete',
                'message' => _p('are_you_sure_you_want_to_delete_selected_quizzes_permanently')
            ];
        }

        $bCanModerate = (boolean)count($aModerationMenu);
        $this->template()
            ->setMeta('keywords', Phpfox::getParam('quiz.quiz_meta_keywords'))
            ->setMeta('description', Phpfox::getParam('quiz.quiz_meta_description'))
            ->setHeader('cache', [
                    'jquery/plugin/jquery.highlightFade.js' => 'static_script',
                ]
            )
            ->setPhrase([
                    'are_you_sure_you_want_to_delete_this_quiz_permanently'
                ]
            )
            ->assign([
                    'aQuizzes' => $aQuizzes,
                    'bIsProfile' => $bIsProfile,
                    'bCanModerate' => $bCanModerate,
                    'bIsAdmin' => $bIsAdmin,
                    'sView' => $sView
                ]
            );
        if ($bCanModerate) {
            $this->setParam('global_moderation', [
                    'name' => 'quiz',
                    'ajax' => 'quiz.moderation',
                    'menu' => $aModerationMenu
                ]
            );
        }
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('quiz.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}