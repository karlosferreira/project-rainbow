<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_4\Service;

use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class ForumPostApi extends \Apps\Core_MobileApi\Service\ForumPostApi
{
    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'sort_type', 'limit', 'page', 'show', 'skip_start_post',
            'author', 'forum', 'days_prune', 'module_id', 'item_id', 'tag', 'thread', 'forums'
        ])
            ->setAllowedValues('view', ['new', 'pending'])
            ->setAllowedValues('sort', ['time_stamp', 'full_name', 'total_post', 'title', 'total_view'])
            ->setAllowedValues('sort_type', ['desc', 'DESC', 'asc', 'ASC'])
            ->setAllowedValues('days_prune', ['1', '2', '7', '10', '14', '30', '45', '60', '75', '100', '365', '-1'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->setAllowedTypes('forum', 'int')
            ->setAllowedTypes('thread', 'int')
            ->setDefault([
                'limit'           => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'            => 1,
                'sort_type'       => 'DESC',
                'skip_start_post' => 0
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $isSearch = false;
        $sort = $params['sort'];
        $view = $params['view'];
        $parentModule = null;
        if (!empty($params['module_id']) && !empty($params['item_id'])) {
            $parentModule = [
                'module_id' => $params['module_id'],
                'item_id'   => $params['item_id'],
            ];
            if (Phpfox::hasCallback($parentModule['module_id'],
                    'checkPermission') && !Phpfox::callback($parentModule['module_id'] . '.checkPermission',
                    $parentModule['item_id'], 'forum.view_browse_forum')
            ) {
                return $this->permissionError();
            }
        }
        $isThreadDetail = !empty($params['thread']);
        if (!empty($params['forum'])) {
            $forum = $this->database()->select('*')
                ->from(':forum')
                ->where('forum_id = ' . (int)$params['forum'])
                ->execute('getSlaveRow');
            if (!$forum) {
                return $this->notFoundError();
            }
            if (!$this->forumService->hasAccess($forum['forum_id'], 'can_view_forum')) {
                return $this->permissionError();
            }
        }
        //If search multiple forum
        if (!empty($params['forums'])) {
            $forumIds = [];
            if (is_array($params['forums'])) {
                $searchForumId = $params['forums'];
            } else {
                $searchForumId = explode(',', $params['forums']);
            }
            foreach ($searchForumId as $iSearchForum) {
                if (!is_numeric($iSearchForum)) {
                    continue;
                }
                $forumIds[] = $iSearchForum;
            }
            $forumIds = $this->threadService->getCanViewForumIdList($forumIds);
            if (!empty($forumIds)) {
                $this->search()->setCondition('AND ft.forum_id IN(' . implode(',', $forumIds) . ')');
                $params['forum'] = 0;
            }
        }
        $thread = [];
        if ($isThreadDetail) {
            $thread = $this->threadService->getActualThread($params['thread']);
            if (!$thread) {
                return $this->notFoundError();
            }
            if ($thread['forum_id'] && (!$this->forumService->hasAccess($thread['forum_id'], 'can_view_forum') || !$this->forumService->hasAccess($thread['forum_id'], 'can_view_thread_content'))) {
                return $this->permissionError();
            }
        }
        if (!$isThreadDetail) {
            $pendingSearch = false;
            $viewId = ' ';
            if ($view == 'pending') {
                $isSearch = true;
                if (!Phpfox::getUserParam('forum.can_approve_forum_post')) {
                    return $this->permissionError();
                }
                $pendingSearch = true;
                $this->search()->clearConditions();
                $this->search()->setCondition('AND fp.view_id = 1');
            } else {
                $viewId .= 'AND ft.view_id = 0';
            }
            $isTagSearch = false;
            $isModuleTagSearch = false;
            if ($parentModule !== null) {
                $this->search()->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $parentModule['item_id'] . $viewId . ' AND ft.is_announcement = 0');
            } else {
                $this->search()->setCondition(($params['forum'] ? 'AND ft.forum_id = ' . $params['forum'] : '') . $viewId . ($pendingSearch ? '' : ' AND ft.is_announcement = 0 AND ft.group_id = 0'));
            }
            if (!empty($params['tag'])) {
                $isTagSearch = true;
                $isSearch = true;
                if ($parentModule) {
                    $isModuleTagSearch = true;
                    $this->search()->setCondition("AND ft.group_id = " . (int)$parentModule['item_id'] . " AND tag.tag_url = '" . $params['tag'] . "'");
                } else {
                    $this->search()->setCondition("AND ft.group_id = 0 AND tag.tag_url = '" . $params['tag'] . "'");
                }
            }
            if ($params['q']) {
                $isSearch = true;
                $this->search()->setCondition('AND (fp.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '" OR fpt.text LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '")');
            }
            if ($params['author']) {
                $isSearch = true;
                $this->search()->setCondition('AND u.full_name LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['author'] . '%') . '"');
            }

            $sortBy = $params['sort_type'];
            switch ($sort) {
                case 'full_name':
                    $sort = 'u.full_name';
                    break;
                case 'total_post':
                    $sort = 'ft.total_post';
                    break;
                case 'title':
                    $sort = 'ft.title';
                    break;
                case 'total_view':
                    $sort = 'ft.total_view';
                    break;
                default:
                    $sort = 'fp.time_stamp';
                    break;
            }
            $sort = $sort . ' ' . $sortBy;
            if (($params['days_prune']) && $params['days_prune'] != '-1') {
                $isSearch = true;
                $this->search()->setCondition('AND fp.time_stamp >= ' . (PHPFOX_TIME - ($params['days_prune'] * 86400)));
            }
            list(, $items) = $this->postService->isTagSearch($isTagSearch)
                ->isSearch($isSearch)
                ->isAdvSearch($isSearch)
                ->isModuleSearch($isModuleTagSearch)
                ->isSubscribeSearch($view == 'subscribed')
                ->isNewSearch($view == 'new')
                ->get($this->search()->getConditions(), $sort, $params['page'], $params['limit']);
        } else {
            $items = $this->getPosts($params, null, $thread);
        }
        $this->processRows($items);
        return $this->success($items);
    }

    /**
     * @param       $params
     * @param null  $postId
     * @param array $thread
     *
     * @return array|int|string
     */
    private function getPosts($params, $postId = null, $thread = null)
    {
        $conditions = [];
        if (empty($thread)) {
            $thread = $this->database()->select('ft.thread_id, ft.time_stamp, ft.time_update, ft.group_id, ft.view_id, ft.forum_id, ft.is_closed, ft.user_id, ft.is_announcement, ft.order_id, ft.title_url, ft.time_update AS last_time_stamp, ft.title, fs.subscribe_id AS is_subscribed, ft.poll_id, f.forum_id, f.is_closed as forum_is_closed, ft.start_id')
                ->from(':forum_thread', 'ft')
                ->leftjoin(':forum', 'f', 'f.forum_id = ft.forum_id')
                ->leftJoin(':forum_subscribe', 'fs',
                    'fs.thread_id = ft.thread_id AND fs.user_id = ' . Phpfox::getUserId())
                ->where('ft.thread_id = ' . $params['thread'])
                ->execute('getSlaveRow');
        }
        if (empty($thread['thread_id'])) {
            $this->notFoundError();
        }
        if ($thread['view_id'] != '0' && $thread['user_id'] != Phpfox::getUserId()) {
            if (!Phpfox::getUserParam('forum.can_approve_forum_thread') && !$this->moderatorService->hasAccess($thread['forum_id'],
                    'approve_thread')
            ) {
                $this->permissionError();
            }
        }
        $viewId = (Phpfox::getUserParam('forum.can_approve_forum_post') || $this->moderatorService->hasAccess($thread['forum_id'],
                'approve_post')) ? '' : ' AND fp.view_id = 0';
        $conditions[] = 'fp.thread_id = ' . $thread['thread_id'] . $viewId;
        if (!empty($params['skip_start_post'])) {
            //Skip start post of thread
            $conditions[] = 'AND fp.post_id !=' . (int)$thread['start_id'];
        }
        if (!empty($postId)) {
            $conditions[] = 'AND fp.post_id = ' . (int)$postId;
        }
        if (!empty($blockedUserIds = $this->forumService->getBlockedUserIds())) {
            $conditions[] = 'AND (fp.user_id NOT IN (' . implode(',', $blockedUserIds) . ') AND fth.user_id NOT IN (' . implode(',', $blockedUserIds) . '))';
        }
        //Item in pages/group
        if ($thread['group_id'] || empty($thread['forum_id'])) {
            $thread['forum_is_closed'] = 0;
            $thread['forum_id'] = 0;
        }
        $cnt = $this->database()->select('COUNT(*)')
            ->from(':forum_post', 'fp')
            ->join(':forum_thread', 'fth', 'fth.thread_id = fp.thread_id')
            ->where($conditions)
            ->execute('getSlaveField');
        return $this->database()->select('fp.*, ft.thank_id, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS text, ' . Phpfox::getUserField() . ', u.joined, u.country_iso, uf.signature, uf.total_post, ' . $thread['forum_is_closed'] . ' as forum_is_closed, ' . $thread['forum_id'] . ' as forum_id, ' . $thread['group_id'] . ' as group_id, \'' . $thread['title'] . '\' as thread_title, l.like_id AS is_liked')
            ->from(':forum_post', 'fp')
            ->join(':forum_thread', 'fth', 'fth.thread_id = fp.thread_id')
            ->join(':forum_post_text', 'fpt', 'fpt.post_id = fp.post_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id')->join(Phpfox::getT('user_field'), 'uf',
                'uf.user_id = fp.user_id')
            ->leftJoin(':forum_thank', 'ft', 'ft.post_id = fp.post_id AND ft.user_id =' . (int)Phpfox::getUserId())
            ->leftJoin(':like', 'l',
                'l.type_id = \'forum_post\' AND l.item_id = fp.post_id AND l.user_id = ' . Phpfox::getUserId())
            ->where($conditions)
            ->order('fp.time_stamp ASC')
            ->limit($params['page'], $params['limit'], $cnt)
            ->execute('getSlaveRows');
    }
}