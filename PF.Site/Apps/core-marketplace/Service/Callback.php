<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Marketplace\Service;


use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Template;
use Phpfox_Url;


defined('PHPFOX') or exit('NO DICE!');


class Callback extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('marketplace');
    }

    public function processInstallRss()
    {
        (new \Apps\Core_Marketplace\Installation\Version\v462())->importToRssFeed();
    }

    public function getSiteStatsForAdmin($iStartTime, $iEndTime)
    {
        $aCond = [];
        $aCond[] = 'view_id = 0';
        if ($iStartTime > 0) {
            $aCond[] = 'AND time_stamp >= \'' . $this->database()->escape($iStartTime) . '\'';
        }
        if ($iEndTime > 0) {
            $aCond[] = 'AND time_stamp <= \'' . $this->database()->escape($iEndTime) . '\'';
        }

        $iCnt = (int)$this->database()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($aCond)
            ->execute('getSlaveField');

        return [
            'phrase' => 'marketplace.marketplace',
            'total'  => $iCnt
        ];
    }

    public function enableSponsor($aParams)
    {
        return Phpfox::getService('marketplace.process')->sponsor($aParams['item_id'], 1);
    }

    public function getDashboardActivity()
    {
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return [];
        }
        $aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);
        if (!$aUser) {
            return [];
        }
        return [
            _p('marketplace_listings') => $aUser['activity_marketplace']
        ];
    }

    public function getAjaxCommentVar()
    {
        return 'marketplace.can_post_comment_on_listing';
    }

    public function getCommentItem($iId)
    {
        $aListing = $this->database()->select('listing_id AS comment_item_id, user_id AS comment_user_id')
            ->from($this->_sTable)
            ->where('listing_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aListing['comment_view_id'] = 1;

        return $aListing;
    }

    public function getActivityFeedComment($aRow)
    {
        if (Phpfox::isUser()) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = $this->database()->select('b.listing_id, b.title, b.module_id, b.item_id, b.time_stamp, b.total_comment, b.total_like, c.total_like, ct.text_parsed AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('marketplace'), 'b',
                'c.type_id = \'marketplace\' AND c.item_id = b.listing_id AND c.view_id = 0')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('c.comment_id = ' . (int)$aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['listing_id'])) {
            return false;
        }

        if ($aItem['module_id'] == 'groups' && !Phpfox::getService('groups')->isMember($aItem['item_id'])) {
            return false;
        }

        $sLink = Phpfox::permalink('marketplace', $aItem['listing_id'], $aItem['title']);
        $sTitle = Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('parse.output')->clean($aItem['title']),
            (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : 50));
        $sUser = '<a href="' . Phpfox_Url::instance()->makeUrl($aItem['user_name']) . '">' . $aItem['full_name'] . '</a>';
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id']) {
            $sMessage = _p('posted_a_comment_on_gender_listing_a_href_link_title_a',
                ['gender' => $sGender, 'link' => $sLink, 'title' => $sTitle]);
        } else {
            $sMessage = _p('posted_a_comment_on_user_name_s_listing_a_href_link_title_a',
                ['user_name' => $sUser, 'link' => $sLink, 'title' => $sTitle]);
        }

        return [
            'no_share'        => true,
            'feed_info'       => $sMessage,
            'feed_link'       => $sLink,
            'feed_status'     => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked'   => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon'       => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/marketplace.png',
                'return_url' => true
            ]),
            'time_stamp'      => $aRow['time_stamp'],
            'like_type_id'    => 'feed_mini'
        ];
    }

    /**
     * @param      $aVals
     * @param null $iUserId   remove in v4.7
     * @param null $sUserName remove in v4.7
     *
     * @return bool
     */
    public function addComment($aVals, $iUserId = null, $sUserName = null)
    {
        $aRow = $this->database()->select('m.listing_id, m.title, u.full_name, u.user_id, u.gender, u.user_name')
            ->from($this->_sTable, 'm')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
            ->where('m.listing_id = ' . (int)$aVals['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['listing_id'])) {
            return Phpfox_Error::trigger(_p('invalid_callback_on_marketplace_listing'));
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add($aVals['type'] . '_comment',
            $aVals['comment_id']) : null);

        // Update the post counter if its not a comment put under moderation or if the person posting the comment is the owner of the item.
        if (empty($aVals['parent_id'])) {
            $this->database()->updateCounter('marketplace', 'total_comment', 'listing_id', $aVals['item_id']);
        }

        // Send the user an email
        $sLink = Phpfox::permalink('marketplace', $aRow['listing_id'], $aRow['title']);

        Phpfox::getService('comment.process')->notify([
                'user_id'            => $aRow['user_id'],
                'item_id'            => $aRow['listing_id'],
                'owner_subject'      => [
                    'full_name_commented_on_your_listing_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ],
                'owner_message'      => [
                    'full_name_commented_on_your_listing_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aRow['title']]
                ],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id'          => 'comment_marketplace',
                'mass_id'            => 'marketplace',
                'mass_subject'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_listing', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1)
                    ]
                    ]
                    :
                    [
                        'full_name_commented_on_other_full_name_s_listing',
                        [
                            'full_name'       => Phpfox::getUserBy('full_name'),
                            'other_full_name' => $aRow['full_name']
                        ]
                    ]),
                'mass_message'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_listing_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                            'title'     => $aRow['title'],
                            'link'      => $sLink
                        ]
                    ]

                    :
                    [
                        'full_name_commented_on_other_full_name', [
                        'full_name'       => Phpfox::getUserBy('full_name'),
                        'other_full_name' => $aRow['full_name'],
                        'link'            => $sLink,
                        'title'           => $aRow['title']
                    ]
                    ]


                )
            ]
        );
    }

    public function updateCommentText($aVals, $sText)
    {
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('comment_marketplace', $aVals['item_id'],
            $sText, $aVals['comment_id']) : null);
    }

    public function getItemName($iId, $sName)
    {
        return _p('a_href_link_on_name_s_listing_a',
            ['link' => Phpfox_Url::instance()->makeUrl('comment.view', ['id' => $iId]), 'name' => $sName]);

    }

    public function getLink($aParams)
    {
        $aListing = $this->database()->select('m.listing_id, m.title')
            ->from(Phpfox::getT('marketplace'), 'm')
            ->where('m.listing_id = ' . (int)$aParams['item_id'])
            ->execute('getSlaveRow');

        if (empty($aListing)) {
            return false;
        }

        return Phpfox::permalink('marketplace', $aListing['listing_id'], $aListing['title']);
    }

    public function getCommentNewsFeed($aRow)
    {
        $oUrl = Phpfox_Url::instance();

        if ($aRow['owner_user_id'] == $aRow['item_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_their_own_a_href_title_link_listin',
                [
                    'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                    'full_name'  => $this->preParse()->clean($aRow['owner_full_name']),
                    'title_link' => $aRow['link']
                ]
            );
        } else {
            if ($aRow['item_user_id'] == Phpfox::getUserBy('user_id')) {
                $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_your_a_href_title_link_listing_a',
                    [
                        'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                        'full_name'  => $this->preParse()->clean($aRow['owner_full_name']),
                        'title_link' => $aRow['link']
                    ]
                );
            } else {
                $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_a_href_item_user_link_item_user_n',
                    [
                        'user_link'      => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                        'full_name'      => $this->preParse()->clean($aRow['owner_full_name']),
                        'title_link'     => $aRow['link'],
                        'item_user_name' => $this->preParse()->clean($aRow['viewer_full_name']),
                        'item_user_link' => $oUrl->makeUrl('feed.user', ['id' => $aRow['viewer_user_id']])
                    ]
                );
            }
        }

        $aRow['text'] .= Phpfox::getService('feed')->quote($aRow['content']);

        return $aRow;
    }

    public function getReportRedirect($iId)
    {
        return $this->getFeedRedirect($iId);
    }

    /**
     * @param      $iId
     * @param null $iChild remove in v4.7
     *
     * @return bool|string
     */
    public function getFeedRedirect($iId, $iChild = null)
    {
        $aListing = $this->database()->select('m.listing_id, m.title')
            ->from($this->_sTable, 'm')
            ->where('m.listing_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aListing['listing_id'])) {
            return false;
        }

        (($sPlugin = Phpfox_Plugin::get('marketplace.service_callback_getfeedredirect')) ? eval($sPlugin) : false);

        return Phpfox::permalink('marketplace', $aListing['listing_id'], $aListing['title']);
    }

    public function deleteComment($iId)
    {
        $this->database()->updateCounter('marketplace', 'total_comment', 'listing_id', $iId, true);
    }

    public function getProfileLink()
    {
        return 'profile.marketplace';
    }

    public function getNewsFeed($aRow)
    {
        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_callback_getnewsfeed_start')) {
            eval($sPlugin);
        }
        $oUrl = Phpfox_Url::instance();
        $oParseOutput = Phpfox::getLib('parse.output');

        $aRow['text'] = _p('a_href_user_link_owner_full_name_a_added_a_new_listing_a_href_title_link_title_a', [
                'owner_full_name' => $this->preParse()->clean($aRow['owner_full_name']),
                'title'           => $oParseOutput->shorten($oParseOutput->clean($aRow['content']), 30, '...'),
                'user_link'       => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                'title_link'      => $aRow['link']
            ]
        );

        $aRow['icon'] = 'module/marketplace.png';
        $aRow['enable_like'] = true;

        return $aRow;
    }

    public function getBlockDetailsProfile()
    {
        return [
            'title' => _p('marketplace')
        ];
    }

    /**
     * Action to take when user cancelled their account
     *
     * @param int $iUser
     */
    public function onDeleteUser($iUser)
    {
        $aListings = $this->database()
            ->select('listing_id, user_id, image_path, view_id')
            ->from($this->_sTable)
            ->where('user_id = ' . (int)$iUser)
            ->execute('getSlaveRows');

        foreach ($aListings as $aListing) {
            Phpfox::getService('marketplace.process')->delete($aListing['listing_id'], $aListing, true);
        }
        // delete invites
        $this->database()->delete(Phpfox::getT('marketplace_invite'), 'user_id = ' . (int)$iUser);

    }

    public function getNotificationFeedApproved($aRow)
    {
        return [
            'message' => _p('your_listing_title_has_been_approved',
                ['title' => Phpfox::getLib('parse.output')->shorten($aRow['item_title'], 20, '...')]),
            'link'    => Phpfox_Url::instance()->makeUrl('marketplace', ['redirect' => $aRow['item_id']]),
            'path'    => 'marketplace.url_image',
            'suffix'  => '_120_square'
        ];
    }

    public function getGlobalPrivacySettings()
    {
        return [
            'marketplace.display_on_profile' => [
                'phrase' => _p('listings')
            ]
        ];
    }

    public function pendingApproval()
    {
        return [
            'phrase' => _p('listings'),
            'value'  => Phpfox::getService('marketplace')->getPendingTotal(),
            'link'   => Phpfox_Url::instance()->makeUrl('marketplace', ['view' => 'pending'])
        ];
    }

    public function legacyRedirect($aRequest)
    {
        if (isset($aRequest['req2'])) {
            switch ($aRequest['req2']) {
                case 'viewall':
                    if (isset($aRequest['cat'])) {
                        $aItem = Phpfox::getService('core')->getLegacyUrl([
                                'url_field' => 'name_url',
                                'table'     => 'marketplace_category',
                                'field'     => 'upgrade_item_id',
                                'id'        => $aRequest['cat'],
                                'user_id'   => false
                            ]
                        );

                        if ($aItem !== false) {
                            return ['marketplace', $aItem['name_url']];
                        }
                    }
                    break;
                case 'view':
                    if (isset($aRequest['id'])) {
                        $aItem = Phpfox::getService('core')->getLegacyUrl([
                                'url_field' => 'title_url',
                                'table'     => 'marketplace',
                                'field'     => 'upgrade_item_id',
                                'id'        => $aRequest['id'],
                                'user_id'   => false
                            ]
                        );

                        if ($aItem !== false) {
                            return ['marketplace', ['view', $aItem['title_url']]];
                        }
                    }
                    break;
            }
        }

        return 'marketplace';
    }

    public function getUserCountFieldInvite()
    {
        return 'marketplace_invite';
    }

    public function getNotificationFeedInvite($aRow)
    {
        return [
            'message' => _p('user_link_invited_you_to_a_marketplace_listing', ['user' => $aRow]),
            'link'    => Phpfox_Url::instance()->makeUrl('marketplace', ['redirect' => $aRow['item_id']])
        ];
    }

    public function reparserList()
    {
        return [
            'name'       => _p('marketplace_text'),
            'table'      => 'marketplace_text',
            'original'   => 'description',
            'parsed'     => 'description_parsed',
            'item_field' => 'listing_id'
        ];
    }

    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        return [
            'phrase' => _p('listings'),
            'value'  => $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('marketplace'))
                ->where('view_id = 0 AND time_stamp >= ' . $iToday)
                ->execute('getSlaveField')
        ];
    }

    /**
     * @param int $iId video_id
     *
     * @return array in the format:
     * array(
     *    'title' => 'item title',            <-- required
     *    'link'  => 'makeUrl()'ed link',            <-- required
     *    'paypal_msg' => 'message for paypal'        <-- required
     *    'item_id' => int                <-- required
     *    'user_id;   => owner's user id            <-- required
     *    'error' => 'phrase if item doesnt exit'        <-- optional
     *    'extra' => 'description'            <-- optional
     *    'image' => 'path to an image',            <-- optional
     *    'image_dir' => 'photo.url_photo|...        <-- optional (required if image)
     *    'server_id' => db value                <-- optional (required if image)
     * )
     */
    public function getToSponsorInfo($iId)
    {
        $aListing = $this->database()->select('ml.user_id, ml.listing_id as item_id, ml.title, ml.image_path as image, ml.server_id')
            ->from($this->_sTable, 'ml')
            ->where('ml.listing_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aListing)) {
            return ['error' => _p('sponsor_error_not_found_listing')];
        }

        $aListing['title'] = _p('sponsor_title_listing', ['sListingTitle' => $aListing['title']]);
        $aListing['paypal_msg'] = _p('sponsor_paypal_message_listing', ['sListingTitle' => $aListing['title']]);
        $aListing['link'] = Phpfox::permalink('marketplace', $aListing['item_id'], $aListing['title']);
        if (isset($aListing['image']) && $aListing['image'] != '') {
            $aListing['image_dir'] = 'marketplace.url_image';
            $aListing['image'] = sprintf($aListing['image'], '_200_square');
        }
        $aListing = array_merge($aListing, [
            'redirect_completed'        => 'marketplace',
            'message_completed'         => _p('purchase_listing_sponsor_completed'),
            'redirect_pending_approval' => 'marketplace',
            'message_pending_approval'  => _p('purchase_listing_sponsor_pending_approval')
        ]);
        return $aListing;
    }

    /**
     * @param     $iId | remove in v4.7
     * @param int $iChildId
     *
     * @return bool|string
     */
    public function getFeedRedirectFeedLike($iId, $iChildId = 0)
    {
        return $this->getFeedRedirect($iChildId);
    }

    public function getNewsFeedFeedLike($aRow)
    {
        if ($aRow['owner_user_id'] == $aRow['viewer_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_likes_their_own_a_href_link_listing_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link' => Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                    'gender'    => Phpfox::getService('user')->gender($aRow['owner_gender'], 1),
                    'link'      => $aRow['link']
                ]
            );
        } else {
            $aRow['text'] = _p('a_href_user_link_full_name_a_likes_a_href_view_user_link_view_full_name_a_s_a_href_link_listing_a',
                [
                    'full_name'      => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link'      => Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                    'view_full_name' => Phpfox::getLib('parse.output')->clean($aRow['viewer_full_name']),
                    'view_user_link' => Phpfox_Url::instance()->makeUrl($aRow['viewer_user_name']),
                    'link'           => $aRow['link']
                ]
            );
        }

        $aRow['icon'] = 'misc/thumb_up.png';

        return $aRow;
    }

    public function getNotificationFeedNotifyLike($aRow)
    {
        return [
            'message' => _p('a_href_user_link_full_name_a_likes_your_a_href_link_listing_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['full_name']),
                    'user_link' => Phpfox_Url::instance()->makeUrl($aRow['user_name']),
                    'link'      => Phpfox_Url::instance()->makeUrl('marketplace', ['redirect' => $aRow['item_id']])
                ]
            ),
            'link'    => Phpfox_Url::instance()->makeUrl('marketplace', ['redirect' => $aRow['item_id']])
        ];
    }

    public function sendLikeEmail($iItemId)
    {
        return _p('a_href_user_link_full_name_a_likes_your_a_href_link_listing_a', [
                'full_name' => Phpfox::getLib('parse.output')->clean(Phpfox::getUserBy('full_name')),
                'user_link' => Phpfox_Url::instance()->makeUrl(Phpfox::getUserBy('user_name')),
                'link'      => Phpfox_Url::instance()->makeUrl('marketplace', ['redirect' => $iItemId])
            ]
        );
    }

    public function paymentApiCallback($aParams)
    {
        Phpfox::log('Module callback received: ' . var_export($aParams, true));
        Phpfox::log('Attempting to retrieve purchase from the database');

        $aInvoice = Phpfox::getService('marketplace')->getInvoice($aParams['item_number']);

        if ($aInvoice === false) {
            Phpfox::log('Not a valid invoice');

            return false;
        }

        $aListing = Phpfox::getService('marketplace')->getForEdit($aInvoice['listing_id'], true);

        if ($aListing === false) {
            Phpfox::log('Not a valid listing.');

            return false;
        }

        Phpfox::log('Purchase is valid: ' . var_export($aInvoice, true));

        if ($aParams['status'] == 'completed') {
            if ($aParams['total_paid'] == $aInvoice['price']) {
                Phpfox::log('Paid correct price');
            } else {
                Phpfox::log('Paid incorrect price');

                return false;
            }
        } else {
            Phpfox::log('Payment is not marked as "completed".');

            return false;
        }

        Phpfox::log('Handling purchase');

        $this->database()->update(Phpfox::getT('marketplace_invoice'), [
            'status'          => $aParams['status'],
            'time_stamp_paid' => PHPFOX_TIME
        ], 'invoice_id = ' . $aInvoice['invoice_id']
        );

        if ($aListing['auto_sell']) {
            $this->database()->update(Phpfox::getT('marketplace'), [
                'view_id' => '2'
            ], 'listing_id = ' . $aListing['listing_id']
            );
        }
        //Update point for seller if purchased by points
        if ($aParams['gateway'] == 'activitypoints') {
            $aSetting = Phpfox::getParam('activitypoint.activity_points_conversion_rate');
            $iConversion = ($aSetting[$aInvoice['currency_id']] == 0) ? 0 : $aInvoice['price'] / $aSetting[$aInvoice['currency_id']];
            $iTotalPoints = (int)$this->database()->select('activity_points')
                ->from(Phpfox::getT('user_activity'))
                ->where('user_id = ' . (int)$aListing['user_id'])
                ->execute('getSlaveField');
            $this->database()->update(Phpfox::getT('user_activity'), ['activity_points' => ($iConversion + $iTotalPoints)], 'user_id = ' . (int)$aListing['user_id']);
        }
        //Purchased by points, buyer is current user -> phpFox does not allow send email to current user.
        if (isset($aInvoice['user_id'])) {
            $sBuyerEmail = $aInvoice['user_id'];
        } else {
            $sBuyerEmail = Phpfox::getUserBy('email');
        }

        // Notify seller
        Phpfox::getService('notification.process')->add('marketplace_item_sold', $aListing['listing_id'],
            $aListing['user_id'], $aListing['user_id']);

        //Sending email to seller
        Phpfox::getLib('mail')->to($aListing['user_id'])
            ->sendToSelf(true)
            ->subject([
                'item_sold_title', ['title' => Phpfox::getLib('parse.input')->clean($aListing['title'], 255)]
            ])
            ->fromName($aInvoice['full_name'])
            ->message([
                    'marketplace.full_name_has_purchased_an_item_of_yours_on_site_name',
                    [
                        'full_name' => $aInvoice['full_name'],
                        'site_name' => Phpfox::getParam('core.site_title'),
                        'title'     => $aListing['title'],
                        'link'      => Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'],
                            $aListing['title']),
                        'user_link' => Phpfox_Url::instance()->makeUrl($aInvoice['user_name']),
                        'price'     => Phpfox::getService('core.currency')->getCurrency($aInvoice['price'],
                            $aInvoice['currency_id'])
                    ]
                ]
            )
            ->notification('marketplace.email_notification')
            ->send();
        //Sending email to buyer
        Phpfox::getLib('mail')->reset()
            ->to($sBuyerEmail)
            ->sendToSelf(true)
            ->subject([
                'you_have_purchased_item_title',
                ['title' => Phpfox::getLib('parse.input')->clean($aListing['title'], 255)]
            ])
            ->fromName(Phpfox::getParam('core.mail_from_name'))
            ->message([
                    'you_have_purchased_an_item_on_site_name', [
                        'site_name' => Phpfox::getParam('core.site_title'),
                        'title'     => $aListing['title'],
                        'link'      => Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'],
                            $aListing['title']),
                        'price'     => Phpfox::getService('core.currency')->getCurrency($aInvoice['price'],
                            $aInvoice['currency_id'])
                    ]
                ]
            )
            ->notification('marketplace.email_notification')
            ->send();

        Phpfox::log('Handling complete');
    }

    public function getRedirectComment($iId)
    {
        return $this->getFeedRedirect($iId);
    }

    public function addLike($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = $this->database()->select('listing_id, title, user_id')
            ->from(Phpfox::getT('marketplace'))
            ->where('listing_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['listing_id'])) {
            return false;
        }

        $this->database()->updateCount('like', 'type_id = \'marketplace\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'marketplace', 'listing_id = ' . (int)$iItemId);

        if (!$bDoNotSendEmail) {
            $sLink = Phpfox::permalink('marketplace', $aRow['listing_id'], $aRow['title']);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'marketplace.full_name_liked_your_listing_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ])
                ->message([
                    'marketplace.full_name_liked_your_listing_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aRow['title']]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('marketplace_like', $aRow['listing_id'], $aRow['user_id']);
        }
    }

    public function deleteLike($iItemId)
    {
        $this->database()->updateCount('like', 'type_id = \'marketplace\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'marketplace', 'listing_id = ' . (int)$iItemId);
    }

    public function getNotificationLike($aNotification)
    {
        $aRow = $this->database()->select('e.listing_id, e.title, e.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('marketplace'), 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.listing_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['listing_id'])) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('user_name_liked_gender_own_listing_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('user_names_liked_your_listing_title', [
                'user_names' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'      => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        } else {
            $sPhrase = _p('user_names_liked_span_class_drop_data_user_full_name_s_span_listing_title', [
                'user_names' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name'  => $aRow['full_name'],
                'title'      => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);

        }

        return [
            'link'    => Phpfox_Url::instance()->permalink('marketplace', $aRow['listing_id'], $aRow['title']),
            'message' => $sPhrase,
            'icon'    => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getCommentNotification($aNotification)
    {
        $aRow = $this->database()->select('b.listing_id, b.title, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('marketplace'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.listing_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (empty($aRow)) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users'])) {
            $sPhrase = _p('user_names_commented_on_gender_listing_title', [
                'user_names' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'     => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'      => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('user_names_commented_on_your_listing_title', [
                'user_names' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'      => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        } else {
            $sPhrase = _p('user_names_commented_on_span_class_drop_data_user_full_name_s_span_listing_title', [
                'user_names' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name'  => $aRow['full_name'],
                'title'      => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        }

        return [
            'link'    => Phpfox_Url::instance()->permalink('marketplace', $aRow['listing_id'], $aRow['title']),
            'message' => $sPhrase,
            'icon'    => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationInvite($aNotification)
    {
        $aRow = $this->database()->select('e.listing_id, e.title, e.user_id, u.full_name')
            ->from(Phpfox::getT('marketplace'), 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.listing_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['listing_id'])) {
            return false;
        }

        $sPhrase = _p('users_wants_you_to_check_out_the_listing_title', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link'    => Phpfox_Url::instance()->permalink('marketplace', $aRow['listing_id'], $aRow['title']),
            'message' => $sPhrase,
            'icon'    => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function canShareItemOnFeed()
    {
    }

    /**
     * @param      $aItem
     * @param null $aCallback remove in v4.7
     * @param bool $bIsChildItem
     *
     * @return array
     */
    public function getActivityFeed($aItem, $aCallback = null, $bIsChildItem = false)
    {
        if ($bIsChildItem) {
            $this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
                'u2.user_id = e.user_id');
        }

        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'marketplace\' AND l.item_id = e.listing_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aRow = $this->database()->select('e.*, mc.name AS category_name, et.description_parsed')
            ->from(Phpfox::getT('marketplace'), 'e')
            ->leftJoin(Phpfox::getT('marketplace_text'), 'et', 'et.listing_id = e.listing_id')
            ->leftJoin(Phpfox::getT('marketplace_category_data'), 'mcd', 'mcd.listing_id = e.listing_id')
            ->leftJoin(Phpfox::getT('marketplace_category'), 'mc', 'mc.category_id = mcd.category_id')
            ->where('e.listing_id = ' . (int)$aItem['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['listing_id'])) {
            return false;
        }

        if ($bIsChildItem) {
            $aItem = $aRow;
        }

        if (!empty($aRow['module_id']) && !Phpfox::isModule($aRow['module_id'])) {
            return false;
        }

        if ($aRow['module_id'] && Phpfox::hasCallback($aRow['module_id'], 'checkPermission') && !Phpfox::callback($aRow['module_id'] . '.checkPermission', $aRow['item_id'], 'marketplace.view_browse_marketplace_listings')) {
            return false;
        }

        $aRow['is_in_feed'] = true;
        $aRow['url'] = Phpfox::permalink('marketplace', $aRow['listing_id'], $aRow['title']);
        $aRow['categories'] = Phpfox::getService('marketplace.category')->getCategoriesById($aRow['listing_id']);
        Phpfox_Template::instance()->assign('aListing', $aRow);

        $aReturn = [
            'feed_title'      => '', // $aRow['title'],
            'feed_info'       => _p('created_a_listing'),
            'feed_link'       => $aRow['url'],
            'time_stamp'      => $aRow['time_stamp'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked'   => (isset($aRow['is_liked']) ? $aRow['is_liked'] : false),
            'enable_like'     => true,
            'like_type_id'    => 'marketplace',
            'total_comment'   => $aRow['total_comment'],
            'comment_type_id' => 'marketplace',
            'load_block'      => 'marketplace.feed'
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        if (!defined('PHPFOX_IS_PAGES_VIEW') && (($aRow['module_id'] == 'groups' && Phpfox::isAppActive('PHPfox_Groups')) || ($aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages')))) {
            $aPage = $this->database()->select('p.*, pu.vanity_url, ' . Phpfox::getUserField('u', 'parent_'))
                ->from(':pages', 'p')
                ->join(':user', 'u', 'p.page_id=u.profile_page_id')
                ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
                ->where('p.page_id=' . (int)$aRow['item_id'])
                ->execute('getSlaveRow');

            if (empty($aPage)) {
                return false;
            }
            $aReturn['parent_user_name'] = Phpfox::getService($aRow['module_id'])->getUrl($aPage['page_id'],
                $aPage['title'], $aPage['vanity_url']);
            $aReturn['feed_table_prefix'] = 'pages_';
            if ($aRow['user_id'] != $aPage['parent_user_id']) {
                $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aPage, 'parent_');
                unset($aReturn['feed_info']);
            }
        }

        (($sPlugin = Phpfox_Plugin::get('marketplace.component_service_callback_getactivityfeed__1')) ? eval($sPlugin) : false);

        return $aReturn;
    }

    public function getActivityPointField()
    {
        return [
            _p('marketplace') => 'activity_marketplace'
        ];
    }

    public function getProfileMenu($aUser)
    {
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return false;
        }
        $countResult = $this->getTotalItemCount($aUser['user_id']);
        if (!empty($countResult)) {
            $aUser['total_listing'] = $countResult['total'];
        }
        if (!Phpfox::getParam('profile.show_empty_tabs')) {
            if (!isset($aUser['total_listing'])) {
                return false;
            }

            if (isset($aUser['total_listing']) && (int)$aUser['total_listing'] === 0) {
                return false;
            }
        }

        $aMenus[] = [
            'phrase' => _p('listings'),
            'url'    => 'profile.marketplace',
            'total'  => (int)(isset($aUser['total_listing']) ? $aUser['total_listing'] : 0),
            'icon'   => 'module/marketplace.png'
        ];

        return $aMenus;
    }

    public function getTotalItemCount($iUserId)
    {
        $sCond = 'item_id = 0 AND view_id = 0 AND user_id = ' . (int)$iUserId;
        if (Phpfox::getParam('marketplace.days_to_expire_listing') > 0) {
            $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400));
            $sCond .= ' AND time_stamp >=' . $iExpireTime;
        }

        return [
            'field' => 'total_listing',
            'total' => $this->database()->select('COUNT(*)')->from(Phpfox::getT('marketplace'))->where($sCond)->execute('getSlaveField')
        ];
    }

    public function globalUnionSearch($sSearch)
    {
        $this->database()->select('item.listing_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'marketplace\' AS item_type_id, item.image_path AS item_photo, item.server_id AS item_photo_server')
            ->from(Phpfox::getT('marketplace'), 'item')
            ->where('item.view_id = 0 AND item.privacy = 0 AND ' . $this->database()->searchKeywords('item.title',
                    $sSearch))
            ->union();
    }

    public function getSearchInfo($aRow)
    {
        $aInfo = [];
        $aInfo['item_link'] = Phpfox_Url::instance()->permalink('marketplace', $aRow['item_id'], $aRow['item_title']);
        $aInfo['item_name'] = _p('marketplace_listing');

        if (!empty($aRow['item_photo'])) {
            $aInfo['item_display_photo'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aRow['item_photo_server'],
                    'file'       => $aRow['item_photo'],
                    'path'       => 'marketplace.url_image',
                    'suffix'     => '_400_square',
                    'max_width'  => '320',
                    'max_height' => '320'
                ]
            );
        } else {
            $aInfo['item_display_photo'] = '<img src="' . Phpfox::getParam('marketplace.marketplace_default_photo') . '"/>';
        }

        return $aInfo;
    }

    public function getSearchTitleInfo()
    {
        return [
            'name' => _p('listings')
        ];
    }

    public function getNotificationApproved($aNotification)
    {
        $aRow = $this->database()->select('v.listing_id, v.title, v.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('marketplace'), 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.listing_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['listing_id'])) {
            return false;
        }

        $sPhrase = _p('your_marketplace_listing_title_has_been_approved', [
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link'             => Phpfox_Url::instance()->permalink('marketplace', $aRow['listing_id'], $aRow['title']),
            'message'          => $sPhrase,
            'icon'             => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog'),
            'no_profile_image' => true
        ];
    }

    public function getCommentNotificationTag($aNotification)
    {
        $aRow = $this->database()->select('m.listing_id, m.title, u.user_name, u.full_name')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('marketplace'), 'm', 'm.listing_id = c.item_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (empty($aRow)) {
            return false;
        }

        $sPhrase = _p('user_name_tagged_you_in_a_marketplace_listing', ['user_name' => $aRow['full_name']]);

        return [
            'link'    => Phpfox_Url::instance()->permalink('marketplace', $aRow['listing_id'],
                    $aRow['title']) . 'comment_' . $aNotification['item_id'],
            'message' => $sPhrase,
            'icon'    => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getEnabledInputField()
    {
        return [
            // One module may have Inputs in several locations
            [
                'product_id'    => 'phpfox',
                // still not used, candidate for removal
                'module_id'     => 'marketplace',
                // internal identifier
                'module_phrase' => 'Marketplace listing',
                // display name for the AdminCP when adding a new Input
                'action'        => 'add-listing',
                // This is a unique identifier within this module
                'add_url'       => 'marketplace.add',
                'item_column'   => 'listing_id',
                // this is the column in the marketplace table, we use this field in the search library,
                'table'         => Phpfox::getT('marketplace')
            ]
        ];
    }

    public function ignoreDeleteLikesAndTagsWithFeed()
    {
        return true;
    }

    public function getNotificationItem_Sold($aNotification)
    {
        $aRow = $this->database()->select('v.listing_id, v.title, v.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('marketplace'), 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.listing_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['listing_id'])) {
            return false;
        }

        $sPhrase = _p('your_item_has_been_sold', ['item' => $aRow['title']]);

        return [
            'link'    => Phpfox_Url::instance()->permalink('marketplace', $aRow['listing_id'], $aRow['title']),
            'message' => $sPhrase,
            'icon'    => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * @param int      $iId
     * @param null|int $iUserId
     */
    public function addTrack($iId, $iUserId = null)
    {
        (($sPlugin = Phpfox_Plugin::get('marketplace.component_service_callback_addtrack__start')) ? eval($sPlugin) : false);

        if ($iUserId == null) {
            $iUserId = Phpfox::getUserBy('user_id');
        }

        db()->insert(Phpfox::getT('track'), [
            'type_id'    => 'marketplace',
            'item_id'    => (int)$iId,
            'ip_address' => Phpfox::getIp(),
            'user_id'    => $iUserId,
            'time_stamp' => PHPFOX_TIME
        ]);
    }

    /**
     * @return array
     */
    public function getAttachmentField()
    {
        return [
            'marketplace',
            'listing_id'
        ];
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_callback__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }

    /**
     * This callback will be called when admin delete a sponsor in admincp
     *
     * @param $aParams
     */
    public function deleteSponsorItem($aParams)
    {
        db()->update($this->_sTable, ['is_sponsor' => 0], ['listing_id' => $aParams['item_id']]);
        $this->cache()->remove('marketplace_sponsored');
    }

    /**
     * @param $iUserId
     *
     * @return array|bool
     */
    public function getUserStatsForAdmin($iUserId)
    {
        if (!$iUserId) {
            return false;
        }

        $iTotal = db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('user_id =' . (int)$iUserId)
            ->execute('getField');
        return [
            'total_name'  => _p('marketplace'),
            'total_value' => $iTotal,
            'type'        => 'item'
        ];
    }

    public function getUploadParams($aParams = null)
    {
        return Phpfox::getService('marketplace')->getUploadParams($aParams);
    }

    /** Start View Listing on Map */

    /**
     * Get listings for map view
     *
     * @param $aParams
     */
    public function getMapViewItemsListing($aParams)
    {
        $bIsUserProfile = isset($aParams['aUser']);
        $aUser = $bIsUserProfile ? $aParams['aUser'] : null;
        $sView = isset($aParams['view']) ? $aParams['view'] : '';
        $oServiceBrowse = Phpfox::getService('marketplace.browse');
        switch ($sView) {
            case 'sold':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND l.user_id = ' . Phpfox::getUserId());
                $this->search()->setCondition('AND (l.is_sell = 1 OR l.allow_point_payment = 1)');
                break;
            case 'featured':
                $this->search()->setCondition('AND l.is_featured = 1');
                break;
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND l.user_id = ' . Phpfox::getUserId());
                break;
            case 'pending':
                if (Phpfox::getUserParam('marketplace.can_approve_listings')) {
                    $this->search()->setCondition('AND l.view_id = 1');
                } else {
                    if ($bIsUserProfile === true) {
                        $this->search()->setCondition("AND l.view_id IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND l.user_id = " . $aUser['user_id'] . "");
                    } else {
                        $this->search()->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%)');
                    }
                }
                break;
            case 'expired':
                if (Phpfox::getParam('marketplace.days_to_expire_listing') > 0 && Phpfox::getUserParam('marketplace.can_view_expired')) {
                    $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400));
                    $this->search()->setCondition('AND l.time_stamp < ' . $iExpireTime);
                    break;
                } else {
                    $this->search()->setCondition('AND l.time_stamp < 0');
                }
                break;
            default:
                if ($bIsUserProfile === true) {
                    $this->search()->setCondition("AND l.view_id = 0 AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND l.user_id = " . $aUser['user_id'] . "");
                } else {
                    if ($sView == 'invites') {
                        Phpfox::isUser(true);
                        $oServiceBrowse->seen();
                        break;
                    }
                }
                $this->search()->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%)');
                break;
        }

        if (!in_array($sView, ['my', 'sold', 'pending', 'invites', 'featured'])) {
            if ((Phpfox::getParam('marketplace.display_marketplace_created_in_page') || Phpfox::getParam('marketplace.display_marketplace_created_in_group'))) {
                $aModules = [];
                if (Phpfox::getParam('marketplace.display_marketplace_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                    $aModules[] = 'groups';
                }
                if (Phpfox::getParam('marketplace.display_marketplace_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                    $aModules[] = 'pages';
                }
                if (count($aModules)) {
                    $this->search()->setCondition('AND (l.module_id IN ("' . implode('","', $aModules) . '") OR l.module_id = \'marketplace\')');
                } else {
                    $this->search()->setCondition('AND l.module_id = \'marketplace\'');
                }
            } else {
                $this->search()->setCondition('AND l.item_id = 0');
            }
        }

        if ($this->search()->isSearch()) {
            $oServiceBrowse->search();
        }
    }

    /**
     * Get params for search listings
     *
     * @param $aParams
     *
     * @return array
     */
    public function getMapViewParamsListing($aParams)
    {
        $bIsUserProfile = isset($aParams['aUser']);
        $aUser = $bIsUserProfile ? $aParams['aUser'] : null;
        $aSearchFields = [
            'type'           => 'marketplace',
            'field'          => 'l.listing_id',
            'ignore_blocked' => true,
            'search_tool'    => [
                'location_field' => [
                    'latitude_field'  => 'location_lat',
                    'longitude_field' => 'location_lng'
                ],
                'table_alias'    => 'l',
                'search'         => [
                    'action'        => ($bIsUserProfile === true ? Phpfox_Url::instance()->makeUrl($aUser['user_name'], [
                        'marketplace',
                        'view' => $this->request()->get('view')
                    ]) : Phpfox_Url::instance()->makeUrl('marketplace', ['view' => $this->request()->get('view')])),
                    'default_value' => _p('search_listings'),
                    'name'          => 'search',
                    'field'         => ['l.title', 'mt.description_parsed']
                ],
                'sort'           => [
                    'latest'      => ['l.time_stamp', _p('latest')],
                    'most-liked'  => ['l.is_sponsor DESC, l.total_like', _p('most_liked')],
                    'most-talked' => ['l.is_sponsor DESC, l.total_comment', _p('most_discussed')]
                ],
                'show'           => [20],
            ],

        ];
        $aBrowseParams = [
            'module_id' => 'marketplace',
            'alias'     => 'l',
            'field'     => 'listing_id',
            'table'     => Phpfox::getT('marketplace'),
            'hide_view' => ['pending', 'my']
        ];

        return [
            'search_params'    => $aSearchFields,
            'pagination_style' => Phpfox::getParam('marketplace.marketplace_paging_mode_map_view'),
            'browse_params'    => $aBrowseParams,
            'card_view'        => [
                'title'           => _p('listings'),
                'no_item_message' => _p('no_marketplace_listings_found'),
            ],
            'map_marker'       => [
                'icon'       => Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-marketplace/assets/image/map_ico.png',
                'hover_icon' => Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-marketplace/assets/image/map_ico_hover.png'
            ]
        ];
    }

    /**
     * convert item info to show on map
     *
     * @param $aListing
     *
     * @return array
     */
    public function convertItemOnMapListing($aListing)
    {
        if (empty($aListing['listing_id'])) {
            return $aListing;
        }
        $template = Phpfox::getLib('template');

        $actions = '';
        if (Phpfox::isUser()) {
            // get actions
            $template->assign(['aListing' => $aListing]);
            $template->getTemplate('marketplace.block.menu');
            $actions = ob_get_contents();
            ob_clean();
        }

        // get statistics
        $statistics = [];
        if ($aListing['total_view'] > 0) {
            $statistics[] = [
                'label' => '',
                'value' => Phpfox::getService('core.helper')->shortNumber($aListing['total_view']) . ' ' . ($aListing['total_view'] == 1 ? _p('view_lowercase') : _p('views_lowercase'))
            ];
        }
        if ($aListing['total_like'] > 0) {
            $statistics[] = [
                'label' => '',
                'value' => Phpfox::getService('core.helper')->shortNumber($aListing['total_like']) . ' ' . ($aListing['total_like'] == 1 ? _p('like_lowercase') : _p('likes_lowercase'))
            ];
        }
        $price = ($aListing['price'] == '0.00') ? _p('free') : Phpfox::getService('core.currency')->getCurrency($aListing['price'], $aListing['currency_id']);
        $item_price = ($aListing['price'] == '0.00') ? '<span class="free">' . $price . '</span>' : $price;
        $categories = '';
        if (is_array($aListing['categories']) && count($aListing['categories'])) {
            $categories = '<div class="item-info-categories"><span class="item-label">' . _p('categories') . ': </span>' . Phpfox::getService('core.category')->displayView($aListing['categories']) . '</div>';
        }

        return [
            'item_link'              => Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'], $aListing['title']),
            'item_title'             => $aListing['title'],
            'item_info_window_title' => $aListing['title'] . ' (' . $price . ')',
            'item_price'             => $item_price,
            'item_is_featured'       => $aListing['is_featured'],
            'item_is_sponsor'        => $aListing['is_sponsor'],
            'item_actions'           => trim($actions),
            'item_image'             => $aListing['image_path'] ? Phpfox::getLib('image.helper')->display([
                'server_id'  => $aListing['server_id'],
                'title'      => $aListing['title'],
                'path'       => 'marketplace.url_image',
                'file'       => $aListing['image_path'],
                'suffix'     => '_400_square',
                'return_url' => true
            ]) : Phpfox::getParam('marketplace.marketplace_default_photo'),
            'item_author'            => [
                'user_id'   => $aListing['user_id'],
                'full_name' => $aListing['full_name'],
                'user_name' => $aListing['user_name']
            ],
            'item_statistics'        => $statistics,
            'item_first_minor_info'  => Phpfox::getLib('parse.output')->clean($aListing['location']),
            'item_second_minor_info' => $categories
        ];
    }

    /** End View Listing on Map */

    /**
     * @return array
     */
    public function getPagePerms()
    {
        return [
            'marketplace.share_marketplace_listings'       => _p('who_can_share_marketplace_listings'),
            'marketplace.view_browse_marketplace_listings' => _p('who_can_view_marketplace_listings')
        ];
    }

    /**
     * @param array $aPage
     *
     * @return array|null
     */
    public function getPageMenu($aPage)
    {
        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'],
                'marketplace.view_browse_marketplace_listings') || !Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return null;
        }

        $aMenus[] = [
            'phrase'  => _p('listings'),
            'url'     => Phpfox::getService('pages')
                    ->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']) . 'marketplace/',
            'icon'    => 'module/core-marketplace.png',
            'landing' => 'marketplace'
        ];

        return $aMenus;
    }

    /**
     * @param array $aPage
     *
     * @return array|null
     */
    public function getPageSubMenu($aPage)
    {
        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'marketplace.share_marketplace_listings')
            || !Phpfox::getUserParam('marketplace.can_create_listing')
            || !Phpfox::getService('marketplace')->checkLimitation()
            || !Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return null;
        }

        return [
            [
                'phrase' => _p('menu_add_new_listing'),
                'url'    => Phpfox::getLib('url')->makeUrl('marketplace.add', [
                    'module' => 'pages',
                    'item'   => $aPage['page_id']
                ])
            ]
        ];
    }

    /**
     * This callback will be called when a page or group be deleted
     *
     * @param $iId
     * @param $sType
     *
     * @throws \Exception
     */
    public function onDeletePage($iId, $sType)
    {
        $aListings = db()->select('listing_id')->from($this->_sTable)->where([
            'module_id' => $sType,
            'item_id'   => $iId
        ])->executeRows();
        foreach ($aListings as $aListing) {
            Phpfox::getService('marketplace.process')->delete($aListing['listing_id']);
        }
    }

    /**
     * @param int $iPage
     *
     * @return bool
     */
    public function canViewPageSection($iPage)
    {
        if (!Phpfox::getService('pages')->hasPerm($iPage,
                'marketplace.view_browse_marketplace_listings') || !Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getGroupPerms()
    {
        return ['marketplace.share_marketplace_listings' => _p('who_can_share_marketplace_listings')];
    }

    /**
     * @param array $aPage
     *
     * @return array|null
     */
    public function getGroupMenu($aPage)
    {
        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'],
                'marketplace.view_browse_marketplace_listings') || !Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return null;
        }

        $aMenus[] = [
            'phrase'  => _p('listings'),
            'url'     => Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']) . 'marketplace/',
            'icon'    => 'module/core-marketplace.png',
            'landing' => 'marketplace'
        ];

        return $aMenus;
    }

    /**
     * @param array $aPage
     *
     * @return array|null
     */
    public function getGroupSubMenu($aPage)
    {
        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'], 'marketplace.share_marketplace_listings')
            || !Phpfox::getUserParam('marketplace.can_create_listing')
            || !Phpfox::getService('marketplace')->checkLimitation()) {
            return null;
        }

        return [
            [
                'phrase' => _p('menu_add_new_listing'),
                'url'    => Phpfox::getLib('url')->makeUrl('marketplace.add', [
                    'module' => 'groups',
                    'item'   => $aPage['page_id']
                ])
            ]
        ];
    }

    /**
     * @param array $aNotification
     *
     * @return array|bool
     */
    public function getNotificationNewItem_Groups($aNotification)
    {
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            return false;
        }
        $aListing = Phpfox::getService('marketplace')->getListing($aNotification['item_id']);
        if (empty($aListing) || empty($aListing['item_id']) || ($aListing['module_id'] != 'groups')) {
            return false;
        }

        $aRow = Phpfox::getService('groups')->getPage($aListing['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('users_add_a_new_marketplace_listing_in_the_group_title', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link'    => Phpfox::getLib('url')->permalink('marketplace', $aListing['listing_id'], $aListing['title']),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'marketplace')
        ];
    }

    public function getNotificationSettings()
    {
        return [
            'marketplace.email_notification' => [
                'phrase' => _p('marketplace_notifications'),
                'default' => 1
            ]
        ];
    }
}
