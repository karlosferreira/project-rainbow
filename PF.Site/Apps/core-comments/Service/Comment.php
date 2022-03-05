<?php

namespace Apps\Core_Comments\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author           phpFox LLC
 * @package          App_Core_Comments
 * @version          $Id: comment.class.php 7059 2014-01-22 14:20:10Z Fern $
 */
class Comment extends Phpfox_Service
{
    private static $_iLimitStickers = 48;
    protected $_specialTypes = [];

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('comment');
        $this->_specialTypes = [
            'user_status' => 'user',
        ];
    }

    /**
     * Truncate text for comment feed
     * @param $html
     * @param $maxLength
     * @param $emojiPattern
     * @return mixed|string
     */
    public function truncateCommentFeedStatus($html, $maxLength)
    {
        $maxLength = (int)$maxLength;

        if ($maxLength == 0 || mb_strlen($html) <= $maxLength) {
            return $html;
        }

        $statusPattern = '/(<span class="item-tag-emoji"><img class="comment_content_emoji"(?:[^<>]+)?\/><\/span>|<span\s+class="user_profile_link_span".*?><a\s+class="status_user_tag".*?>(.*?)<\/a><\/span>|<a.*?class="site_hash_tag".*?>(.*?)<\/a>|<a.*?class=".*?comment_parsed_url.*?".*?>(.*?)<\/a>)/';
        $textParts = preg_split($statusPattern, $html);
        preg_match_all($statusPattern, $html, $items);

        if (empty($textParts) || empty($items) || (is_array($textParts) && $textParts[0] == $html)) {
            return Phpfox::getLib('parse.output')->truncate($html, $maxLength);
        }

        $items = array_shift($items);
        $newHtml = '';
        $currentLength = 0;

        foreach ($textParts as $key => $textPart) {
            $textLength = mb_strlen($textPart);
            if ($textLength + $currentLength > $maxLength) {
                break;
            }
            if ($textLength > 0) {
                $newHtml .= $textPart;
                $currentLength+= $textLength;
            }
            if (isset($items[$key])) {
                if (preg_match('/<span class="item-tag-emoji"><img class="comment_content_emoji"(?:[^<>]+)?\/><\/span>/', $items[$key])) {
                    if ($currentLength + 1 > $maxLength) {
                        break;
                    }
                    $newHtml .= $items[$key];
                    $currentLength++;
                } else {
                    preg_match('/<span\s+class="user_profile_link_span".*?><a\s+class="status_user_tag".*?>(.*?)<\/a><\/span>/', $items[$key], $matches);

                    if (empty($matches)) {
                        preg_match('/<a.*?class="site_hash_tag".*?>(.*?)<\/a>/', $items[$key], $matches);
                    }

                    if (empty($matches)) {
                        preg_match('/<a.*?class=".*?comment_parsed_url.*?".*?>(.*?)<\/a>/', $items[$key], $matches);
                    }

                    if (!empty($matches[1])) {
                        if (mb_strlen($matches[1]) + $currentLength > $maxLength) {
                            break;
                        }
                        $newHtml .= $items[$key];
                        $currentLength+= mb_strlen($matches[1]);
                    }
                }
            }
        }

        return $newHtml;
    }

    /**
     * @param int $iCommentId
     *
     * @return array|mixed
     */
    public function getQuote($iCommentId)
    {
        $sCacheId = $this->cache()->set('comment_quote_' . (int)$iCommentId);

        if (false === ($aRow = $this->cache()->get($sCacheId))) {

            (($sPlugin = Phpfox_Plugin::get('comment.service_comment_getquote_start')) ? eval($sPlugin) : false);

            $aRow = $this->database()->select('cmt.comment_id, cmt.author, comment_text.text AS text, u.user_id')
                ->from($this->_sTable, 'cmt')
                ->join(Phpfox::getT('comment_text'), 'comment_text', 'comment_text.comment_id = cmt.comment_id')
                ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = cmt.user_id')
                ->where('cmt.comment_id = ' . (int)$iCommentId)
                ->execute('getSlaveRow');

            if (!isset($aRow['comment_id'])) {
                return false;
            }

            if ($aRow['comment_id'] && !$aRow['user_id']) {
                $aRow['user_id'] = $aRow['author'];
            }

            (($sPlugin = Phpfox_Plugin::get('comment.service_comment_getquote_end')) ? eval($sPlugin) : false);

            $this->cache()->save($sCacheId, $aRow);
            Phpfox::getLib('cache')->group('comment', $sCacheId);
        }
        return $aRow;
    }

    /**
     * @param int $iId
     *
     * @return array
     */
    public function getComment($iId)
    {
        list(, $aRows) = $this->get('cmt.*', ['AND cmt.comment_id = ' . $iId], 'cmt.time_stamp DESC', 0, 1, 1);

        return (isset($aRows[0]['comment_id']) ? $aRows[0] : []);
    }

    /**
     * @param string $sSelect
     * @param array $aConds
     * @param string $sSort
     * @param string $iRange
     * @param string $sLimit
     * @param null $iCnt
     * @param bool $bIncludeOwnerDetails
     *
     * @return array
     */
    public function get(
        $sSelect,
        $aConds,
        $sSort = 'cmt.time_stamp DESC',
        $iRange = '',
        $sLimit = '',
        $iCnt = null,
        $bIncludeOwnerDetails = false
    )
    {
        (($sPlugin = Phpfox_Plugin::get('comment.service_comment_get__start')) ? eval($sPlugin) : false);

        $aRows = [];

        if ($iCnt === null) {
            (($sPlugin = Phpfox_Plugin::get('comment.service_comment_get_count_query')) ? eval($sPlugin) : false);

            $iCnt = $this->database()->select('COUNT(*)')
                ->from($this->_sTable, 'cmt')
                ->where($aConds)
                ->execute('getSlaveField');
        }

        if ($iCnt) {
            if (Phpfox::isUser()) {
                $aUserIds = Phpfox::getService('user.block')->get(Phpfox::getUserId(), true);
                if ($aUserIds) {
                    $aConds[] = 'AND cmt.user_id NOT IN (' . implode(',', $aUserIds) . ')';
                }
            }

            if ($bIncludeOwnerDetails) {
                $this->database()->select(Phpfox::getUserField('owner', 'owner_') . ', ')
                    ->leftJoin(Phpfox::getT('user'), 'owner', 'owner.user_id = cmt.owner_user_id');
            }

            if (Phpfox::isModule('like')) {
                $this->database()->select('l.like_id AS is_liked, ')
                    ->leftJoin(Phpfox::getT('like'), 'l',
                        'l.type_id = \'feed_mini\' AND l.item_id = cmt.comment_id AND l.user_id = ' . Phpfox::getUserId());
            }

            (($sPlugin = Phpfox_Plugin::get('comment.service_comment_get_query')) ? eval($sPlugin) : false);

            $aRows = $this->database()->select($sSelect . ", " . (Phpfox::getParam('core.allow_html') ? "comment_text.text_parsed" : "comment_text.text") . " AS text, " . Phpfox::getUserField())
                ->from($this->_sTable, 'cmt')
                ->leftJoin(Phpfox::getT('comment_text'), 'comment_text', 'comment_text.comment_id = cmt.comment_id')
                ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = cmt.user_id')
                ->where($aConds)
                ->order($sSort)
                ->limit($iRange, $sLimit, $iCnt)
                ->execute('getSlaveRows');

        }

        $oUrl = Phpfox_Url::instance();
        $oParseOutput = Phpfox::getLib('parse.output');
        foreach ($aRows as $iKey => $aRow) {
            $aRows[$iKey]['link'] = '';
            if ($aRow['user_name']) {
                $aRows[$iKey]['link'] = $oUrl->makeUrl($aRow['user_name']);
                $aRows[$iKey]['is_guest'] = false;
            } else {
                if (Phpfox::getUserBy('profile_page_id') > 0 && Phpfox::isModule('pages')) {
                    $aRows[$iKey]['full_name'] = $oParseOutput->clean(Phpfox::getUserBy('full_name'));
                } else {
                    $aRows[$iKey]['full_name'] = $oParseOutput->clean($aRow['author']);
                }

                $aRows[$iKey]['is_guest'] = true;
                if ($aRow['author_url']) {
                    $aRows[$iKey]['link'] = $aRow['author_url'];
                }
            }
            $aRows[$iKey]['unix_time_stamp'] = $aRow['time_stamp'];
            $aRows[$iKey]['time_stamp'] = Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aRow['time_stamp']);
            $aRows[$iKey]['posted_on'] = _p('user_link_at_item_time_stamp', [
                    'item_time_stamp' => Phpfox::getTime(Phpfox::getParam('core.global_update_time'),
                        $aRow['time_stamp']),
                    'user' => $aRow,
                ]
            );
            $aRows[$iKey]['unix_update_time'] = $aRow['update_time'];
            $aRows[$iKey]['update_time'] = $aRow['update_time'] > 0 ? Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aRow['update_time']) : '';
            $aRows[$iKey]['post_convert_time'] = Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'core.global_update_time');

            if ($bIncludeOwnerDetails) {
                $aRow['owner_full_name'] = $oParseOutput->clean($aRow['owner_full_name']);
                $commentType = $aRow['type_id'];
                if (isset($this->_specialTypes[$commentType])) {
                    $commentType = $this->_specialTypes[$commentType];
                }
                if (Phpfox::hasCallback($commentType, 'getItemName')) {
                    $aRows[$iKey]['item_name'] = Phpfox::callback($commentType . '.getItemName', $aRow['comment_id'], $aRow['owner_full_name']);
                } else if (Phpfox::hasCallback($commentType, 'getCommentItemName')) {
                    $itemName = Phpfox::callback($commentType . '.getCommentItemName');
                    $aRows[$iKey]['item_name'] = _p('a_href_link_on_name_s_item_name_a',
                        [
                            'link' => Phpfox::getLib('url')->makeUrl('comment.view', ['id' => $aRow['comment_id']]),
                            'name' => $aRow['owner_full_name'],
                            'item_name' => _p($itemName),
                        ]);
                } else {
                    $aRows[$iKey]['item_name'] = _p('a_href_link_on_name_s_item_name_a',
                        [
                            'link' => Phpfox::getLib('url')->makeUrl('comment.view', ['id' => $aRow['comment_id']]),
                            'name' => $aRow['owner_full_name'],
                            'item_name' => str_replace(':', '', strtolower(_p($commentType))),
                        ]);
                }
            }
            $aRows[$iKey]['extra_data'] = $this->getExtraByComment($aRow['comment_id']);
        }

        (($sPlugin = Phpfox_Plugin::get('comment.service_comment_get__end')) ? eval($sPlugin) : false);

        return [$iCnt, $aRows];
    }

    /**
     * @param        $iCommentId
     * @param bool $bGetDeleted
     * @param string $sType
     *
     * @return array|bool|int|string
     */
    public function getExtraByComment($iCommentId, $bGetDeleted = false, $sType = '')
    {
        if (!$iCommentId) {
            return false;
        }
        $aExtra = db()->select('*')
            ->from(':comment_extra')
            ->where('comment_id =' . (int)$iCommentId . (!$bGetDeleted ? ' AND is_deleted = 0' : '') . (!empty($sType) ? ' AND extra_type = \'' . $sType . '\'' : ''))
            ->execute('getRow');
        if (count($aExtra)) {
            if ($aExtra['extra_type'] == 'preview') {
                $aExtra['params'] = json_decode($aExtra['params'], true);
                if (!empty($aExtra['params']['link'])) {
                    $aExtra['params']['actual_link'] = $aExtra['params']['link'];
                    if (Phpfox::getParam('core.warn_on_external_links') && !preg_match('/' . preg_quote(Phpfox::getParam('core.host')) . '/i', $aExtra['params']['link'])) {
                        $aExtra['params']['custom_css'] = 'external_link_warning';
                    }
                } else {
                    $aExtra['params']['actual_link'] = '';
                }
            } else if ($aExtra['extra_type'] == 'sticker') {
                $aSticker = Phpfox::getService('comment.stickers')->getStickerById($aExtra['item_id']);
                if ($aSticker) {
                    $aExtra['image_path'] = $aSticker['image_path'];
                    $aExtra['server_id'] = $aSticker['server_id'];
                    $aExtra['full_path'] = $aSticker['full_path'];
                }
            }
        }
        return $aExtra;
    }

    /**
     * Get a Comment for edit
     *
     * @param int $iCommentId
     *
     * @return array
     */
    public
    function getCommentForEdit($iCommentId)
    {
        (($sPlugin = Phpfox_Plugin::get('comment.service_comment_getcommentforedit')) ? eval($sPlugin) : false);

        $aComment = $this->database()->select('cmt.*, comment_text.text AS text')
            ->from($this->_sTable, 'cmt')
            ->join(Phpfox::getT('comment_text'), 'comment_text', 'comment_text.comment_id = cmt.comment_id')
            ->where('cmt.comment_id = ' . (int)$iCommentId)
            ->execute('getSlaveRow');
        $aComment['extra_data'] = $this->getExtraByComment($iCommentId);
        return $aComment;
    }

    /**
     * @param int $iCommentId
     * @param string $sUserPerm
     * @param string $sGlobalPerm
     *
     * @return bool
     */
    public function hasAccess($iCommentId, $sUserPerm, $sGlobalPerm)
    {
        (($sPlugin = Phpfox_Plugin::get('comment.service_comment_hasaccess_start')) ? eval($sPlugin) : false);

        $sCacheId = $this->cache()->set('comment_detail_access_' . $iCommentId);

        if (false === ($aRow = $this->cache()->get($sCacheId))) {
            $aRow = $this->database()->select('u.user_id')
                ->from($this->_sTable, 'cmt')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = cmt.user_id')
                ->where('cmt.comment_id = ' . (int)$iCommentId)
                ->execute('getSlaveRow');
            $this->cache()->save($sCacheId, $aRow);
            Phpfox::getLib('cache')->group('comment', $sCacheId);
        }

        (($sPlugin = Phpfox_Plugin::get('comment.service_comment_hasaccess_end')) ? eval($sPlugin) : false);

        if (!isset($aRow['user_id'])) {
            return false;
        }

        if ((Phpfox::getUserId() == $aRow['user_id'] && Phpfox::getUserParam('comment.' . $sUserPerm)) || Phpfox::getUserParam('comment.' . $sGlobalPerm)) {
            return $aRow['user_id'];
        }

        return false;
    }

    /**
     * @param string $sType
     * @param int $iItem
     *
     * @return array
     */
    public function getForRss($sType, $iItem)
    {
        if (!Phpfox::isAppActive('Core_RSS')) {
            return [];
        }
        $sCacheId = $this->cache()->set('comment_rss_' . $sType . '_' . $iItem);
        if (false === ($aRss = $this->cache()->get($sCacheId))) {

            (($sPlugin = Phpfox_Plugin::get('comment.service_comment_getforrss__start')) ? eval($sPlugin) : false);

            $oUrl = Phpfox_Url::instance();

            $aSql = [
                "AND cmt.type_id = '" . db()->escape($sType) . "'",
                'AND cmt.item_id = ' . $iItem,
                'AND cmt.view_id = 0',
            ];

            // Get the comments for this page
            list(, $aRows) = $this->get('cmt.*', $aSql, 'cmt.time_stamp DESC', 0, Phpfox::getParam('rss.total_rss_display'));

            $aItems = [];
            foreach ($aRows as $aRow) {
                $aItems[] = [
                    'title' => _p('by_full_name', [
                        'full_name' => Phpfox::getLib('parse.output')->clean($aRow['full_name']),
                    ]),
                    'link' => $oUrl->makeUrl('comment.view', ['id' => $aRow['comment_id']]),
                    'description' => $aRow['text'],
                    'time_stamp' => $aRow['unix_time_stamp'],
                    'creator' => Phpfox::getLib('parse.output')->clean($aRow['full_name']),
                ];
            }

            $aRss = [
                'href' => $oUrl->makeUrl('comment.rss', ['type' => $sType, 'item' => $iItem]),
                'title' => (Phpfox::hasCallback($sType, 'getRssTitle') ? Phpfox::callback($sType . '.getRssTitle', $iItem) : _p('latest_comments')),
                'description' => _p('latest_comments_on_site_title', ['site_title' => Phpfox::getParam('core.site_title')]),
                'items' => $aItems,
            ];

            (($sPlugin = Phpfox_Plugin::get('comment.service_comment_getforrss__end')) ? eval($sPlugin) : false);

            $this->cache()->save($sCacheId, $aRss);
            Phpfox::getLib('cache')->group('comment', $sCacheId);
        }
        return $aRss;
    }

    /**
     * @return int
     */
    public function getSpamTotal()
    {
        $sCacheId = $this->cache()->set('comment_spam_total');

        if (false === ($iTotalSpam = $this->cache()->get($sCacheId))) {
            $iTotalSpam = $this->database()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where('view_id = 9')
                ->execute('getSlaveField');

            $this->cache()->save($sCacheId, $iTotalSpam);
            Phpfox::getLib('cache')->group('comment', $sCacheId);
        }
        return $iTotalSpam;
    }

    /**
     * @param        $sType
     * @param        $iItemId
     * @param int $iLimit
     * @param null $mPager
     * @param null $iCommentId
     * @param string $sPrefix
     * @param null $iTimeStamp
     *
     * @return array
     */
    public function getCommentsForFeed(
        $sType,
        $iItemId,
        $iLimit = 2,
        $mPager = null,
        $iCommentId = null,
        $sPrefix = '',
        $iTimeStamp = null
    )
    {
        if ($iCommentId === null) {
            if ($mPager !== null && !$iTimeStamp) {
                $this->database()->limit(Phpfox_Request::instance()->getInt('page'), $iLimit, $mPager);
            } else {
                $this->database()->limit($iLimit);
            }
        }

        if ($iCommentId !== null) {
            $sWhere = 'c.comment_id = ' . (int)$iCommentId;
        } else {
            if ($sType == 'app') {
                $sWhere = 'c.parent_id = 0 AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0 AND c.feed_table = "' . $sPrefix . 'feed';
            } else {
                $sWhere = 'c.parent_id = 0 AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0';
            }
        }

        if (Phpfox::isUser()) {
            $aUserIds = Phpfox::getService('user.block')->get(Phpfox::getUserId(), true);
            if ($aUserIds) {
                $sWhere .= ' AND c.user_id NOT IN (' . implode(',', $aUserIds) . ')';
            }
        }

        if ($iTimeStamp) {
            $sWhere .= Phpfox::getParam('comment.newest_comment_on_top') ? ' AND c.time_stamp > ' . (int)$iTimeStamp : ' AND c.time_stamp < ' . (int)$iTimeStamp;
        }

        $this->database()->where($sWhere);

        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }
        if (Phpfox::getParam('comment.newest_comment_on_top')) {
            Phpfox::getLib('database')->order('c.time_stamp ASC');
        } else {
            Phpfox::getLib('database')->order('c.time_stamp DESC');
        }
        $aFeedComments = $this->database()->select('c.*, ' . (Phpfox::getParam('core.allow_html') ? "ct.text_parsed" : "ct.text") . ' AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->execute('getSlaveRows');

        $aComments = [];
        if (count($aFeedComments)) {
            foreach ($aFeedComments as $iFeedCommentKey => $aFeedComment) {
                $aFeedComments[$iFeedCommentKey]['unix_update_time'] = $aFeedComment['update_time'];
                $aFeedComments[$iFeedCommentKey]['extra_data'] = $this->getExtraByComment($aFeedComment['comment_id']);
                $aFeedComments[$iFeedCommentKey]['is_hidden'] = $this->checkHiddenComment($aFeedComment['comment_id'],
                    Phpfox::getUserId());
                $aFeedComments[$iFeedCommentKey]['total_hidden'] = 1;
                $aFeedComments[$iFeedCommentKey]['hide_ids'] = $aFeedComment['comment_id'];
                $aFeedComments[$iFeedCommentKey]['hide_this'] = $aFeedComments[$iFeedCommentKey]['is_hidden'];
                if ($iFeedCommentKey && $aFeedComments[$iFeedCommentKey - 1]['is_hidden'] && $aFeedComments[$iFeedCommentKey]['is_hidden']) {
                    $aFeedComments[$iFeedCommentKey - 1]['hide_this'] = false;
                    $aFeedComments[$iFeedCommentKey]['hide_ids'] = $aFeedComments[$iFeedCommentKey - 1]['hide_ids'] . ',' . $aFeedComment['comment_id'];
                    $aFeedComments[$iFeedCommentKey]['total_hidden'] = $aFeedComments[$iFeedCommentKey - 1]['total_hidden'] + 1;
                }
                $aFeedComments[$iFeedCommentKey]['post_convert_time'] = Phpfox::getLib('date')->convertTime($aFeedComment['time_stamp'],
                    'core.global_update_time');

                if (Phpfox::getParam('comment.comment_is_threaded')) {
                    $aFeedComments[$iFeedCommentKey]['children'] = $aFeedComment['child_total'] > 0 ? $this->_getChildren($aFeedComment['comment_id'],
                        $sType, $iItemId, $iCommentId) : [];
                }
                if (!setting('comment.comment_show_replies_on_comment')) {
                    $aFeedComments[$iFeedCommentKey]['last_reply'] = Phpfox::getService('comment')->getLastChild($aFeedComment['comment_id'],
                        $aFeedComment['type_id'], $aFeedComment['item_id']);
                }
            }

            $aComments = array_reverse($aFeedComments);
        }

        return $aComments;
    }

    /**
     * @param $iCommentId
     * @param $iUserId
     *
     * @return bool
     */
    public function checkHiddenComment($iCommentId, $iUserId)
    {
        $iHide = db()->select('hide_id')
            ->from(':comment_hide')
            ->where('comment_id = ' . (int)$iCommentId . ' AND user_id =' . (int)$iUserId)
            ->execute('getField');
        if ($iHide) {
            return true;
        }
        return false;
    }

    /**
     * @param      $iParentId
     * @param      $sType
     * @param      $iItemId
     * @param null $iCommentId
     * @param int $iCnt
     * @param null $iTimStamp
     * @param null $iMaxTime
     * @param null $iLimit
     *
     * @return array
     */
    private function _getChildren(
        $iParentId,
        $sType,
        $iItemId,
        $iCommentId = null,
        $iCnt = 0,
        $iTimStamp = null,
        $iMaxTime = null,
        $iLimit = null
    )
    {
        if ($iLimit != null) {
            $this->database()->limit($iLimit);
        }
        $iTotalComments = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.parent_id = ' . (int)$iParentId . ' AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0' . ($iTimStamp != null ? ' AND c.time_stamp > ' . (int)$iTimStamp : '') . ($iMaxTime != null ? ' AND c.time_stamp <= ' . (int)$iMaxTime : ''))
            ->execute('getSlaveField');
        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if ($iCommentId === null) {
            $this->database()->limit(Phpfox::getParam('comment.thread_comment_total_display'));
        } else if ($iLimit != null) {
            $this->database()->limit($iLimit);
        }

        $this->database()->select('c.*, ' . (Phpfox::getParam('core.allow_html') ? "ct.text_parsed" : "ct.text") . ' AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->order('c.time_stamp ASC');

        $where = 'c.parent_id = ' . (int)$iParentId . ' AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0' . ($iTimStamp != null ? ' AND c.time_stamp > ' . (int)$iTimStamp : '') . ($iMaxTime != null ? ' AND c.time_stamp <= ' . (int)$iMaxTime : '');
        if (Phpfox::isUser()) {
            $aUserIds = Phpfox::getService('user.block')->get(Phpfox::getUserId(), true);
            if ($aUserIds) {
                $where .= ' AND c.user_id NOT IN (' . implode(',', $aUserIds) . ')';
            }
        }

        $aFeedComments = $this->database()
            ->where($where)
            ->execute('getSlaveRows');

        $iCnt++;
        if (count($aFeedComments)) {
            foreach ($aFeedComments as $iFeedCommentKey => $aFeedComment) {
                if ($iTimStamp != null || !setting('comment.comment_show_replies_on_comment')) {
                    $aFeedComments[$iFeedCommentKey]['is_loaded_more'] = true;
                }
                $aFeedComments[$iFeedCommentKey]['unix_update_time'] = $aFeedComment['update_time'];
                $aFeedComments[$iFeedCommentKey]['iteration'] = $iCnt;
                $aFeedComments[$iFeedCommentKey]['extra_data'] = $this->getExtraByComment($aFeedComment['comment_id']);
                $aFeedComments[$iFeedCommentKey]['is_hidden'] = $this->checkHiddenComment($aFeedComment['comment_id'],
                    Phpfox::getUserId());
                $aFeedComments[$iFeedCommentKey]['total_hidden'] = 1;
                $aFeedComments[$iFeedCommentKey]['hide_ids'] = $aFeedComment['comment_id'];
                $aFeedComments[$iFeedCommentKey]['hide_this'] = $aFeedComments[$iFeedCommentKey]['is_hidden'];
                if ($iFeedCommentKey && $aFeedComments[$iFeedCommentKey - 1]['is_hidden'] && $aFeedComments[$iFeedCommentKey]['is_hidden']) {
                    $aFeedComments[$iFeedCommentKey - 1]['hide_this'] = false;
                    $aFeedComments[$iFeedCommentKey]['hide_ids'] = $aFeedComments[$iFeedCommentKey - 1]['hide_ids'] . ',' . $aFeedComment['comment_id'];
                    $aFeedComments[$iFeedCommentKey]['total_hidden'] = $aFeedComments[$iFeedCommentKey - 1]['total_hidden'] + 1;
                }
                $aFeedComments[$iFeedCommentKey]['post_convert_time'] = Phpfox::getLib('date')->convertTime($aFeedComment['time_stamp'],
                    'core.global_update_time');
                $aFeedComments[$iFeedCommentKey]['children'] = $this->_getChildren($aFeedComment['comment_id'], $sType,
                    $iItemId, $iCommentId, $iCnt);
            }
        }

        return [
            'total' => (int)($iTotalComments - Phpfox::getParam('comment.thread_comment_total_display')),
            'comments' => $aFeedComments,
        ];
    }

    /**
     * @param int $iUserId owner (user_id) of the item to comment on (owner of the blog for example)
     * @param int $iPrivacy
     *
     * @return boolean
     */
    public function canPostComment($iUserId, $iPrivacy)
    {
        $bCanPostComment = true;
        if ($iUserId != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items')) {
            $bIsFriend = (Phpfox::isModule('friend')) ? Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $iUserId) : false;

            switch ((int)$iPrivacy) {
                case 1:
                    if ($bIsFriend <= 0) {
                        $bCanPostComment = false;
                    }
                    break;
                case 2:
                    if ($bIsFriend > 0) {
                        $bCanPostComment = true;
                    } else {
                        if (Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriendOfFriend($iUserId)) {
                            $bCanPostComment = false;
                        }
                    }
                    break;
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }

        return $bCanPostComment;
    }

    /**
     * This function use to send mails and notifications to users that commented on an item
     *
     * @param string $sModule
     * @param int $iItemId
     * @param int $iOwnerUserId
     * @param array $aMessage
     * @param null $iSenderUserId
     * @param array $aExcludeUsers
     *
     * @return mixed|null
     */
    public function massMail($sModule, $iItemId, $iOwnerUserId, $aMessage = [], $iSenderUserId = null, $aExcludeUsers = [])
    {
        if (!is_array($aExcludeUsers)) {
            $aExcludeUsers = [];
        }
        if ($sPlugin = Phpfox_Plugin::get('comment.service_comment_massmail__0')) {
            eval($sPlugin);
            if (isset($aPluginReturn)) {
                return $aPluginReturn;
            }
        }
        $aConditions = [
            'type_id' => $sModule,
            'item_id' => intval($iItemId),
            'view_id' => 0,
            'AND user_id != ' . $iOwnerUserId,
        ];
        if ($iSenderUserId) {
            $aUserIds = Phpfox::getService('user.block')->get($iSenderUserId, true);
            if ($aUserIds) {
                $aConditions[] = 'AND user_id NOT IN (' . implode(',', $aUserIds) . ')';
            }
        }

        $aRows = $this->database()->select('*')
            ->from($this->_sTable)
            ->where($aConditions)
            ->group('user_id', true)
            ->executeRows();

        if ($sPlugin = Phpfox_Plugin::get('comment.service_comment_massmail__1')) {
            eval($sPlugin);
        }

        foreach ($aRows as $aRow) {
            if (in_array($aRow['user_id'], $aExcludeUsers) || $aRow['user_id'] == $iSenderUserId) {
                continue;
            }

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject($aMessage['subject'])
                ->message($aMessage['message'])
                ->notification('comment.add_new_comment')
                ->send();

            Phpfox::getService('notification.process')->add('comment_' . $sModule, $iItemId, $aRow['user_id'],
                $iSenderUserId);
        }

        return null;
    }

    /**
     * @param null $aParams
     *
     * @return array
     */
    public function getUploadParams($aParams = null)
    {
        if (isset($aParams['id'])) {
            $iTotalStickers = Phpfox::getService('comment.stickers')->countStickers($aParams['id']);
            $iRemainImage = self::$_iLimitStickers - $iTotalStickers;
        } else {
            $iRemainImage = self::$_iLimitStickers;
        }
        $iMaxFileSize = null;
        $aEvents = [
            'sending' => 'admin_Comment.dropzoneOnSending',
            'success' => 'admin_Comment.dropzoneOnSuccess',
            'queuecomplete' => 'admin_Comment.dropzoneQueueComplete',
            'removedfile' => 'admin_Comment.dropzoneOnRemoveFile',
            'error' => 'admin_Comment.dropzoneOnError',
        ];
        return [
            'max_size' => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'upload_url' => Phpfox::getLib('url')->makeUrl('admincp.comment.frame-upload'),
            'component_only' => true,
            'max_file' => $iRemainImage,
            'js_events' => $aEvents,
            'upload_now' => "false",
            'submit_button' => '',
            'first_description' => _p('drag_n_drop_multi_photos_here_to_upload'),
            'upload_dir' => Phpfox::getParam('core.dir_pic') . 'comment/',
            'upload_path' => Phpfox::getParam('core.url_pic') . 'comment/',
            'update_space' => true,
            'no_square' => true,
            'type_list' => ['jpg', 'gif', 'png'],
            'style' => '',
            'extra_description' => [
                _p('maximum_photos_you_can_upload_is_number', ['number' => $iRemainImage]),
            ],
            'thumbnail_sizes' => Phpfox::getParam('comment.thumbnail_sizes'),
        ];
    }

    /**
     * @param null $aParams
     *
     * @return array
     */
    public function getUploadParamsComment($aParams = null)
    {
        $iMaxFileSize = Phpfox::getUserParam('photo.photo_max_upload_size');
        $iMaxFileSize = $iMaxFileSize > 0 ? $iMaxFileSize / 1024 : 0;
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);
        $aEvents = [
            'success' => '$Core.Comment.dropzoneOnSuccessAttach',
            'error' => '$Core.Comment.dropzoneOnErrorAttach',
            'sending' => '$Core.Comment.dropzoneOnSendingAttach',
            'init' => '$Core.Comment.dropzoneOnInitAttach',
        ];
        $sType = 'comment_comment';
        if (!empty($aParams['parent_id'])) {
            $sType = 'comment_photo_parent_' . $aParams['parent_id'];
        } else if (!empty($aParams['feed_id'])) {
            $sType = 'comment_photo_' . $aParams['feed_id'];
        } else if (!empty($aParams['edit_id'])) {
            $sType = 'comment_edit_photo_' . $aParams['edit_id'];
        }
        $sPreviewTemplate = '';
        return [
            'max_size' => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'component_only' => true,
            'max_file' => 1,
            'js_events' => $aEvents,
            'upload_now' => true,
            'first_description' => _p('drag_n_drop_multi_photos_here_to_upload'),
            'upload_dir' => Phpfox::getParam('core.dir_pic') . 'comment/',
            'upload_path' => Phpfox::getParam('core.url_pic') . 'comment/',
            'update_space' => false,
            'no_square' => true,
            'keep_form' => true,
            'type_list' => ['jpg', 'gif', 'png'],
            'on_remove' => 'admin_Comment.deleteAttachPhoto',
            'style' => 'mini',
            'extra_description' => [
                _p('maximum_photos_you_can_upload_is_number', ['number' => 1]),
            ],
            'thumbnail_sizes' => Phpfox::getParam('comment.attach_sizes'),
            'type' => $sType,
            'preview_template' => $sPreviewTemplate,
        ];
    }

    /**
     * @param $iCommentId
     * @param $sType
     * @param $iItemId
     *
     * @return array|int|string
     */
    public function getLastChild($iCommentId, $sType, $iItemId)
    {
        $comment = $this->database()->select('c.*, ' . (Phpfox::getParam('core.allow_html') ? "ct.text_parsed" : "ct.text") . ' AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.parent_id = ' . (int)$iCommentId . ' AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int)$iItemId . ' AND c.view_id = 0')
            ->order('c.time_stamp DESC')
            ->execute('getRow');
        if ($comment) {
            $comment['full_name'] = Phpfox::getLib('parse.output')->clean($comment['full_name']);
        }
        return $comment;
    }

    /**
     * @param $iParentId
     * @param $sType
     * @param $iItemId
     * @param $iTimeStamp
     * @param $iMaxTime
     * @param $iLimit
     *
     * @return bool|mixed
     */
    public function loadMoreChild($iParentId, $sType, $iItemId, $iTimeStamp, $iMaxTime, $iLimit)
    {
        $aChilds = $this->_getChildren($iParentId, $sType, $iItemId, $iParentId, 0, $iTimeStamp, $iMaxTime, $iLimit);
        if ($aChilds) {
            return $aChilds['comments'];
        }
        return false;

    }

    /**
     * @param string $sMethod
     * @param array $aArguments
     *
     * @return mixed|null
     */
    public function __call($sMethod, $aArguments)
    {
        if ($sPlugin = Phpfox_Plugin::get('comment.service_comment___call')) {
            return eval($sPlugin);
        }

        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
        return null;
    }

    public function setCommentLimitSettings()
    {
        if (in_array(Phpfox::getLib('module')->getFullControllerName(), ['core.index-member', 'pages.view', 'groups.view', 'event.view'])) {
            $comments_feed = setting('comment.comments_show_on_activity_feeds', 4) == 0 ? null : setting('comment.comments_show_on_activity_feeds', 4);
            Phpfox::getLib('setting')->setParam('comment.comment_page_limit', $comments_feed);
            Phpfox::getLib('setting')->setParam('comment.total_comments_in_activity_feed', $comments_feed);
            if (!setting('comment.comment_show_replies_on_comment')) {
                Phpfox::getLib('setting')->setParam('comment.thread_comment_total_display', 0);
            } else {
                Phpfox::getLib('setting')->setParam('comment.thread_comment_total_display',
                    setting('comment.comment_replies_show_on_activity_feeds',
                        4) == 0 ? null : setting('comment.comment_replies_show_on_activity_feeds', 1));
            }
        } else {
            Phpfox::getLib('setting')->setParam('comment.comment_page_limit',
                setting('comment.comments_show_on_item_details',
                    4) == 0 ? null : setting('comment.comments_show_on_item_details', 4));
            if (!setting('comment.comment_show_replies_on_comment')) {
                Phpfox::getLib('setting')->setParam('comment.thread_comment_total_display', 0);
            } else {
                Phpfox::getLib('setting')->setParam('comment.thread_comment_total_display',
                    setting('comment.comment_replies_show_on_item_details',
                        4) == 0 ? null : setting('comment.comment_replies_show_on_item_details', 1));
            }
        }
    }
}
