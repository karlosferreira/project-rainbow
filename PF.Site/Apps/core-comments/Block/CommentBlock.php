<?php

namespace Apps\Core_Comments\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;
use Phpfox_Plugin;
use Phpfox_Request;

class CommentBlock extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::isModule('feed')) {
            return false;
        }
        $aFeed = $this->getParam('aFeed');
        if (!isset($aFeed['comment_type_id'])) {
            $aFeed['comment_type_id'] = isset($aFeed['type_id']) ? $aFeed['type_id'] : '';
        }
        if (!isset($aFeed['feed_id'])) {
            $aFeed['feed_id'] = $aFeed['item_id'];
        }
        $aFeed['is_view_item'] = true;
        $sFeedType = (isset($aFeed['feed_display']) ? $aFeed['feed_display'] : null);

        if (Phpfox::isModule('comment') && Phpfox::getUserParam('comment.can_delete_comment_on_own_item') && ($iOwnerDeleteCmt = $this->request()->getInt('ownerdeletecmt')) && isset($aFeed['user_id']) && $aFeed['user_id'] == Phpfox::getUserId()) {
            if (Phpfox::getService('comment.process')->deleteInline($iOwnerDeleteCmt, $aFeed['comment_type_id'],
                true)
            ) {
                $this->url()->forward($aFeed['feed_link'], _p('comment_successfully_deleted'));
            }
        }

        $bCanPostComment = true;

        // check user group setting of module/app -- Can add comment?
        if ($aFeed['comment_type_id'] != 'app' && Phpfox::hasCallback($aFeed['comment_type_id'], 'getAjaxCommentVar')) {
            $sVar = Phpfox::callback($aFeed['comment_type_id'] . '.getAjaxCommentVar');
            if ($sVar !== null) {
                $bCanPostComment = Phpfox::getUserParam($sVar);
            }
        }

        if ($bCanPostComment && isset($aFeed['comment_privacy']) && (Phpfox::isModule('privacy') && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))) {
            switch ($aFeed['comment_privacy']) {
                case 1:
                    if ((int)$aFeed['feed_is_friend'] <= 0) {
                        $bCanPostComment = false;
                    }
                    break;
                case 2:
                    if ((int)$aFeed['feed_is_friend'] > 0) {
                        $bCanPostComment = true;
                    } else {
                        if (Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriendOfFriend($aFeed['user_id'])) {
                            $bCanPostComment = false;
                        }
                    }
                    break;
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
        $aFeed['can_post_comment'] = $bCanPostComment;

        if (isset($aFeed['total_like']) && (int)$aFeed['total_like'] > 0 && Phpfox::isModule('like')) {
            $aFeed['likes'] = Phpfox::getService('like')->getLikesForFeed($aFeed['like_type_id'], $aFeed['item_id'],
                (int)$aFeed['feed_is_liked'] > 0, Phpfox::getParam('feed.total_likes_to_display'),
                false, (isset($aFeed['feed_table_prefix']) ? $aFeed['feed_table_prefix'] : ''));
        }

        /* Quick check without the actions*/
        $aFeed['bShowEnterCommentBlock'] = false;

        if (Phpfox::isModule('like') && ((isset($aFeed['total_like']) && $aFeed['total_like'] > 0) ||
                (isset($aFeed['total_comment']) && $aFeed['total_comment'] > 0)
            )
        ) {
            $aFeed['bShowEnterCommentBlock'] = true;
        }

        $iPageLimit = 2;
        $mPager = null;
        $iCommentId = null;
        $bIsViewingComments = false;
        $bIsLoadMoreComment = $this->getParam('bIsLoadMoreComment', false);
        $iShownTotal = $this->getParam('iShownTotal', 0);
        $iCommentId = $this->request()->getInt('comment', 0);
        if (Phpfox::isModule('comment') && $sFeedType != 'mini') {
            if ((int)$aFeed['total_comment'] > 0 || $iCommentId) {
                if ($sFeedType == 'view') {
                    $iPageLimit = $bIsLoadMoreComment ? 10 : Phpfox::getParam('comment.comment_page_limit');
                    if ($this->request()->get('stream-mode')) {
                        $iPageLimit = ($iPageLimit + 1);
                        if (!defined('PHPFOX_FEED_STREAM_MODE')) {
                            define('PHPFOX_FEED_STREAM_MODE', true);
                        }
                    }
                    $mPager = $aFeed['total_comment'];
                }
                if ($iCommentId) {
                    $bIsViewingComments = true;
                } else {
                    $iCommentId = null;
                }
                $aFeed['comments'] = Phpfox::getService('comment')->getCommentsForFeed($aFeed['comment_type_id'],
                    $aFeed['item_id'], $iPageLimit, $mPager, $iCommentId,
                    (isset($aFeed['feed_table_prefix']) ? $aFeed['feed_table_prefix'] : ''),
                    $this->getParam('iTimeStamp'));
            }
        }
        if ($sFeedType == 'view') {
            Phpfox_Pager::instance()->set([
                    'ajax'    => 'comment.viewMoreFeed',
                    'page'    => Phpfox_Request::instance()->getInt('page'),
                    'size'    => $iPageLimit,
                    'count'   => $mPager,
                    'phrase'  => Phpfox::isModule('comment') ? _p('view_previous_comments') : '',
                    'icon'    => 'misc/comment.png',
                    'aParams' => [
                        'comment_type_id'   => $aFeed['comment_type_id'],
                        'item_id'           => $aFeed['item_id'],
                        'append'            => true,
                        'pagelimit'         => $iPageLimit,
                        'total'             => $mPager,
                        'feed_table_prefix' => (isset($aFeed['feed_table_prefix']) ? $aFeed['feed_table_prefix'] : '')
                    ]
                ]
            );
        }

        $aFeed['type_id'] = (!empty($aFeed['type_id']) ? $aFeed['type_id'] : (isset($aFeed['report_module']) ? $aFeed['report_module'] : ''));

        if ($aFeed['type_id'] == 'forum_reply') {
            $aFeed['type_id'] = 'forum_post';
        }
        if (!isset($aFeed['feed_like_phrase']) && Phpfox::isModule('like')) {
            Phpfox::getService('feed')->getPhraseForLikes($aFeed);
        }
        if (Phpfox::isModule('share') and !isset($aFeed['total_share'])) {
            $aFeed['total_share'] = Phpfox::getService('feed')->getShareCount($aFeed['type_id'], $aFeed['item_id']);
        }

        $aFeedActions = Phpfox::getService('feed')->getFeedActions($aFeed);
        $aFeed = array_merge($aFeed, $aFeedActions);

        if (Phpfox::isAppActive('P_Reaction')) { // check and support reaction app
            if (!isset($aFeed['like_item_id'])) {
                $aFeed['like_item_id'] = $aFeed['item_id'];
            }
            Phpfox::getService('preaction')->getReactionsPhrase($aFeed);
        }
        
        $this->template()->assign([
                'aFeed'              => $aFeed,
                'sFeedType'          => $sFeedType,
                'bIsViewingComments' => $bIsViewingComments,
                'feedJson'           => json_encode($aFeed),
                'iShownTotal'        => $iShownTotal + (!empty($aFeed['comments']) ? count($aFeed['comments']) : 0),
            ]
        );

        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_block_comment_clean')) ? eval($sPlugin) : false);
    }
}