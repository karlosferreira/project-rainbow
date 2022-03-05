<?php

namespace Apps\Core_Forums\Service;

use Core\Api\ApiServiceBase;
use Phpfox;
use Phpfox_Database;
use Phpfox_Validator;

class Api extends ApiServiceBase
{
    private function _getPublicFields($itemType = 'forum')
    {
        switch ($itemType) {
            case 'thread':
                $publicFields = [
                    'thread_id',
                    'forum_id',
                    'group_id',
                    'poll_id',
                    'view_id',
                    'start_id',
                    'is_announcement',
                    'is_closed',
                    'user_id',
                    'title',
                    'title_url',
                    'time_stamp',
                    'time_update',
                    'order_id',
                    'post_id',
                    'last_user_id',
                    'total_post',
                    'total_view',
                ];
                break;
            case 'post':
                $publicFields = [
                    'post_id',
                    'thread_id',
                    'view_id',
                    'user_id',
                    'title',
                    'total_attachment',
                    'time_stamp',
                    'update_time',
                    'total_like',
                    'text',
                ];
                break;
            default:
                $publicFields = [
                    'forum_id',
                    'parent_id',
                    'view_id',
                    'is_category',
                    'name',
                    'name_url',
                    'description',
                    'is_closed',
                    'thread_id',
                    'post_id',
                    'last_user_id',
                    'total_post',
                    'total_thread',
                    'ordering',
                ];
                break;
        }

        return $publicFields;
    }

    /**
     * @description: update a Forum
     * @param $params
     *
     * @return array|bool
     */
    public function put($params)
    {
        //Do not support because Forum is updated in AdminCP
        return $this->error();
    }

    /**
     * @description: update a Thread
     * @param $params
     *
     * @return array|bool
     */
    public function putThread($params)
    {
        $this->isUser();

        $vals = $this->request()->get('val');

        $aThread = Phpfox::getService('forum.thread')->getForEdit((int)$params['id']);

        if (!isset($aThread['thread_id'])) {
            return $this->error(_p('item_not_found'));
        }

        $forumId = $aThread['forum_id'];
        $aAccess = Phpfox::getService('forum')->getUserGroupAccess($forumId, Phpfox::getUserBy('user_group_id'));
        if ($aAccess['can_view_thread_content']['value'] != true) {
            return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
        }

        if ($aThread['forum_id']) {
            $forum = Phpfox::getService('forum')
                ->id($forumId)
                ->getForum();

            if (!isset($forum['forum_id'])) {
                return $this->error(_p('not_a_valid_forum'));
            } elseif ($forum['is_closed']) {
                return $this->error(_p('forum_is_closed'));
            }
        }

        if ((!Phpfox::getUserParam('forum.can_edit_own_post') || $aThread['user_id'] != Phpfox::getUserId())
            && !Phpfox::getUserParam('forum.can_edit_other_posts')
            && !Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'edit_post')
            && !Phpfox::getService('forum.thread')->isAdminOfParentItem($aThread['thread_id'])) {
            return $this->error(_p('insufficient_permission_to_edit_this_thread'));
        }

        if (!Phpfox::getService('forum')->hasAccess($forumId, 'can_start_thread')) {
            return $this->error(_p('you_are_unable_to_create_a_new_post_in_this_forum_dot'));
        }

        $validationParams = [
            'title' => _p('provide_a_title_for_your_thread'),
            'text' => _p('provide_some_text')
        ];

        $validObject = Phpfox_Validator::instance()->set([
            'sFormName' => 'js_form',
            'aParams' => $validationParams
        ]);

        if ($validObject->isValid($vals)) {
            $vals = array_merge($vals, [
                'post_id' => $aThread['start_id'],
                'was_announcement' => $aThread['is_announcement'],
                'forum_id' => $aThread['forum_id'],
            ]);
            if (Phpfox::getService('forum.thread.process')->update($aThread['thread_id'], $aThread['user_id'], $vals)) {
                return $this->getThread(['id' => $params['id']], [_p('{{ item }} successfully updated.', ['item' => _p('thread')])]);
            }
        }

