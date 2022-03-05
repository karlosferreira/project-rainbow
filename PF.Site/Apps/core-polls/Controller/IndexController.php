<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Polls\Controller;

use Phpfox;
use Phpfox_Error;
use Phpfox_Module;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');


class IndexController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (defined('PHPFOX_IS_USER_PROFILE') && ($sLegacyTitle = $this->request()->get('req3')) && !empty($sLegacyTitle)) {
            \Phpfox::getService('core')->getLegacyItem([
                    'field' => ['poll_id', 'question'],
                    'table' => 'poll',
                    'redirect' => 'poll',
                    'search' => 'question_url',
                    'title' => $sLegacyTitle
                ]
            );
        }

        Phpfox::getUserParam('poll.can_access_polls', true);

        if (($iRedirect = $this->request()->getInt('redirect')) && ($sUrl = \Phpfox::getService('poll.callback')->getFeedRedirect($iRedirect))) {
            $this->url()->forward($sUrl);
        }

        (($sPlugin = Phpfox_Plugin::get('poll.component_controller_index_process_start')) ? eval($sPlugin) : false);

        $sView = $this->request()->get('view');
        $aCallback = $this->getParam('aCallback', false);

        if ($iDeleteId = $this->request()->getInt('delete')) {
            if (\Phpfox::getService('user.auth')->hasAccess('poll', 'poll_id', $iDeleteId,
                    'poll.poll_can_delete_own_polls',
                    'poll.poll_can_delete_others_polls') && \Phpfox::getService('poll.process')->moderatePoll($iDeleteId,
                    2)
            ) {
                $this->url()->send('poll', null, _p('poll_successfully_deleted'));
            }
        }

        $aUser = null;
        if (defined('PHPFOX_IS_AJAX_CONTROLLER')) {
            $bIsProfile = true;
            $aUser = \Phpfox::getService('user')->get($this->request()->get('profile_id'));
            $this->setParam('aUser', $aUser);
        } else {
            $bIsProfile = $this->getParam('bIsProfile');
            if ($bIsProfile === true) {
                $aUser = $this->getParam('aUser');
            }
        }

        if (defined('PHPFOX_IS_USER_PROFILE')) {
            $bIsProfile = true;
            $aUser = $this->getParam('aUser');
        }

        $aParentModule = $this->getParam('aParentModule');

        if ($aParentModule === null && $this->request()->getInt('req2') > 0) {
            return Phpfox_Module::instance()->setController('poll.view');
        }

        $this->search()->set([
                'type' => 'poll',
                'field' => 'poll.poll_id',
                'ignore_blocked' => true,
                'search_tool' => [
                    'table_alias' => 'poll',
                    'search' => [
                        'action' => ($aParentModule === null ? ($bIsProfile ? $this->url()->makeUrl($aUser['user_name'],
                            ['poll', 'view' => $sView]) : $this->url()->makeUrl('poll',
                            ['view' => $sView])) :
                            $aParentModule['url'] . 'poll/view_' . $sView . '/'),
                        'default_value' => _p('search_polls'),
                        'name' => 'search',
                        'field' => 'poll.question'
                    ],
                    'sort' => [
                        'latest' => ['poll.time_stamp', _p('latest')],
                        'most-viewed' => ['poll.total_view', _p('most_viewed')],
                        'most-liked' => ['poll.total_like', _p('most_liked')],
                        'most-talked' => ['poll.total_comment', _p('most_discussed')]
                    ],
                    'show' => [5, 10, 15]
                ]
            ]
        );

        if ($aParentModule === null && (!$bIsProfile || (Phpfox::getUserId() == $aUser['user_id']))) {
            if (Phpfox::getUserParam('poll.can_create_poll') && Phpfox::getService('poll')->checkLimitation()) {
                sectionMenu(_p('add_new_poll'), url('/poll/add'));
            }
        }
        $aBrowseParams = [
            'module_id' => 'poll',
            'alias' => 'poll',
            'field' => 'poll_id',
            'table' => Phpfox::getT('poll'),
            'hide_view' => ['pending', 'my']
        ];

        switch ($sView) {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND poll.user_id = ' . (int)Phpfox::getUserId());
                break;
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('poll.poll_can_moderate_polls', true);
                $this->search()->setCondition('AND poll.view_id = 1');
                break;
            default:
                if ($bIsProfile === true) {
                    $this->search()->setCondition('AND poll.item_id = 0 AND poll.user_id = ' . (int)$aUser['user_id'] . ' AND poll.view_id IN(' . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ') AND poll.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($aUser)) . ')');
                } elseif ($aParentModule !== null) {
                    $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND poll.module_id = \'' . \Phpfox_Database::instance()->escape($aParentModule['module_id']) . '\' AND poll.item_id = ' . (int)$aParentModule['item_id'] . '');
                } else {
                    if ($aCallback !== false) {
                        $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND poll.item_id = ' . $aCallback['item'] . '');
                    } else {
                        if ((Phpfox::getParam('poll.display_polls_created_in_page') || Phpfox::getParam('poll.display_polls_created_in_group'))) {
                            $aModules = [];
                            if (Phpfox::getParam('poll.display_polls_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                                $aModules[] = 'groups';
                            }
                            if (Phpfox::getParam('poll.display_polls_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                                $aModules[] = 'pages';
                            }
                            if (count($aModules)) {
                                $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND (poll.module_id IN ("' . implode('","', $aModules) . '") OR poll.module_id IS NULL)');
                            } else {
                                $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND poll.module_id IS NULL');
                            }
                        } else {
                            $this->search()->setCondition('AND poll.item_id = 0 AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%)');
                        }
                    }

                }
                $this->search()->setCondition('AND (poll.close_time = 0 OR poll.close_time > ' . PHPFOX_TIME . ')');
                break;
        }

        // PARENT MODULE: PRIVACY AND BREADCRUMB
        $bIsAdmin = false;
        if (!empty($aParentModule['module_id']) && Phpfox::hasCallback($aParentModule['module_id'], 'isAdmin')) {
            $bIsAdmin = Phpfox::callback($aParentModule['module_id'] . '.isAdmin', $aParentModule['item_id']);
        }
        if (defined('PHPFOX_IS_PAGES_VIEW') && PHPFOX_IS_PAGES_VIEW && defined('PHPFOX_PAGES_ITEM_TYPE') && $aParentModule) {
            $sService = PHPFOX_PAGES_ITEM_TYPE ? PHPFOX_PAGES_ITEM_TYPE : 'pages';
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $aParentModule['item_id'], 'poll.view_browse_polls')
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
                ->setBreadCrumb(_p('polls'), $aParentModule['url'] . 'poll/')
                ->setTitle(_p('polls') . ' &raquo; ' . $sTitle, true);
        }
        else {
            $this->template()
                ->setTitle(($bIsProfile ? _p('full_name_s_polls_upper', ['full_name' => $aUser['full_name']]) : _p('polls')))
                ->setBreadCrumb(_p('all_polls'), ($bIsProfile ? $this->url()->makeUrl($aUser['user_name'], 'poll') : $this->url()->makeUrl('poll')));;
        }

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('poll.poll_paging_mode', 'loadmore'))
            ->execute();

        $iCnt = $this->search()->browse()->getCount();
        $aPolls = $this->search()->browse()->getRows();

        Phpfox::getLib('pager')->set([
            'page' => $this->search()->getPage(),
            'size' => $this->search()->getDisplay(),
            'count' => $iCnt,
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ]);

        // check if user has voted here already
        // check editing permissions
        foreach ($aPolls as $iKey => &$aPoll) {
            // is guest the owner?
            $aPoll['bCanEdit'] = \Phpfox::getService('poll')->bCanEdit($aPoll['user_id']);
            $aPoll['bCanDelete'] = \Phpfox::getService('poll')->bCanDelete($aPoll['user_id']);
            $this->template()->setMeta('keywords', $this->template()->getKeywords($aPoll['question']));
        }

        if (empty($aParentModule['module_id'])) {
            Phpfox::getService('poll')->buildMenu();
        }

        $this->template()
            ->setMeta('description', Phpfox::getParam('poll.poll_meta_description'))
            ->setMeta('keywords', Phpfox::getParam('poll.poll_meta_keywords'))
            ->assign([
                    'aPolls' => $aPolls
                ]
            )
            ->setPhrase([
                'are_you_sure_you_want_to_delete_this_poll'
            ]);
        $aModerationMenu = [];
        $bShowModerator = $bIsAdmin;
        if ($sView == 'pending') {
            if (Phpfox::getUserParam('poll.poll_can_moderate_polls')) {
                $aModerationMenu[] = [
                    'phrase' => _p('approve'),
                    'action' => 'approve'
                ];
            }
        } elseif (Phpfox::getUserParam('poll.can_feature_poll')) {
            $aModerationMenu[] = [
                'phrase' => _p('feature'),
                'action' => 'feature'
            ];
            $aModerationMenu[] = [
                'phrase' => _p('un_feature'),
                'action' => 'un-feature'
            ];
        }
        if (Phpfox::getUserParam('poll.poll_can_delete_others_polls') || $bIsAdmin) {
            $aModerationMenu[] = [
                'phrase' => _p('delete'),
                'action' => 'delete',
                'message' => _p('are_you_sure_you_want_to_delete_selected_polls_permanently')
            ];
        }
        if (count($aModerationMenu)) {
            $this->setParam('global_moderation', [
                    'name' => 'poll',
                    'ajax' => 'poll.moderation',
                    'menu' => $aModerationMenu
                ]
            );
            $bShowModerator = true;
        }
        $this->template()->assign([
            'bShowModerator' => $bShowModerator,
            'sView' => $sView,
        ]);

        (($sPlugin = Phpfox_Plugin::get('poll.component_controller_index_process_end')) ? eval($sPlugin) : false);
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('poll.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}