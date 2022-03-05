<?php
namespace Apps\Core_Blogs\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Blog
 * @package Apps\Core_Blogs\Service
 */
class Blog extends Phpfox_Service
{
    /**
     * @var array
     */
    private $_aSpecial = [
        'category',
        'tag'
    ];

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('blog');

        (($sPlugin = Phpfox_Plugin::get('blog.service_blog___construct')) ? eval($sPlugin) : false);
    }

    public function getHiddenViews()
    {
        return array('pending', 'my');
    }

    /**
     * @param string $sUrl
     *
     * @return bool
     */
    public function isValidUrl($sUrl)
    {
        return (in_array(Phpfox::getLib('parse.input')->cleanTitle($sUrl),
            $this->_aSpecial) ? true : Phpfox_Error::set(_p('invalid')));
    }

    /**
     * @param int $iUserId
     *
     * @return int
     */
    public function getDraftsCount($iUserId)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getdraftscount__start')) ? eval($sPlugin) : false);

        $sCacheId = $this->cache()->set('blog_draft_count_' . (int)$iUserId);
        if (!$iBlogDraftsCount = $this->cache()->get($sCacheId, 1)) {
            $iBlogDraftsCount = db()->select("COUNT(*)")
                ->from($this->_sTable, 'blog')
                ->where('user_id = ' . $iUserId . ' AND post_status = ' . BLOG_STATUS_DRAFT)
                ->execute('getSlaveField');
            $this->cache()->save($sCacheId, $iBlogDraftsCount);
        }
        return $iBlogDraftsCount;
    }

    /**
     * @param string $sLimit
     * This function use for newest blog and newest blog show on Core What's New block
     * @return array
     */
    public function getNewBlogs($sLimit)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getnewblogs__start')) ? eval($sPlugin) : false);
        $aRows = db()->select('b.blog_id, b.title, u.user_name')
            ->from($this->_sTable, 'b')
            ->join(Phpfox::getT('user'), 'u', 'b.user_id = u.user_id')
            ->limit($sLimit)
            ->execute('getSlaveRows');
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getnewblogs__end')) ? eval($sPlugin) : false);
        return $aRows;
    }

    /**
     * Get a blog detail for edit
     * @param int $iBlogId ID of a blog
     *
     * @return array detail of a blog
     */
    public function getBlogForEdit($iBlogId)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getblogsforedit__start')) ? eval($sPlugin) : false);

        $aBlog = db()->select("blog.*, blog_text.text AS text, u.user_name")
            ->from($this->_sTable, 'blog')
            ->join(Phpfox::getT('blog_text'), 'blog_text', 'blog_text.blog_id = blog.blog_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = blog.user_id')
            ->where('blog.blog_id = ' . (int)$iBlogId)
            ->execute('getSlaveRow');

        return $aBlog;
    }

    /**
     * Get detail of a blog for display
     * @param int $iBlogId ID of a blog
     *
     * @return array detail of a blog
     */
    public function getBlog($iBlogId)
    {

        (($sPlugin = Phpfox_Plugin::get('blog.service_blog_getblog')) ? eval($sPlugin) : false);

        if (Phpfox::isModule('track')) {

            $sJoinQuery = Phpfox::isUser() ? 'blog_track.user_id = ' . Phpfox::getUserBy('user_id') : 'blog_track.ip_address = \'' . $this->database()->escape(Phpfox::getIp()) . '\'';
            db()->select("blog_track.item_id AS is_viewed, ")
                ->leftJoin(Phpfox::getT('track'), 'blog_track',
                    'blog_track.item_id = blog.blog_id AND type_id=\'blog\' AND ' . $sJoinQuery);
        }

        if (Phpfox::isModule('friend')) {
            db()->select('f.friend_id AS is_friend, ')
                ->leftJoin(Phpfox::getT('friend'), 'f',
                    "f.user_id = blog.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }

        if (Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'blog\' AND l.item_id = blog.blog_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aRow = db()
            ->select("blog.*, " . (Phpfox::getParam('core.allow_html') ? "blog_text.text_parsed" : "blog_text.text") . " AS text, " . Phpfox::getUserField())
            ->from($this->_sTable, 'blog')
            ->join(Phpfox::getT('blog_text'), 'blog_text', 'blog_text.blog_id = blog.blog_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = blog.user_id')
            ->where('blog.blog_id = ' . (int)$iBlogId)
            ->execute('getSlaveRow');

        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getblog__end')) ? eval($sPlugin) : false);

        if (!isset($aRow['is_friend'])) {
            $aRow['is_friend'] = 0;
        }
        if (!isset($aRow['is_viewed'])) {
            $aRow['is_viewed'] = 0;
        }
        return $aRow;
    }

    /**
     * Check user can view a blog or not
     * @param int $iBlogId
     * @param string $sUserPerm
     * @param string $sGlobalPerm
     *
     * @return bool
     */
    public function hasAccess($iBlogId, $sUserPerm, $sGlobalPerm)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.service_blog_hasaccess_start')) ? eval($sPlugin) : false);

        $aRow = db()->select('u.user_id')
            ->from($this->_sTable, 'blog')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = blog.user_id')
            ->where('blog.blog_id = ' . (int)$iBlogId)
            ->execute('getSlaveRow');

        (($sPlugin = Phpfox_Plugin::get('blog.service_blog_hasaccess_end')) ? eval($sPlugin) : false);

        if (!isset($aRow['user_id'])) {
            return false;
        }

        if ((Phpfox::getUserId() == $aRow['user_id'] && Phpfox::getUserParam('blog.' . $sUserPerm)) || Phpfox::getUserParam('blog.' . $sGlobalPerm)) {
            return $aRow['user_id'];
        }

        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getblog__end')) ? eval($sPlugin) : false);

        return false;
    }

    /**
     * @param string $sTitle
     * @param bool $bCleanOnly
     *
     * @return string
     */
    public function prepareTitle($sTitle, $bCleanOnly = false)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_preparetitle__start')) ? eval($sPlugin) : false);

        return Phpfox::getLib('parse.input')->prepareTitle('blog', $sTitle, 'title_url', Phpfox::getUserId(),
            Phpfox::getT('blog'), null, $bCleanOnly);
    }

    /**
     * @param array $aItems
     * @param null|string $sType
     */
    public function getExtra(&$aItems, $sType = null)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getextra__start')) ? eval($sPlugin) : false);

        if (!is_array($aItems)) {
            $aItems = array();
        }

        $aIds = array();
        foreach ($aItems as $iKey => $aValue) {
            $aIds[] = $aValue['blog_id'];
        }

        foreach ($aItems as $iKey => $aValue) {
            $aCategories = Phpfox::getService('blog.category')->getCategoriesByBlogId($aValue['blog_id']);
            if (!empty($aCategories)) {
                $sCategories = '';
                $aCacheCategory = array();
                foreach ($aCategories as $aCategory) {
                    if (isset($aCacheCategory[$aCategory['category_id']])) {
                        continue;
                    }

                    $aCacheCategory[$aCategory['category_id']] = true;

                    if (!empty($aCategory['user_id']) && $sType == 'user_profile') {
                        $sCategories .= ', <a href="' . Phpfox::getLib('url')->permalink($aValue['user_name'] . '.blog.category',
                                $aCategory['category_id'],
                                _p($aCategory['category_name'])) . '">' . _p($aCategory['category_name']) . '</a>';
                    } else {
                        $sCategories .= ', <a href="' . $aCategory['link'] . '">' . _p($aCategory['category_name']) . '</a>';
                    }
                }

                $sCategories = trim(ltrim($sCategories, ','));

                $aItems[$iKey]['categories'] = $sCategories;

                $aItems[$iKey]['info'] = _p('posted_x_by_x_in_x', array(
                    'date' => Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aValue['time_stamp']),
                    'link' => Phpfox::getLib('url')->makeUrl($aValue['user_name']),
                    'user' => $aValue,
                    'categories' => $sCategories
                ));
            } else {
                $aItems[$iKey]['info'] = _p('posted_x_by_x', [
                    'date' => Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aValue['time_stamp']),
                    'link' => Phpfox::getLib('url')->makeUrl($aValue['user_name']),
                    'user' => $aValue
                ]);
            }

            $aItems[$iKey]['bookmark_url'] = Phpfox::permalink('blog', $aValue['blog_id'], $aValue['title']);

            $aItems[$iKey]['aFeed'] = array(
                'feed_display' => 'mini',
                'comment_type_id' => 'blog',
                'privacy' => $aValue['privacy'],
                'comment_privacy' => $aValue['privacy_comment'],
                'like_type_id' => 'blog',
                'feed_is_liked' => (isset($aValue['is_liked']) ? $aValue['is_liked'] : false),
                'feed_is_friend' => (isset($aValue['is_friend']) ? $aValue['is_friend'] : false),
                'item_id' => $aValue['blog_id'],
                'user_id' => $aValue['user_id'],
                'total_comment' => $aValue['total_comment'],
                'feed_total_like' => $aValue['total_like'],
                'total_like' => $aValue['total_like'],
                'feed_link' => $aItems[$iKey]['bookmark_url'],
                'feed_title' => $aValue['title'],
                'time_stamp' => $aValue['time_stamp'],
                'type_id' => 'blog'
            );
        }

        unset($aTags, $aCategories);

        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getextra__end')) ? eval($sPlugin) : false);
    }

    /**
     * @param int $iLimit
     * @param int $iCacheTime
     *
     * @return array
     */
    public function getNew($iLimit = 3, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('blog_new_' . (int)$iLimit);
        if (!$iCacheTime || ($aRows = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getnew__start')) ? eval($sPlugin) : false);
            $sWhere = 'b.is_approved = 1 AND b.privacy = ' . PRIVACY_EVERYONE . ' AND b.post_status = ' . BLOG_STATUS_PUBLIC;
            $aModules = ['blog'];
            // Apply settings show blog of pages / groups
            if (Phpfox::getParam('blog.display_blog_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                $aModules[] = 'groups';
            }
            if (Phpfox::getParam('blog.display_blog_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                $aModules[] = 'pages';
            }
            $sWhere .= ' AND b.module_id IN ("' . implode('","', $aModules) . '")';

            $aRows = db()
                ->select('b.blog_id, b.title, b.image_path, b.server_id, b.time_stamp, ' . Phpfox::getUserField())
                ->from($this->_sTable, 'b')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
                ->where($sWhere)
                ->limit($iLimit)
                ->order('b.time_stamp DESC')
                ->execute('getSlaveRows');
            (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getnew__end')) ? eval($sPlugin) : false);
            $this->cache()->save($sCacheId, $aRows);
        }

        return $aRows;
    }

    /**
     * Get total blog marked as spam on site
     * @return int
     */
    public function getSpamTotal()
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getspamtotal__start')) ? eval($sPlugin) : false);

        $sCacheId = $this->cache()->set('blog_spam_total');
        if (!$iTotalSpam = $this->cache()->get($sCacheId)) {
            $iTotalSpam = (int)db()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where('is_approved = 9')
                ->execute('getSlaveField');
            $this->cache()->save($sCacheId, $iTotalSpam);
        }
        return $iTotalSpam;
    }

    /**
     * Get total pending blog of site
     * @return int
     */
    public function getPendingTotal()
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getpendingtotal')) ? eval($sPlugin) : false);

        $sCacheId = $this->cache()->set('blog_pending_total');
        if (!$iTotalPending = $this->cache()->get($sCacheId, 3)) {
            $iTotalPending = (int)db()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where('is_approved = 0')
                ->execute('getSlaveField');
            $this->cache()->save($sCacheId, $iTotalPending);
        }

        return $iTotalPending;
    }

    /**
     * Get total blog draft of a user
     * @param int $iUserId
     *
     * @return int
     */
    public function getTotalDrafts($iUserId = 0)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_gettotaldrafts')) ? eval($sPlugin) : false);

        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }

        $sCacheId = $this->cache()->set('blog_draft_total_' . (int)$iUserId);
        if (!$iTotalDrafts = $this->cache()->get($sCacheId, 3)) {
            $iTotalDrafts = (int)db()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where('user_id = ' . (int)$iUserId . ' AND post_status = ' . BLOG_STATUS_DRAFT)
                ->execute('getSlaveField');
            $this->cache()->save($sCacheId, $iTotalDrafts);
        }
        return $iTotalDrafts;
    }

    /**
     * Get total blog of a user in profile menu
     * @param int $iUserId
     *
     * @return int
     */
    public function getProfileTotal($iUserId = 0)
    {
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }

        $iTotalDrafts = (int)db()->select('COUNT(*)')
            ->from($this->_sTable, 'b')
            ->where('b.user_id = ' . (int)$iUserId . ' AND b.post_status = ' . BLOG_STATUS_PUBLIC . ' AND b.is_approved = ' . ACTIVATE . $this->getConditionsForSettingPageGroup())
            ->execute('getSlaveField');

        return $iTotalDrafts;
    }

    /**
     * Return total blog of current user. Include blog of pages / groups
     * @return int
     */
    public function getMyBlogTotal()
    {
        $sWhere = 'user_id = ' . Phpfox::getUserId();
        $aModules = [];
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (!Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        $sWhere .= (!empty($aModules) ? ' AND (module_id NOT IN ("' . implode('","',
                $aModules) . '") OR module_id is NULL)' : '');

        return (int)db()->select('COUNT(blog_id)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    /**
     * @description: check if current user can view a blog
     * @param      $iId
     * @param bool $bReturnItem
     *
     * @return array|bool
     */
    public function canViewItem($iId, $bReturnItem = false)
    {

        if (!Phpfox::getUserParam('blog.view_blogs')) {
            return false;
        }

        $aItem = $this->getBlog($iId);
        if (empty($aItem['blog_id'])) {
            Phpfox_Error::set(_p('blog_not_found'));
            return false;
        }

        if (isset($aItem['module_id']) && !empty($aItem['item_id']) && !Phpfox::isModule($aItem['module_id'])) {
            Phpfox_Error::set(_p('Cannot find the parent item.'));
            return false;
        }

        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $aItem['user_id'])) {
            Phpfox_Error::set(_p('Sorry, this content isn\'t available right now'));
            return false;
        }

        if (Phpfox::isModule('privacy')) {
            if (!Phpfox::getService('privacy')->check('blog', $aItem['blog_id'], $aItem['user_id'], $aItem['privacy'],
                $aItem['is_friend'], true)) {
                return false;
            }
        }

        if (isset($aItem['module_id']) && Phpfox::isModule($aItem['module_id']) && Phpfox::hasCallback($aItem['module_id'],
                'checkPermission')) {
            if (!Phpfox::callback($aItem['module_id'] . '.checkPermission', $aItem['item_id'],
                'blog.view_browse_blogs')) {
                Phpfox_Error::set(_p('unable_to_view_this_item_due_to_privacy_settings'));
                return false;
            }
        }

        if (!Phpfox::getUserParam('blog.can_approve_blogs')) {
            if ($aItem['is_approved'] != '1' && $aItem['user_id'] != Phpfox::getUserId()) {
                Phpfox_Error::set(_p('blog_not_found'));
                return false;
            }
        }

        if ($aItem['post_status'] == BLOG_STATUS_DRAFT && Phpfox::getUserId() != $aItem['user_id'] && !Phpfox::getUserParam('blog.edit_user_blog')) {
            Phpfox_Error::set(_p('blog_not_found'));
            return false;
        }

        if ($bReturnItem) {
            $aCategories = Phpfox::getService('blog.category')->getCategoriesByBlogId($aItem['blog_id']);
            $aItem['categories'] = isset($aCategories) ? $aCategories : [];

            if (Phpfox::isModule('tag')) {
                $aTags = Phpfox::getService('tag')->getTagsById('blog', $aItem['blog_id']);
                $aItem['tag_list'] = '';
                if (isset($aTags[$aItem['blog_id']])) {
                    $aItem['tag_list'] = '';
                    foreach ($aTags[$aItem['blog_id']] as $aTag) {
                        $aItem['tag_list'] .= ' ' . $aTag['tag_text'] . ',';
                    }
                    $aItem['tag_list'] = trim(trim($aItem['tag_list'], ','));
                }
            }

            if (Phpfox::isModule('attachment')) {
                $aAttachments = Phpfox::getService('attachment')->getForItemEdit($iId, 'blog', null, true);
                $aItem['attachments'] = [];
                if (is_array($aAttachments)) {
                    foreach ($aAttachments as $aAttachment) {
                        $aItem['attachments'][] = [
                            'attachment_id' => $aAttachment['attachment_id'],
                            'attachment_link' => Phpfox::getLib('url')->makeUrl('blog.download', ['url' => $aAttachment['url']])
                        ];
                    }
                }
            }
        }

        return $bReturnItem ? $aItem : true;
    }

    /**
     * @param string $sMethod
     * @param array $aArguments
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        if ($sPlugin = Phpfox_Plugin::get('blog.service_blog__call')) {
            eval($sPlugin);
            return null;
        }

        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);

        return false;
    }

    /**
     * Get image of a blog
     *
     * @param $sImage
     * @param $iServerId
     * @param int $iSuffix
     * @return string
     */
    public function getImageUrl($sImage, $iServerId, $iSuffix = 500)
    {
        return Phpfox::getLib('image.helper')->display([
                'server_id' => $iServerId,
                'path' => 'core.url_pic',
                'file' => 'blog/' . $sImage,
                'suffix' => $iSuffix,
                'return_url' => true
            ]
        );
    }

    /**
     * Retrieve more permissions on a blog. We'll show dropdown action when permission_enable = true
     *
     * @param $aBlog
     */
    public function retrievePermission(&$aBlog)
    {
        $aBlog['canApprove'] = Phpfox::getService('blog.permission')->canApprove($aBlog);

        $aBlog['canSponsorInFeed'] = $aBlog['canSponsor'] = $aBlog['canPurchaseSponsor'] = false;
        if(Phpfox::isAppActive('Core_BetterAds') && Phpfox::getUserBy('profile_page_id') == 0)
        {
            $aBlog['canSponsorInFeed'] = Phpfox::getService('blog.permission')->canSponsorInFeed($aBlog);
            $aBlog['canSponsor'] = Phpfox::getService('blog.permission')->canSponsor();
            $aBlog['canPurchaseSponsor'] = Phpfox::getService('blog.permission')->canPurchaseSponsor($aBlog);
        }
        $aBlog['canDelete'] = Phpfox::getService('blog.permission')->canDelete($aBlog);
        $aBlog['canEdit'] = Phpfox::getService('blog.permission')->canEdit($aBlog);
        $aBlog['canFeature'] = Phpfox::getService('blog.permission')->canFeature();
        $aBlog['canPublish'] = Phpfox::getService('blog.permission')->canPublish($aBlog);
        $aBlog['permission_enable'] = ($aBlog['canApprove'] || $aBlog['canSponsor'] || $aBlog['canSponsorInFeed'] || $aBlog['canPurchaseSponsor'] || $aBlog['canDelete'] || $aBlog['canEdit'] || $aBlog['canFeature'] || $aBlog['canPublish']);
    }

    /**
     * Get list blog ids which have same category with given categories
     *
     * @param $aRow
     * @param $aCates
     * @return array|int|string
     */
    public function getIdsInThisCategory($aRow, $aCates)
    {
        return trim(db()->select('GROUP_CONCAT(DISTINCT blog_id)')
            ->from(Phpfox::getT('blog_category_data'))
            ->where('category_id IN (' . implode(',', $aCates) . ') AND blog_id <> ' . $aRow['blog_id'])
            ->execute('getSlaveField'), ',');
    }

    /**
     * For block Related. Get all blog which have same categories with a given blog
     *
     * @param $aRow
     * @param $aCates
     * @param $iLimit
     * @return array|bool|int|string
     */
    public function inThisCategory($aRow, $aCates, $iLimit)
    {
        $aRelated = array();
        $aIdsInThisCategory = $this->getIdsInThisCategory($aRow, $aCates);

        if (empty($aIdsInThisCategory)) {
            return false;
        }

        $sWhere = 'b.blog_id IN (' . $aIdsInThisCategory . ') AND b.is_approved = 1 AND b.post_status = ' . BLOG_STATUS_PUBLIC;
        // Apply settings show blog of pages / groups
        $sWhere .= $this->getConditionsForSettingPageGroup();

        $aRows = db()->select('b.*, ' . (Phpfox::getParam('core.allow_html') ? 'bt.text_parsed' : 'bt.text') . ' as text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('blog'), 'b')
            ->join(Phpfox::getT('blog_text'), 'bt', 'b.blog_id = bt.blog_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where($sWhere)
            ->limit(100)
            ->execute('getSlaveRows');

        if (is_array($aRows) && count($aRows)) {
            shuffle($aRows);
            if ($iLimit) {
                for ($i = 0; ($i < $iLimit) && !empty($aRows); ++$i) {
                    $aRow = array_pop($aRows);
                    $aRelated[] = $aRow;
                }
            } else {
                foreach ($aRows as $iKey => $aRow) {
                    $aRelated[] = $aRow;
                }
            }
        }

        return $aRelated;
    }

    /**
     * Get all featured/sponsor blog which show up in block Feature Blog in Blog Home Page.
     * We also shuffle those for making sure that all featured blog still show up
     * at least one time.
     *
     * @param int $iLimit
     * @param int $iCacheTime
     * @return array
     */
    public function getFeatured($iLimit, $iCacheTime = 5)
    {
        $aFeatured = [];
        $sWhere = '';
        $sCacheId = $this->cache()->set('blog_featured');
        if (!$iCacheTime || ($aBlogIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $aBlogIds = [];
            $sWhere = 'b.is_featured = 1 ';
            $sWhere .= $this->getConditionsForSettingPageGroup();
            $aIds = db()->select('b.blog_id')
                ->from($this->_sTable, 'b')
                ->where($sWhere)
                ->limit(Phpfox::getParam('core.cache_total', 100))
                ->executeRows();

            if (!empty($aIds)) {
                foreach ($aIds as $aId) {
                    $aBlogIds[] = $aId['blog_id'];
                }

                ((!empty($aBlogIds) && $iCacheTime) ? $this->cache()->save($sCacheId, $aBlogIds) : null);
            } else {
                return $aFeatured;
            }
        }

        shuffle($aBlogIds);
        $aBlogIds = array_slice($aBlogIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        if (is_array($aBlogIds) && count($aBlogIds)) {
            if ($iLimit) {
                $aFeatured = $this->getBlogsByIds($aBlogIds, $sWhere);

                foreach($aFeatured as $key => $featured)
                {
                    $aFeatured[$key]['total_view'] = $featured['ttv_blog'];
                    $aFeatured[$key]['module_id'] = $featured['blog_module_id'];
                    $aFeatured[$key]['item_id'] = $featured['blog_item_id'];
                }

                shuffle($aFeatured);
            }
        }


        return $aFeatured;
    }

    /**
     * Get all sponsored blog which show up in block Sponsor Blog in Blog Home Page.
     * We also shuffle those for making sure that all featured blog still show up
     * at least one time.
     *
     * @param $iLimit
     * @param int $iCacheTime
     * @return array|bool
     */
    public function getRandomSponsored($iLimit, $iCacheTime = 5)
    {
        $aSponsored = array();
        $sWhere = '';
        $sCacheId = $this->cache()->set('blog_sponsored');
        if (!$iCacheTime || ($aBlogIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $aBlogIds = [];
            $sWhere = 'b.is_sponsor = 1 ';
            $sWhere .= $this->getConditionsForSettingPageGroup();
            $aIds = db()->select('b.blog_id')
                ->from($this->_sTable, 'b')
                ->where($sWhere)
                ->limit(Phpfox::getParam('core.cache_total', 100))
                ->executeRows();

            if (!empty($aIds)) {
                foreach ($aIds as $aId) {
                    $aBlogIds[] = $aId['blog_id'];
                }

                ((!empty($aBlogIds) && $iCacheTime) ? $this->cache()->save($sCacheId, $aBlogIds) : null);
            } else {
                return $aSponsored;
            }
        }

        shuffle($aBlogIds);
        $aBlogIds = array_slice($aBlogIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        if (is_array($aBlogIds) && count($aBlogIds)) {
            if ($iLimit) {
                $sSelect = ', s.*';
                db()->join(Phpfox::getT('better_ads_sponsor'), 's',
                    's.item_id = b.blog_id AND s.module_id = \'blog\' AND s.is_custom = 3 AND s.is_active = 1');
                $aSponsored = $this->getBlogsByIds($aBlogIds, $sWhere, $sSelect);
                shuffle($aSponsored);
            }
        }

        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aSponsored = Phpfox::getService('ad')->filterSponsor($aSponsored);
        }

        foreach($aSponsored as $key => $sponsored)
        {
            $aSponsored[$key]['total_view'] = $sponsored['ttv_blog'];
            $aSponsored[$key]['module_id'] = $sponsored['blog_module_id'];
            $aSponsored[$key]['item_id'] = $sponsored['blog_item_id'];
        }

        shuffle($aSponsored);

        return $aSponsored;
    }

    /**
     * Return prepared params for generating main menu of blog app
     *
     * @return array
     */
    public function getSectionMenu()
    {
        $iMyTotal = $this->getMyBlogTotal();
        $aFilterMenu = [
            _p('all_blogs') => '',
            _p('my_blogs') . ($iMyTotal ? ('<span class="my count-item">' . (($iMyTotal >= 100) ? '99+' : $iMyTotal) . '</span>') : '') => 'my'

        ];

        if ($iDraftTotal = Phpfox::getService('blog')->getTotalDrafts()) {
            $aFilterMenu[_p('my_draft_blog') . '<span class="pending count-item">' . (($iDraftTotal >= 100) ? '99+' : $iDraftTotal) . '</span>'] = 'draft';
        }

        if (!Phpfox::getParam('core.friends_only_community') && Phpfox::isModule('friend')) {
            $aFilterMenu[_p('friends_blogs')] = 'friend';
        }

        if (Phpfox::getUserParam('blog.can_approve_blogs')) {
            $iPendingTotal = Phpfox::getService('blog')->getPendingTotal();
            if ($iPendingTotal) {
                $aFilterMenu[_p('pending_blogs') . (Phpfox::getUserParam('blog.can_approve_blogs') ? '<span id="total_pending" class="pending count-item">' . (($iPendingTotal >= 100) ? '99+' : $iPendingTotal) . '</span>' : 0)] = 'pending';
            }
        }

        return $aFilterMenu;
    }

    /**
     * Get all users which have most posted blogs
     *
     * @param $iLimit
     * @param $iMinPost
     * @param $bCache
     * @param $iCacheTime
     * @return array|int|mixed|string
     */
    public function getTopUsers($iLimit, $iMinPost, $bCache, $iCacheTime)
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_gettop__start')) ? eval($sPlugin) : false);

        $bShowQuery = true;
        $aBloggers = array();
        if ($bCache) {
            $sCacheId = $this->cache()->set('user_activity_blog');
            if ($aBloggers = $this->cache()->get($sCacheId, abs($iCacheTime))) {
                $bShowQuery = false;
            }
        }

        if ($bShowQuery) {
            $sCacheId = $this->cache()->set('user_activity_blog');
            $aBloggers = db()->select(Phpfox::getUserField() . ', COUNT(b.blog_id) AS top_total')
                ->from(Phpfox::getT('user'), 'u')
                ->leftJoin(Phpfox::getT('blog'), 'b',
                    'b.user_id = u.user_id AND b.is_approved = 1 AND b.post_status = ' . BLOG_STATUS_PUBLIC . ' AND b.module_id = \'blog\'')
                ->having('COUNT(b.blog_id) >= ' . $iMinPost)
                ->order('top_total DESC')
                ->group('u.user_id')
                ->limit($iLimit)
                ->execute('getSlaveRows');

            if ($bCache) {
                $this->cache()->save($sCacheId, $aBloggers);
            }
        }

        if (is_array($aBloggers) && count($aBloggers)) {
            $sDefaultCover = flavor()->active->default_photo('user_cover_default', true);
            foreach ($aBloggers as $iKey => $aBlogger) {
                $aBloggers[$iKey]['link'] = Phpfox::getService('user')->getLink($aBlogger['user_id'],
                    $aBlogger['user_name'], 'blog');
                $iCoverId = db()->select('cover_photo')->from(':user_field')->where(['user_id' => $aBlogger['user_id']])->executeField();
                if (!$iCoverId) {
                    $sDefaultCover && $aBloggers[$iKey]['cover_default'] = $sDefaultCover;
                    continue;
                }
                $aCoverPhoto = Phpfox::getService('photo')->getCoverPhoto($iCoverId);
                if (!$aCoverPhoto) {
                    continue;
                }
                $aBloggers[$iKey]['cover_destination'] = $aCoverPhoto['destination'];
                $aBloggers[$iKey]['cover_server_id'] = $aCoverPhoto['server_id'];
            }
        }

        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_gettop__end')) ? eval($sPlugin) : false);

        return $aBloggers;
    }

    /**
     * Apply settings show blog of pages / groups
     * @param $sPrefix
     * @return string
     */
    public function getConditionsForSettingPageGroup($sPrefix = 'b')
    {
        $aModules = ['blog'];
        // Apply settings show blog of pages / groups
        if (Phpfox::getParam('blog.display_blog_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (Phpfox::getParam('blog.display_blog_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }

        (($sPlugin = Phpfox_Plugin::get('blog.service_blog_getconditionsforsettingpagegroup')) ? eval($sPlugin) : false);

        return ' AND ' . $sPrefix . '.module_id IN ("' . implode('","', $aModules) . '")';
    }

    /**
     * Get blogs by list id
     * @param string $sId
     * @param string $sWhere
     * @param string $sSelect
     * @return array|int|string
     */
    public function getBlogsByIds($sId = '', $sWhere = '', $sSelect = '')
    {
        if (empty($sId)) {
            return [];
        }

        if (is_array($sId)) {
            $sId = implode(',', $sId);
        }

        $sWhere .= (!empty($sWhere) ? ' AND' : '') . ' b.blog_id IN (' . $sId . ')';
        $aRows = db()->select('b.module_id AS blog_module_id, b.item_id AS blog_item_id, b.blog_id, b.title, b.image_path, b.server_id, b.time_stamp, b.total_like, b.total_view as ttv_blog, ' . (Phpfox::getParam('core.allow_html') ? 'bt.text_parsed' : 'bt.text') . ' as text, ' . Phpfox::getUserField() . $sSelect)
            ->from($this->_sTable, 'b')
            ->join(Phpfox::getT('blog_text'), 'bt', 'b.blog_id = bt.blog_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->group('b.blog_id')
            ->where($sWhere)
            ->execute('getSlaveRows');

        return $aRows;
    }

    /**
     * Get all blog count
     * @return int
     */
    public function getAllBlogsCount()
    {
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_getallcount__start')) ? eval($sPlugin) : false);

        $sCacheId = $this->cache()->set('blog_all_count');
        if (!$iAllBlogsCount = $this->cache()->get($sCacheId, 1)) {
            $iAllBlogsCount = db()->select("COUNT(*)")
                ->from($this->_sTable)
                ->where(['is_approved' => 1])
                ->executeField();

            $this->cache()->save($sCacheId, $iAllBlogsCount);
        }

        return $iAllBlogsCount;
    }

    /**
     * Check if current user is admin of blog's parent item
     * @param $iBlogId
     * @return bool|mixed
     */
    public function isAdminOfParentItem($iBlogId)
    {
        $aBlog = db()->select('blog_id, module_id, item_id')->from($this->_sTable)->where('blog_id = '.(int)$iBlogId)->execute('getRow');
        if (!$aBlog) {
            return false;
        }
        if ($aBlog['module_id'] != 'blog' && Phpfox::hasCallback($aBlog['module_id'], 'isAdmin')) {
            return Phpfox::callback($aBlog['module_id'] . '.isAdmin', $aBlog['item_id']);
        }
        return false;
    }

    /**
     * @return array
     */
    public function getUploadPhotoParams() {
        $iMaxFileSize = user('blog_photo_max_upload_size');
        $iMaxFileSize = $iMaxFileSize > 0 ? $iMaxFileSize/1024 : 0;
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);
        return [
            'max_size' => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'type_list' => ['jpg', 'jpeg', 'gif', 'png'],
            'upload_dir' => Phpfox::getParam('core.dir_pic') . 'blog' . PHPFOX_DS,
            'upload_path' => Phpfox::getParam('blog.url_photo'),
            'thumbnail_sizes' => Phpfox::getParam('blog.thumbnail_sizes'),
            'no_square' => true
        ];
    }

    /**
     * Apply settings show blog of pages / groups
     * @param $sPrefix
     * @return string
     */
    public function getConditionsForTagCloud(&$aWhere)
    {
        $bIncludeExtraCondition = false;
        if (!Phpfox::getParam('blog.display_blog_created_in_group')) {
            $bIncludeExtraCondition = true;
            $aWhere[] = 'AND blog.module_id != \'groups\'';
        }
        if (!Phpfox::getParam('blog.display_blog_created_in_page')) {
            $aWhere[] = 'AND blog.module_id != \'pages\'';
            $bIncludeExtraCondition = true;
        }
        if ($bIncludeExtraCondition) {
            $this->database()->join(':blog', 'blog', 'blog.blog_id = t.item_id');
        }
        return true;
    }

    public function getForRssFeed()
    {
        $sCondition = 'b.is_approved = 1 AND b.privacy = 0 AND b.post_status = 1';
        $sCondition .= $this->getConditionsForSettingPageGroup();
        if (Phpfox::isAppActive('PHPfox_Groups')) {
            //Don't get blog post in close, secret group
            $aNotInclude = $this->database()->select('b.blog_id')
                ->from(':blog', 'b')
                ->join(':pages', 'p', 'p.page_id = b.item_id AND b.module_id = \'groups\'')
                ->where('p.item_type = 1 AND p.reg_method != 0')->execute('');
            $sCondition .= ' AND b.blog_id NOT IN (' . $aNotInclude . ')';
        }

        (($sPlugin = Phpfox_Plugin::get('blog.component_service_blog_get_for_rss_feed')) ? eval($sPlugin) : false);

        $aRows = $this->database()->select('bt.text_parsed AS text, b.blog_id, b.title, u.user_name, u.full_name, b.time_stamp')
            ->from(Phpfox::getT('blog'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->join(Phpfox::getT('blog_text'), 'bt', 'bt.blog_id = b.blog_id')
            ->where($sCondition)
            ->limit(Phpfox::getParam('rss.total_rss_display'))
            ->order('b.blog_id DESC')
            ->executeRows();

        foreach ($aRows as $iKey => $aRow) {
            $aRows[$iKey]['description'] = $aRow['text'];
            $aRows[$iKey]['link'] = Phpfox::permaLink('blog', $aRow['blog_id'], $aRow['title']);
            $aRows[$iKey]['creator'] = $aRow['full_name'];
        }
        return $aRows;
    }

    public function checkLimitation($userId = null)
    {
        empty($userId) && $userId = Phpfox::getUserId();

        static $permissions = [];

        if (isset($permissions[$userId])) {
            return $permissions[$userId];
        }

        $limit = trim(Phpfox::getUserParam('blog.blog_total_items_can_create'));

        if (!isset($limit) || $limit == '') {
            $permissions[$userId] = true;
        } elseif (is_numeric($limit) && (int)$limit == 0) {
            $permissions[$userId] = false;
        } else {
            $total = (int)db()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where([
                    'user_id' => $userId
                ])->executeField(false);
            $permissions[$userId] = $total < $limit;
        }

        return $permissions[$userId];
    }

}