        return $this->error();
    }

    /**
     * @description: update a Post
     * @param $params
     *
     * @return array|bool
     */
    public function putPost($params)
    {
        $this->isUser();

        $aVals = (array)$this->request()->get('val');
        $sTxt = $aVals['text'];

        if (Phpfox::getLib('parse.format')->isEmpty($sTxt)) {
            return $this->error(_p('add_some_text'));
        }

        $aPost = Phpfox::getService('forum.post')->getPost((int)$params['id']);
        $aVals['user_id'] = $aPost['user_id'];
        $bHasAccess = false;

        if ((int)$aPost['group_id'] > 0 && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups'))) {
            $sModule = Phpfox::getPagesType($aPost['group_id']);
            if (Phpfox::isModule($sModule)) {
                if ((Phpfox::getUserParam('forum.can_edit_own_post') && $aPost['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_edit_other_posts') || Phpfox::getService($sModule)->isAdmin($aPost['group_id'])) {
                    $bHasAccess = true;
                }
            }
        } else {
            if ((Phpfox::getService('forum.moderate')->hasAccess($aPost['forum_id'],
                    'edit_post') || Phpfox::getService('user.auth')->hasAccess('forum_post', 'post_id',
                    $params['id'], 'forum.can_edit_own_post', 'forum.can_edit_other_posts'))
            ) {
                $bHasAccess = true;
            }
        }

        if ($bHasAccess && Phpfox::getService('forum.post.process')->updateText($params['id'], $sTxt, $aVals)) {
            return $this->getPost(['id' => $params['id']], [_p('{{ item }} successfully updated.', ['item' => _p('post')])]);
        }

        return $this->error();
    }

    private function _checkThreadPermission($item)
    {
        $errorMessage = null;
        $callback = null;

        if (empty($item['thread_id'])) {
            $errorMessage = _p('item_not_found');
        } elseif ($item['group_id'] > 0
            && (Phpfox::isModule('pages') || Phpfox::isModule('groups'))
            && ($sParentId = Phpfox::getPagesType($item['group_id']))
            && Phpfox::isModule($sParentId)) {
            $callback = Phpfox::callback($sParentId . '.addForum', $item['group_id']);
            if (isset($callback['module']) && !isset($callback['module_id'])) {
                $callback['module_id'] = $callback['module'];
            }
            if (!Phpfox::getService($sParentId)->hasPerm($item['group_id'], 'forum.view_browse_forum')) {
                $errorMessage = _p('forum_you_do_not_have_permission_to_view_this_item');
            }
        }

        if ($errorMessage === null) {
            if ($item['view_id'] != '0' && $item['user_id'] != Phpfox::getUserId()
                && !Phpfox::getUserParam('forum.can_approve_forum_thread')
                && !Phpfox::getService('forum.moderate')->hasAccess($item['forum_id'], 'approve_thread')) {
                $errorMessage = _p('not_a_valid_thread');
            } elseif ($callback === null) {
                if (!Phpfox::getService('forum')->hasAccess($item['forum_id'], 'can_view_forum')) {
                    $errorMessage = _p(Phpfox::isUser() ? 'you_do_not_have_the_proper_permission_to_view_this_thread' : 'log_in_to_view_thread');
                } elseif (!Phpfox::getService('forum')->hasAccess($item['forum_id'], 'can_view_thread_content')) {
                    $errorMessage = _p('forum_you_do_not_have_permission_to_view_this_item');
                }
            }
        }

        return $errorMessage;
    }

    public function getThread($params, $messages = [])
    {
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->error(_p('forum_you_do_not_have_permission_to_view_this_item'), true);
        }

        list(, $item) = Phpfox::getService('forum.thread')->getThread(['ft.thread_id = ' . (int)$params['id'] . '']);
        $errorMessage = $this->_checkThreadPermission($item);
        if (!$errorMessage && $item) {
            $this->setPublicFields($this->_getPublicFields('thread'));
        }
        return $errorMessage ? $this->error($errorMessage) : $this->success($this->getItem($item), $messages);
    }

    public function getPost($params, $messages = [])
    {
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->error(_p('forum_you_do_not_have_permission_to_view_this_item'), true);
        }

        $item = Phpfox::getService('forum.post')->getPost((int)$params['id']);

        if (empty($item['post_id'])) {
            $errorMessage = _p('item_not_found');
        } else {
            list(, $thread) = Phpfox::getService('forum.thread')->getThread(['ft.thread_id = ' . $item['thread_id'] . '']);
            if (empty($thread['thread_id'])) {
                $errorMessage = _p('item_not_found');
            } elseif (($errorMessage = $this->_checkThreadPermission($thread))
                || Phpfox::getService('user.block')->isBlocked(null, $item['user_id'])) {
                $errorMessage = _p('forum_you_do_not_have_permission_to_view_this_item');
            }
        }

        if (!$errorMessage && !empty($item['post_id'])) {
            $this->setPublicFields($this->_getPublicFields('post'));
        }

        return $errorMessage ? $this->error($errorMessage) : $this->success($this->getItem($item), $messages);
    }

    /**
     * @description: get info of a Forum
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function get($params, $messages = [])
    {
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->error(_p('forum_you_do_not_have_permission_to_view_this_item'), true);
        }

        $item = Phpfox::getService('forum')->id((int)$params['id'])->getForum();

        if (!empty($item['forum_id'])) {
            $this->setPublicFields($this->_getPublicFields());
            if (\Core\Lib::phrase()->isPhrase($item['name'])) {
                $item['name'] = _p($item['name']);
            }
            if (\Core\Lib::phrase()->isPhrase($item['description'])) {
                $item['description'] = _p($item['description']);
            }
        }
        return $this->success($this->getItem($item), $messages);
    }

    /**
     * @description: delete a Forum
     * @param $params
     *
     * @return array|bool
     */
    public function delete($params)
    {
        //Do not support because Forum is deleted in AdminCP
        return $this->error();
    }

    /**
     * @description: delete a Thread
     * @param $params
     *
     * @return array|bool
     */
    public function deleteThread($params)
    {
        $this->isUser();

        $aThread = Phpfox::getService('forum.thread')->getActualThread((int)$params['id']);
        $bHasAccess = false;

        if (empty($aThread['thread_id'])) {
            return $this->error(_p('item_not_found'));
        }

        if ((int)$aThread['group_id'] > 0) {
            if ((Phpfox::getUserParam('forum.can_delete_own_post') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_delete_other_posts') || Phpfox::getService('forum.thread')->isAdminOfParentItem($aThread['thread_id'])) {
                $bHasAccess = true;
            }
        } else {
            if ((Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'],
                    'delete_post') || Phpfox::getService('user.auth')->hasAccess('forum_thread', 'thread_id', $params['id'], 'forum.can_delete_own_post', 'forum.can_delete_other_posts'))
            ) {
                $bHasAccess = true;
            }
        }

        if (!$bHasAccess) {
            return $this->error(_p('you_do_not_have_sufficient_permission_to_delete_this_thread'));
        } elseif (Phpfox::getService('forum.thread.process')->delete($params['id'])) {
            return $this->success([], [_p('{{ item }} successfully deleted.', ['item' => _p('thread')])]);
        }

        return $this->error();
    }

    /**
     * @description: delete a Forum
     * @param $params
     *
     * @return array|bool
     */
    public function deletePost($params)
    {
        $this->isUser();

        $aPost = Phpfox::getService('forum.post')->getPost((int)$params['id']);
        $bHasAccess = false;

        if (empty($aPost['post_id'])) {
            return $this->error(_p('item_not_found'));
        }

        if ((int)$aPost['group_id'] > 0 && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups'))) {
            $sModule = Phpfox::getPagesType($aPost['group_id']);
            if (Phpfox::getService($sModule)->isAdmin($aPost['group_id'])) {
                $bHasAccess = true;
            }
        }
        if ((Phpfox::getService('forum.moderate')->hasAccess($aPost['forum_id'], 'delete_post') ||
            Phpfox::getService('user.auth')->hasAccess('forum_post', 'post_id', (int)$params['id'], 'forum.can_delete_own_post', 'forum.can_delete_other_posts'))
        ) {
            $bHasAccess = true;
        }

        if (!$bHasAccess) {
            return $this->error(_p('you_do_not_have_sufficient_permission_to_delete_this_post'));
        } elseif (Phpfox::getService('forum.post.process')->delete((int)$params['id'])) {
            return $this->success([], _p('{{ item }} successfully deleted.', ['item' => _p('post')]));
        }

        return $this->error();
    }

    /**
     * @description: add new Forum
     * @return array|bool
     */
    public function post()
    {
        //Do not support create forum because this feature is in AdminCP
        return $this->error();
    }

    /**
     * @description: add new Thread
     * @return array|bool
     */
    public function postThread()
    {
        $this->isUser();

        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $vals = $this->request()->get('val');
        $callback = false;

        if ($moduleId && $itemId && Phpfox::isModule($moduleId) && Phpfox::hasCallback($moduleId, 'addForum')) {
            $callback = Phpfox::callback($moduleId . '.addForum', $itemId);
            if ($callback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }
            $vals['forum_id'] = 0;
        } else {
            if ($moduleId && $itemId && $callback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        if ($callback === false && empty($vals['forum_id'])) {
            return $this->error(_p('forum_missing_parameter_name', [
                'name' => 'forum_id'
            ]));
        }

        $forumId = !empty($vals['forum_id']) ? $vals['forum_id'] : 0;
        $aAccess = Phpfox::getService('forum')->getUserGroupAccess($forumId, Phpfox::getUserBy('user_group_id'));
        if ($aAccess['can_view_thread_content']['value'] != true) {
            return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
        }

        if ($forumId) {
            $forum = Phpfox::getService('forum')
                ->id($forumId)
                ->getForum();

            if (!isset($forum['forum_id'])) {
                return $this->error(_p('not_a_valid_forum'));
            } elseif ($forum['is_closed']) {
                return $this->error(_p('forum_is_closed'));
            }
        }

        if ($moduleId && $itemId && Phpfox::hasCallback($moduleId, 'checkPermission') && !Phpfox::callback($moduleId . '.checkPermission', $itemId, 'forum.share_forum')) {
            return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
        }

        if (!Phpfox::getUserParam('forum.can_add_new_thread') && !Phpfox::getService('forum.moderate')->hasAccess($forum['forum_id'], 'add_thread')) {
            return $this->error(_p('insufficient_permission_to_reply_to_this_thread'));
        }

        if (!Phpfox::getService('forum')->hasAccess($forumId, 'can_start_thread')) {
            return $this->error(_p('you_are_unable_to_create_a_new_post_in_this_forum_dot'));
        }

        $validationParams = [
            'title' => _p('provide_a_title_for_your_thread'),
            'text' => _p('provide_some_text')
        ];

        $validObject = Phpfox_Validator::instance()->set([
            'sFormName' => 'js_form',
            'aParams' => $validationParams
        ]);

        if ($validObject->isValid($vals)) {
            if (($iFlood = Phpfox::getUserParam('forum.forum_thread_flood_control')) !== 0) {
                $floodParams = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field' => 'time_stamp', // The time stamp field
                        'table' => Phpfox::getT('forum_thread'), // Database table we plan to check
                        'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];

                // actually check if flooding
                if (Phpfox::getLib('spam')->check($floodParams)) {
                    return $this->error(_p('posting_a_new_thread_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }

            if (\Phpfox_Error::isPassed() && !empty($threadId = Phpfox::getService('forum.thread.process')->add($vals, $callback))) {
                return $this->getThread(['id' => $threadId], [_p('{{ item }} successfully added.', ['item' => _p('thread')])]);
            }
        }
        
        return $this->error();
    }

    /**
     * @description: add new Post
     * @return array|bool
     */
    public function postPost()
    {
        $this->isUser();

        $aVals = $this->request()->get('val');
        if (!Phpfox::getService('forum.thread')->canReplyOnThread($aVals['thread_id'])) {
            return $this->error();
        }

        //support quick reply in bottom
        if (isset($aVals['reply_text'])) {
            $aVals['text'] = $aVals['reply_text'];
        }

        Phpfox::getService('ban')->checkAutomaticBan($aVals['text']);
        if (Phpfox::getLib('parse.format')->isEmpty($aVals['text'])) {
            return $this->error(_p('provide_a_reply'));
        }

        $aCallback = false;
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');

        if ($moduleId && $itemId && Phpfox::isModule($moduleId) && Phpfox::hasCallback($moduleId, 'addForum')) {
            $aCallback = Phpfox::callback($moduleId . '.addForum', $itemId);
            if ($aCallback === false) {
                return $this->error(_p('only_members_can_add_a_reply_to_threads'));
            }
        }

        if (($iFlood = Phpfox::getUserParam('forum.forum_post_flood_control')) !== 0) {
            $aStartPostIds = Phpfox::getService('forum.post')->getStartPostIds();
            $sCond = !empty($aStartPostIds) ? ' AND post_id NOT IN ('.implode(',', $aStartPostIds).')' : '';
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('forum_post'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId() . $sCond, // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->error(_p('posting_a_new_reply_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }

        $aThread = Phpfox::getService('forum.thread')->getActualThread((int)$aVals['thread_id'], $aCallback);

        if (!isset($aThread['thread_id'])) {
            return $this->error();
        }

        if ($aThread['is_closed']) {
            return $this->error(_p('thread_is_closed_for_posting'));
        }

        if ($aCallback === false && $aThread['is_announcement']) {
            return $this->error(_p('thread_is_closed_for_posting'));
        }

        if ((!Phpfox::getUserParam('forum.can_reply_to_own_thread') || $aThread['user_id'] != Phpfox::getUserId()) && !Phpfox::getUserParam('forum.can_reply_on_other_threads') && !Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'can_reply')) {
            return $this->error(_p('insufficient_permission_to_reply_to_this_thread'));
        }

        if ($iId = Phpfox::getService('forum.post.process')->add(array_merge($aVals, ['forum_id' => $aThread['forum_id']]), $aCallback)) {
            return $this->getPost(['id' => $iId], [_p('{{ item }} successfully added.', ['item' => _p('post')])]);
        } elseif (Phpfox::getUserParam('forum.approve_forum_post') && $aCallback === false) {
            return $this->success([], _p('your_post_has_successfully_been_added_however_it_is_pending_an_admins_approval_before_it_can_be_displayed_publicly'));
        }

        return $this->error();
    }

    /**
     * @description: get Forums
     * @return array|bool
     */
    public function gets()
    {
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('Forums')]));
        }

        Phpfox::getService('forum')->getSearchFilter();

        $forums = Phpfox::getService('forum')->live()->getForums($this->request()->getInt('parent_id', 0));

        $parsedForums = [];

        if (!empty($forums)) {
            $this->setPublicFields($this->_getPublicFields());
            foreach ($forums as $forum) {
                if (\Core\Lib::phrase()->isPhrase($forum['name'])) {
                    $forum['name'] = _p($forum['name']);
                }
                if (\Core\Lib::phrase()->isPhrase($forum['description'])) {
                    $forum['description'] = _p($forum['description']);
                }
                $parsedForums[] = $this->getItem($forum);
            }
        }

        return $this->success($parsedForums);
    }

    public function getThreads()
    {
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $view = Phpfox::getLib('parse.output')->cleanScriptTag($this->request()->get('view'));
        $tag = $this->request()->get('tag');

        if (in_array($moduleId, ['pages', 'groups'])
            && Phpfox::hasCallback($moduleId, 'checkPermission')
            && !Phpfox::callback($moduleId . '.checkPermission', $itemId, 'forum.view_browse_forum')) {
            return $this->error(_p('Cannot display this section due to privacy.'));
        }

        if (Phpfox::getParam('core.phpfox_is_hosted') && empty($moduleId) && empty($itemId)) {
            return $this->error();
        } else {
            if (empty($moduleId) && empty($itemId) && $view == 'new') {
                $aDo = explode('/', $this->request()->get('do'));
                if ($aDo[0] == 'mobile' || (isset($aDo[1]) && $aDo[1] == 'mobile')) {
                    return $this->error();
                }
            }
        }

        switch ($moduleId) {
            case 'groups':
            case 'pages':
                $item = Phpfox::getService($moduleId)->getForView($itemId);
                $callback = !empty($item['page_id']) ? array_merge($item, [
                    'module_id' => $moduleId,
                    'item_id' => $item,
                    'url_home' => Phpfox::getService($moduleId)->getUrl($item['page_id'], $item['title'], $item['vanity_url'])
                ]) : null;
                break;
            default:
                if ($moduleId && $itemId && Phpfox::isModule($moduleId) && Phpfox::hasCallback($moduleId, 'getParentCallback')) {
                    $callback = Phpfox::callback($moduleId . 'getParentCallback', $itemId);
                } else {
                    $callback = null;
                }
                break;
        }

        $bIsPendingSearch = $view == 'pending-post';
        $bIsTagSearch = false;
        $bIsModuleTagSearch = false;
        $forumId = $this->request()->get('forum_id');
        $oSearch = Phpfox::getService('forum')->getSearchFilter(false, $forumId, [
            'module_id' => $moduleId,
            'item_id' => $itemId,
        ], $view);
        $bIsSearch = $oSearch->isSearch();

        if ($tag) {
            $bIsSearch = true;
            $bIsTagSearch = true;
            $bIsModuleTagSearch = $moduleId && $itemId;
        }

        $bIsAdvSearch = ($oSearch->get('adv_search') ? true : false);

        if (empty($module_id) && $oSearch->isSearch()) {
            $aIds = [];
            if ($bIsAdvSearch) {
                $forums = ($oSearch->get('forum')) ? $oSearch->get('forum') : [];
                $aSearchForumId = array_unique((!empty($forums) && is_array($forums)) ? $forums : []);
                foreach ($aSearchForumId as $iSearchForum) {
                    if (!is_numeric($iSearchForum)) {
                        continue;
                    }
                    $aIds[] = $iSearchForum;
                }
            } else {
                $iSearchForumId = $forumId;
                $forums = ($iSearchForumId ? Phpfox::getService('forum')->id($iSearchForumId)->live()->getForums() : Phpfox::getService('forum')->live()->getForums());
                if ($iSearchForumId) {
                    $aIds[] = $iSearchForumId;
                }
                foreach ($forums as $forum) {
                    if ($forum['forum_id']) {
                        $aIds[] = $forum['forum_id'];
                    }

                    $aChilds = (array)Phpfox::getService('forum')->id($forum['forum_id'])->getChildren();
                    foreach ($aChilds as $iId) {
                        if ($iId) {
                            $aIds[] = $iId;
                        }
                    }
                }
            }

            $aIds = Phpfox::getService('forum.thread')->getCanViewForumIdList($aIds);
            $oSearch->setCondition('AND ft.forum_id IN(' . implode(',', $aIds) . ')');
        } elseif (!empty($module_id) && !empty($itemId)) {
            $oSearch->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $itemId . ' AND ft.is_announcement = 0');
        }

        $iPageSize = $oSearch->getDisplay();
        $viewId = 'ft.view_id = 0';
        if (empty($moduleId) && empty($itemId)) {
            $aForum = Phpfox::getService('forum')->id($forumId)->getForum();
        } else {
            $aForum = [];
        }

        if (!$bIsSearch && $view != 'pending-post') {
            if (empty($moduleId) && empty($itemId)) {
                if (!empty($view)) {
                    switch ($view) {
                        case 'my-thread':
                            $oSearch->setCondition('AND ft.user_id = ' . Phpfox::getUserId());
                            $viewId = 'ft.view_id >= 0';
                            break;
                        case 'pending-thread':
                            if (Phpfox::getUserParam('forum.can_approve_forum_thread')) {
                                $viewId = 'ft.view_id = 1';
                            }
                            $bIsPendingSearch = true;
                            break;
                        default:
                            break;
                    }
                    $oSearch->setCondition(($bIsPendingSearch ? 'AND ' : 'AND ft.group_id = 0 AND ft.is_announcement = 0 AND ') . $viewId);
                } else {
                    $oSearch->setCondition((!empty($aForum['forum_id']) ? 'AND ft.forum_id = ' . $aForum['forum_id'] : '') . ' AND ft.group_id = 0 AND ' . $viewId . ' AND ft.is_announcement = 0');
                }
            } else {
                $oSearch->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $itemId . ' AND ' . $viewId . ' AND ft.is_announcement = 0');
            }

            // get the forums that we cant access
            $aForbiddenForums = Phpfox::getService('forum')->getForbiddenForums();
            if (!empty($aForbiddenForums)) {
                $oSearch->setCondition(' AND ft.forum_id NOT IN (' . implode(',', $aForbiddenForums) . ')');
            }
        } else {
            if ($moduleId && $itemId) {
                $oSearch->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $itemId . ' AND ' . $viewId . ' AND ft.is_announcement = 0');
            } else {
                $oSearch->setCondition(($aForum ? 'AND ft.forum_id = ' . $aForum['forum_id'] : '') . ' AND ' . $viewId . ($bIsPendingSearch ? '' : ' AND ft.is_announcement = 0 AND ft.group_id = 0'));
            }
            if ($view == 'my-thread') {
                $oSearch->setCondition('AND ft.user_id = ' . Phpfox::getUserId());
            }
        }

        if ($bIsAdvSearch) {
            if ($oSearch->get('user')) {
                $oSearch->search('like%', 'u.full_name', $oSearch->get('user'));
            }
        }

        $sSort = ($this->request()->get('sort') || $this->request()->get('sort_by')) ? $this->_getSort() : $oSearch->getSort();

        if (($iDaysPrune = $oSearch->get('days_prune')) && $iDaysPrune != '-1') {
            $oSearch->setCondition('AND ft.time_stamp >= ' . (PHPFOX_TIME - ($iDaysPrune * 86400)));
        }
        if ($bIsTagSearch === true) {
            if ($bIsModuleTagSearch) {
                $oSearch->setCondition("AND ft.group_id = " . (int)$callback['item_id'] . " AND tag.tag_url = '" . db()->escape($tag) . "'");
            } else {
                $oSearch->setCondition("AND ft.group_id = 0 AND tag.tag_url = '" . db()->escape($tag) . "'");
            }
        }

        if ($oSearch->get('search')) {
            $oSearch->search('like%', ['ft.title'], $oSearch->get('search'));
        }

        list(, $threads) = Phpfox::getService('forum.thread')
            ->isSearch($bIsSearch)
            ->isAdvSearch($bIsAdvSearch)
            ->isTagSearch($bIsTagSearch)
            ->isNewSearch($view == 'new')
            ->isSubscribeSearch($view == 'subscribed')
            ->isModuleSearch($bIsModuleTagSearch)
            ->get($oSearch->getConditions(), 'ft.order_id DESC, ' . $sSort, $oSearch->getPage(), $iPageSize);

        $parsedThreads = [];

        if ($threads) {
            $this->setPublicFields($this->_getPublicFields('thread'));
            foreach ($threads as $thread) {
                $parsedThreads[] = $this->getItem($thread);
            }
        }

        return $this->success($parsedThreads);
    }

    public function getPosts()
    {
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $view = Phpfox::getLib('parse.output')->cleanScriptTag($this->request()->get('view'));
        $tag = $this->request()->get('tag');

        if (in_array($moduleId, ['pages', 'groups'])
            && Phpfox::hasCallback($moduleId, 'checkPermission')
            && !Phpfox::callback($moduleId . '.checkPermission', $itemId, 'forum.view_browse_forum')) {
            return $this->error(_p('Cannot display this section due to privacy.'));
        }

        if (Phpfox::getParam('core.phpfox_is_hosted') && empty($moduleId) && empty($itemId)) {
            return $this->error();
        } else {
            if (empty($moduleId) && empty($itemId) && $view == 'new') {
                $aDo = explode('/', $this->request()->get('do'));
                if ($aDo[0] == 'mobile' || (isset($aDo[1]) && $aDo[1] == 'mobile')) {
                    return $this->error();
                }
            }
        }

        switch ($moduleId) {
            case 'groups':
            case 'pages':
                $item = Phpfox::getService($moduleId)->getForView($itemId);
                $callback = !empty($item['page_id']) ? array_merge($item, [
                    'module_id' => $moduleId,
                    'item_id' => $item,
                    'url_home' => Phpfox::getService($moduleId)->getUrl($item['page_id'], $item['title'], $item['vanity_url'])
                ]) : null;
                break;
            default:
                if ($moduleId && $itemId && Phpfox::isModule($moduleId) && Phpfox::hasCallback($moduleId, 'getParentCallback')) {
                    $callback = Phpfox::callback($moduleId . 'getParentCallback', $itemId);
                } else {
                    $callback = null;
                }
                break;
        }

        $bIsPendingSearch = $view == 'pending-post';
        $bIsTagSearch = false;
        $bIsModuleTagSearch = false;
        $forumId = $this->request()->get('forum_id');
        $oSearch = Phpfox::getService('forum')->getSearchFilter(false, $forumId, [
            'module_id' => $moduleId,
            'item_id' => $itemId,
        ], $view);
        $bIsSearch = $oSearch->isSearch();

        if ($tag) {
            $bIsSearch = true;
            $bIsTagSearch = true;
            $bIsModuleTagSearch = $moduleId && $itemId;
        }

        $bIsAdvSearch = ($oSearch->get('adv_search') ? true : false);

        if (empty($module_id) && $oSearch->isSearch()) {
            $aIds = [];
            if ($bIsAdvSearch) {
                $forums = ($oSearch->get('forum')) ? $oSearch->get('forum') : [];
                $aSearchForumId = array_unique((!empty($forums) && is_array($forums)) ? $forums : []);
                foreach ($aSearchForumId as $iSearchForum) {
                    if (!is_numeric($iSearchForum)) {
                        continue;
                    }
                    $aIds[] = $iSearchForum;
                }
            } else {
                $iSearchForumId = $forumId;
                $forums = ($iSearchForumId ? Phpfox::getService('forum')->id($iSearchForumId)->live()->getForums() : Phpfox::getService('forum')->live()->getForums());
                if ($iSearchForumId) {
                    $aIds[] = $iSearchForumId;
                }
                foreach ($forums as $forum) {
                    if ($forum['forum_id']) {
                        $aIds[] = $forum['forum_id'];
                    }

                    $aChilds = (array)Phpfox::getService('forum')->id($forum['forum_id'])->getChildren();
                    foreach ($aChilds as $iId) {
                        if ($iId) {
                            $aIds[] = $iId;
                        }
                    }
                }
            }

            $aIds = Phpfox::getService('forum.thread')->getCanViewForumIdList($aIds);

            $oSearch->setCondition('AND ft.forum_id IN(' . implode(',', $aIds) . ')');

        } elseif (!empty($module_id) && !empty($itemId)) {
            $oSearch->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $itemId . ' AND ft.is_announcement = 0');
        }

        $iPageSize = $oSearch->getDisplay();
        $viewId = 'ft.view_id = 0';
        if (empty($moduleId) && empty($itemId)) {
            $aForum = Phpfox::getService('forum')->id($forumId)->getForum();
        } else {
            $aForum = [];
        }

        if (!$bIsSearch && $view != 'pending-post') {
            if (empty($moduleId) && empty($itemId)) {
                if (!empty($view)) {
                    $oSearch->setCondition(($bIsPendingSearch ? 'AND ' : 'AND ft.group_id = 0 AND ft.is_announcement = 0 AND ') . $viewId);
                } else {
                    $oSearch->setCondition((!empty($aForum['forum_id']) ? 'AND ft.forum_id = ' . $aForum['forum_id'] : '') . ' AND ft.group_id = 0 AND ' . $viewId . ' AND ft.is_announcement = 0');
                }
            } else {
                $oSearch->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $itemId . ' AND ' . $viewId . ' AND ft.is_announcement = 0');
            }

            // get the forums that we cant access
            $aForbiddenForums = Phpfox::getService('forum')->getForbiddenForums();
            if (!empty($aForbiddenForums)) {
                $oSearch->setCondition(' AND ft.forum_id NOT IN (' . implode(',', $aForbiddenForums) . ')');
            }
        } else {
            if ($moduleId && $itemId) {
                $oSearch->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $itemId . ' AND ' . $viewId . ' AND ft.is_announcement = 0');
            } else {
                $oSearch->setCondition(($aForum ? 'AND ft.forum_id = ' . $aForum['forum_id'] : '') . ' AND ' . $viewId . ($bIsPendingSearch ? '' : ' AND ft.is_announcement = 0 AND ft.group_id = 0'));
            }
        }

        if ($bIsAdvSearch) {
            if ($oSearch->get('user')) {
                $oSearch->search('like%', 'u.full_name', $oSearch->get('user'));
            }
        }

        $sSort = ($this->request()->get('sort') || $this->request()->get('sort_by')) ? $this->_getSort() : $oSearch->getSort();

        if ($view == 'pending-post') {
            $oSearch->clearConditions();
            $oSearch->setCondition('AND fp.view_id = 1');
        }

        if ($bIsTagSearch === true) {
            if ($bIsModuleTagSearch) {
                $oSearch->setCondition("AND ft.group_id = " . (int)$itemId . " AND tag.tag_url = '" . db()->escape($tag) . "'");
            } else {
                $oSearch->setCondition("AND ft.group_id = 0 AND tag.tag_url = '" . db()->escape($tag) . "'");
            }
        }

        $isSearch = false;
        if ($oSearch->get('search')) {
            $oSearch->search('like%', ['fp.title', 'fpt.text'], $oSearch->get('search'));
            $isSearch = true;
        }

        list(, $posts) = Phpfox::getService('forum.post')
            ->callback($callback)
            ->isSearch($isSearch)
            ->isAdvSearch($bIsAdvSearch)
            ->isTagSearch($bIsTagSearch)
            ->isNewSearch($view == 'new')
            ->isModuleSearch($bIsModuleTagSearch)
            ->get($oSearch->getConditions(), $sSort, $oSearch->getPage(), $iPageSize);

        $parsedPosts = [];

        if ($posts) {
            $this->setPublicFields($this->_getPublicFields('post'));
            foreach ($posts as $post) {
                $parsedPosts[] = $this->getItem($post);
            }
        }

        return $this->success($parsedPosts);
    }

    private function _getSort()
    {
        $sView = $this->request()->get('view');
        $isPost = ($sView == 'new' || $sView == 'pending-post');
        $sSort = $this->request()->get('sort');
        $sSortBy = $this->request()->get('sort_by', 'DESC');
        switch ($sSort) {
            case 'time_stamp':
                $sResult = ($isPost ? 'fp' : 'ft') . '.time_stamp';
                break;
            case 'full_name':
                $sResult = 'u.full_name';
                break;
            case 'total_post':
                $sResult = 'ft.total_post';
                break;
            case 'title':
                $sResult = 'ft.title';
                break;
            case 'total_view':
                $sResult = 'ft.total_view';
                break;
            default:
                $sResult = ($isPost ? 'fp' : 'ft') . '.time_stamp';
                break;

        }

        return $sResult . ' ' . $sSortBy;
    }
}