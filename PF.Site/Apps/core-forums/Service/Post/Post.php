<?php
namespace Apps\Core_Forums\Service\Post;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Search;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');


class Post extends Phpfox_Service
{
    /**
     * @var bool
     */
    private $_bIsSearch = false;

    private $_bIsAdvSearch = false;
    /**
     * @var null|array
     */
    private $_aCallback = null;
    /**
     * @var bool
     */
    private $_bIsTagSearch = false;

    /**
     * @var bool
     */
    private $_bIsNewSearch = false;

    /**
     * @var bool
     */
    private $_isSubscribeSearch = false;

    /**
     * @var bool
     */
    private $_bIsModuleTagSearch = false;
    /**
     * @var int
     */
    private $_iTotalPostCount = 0;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('forum_post');
    }

    public function getStartPostIds()
    {
        $sCacheId = $this->cache()->set('forum_start_posts_ids');
        if(false === ($aIds = $this->cache()->get($sCacheId)))
        {
            $aRows = db()->select('start_id')
                    ->from(Phpfox::getT('forum_thread'))
                    ->where('is_closed = 0')
                    ->execute('getSlaveRows');
            $aIds = array_column($aRows, 'start_id');
            $this->cache()->save($sCacheId, $aIds);
        }
        return $aIds;
    }

    /**
     * @param bool $bIsSearch
     *
     * @return $this
     */
    public function isSearch($bIsSearch = true)
    {
        $this->_bIsSearch = $bIsSearch;
        return $this;
    }

    public function isAdvSearch($bIsAdvSearch = true)
    {
        $this->_bIsAdvSearch = $bIsAdvSearch;
        return $this;
    }

    /**
     * @param array $aCallback
     *
     * @return $this
     */
    public function callback($aCallback)
    {
        $this->_aCallback = $aCallback;
        return $this;
    }

    /**
     * @param int $forumId
     *
     * @param int $iLimit
     * @return array
     */
    public function getRecentForForum($forumId = 0, $iLimit = 4)
    {
        $aWhere = [
            'ft.group_id' => 0,
            'ft.view_id' => 0
        ];
        if ($forumId) {
            $aWhere['ft.forum_id'] = $forumId;
        }

        if (!empty($blockedUserIds = Phpfox::getService('forum')->getBlockedUserIds())) {
            $aWhere[] = 'AND (fp.user_id NOT IN (' . implode(',', $blockedUserIds) . ') AND ft.user_id NOT IN (' . implode(',', $blockedUserIds) . '))';
        }

        $sOrder = 'fp.time_stamp DESC, ft.time_update DESC';

        $this->database()->select('fp.*')
            ->from($this->_sTable, 'fp')
            ->join(':forum_thread', 'ft', 'ft.thread_id = fp.thread_id')
            ->where($aWhere)
            ->order($sOrder)
            ->limit(0, $iLimit);

        $this->database()->union()->unionFrom('fp');

        $posts = $this->database()->select('fp.*, fpt.text_parsed, ft.title as thread_title, ' . Phpfox::getUserField())
            ->join(':forum_thread', 'ft', 'ft.thread_id = fp.thread_id')
            ->join(':forum_post_text', 'fpt', 'fpt.post_id = fp.post_id')
            ->join(':user', 'u', 'u.user_id = fp.user_id')
            ->order($sOrder)
            ->executeRows();

        return $posts;
    }

    /**
     * @param int $iId
     *
     * @return array|boolean
     */
    public function getPost($iId)
    {
        (($sPlugin = Phpfox_Plugin::get('forum.service_post_getpost')) ? eval($sPlugin) : false);

        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'forum_post\' AND l.item_id = fp.post_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aPost = $this->database()->select('fp.*, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS text, ' . Phpfox::getUserField() . ', u.joined, u.country_iso, uf.signature, uf.total_post, ft.forum_id, ft.group_id, ft.user_id AS thread_user_id, ft.title AS thread_title')
            ->from($this->_sTable, 'fp')
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
            ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id')
            ->join(Phpfox::getT('user_field'), 'uf', 'uf.user_id = fp.user_id')
            ->where('fp.post_id = ' . $iId)
            ->execute('getSlaveRow');

        if (empty($aPost['post_id'])) {
            return false;
        }

        $aPost['aFeed'] = array(
            'privacy' => 0,
            'comment_privacy' => 0,
            'like_type_id' => 'forum_post',
            'feed_is_liked' => ($aPost['is_liked'] ? true : false),
            'item_id' => $aPost['post_id'],
            'user_id' => $aPost['user_id'],
            'total_like' => $aPost['total_like'],
            'feed_link' => Phpfox::permalink('forum.thread', $aPost['thread_id'],
                    $aPost['thread_title']) . 'view_' . $aPost['post_id'] . '/',
            'feed_title' => $aPost['thread_title'],
            'feed_display' => 'mini',
            'feed_total_like' => $aPost['total_like'],
            'report_module' => 'forum_post',
            'report_phrase' => _p('report_this_post'),
            'time_stamp' => $aPost['time_stamp'],
            'disable_like_function' => Phpfox::getParam('forum.enable_thanks_on_posts')
        );

        if ($aPost['total_attachment']) {
            list(, $aPost['attachments']) = Phpfox::isModule('attachment') ? Phpfox::getService('attachment')->get('attachment.item_id = ' . $aPost['post_id'] . ' AND attachment.view_id = 0 AND attachment.category_id = \'forum\' AND attachment.is_inline = 0',
                'attachment.attachment_id DESC', false) : [0, []];
        }

        $aPost['last_update_on'] = _p('last_update_on_time_stamp_by_update_user', array(
                'time_stamp' => Phpfox::getTime(Phpfox::getParam('forum.forum_time_stamp'), $aPost['update_time']),
                'update_user' => $aPost['update_user']
            )
        );

        return $aPost;
    }

    /**
     * @param int $iId
     *
     * @return array
     */
    public function getForEdit($iId)
    {
        return $this->database()
            ->select('fp.*, ft.forum_id, fpt.text, ft.group_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'fp')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id')
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
            ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
            ->where('fp.post_id = ' . $iId)
            ->executeRow();
    }

    /**
     * @param int $iThread
     * @param string $mId
     *
     * @return string
     */
    public function getQuotes($iThread, $mId)
    {
        if (strpos($mId, ',')) {
            $sIds = '';
            $aParts = explode(',', $mId);
            foreach ($aParts as $iPart) {
                if (empty($iPart)) {
                    continue;
                }

                if (!is_numeric($iPart)) {
                    continue;
                }

                $sIds .= $iPart . ',';
            }
            $sIds = rtrim($sIds, ',');
        } else {
            $sIds = $mId;
        }

        $aPosts = $this->database()->select('fp.post_id, fp.user_id, fpt.text')
            ->from($this->_sTable, 'fp')
            ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
            ->where('fp.post_id IN(' . $sIds . ') AND fp.thread_id = ' . (int)$iThread . ' AND fp.view_id = 0')
            ->order('fp.time_stamp ASC')
            ->execute('getSlaveRows');

        $sQuotes = '';
        foreach ($aPosts as $aPost) {
            $sQuotes .= "[quote={$aPost['user_id']}]\n" . Phpfox::getLib('parse.bbcode')->stripCode($aPost['text'],
                    array('quote', 'attachment')) . "\n[/quote]\n\n\n";
        }

        return $sQuotes;
    }

    /**
     * @param bool $bIsTagSearch
     *
     * @return $this
     */
    public function isTagSearch($bIsTagSearch = false)
    {
        $this->_bIsTagSearch = $bIsTagSearch;
        return $this;
    }

    /**
     * @param bool $bIsNewSearch
     *
     * @return $this
     */
    public function isNewSearch($bIsNewSearch = false)
    {
        $this->_bIsNewSearch = $bIsNewSearch;
        return $this;
    }

    /**
     * @param $bIsSubscribeSearch
     *
     * @return $this
     */
    public function isSubscribeSearch($bIsSubscribeSearch)
    {
        $this->_isSubscribeSearch = $bIsSubscribeSearch;
        return $this;
    }

    /**
     * @param bool $bIsModuleTagSearch
     *
     * @return $this
     */
    public function isModuleSearch($bIsModuleTagSearch)
    {
        $this->_bIsModuleTagSearch = $bIsModuleTagSearch;
        return $this;
    }

    /**
     * @param array $mConditions
     * @param string $sOrder
     * @param string $iPage
     * @param string $iPageSize
     * @param bool $bCount
     *
     * @return array
     */
    public function get(
        $mConditions = array(),
        $sOrder = 'fp.time_stamp DESC',
        $iPage = '',
        $iPageSize = '',
        $bCount = true
    ) {
        if ($this->_bIsNewSearch !== false) {
            $mConditions[] = 'AND ft.time_update > \'' . Phpfox::getService('forum.thread')->getNewTimeStamp() . '\'';
            $mConditions[] = 'AND fp.time_stamp > \'' . Phpfox::getService('forum.thread')->getNewTimeStamp() . '\'';
            $mConditions[] = 'AND fp.title = \'\'';
        }

        if (!empty($blockedUserIds = Phpfox::getService('forum')->getBlockedUserIds())) {
            $mConditions[] = 'AND (fp.user_id NOT IN (' . implode(',', $blockedUserIds) . ') AND ft.user_id NOT IN (' . implode(',', $blockedUserIds) . '))';
        }

        $this->database()->select('fp.*')
            ->from($this->_sTable, 'fp')
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
            ->where($mConditions)
            ->order($sOrder)
            ->limit($iPage, $iPageSize);

        if(strpos($sOrder, 'u.full_name') !== false || $this->_bIsAdvSearch) {
            $this->database()->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id');
        }

        if($this->_bIsSearch) {
            $this->database()->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id');
        }

        if ($this->_bIsTagSearch !== false) {
            $this->database()->join(Phpfox::getT('tag'), 'tag', "tag.item_id = ft.thread_id AND tag.category_id = '" . ($this->_bIsModuleTagSearch ? 'forum_group' : 'forum') . "'");
        }

        if ($this->_isSubscribeSearch !== false) {
            $this->database()->join(Phpfox::getT('forum_subscribe'), 'fs', 'fs.thread_id = ft.thread_id AND fs.user_id = ' . Phpfox::getUserId());
        }

        $this->database()->union()->unionFrom('fp');

        $aPosts = $this->database()->select('f.forum_id, f.name AS forum_name, f.name_url AS forum_url, ft.title AS thread_title, ft.group_id, ft.thread_id, ft.title_url AS thread_title_url, fp.post_id, fp.view_id, fp.time_stamp, fp.title, fp.total_attachment, fp.total_like, fp.update_time, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS text, ' . Phpfox::getUserField())
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
            ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id')
            ->leftJoin(Phpfox::getT('forum'), 'f', 'f.forum_id = ft.forum_id')
            ->order($sOrder)
            ->execute('getSlaveRows');

        $iTotal = ($iPage > 1 ? (($iPageSize * $iPage) - $iPageSize) : 0);
        foreach ($aPosts as $iKey => $aPost) {
            $iTotal++;
            if (isset($this->_aCallback['group_id'])) {
                $sLink = Phpfox_Url::instance()->makeUrl($this->_aCallback['url_home'],
                    array($aPost['thread_title_url'], 'post' => $aPost['post_id']));
            } else {
                $sLink = Phpfox_Url::instance()->makeUrl('forum', array(
                    $aPost['forum_url'] . '-' . $aPost['forum_id'],
                    $aPost['thread_title_url'],
                    'post' => $aPost['post_id']
                ));
            }

            $aPosts[$iKey]['count'] = $iTotal;
            $aPosts[$iKey]['text'] = Phpfox_Search::instance()->highlight('keyword', $aPost['text']);
            $aPosts[$iKey]['forum_info_phrase'] = _p('title_posted_in_forum_name', array(
                    'link' => $sLink,
                    'title' => Phpfox::getLib('parse.output')->clean($aPost['thread_title']),
                    'forum_link' => (isset($this->_aCallback['group_id']) ? Phpfox_Url::instance()->makeUrl($this->_aCallback['url_home']) : Phpfox_Url::instance()->makeUrl('forum',
                        array($aPost['forum_url'] . '-' . $aPost['forum_id']))),
                    'forum_name' => (isset($this->_aCallback['group_id']) ? $this->_aCallback['title'] : $aPost['forum_name'])
                )
            );
        }

        if (!$bCount) {
            return $aPosts;
        }

        $this->database()->select('COUNT(*)')
            ->from($this->_sTable, 'fp')
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
            ->where($mConditions);

        if($this->_bIsSearch) {
            $this->database()->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id');
        }

        if ($this->_bIsTagSearch !== false) {
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag',
                "tag.item_id = ft.thread_id AND tag.category_id = '" . ($this->_bIsModuleTagSearch ? 'forum_group' : 'forum') . "'");
        }

        if($this->_bIsAdvSearch) {
            $this->database()->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id');
        }

        if ($this->_isSubscribeSearch !== false) {
            $this->database()->join(Phpfox::getT('forum_subscribe'), 'fs',
                'fs.thread_id = ft.thread_id AND fs.user_id = ' . Phpfox::getUserId());
        }

        $iCnt = $this->database()   ->execute('getField');

        return [$iCnt, $aPosts];
    }

    /**
     * @param int $iId
     *
     * @return array
     */
    public function getForRss($iId)
    {
        $aRows = $this->database()->select('fp.post_id, ft.title, ft.title_url, ft.forum_id, ft.group_id, ft.time_stamp, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS description, f.name AS forum_name, f.name_url AS forum_url, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('forum_post'), 'fp')
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id')
            ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
            ->leftJoin(Phpfox::getT('forum'), 'f', 'f.forum_id = ft.forum_id')
            ->where('fp.thread_id = ' . (int)$iId)
            ->order('fp.time_stamp DESC')
            ->limit(20)
            ->execute('getSlaveRows');

        if (!count($aRows)) {
            return array();
        }

        foreach ($aRows as $iKey => $aRow) {
            $aRows[$iKey]['link'] = ($aRow['group_id'] ? Phpfox_Url::instance()->makeUrl('group.forum', array(
                $aRow['title_url'],
                'id' => $aRow['group_id'],
                'post' => $aRow['post_id']
            )) : Phpfox_Url::instance()->makeUrl('forum',
                array($aRow['forum_url'] . '-' . $aRow['forum_id'], $aRow['title_url'], 'post' => $aRow['post_id'])));
            $aRows[$iKey]['creator'] = $aRow['full_name'];
        }

        $aRss = array(
            'href' => Phpfox_Url::instance()->makeUrl('forum',
                array($aRows[0]['forum_url'] . '-' . $aRows[0]['forum_id'], $aRows[0]['title_url'])),
            'title' => _p('latest_posts_in') . ': ' . $aRows[0]['title'],
            'description' => _p('latest_forum_posts_on') . ': ' . Phpfox::getParam('core.site_title'),
            'items' => $aRows
        );

        return $aRss;
    }

    /**
     * @param int $iThreadId
     * @param int $iPostId
     * @param int $iTotal
     *
     * @return float
     */
    public function getPostPage($iThreadId, $iPostId, $iTotal)
    {
        $this->_iTotalPostCount = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('forum_post'))
            ->where('thread_id = ' . (int)$iThreadId . ' AND post_id <= ' . (int)$iPostId)
            ->execute('getSlaveField');

        return ceil($this->_iTotalPostCount / $iTotal);
    }

    /**
     * @param null $iThreadId
     * @param null $iPostId
     * @return array|int|string
     */
    public function getPostCount($iThreadId = null, $iPostId = null)
    {
        if ($iThreadId == null || $iPostId == null) {
            return $this->_iTotalPostCount;
        }
        return $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('forum_post'))
            ->where('thread_id = ' . (int)$iThreadId . ' AND post_id <= ' . (int)$iPostId)
            ->execute('getSlaveField');
    }

    /**
     * @return int
     */
    public function getPendingPost()
    {
        return (int)$this->database()
            ->select('COUNT(*)')
            ->from(Phpfox::getT('forum_post'))
            ->where('view_id = 1')
            ->executeField();
    }

    /**
     * @param int $iPostId
     *
     * @return int|string
     */
    public function getThanksCount($iPostId)
    {
        return $this->database()
            ->select('COUNT(*)')
            ->from(Phpfox::getT('forum_thank'),'ft')
            ->join(':user','u','u.user_id = ft.user_id')
            ->where('ft.post_id = ' . (int)$iPostId)
            ->executeField();
    }

    public function getThanksForPost($iPostId, $iPage = 0, $iPageSize = 5, &$iCount = 0)
    {
        $iCount = $this->database()
                ->select('COUNT(*)')
                ->from(Phpfox::getT('forum_thank'), 't')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id=t.user_id')
                ->where('t.post_id=' . intval($iPostId))
                ->execute('getField');
        $aUserThank = [];
        if ($iCount) {
            $aUserThank = $this->database()
                    ->select('t.*, ' . Phpfox::getUserField())
                    ->from(Phpfox::getT('forum_thank'), 't')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id=t.user_id')
                    ->where('t.post_id=' . intval($iPostId))
                    ->limit($iPage,$iPageSize,$iCount)
                    ->execute('getSlaveRows');
        }
        return $aUserThank;
    }

    public function getLastPost($iThreadId)
    {
        return $this->database()->select('fp.post_id, fp.time_stamp, fp.update_time, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'fp')
            ->join(':forum_thread', 'ft', 'ft.thread_id = fp.thread_id')
            ->join(':user', 'u', 'u.user_id = fp.user_id')
            ->where('ft.thread_id = ' . (int)$iThreadId)
            ->order('fp.time_stamp DESC')
            ->execute('getRow');
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('forum.service_post_post__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}