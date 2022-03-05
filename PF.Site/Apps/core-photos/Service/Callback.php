<?php

namespace Apps\Core_Photos\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

class Callback extends Phpfox_Service
{
    private $_iFallbackLength;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('photo');
        // if the notification module is disabled we cannot get the length to shorten, so we fallback to _iFallbackLength.
        $this->_iFallbackLength = 50;
    }

    /**
     * Process send notify to tagged users
     *
     * @param $params
     *
     * @return bool
     */
    public function sendNotifyToTaggedUsers($params)
    {
        $sFeedType = $params['feed_type'];
        $aTagged = $params['tagged_friend'];
        $iItemId = $params['item_id'];
        $iOwnerId = $params['owner_id'];
        $iFeedId = $params['feed_id'];
        $iPrivacy = $params['privacy'];
        $iParentUserId = (int)$params['parent_user_id'];
        $moduleId = isset($params['module_id']) ? $params['module_id'] : '';

        // check photo exist
        $link = Phpfox::getService('photo')->getFeedLink($iItemId);
        if (empty($link)) {
            return false;
        }

        $aCurrentUser = Phpfox::getService('user')->getUser($iOwnerId);
        $sTagger = (isset($aCurrentUser['full_name']) && $aCurrentUser['full_name']) ? $aCurrentUser['full_name'] : $aCurrentUser['user_name'];

        //Send Mail and add feed
        foreach ($aTagged as $iUserId) {
            if (in_array($moduleId, ['', 'user'])) {
                if ($iParentUserId == $iUserId) {
                    continue;
                }
                (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add($sFeedType, $iItemId, $iPrivacy, 0, $iUserId, $iOwnerId, 1, $iFeedId) : null);
            }
            if (empty($params['no_notification']) && Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('photo_feed_tag', $iItemId, $iUserId, $iOwnerId, true);
            }
            if (empty($params['no_email'])) {
                Phpfox::getLib('mail')->to($iUserId)
                    ->notification('feed.tagged_in_post')
                    ->subject(['user_name_tagged_you_in_a_photo_post', ['user_name' => $sTagger]])
                    ->message([
                        'user_name_tagged_you_in_a_photo_post_check_it_out',
                        ['user_name' => $sTagger, 'link' => $link]
                    ])
                    ->send();
            }
        }
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationFeed_Tag($aNotification)
    {
        // check link of photo exist
        $link = Phpfox::getService('photo')->getFeedLink($aNotification['item_id']);
        if (empty($link)) {
            return false;
        }
        $sPhrase = _p('user_name_tagged_you_in_a_photo_post', ['user_name' => Phpfox::getService('notification')->getUsers($aNotification)]);

        return [
            'link'    => $link,
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'photo')
        ];
    }

    /**
     * Process update photo info
     *
     * @param $data
     */
    public function updateFeedItemInfo($data)
    {
        $content = Phpfox::getLib('parse.input')->prepare($data['content'], false);
        $location = isset($data['location']) ? $data['location'] : null;
        $iItemId = (int)$data['item_id'];
        $iPrivacy = $data['privacy'];

        // Update photo info
        db()->update(Phpfox::getT('photo_info'), [
            'description' => $content,
            'location_name' => isset($location['location_name']) ? $location['location_name'] : null,
            'location_latlng' => isset($location['location_latlng']) ? $location['location_latlng'] : null,
        ], 'photo_id = ' . $iItemId);
        db()->update(Phpfox::getT('photo'), ['privacy' => $iPrivacy], 'photo_id = ' . $iItemId);
    }

    /**
     * @param int $iId video_id
     *
     * @return array in the format:
     * array(
     *    'title' => 'item title',            <-- required
     *  'link'  => 'makeUrl()'ed link',            <-- required
     *  'paypal_msg' => 'message for paypal'        <-- required
     *  'item_id' => int                <-- required
     *  'user_id;   => owner's user id            <-- required
     * )
     */
    public function getToSponsorInfoAlbum($iId)
    {
        $aAlbum = $this->database()->select('name, album_id as item_id, user_id')
            ->from(Phpfox::getT('photo_album'))
            ->where('album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aAlbum)) {
            return ['error' => _p('sponsor_error_album_not_found')];
        }

        $albumName = \Phpfox_Locale::instance()->convert($aAlbum['name']);

        $aAlbum['title'] = _p('photo_album_sponsor_title', ['sAlbumTitle' => $albumName]);
        $aAlbum['paypal_msg'] = _p('album_sponsor_paypal_message', ['sAlbumTitle' => $albumName]);
        $aAlbum['link'] = \Phpfox_Url::instance()->makeUrl('photo.album.' . $aAlbum['item_id'] . '.' . $albumName);
        $aAlbum = array_merge($aAlbum, [
            'redirect_completed' => 'photo',
            'message_completed'  => _p('purchase_album_sponsor_completed'),
        ]);
        return $aAlbum;
    }

    /**
     * @param $iStartTime
     * @param $iEndTime
     *
     * @return array
     */
    public function getSiteStatsForAdmin($iStartTime, $iEndTime)
    {
        $aCond = [];
        $aCond[] = 'view_id = 0';
        if ($iStartTime > 0) {
            $aCond[] = 'AND time_stamp >= \'' . db()->escape($iStartTime) . '\'';
        }
        if ($iEndTime > 0) {
            $aCond[] = 'AND time_stamp <= \'' . db()->escape($iEndTime) . '\'';
        }

        $iCnt = (int)db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($aCond)
            ->execute('getSlaveField');
        $iCntAlbum = (int)db()->select('COUNT(*)')
            ->from(':photo_album')
            ->where($aCond)
            ->execute('getSlaveField');

        return [
            'merge_result' => true,
            'result'       => [
                'photo'       => [
                    'phrase' => 'photo.photos',
                    'total'  => $iCnt
                ],
                'photo_album' => [
                    'phrase' => 'photo.photo_albums',
                    'total'  => $iCntAlbum
                ]
            ]
        ];
    }

    /**
     * @param $aParams
     *
     * @return bool
     */
    public function enableSponsorAlbum($aParams)
    {
        return Phpfox::getService('photo.album.process')->sponsor($aParams['item_id'], 1);
    }


    /**
     * @param $aParams
     *
     * @return bool
     */
    public function enableSponsor($aParams)
    {
        if (!empty($aParams['section']) && $aParams['section'] == 'album') {
            return Phpfox::getService('photo.album.process')->sponsor($aParams['item_id'], 1);
        } else {
            return Phpfox::getService('photo.process')->sponsor($aParams['item_id'], 1);
        }
    }

    /**
     * @param $aParams
     *
     * @return bool|string
     */
    public function getLink($aParams)
    {
        if (isset($aParams['section']) && $aParams['section'] == 'album') {
            $aAlbum = Phpfox::getService('photo.album')->getForEdit($aParams['item_id'], true);
            if (empty($aAlbum)) {
                return false;
            }

            if ($aAlbum['profile_id'] > 0) {
                $aAlbum['name'] = _p('user_profile_pictures', ['full_name' => $aAlbum['full_name']]);
                $sLink = Phpfox::permalink('photo.album.profile', $aAlbum['user_id'], $aAlbum['user_name']);
            } else if ($aAlbum['cover_id'] > 0) {
                $aAlbum['name'] = _p('user_cover_photo', ['full_name' => $aAlbum['full_name']]);
                $sLink = Phpfox::permalink('photo.album.cover', $aAlbum['user_id'], $aAlbum['user_name']);
            } else if ($aAlbum['timeline_id'] > 0) {
                $aAlbum['name'] = _p('user_timeline_photos', ['full_name' => $aAlbum['full_name']]);
                $sLink = Phpfox::permalink('photo.album', $aAlbum['album_id'], $aAlbum['name']);
            } else {
                $sLink = Phpfox::permalink('photo.album', $aAlbum['album_id'], $aAlbum['name']);
            }
        } else {
            $aPhoto = db()->select('p.title, p.photo_id')
                ->from(Phpfox::getT('photo'), 'p')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->where('p.photo_id = ' . (int)$aParams['item_id'])
                ->execute('getSlaveRow');
            if (empty($aPhoto)) {
                return false;
            }
            $sLink = Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
        }

        return $sLink;
    }

    public function getProfileLink()
    {
        return 'profile.photo';
    }

    public function getAjaxCommentVar()
    {
        return 'photo.can_post_on_photos';
    }

    public function getTagLink()
    {
        return Phpfox::getLib('url')->makeUrl('photo.tag');
    }

    public function getTagType()
    {
        return 'photo';
    }

    /**
     * @param $iId
     *
     * @return array|int|string
     */
    public function getCommentItem($iId)
    {
        $aRow = db()->select('photo_id AS comment_item_id, privacy, privacy_comment, user_id AS comment_user_id, module_id AS parent_module_id')
            ->from(Phpfox::getT('photo'))
            ->where('photo_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = '0';

        if (!Phpfox::getService('comment')->canPostComment($aRow['comment_user_id'], $aRow['privacy_comment'])) {
            Phpfox_Error::set(_p('unable_to_post_a_comment_on_this_item_due_to_privacy_settings'));

            unset($aRow['comment_item_id']);
        }

        return $aRow;
    }

    public function deleteComment($iId)
    {
        Phpfox::getUserParam('photo.can_post_on_photos', 1);
        Phpfox::getService('photo.process')->updateCounter($iId, 'total_comment', true);
    }

    public function deleteCommentAlbum($iId)
    {
        Phpfox::getUserParam('photo.can_post_on_albums', 1);
        db()->update(Phpfox::getT('photo_album'), ['total_comment' => ['= total_comment -', 1]], 'album_id = ' . (int)$iId);
    }

    public function getFeedRedirectAlbum($iId)
    {
        $aAlbum = db()->select('a.album_id, a.name, u.user_name')
            ->from(Phpfox::getT('photo_album'), 'a')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = a.user_id')
            ->where('a.album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id'])) {
            return false;
        }

        $albumName = \Phpfox_Locale::instance()->convert($aAlbum['name']);

        return Phpfox::getLib('url')->permalink('photo.album', $aAlbum['album_id'], $albumName);
    }

    /**
     * @param     $iId remove in v4.6
     * @param int $iChild
     *
     * @return bool|string
     */
    public function getFeedRedirectAlbum_FeedLike($iId, $iChild = 0)
    {
        return $this->getFeedRedirectAlbum($iChild);
    }

    /**
     * @param     $iId remove in v4.6
     * @param int $iChild
     *
     * @return bool|string
     */
    public function getFeedRedirectFeedLike($iId, $iChild = 0)
    {
        return $this->getFeedRedirect($iChild);
    }

    /**
     * @param     $iId
     * @param int $iChild remove in v4.6
     *
     * @return bool|string
     */
    public function getFeedRedirect($iId, $iChild = 0)
    {
        $aPhoto = db()->select('p.photo_id, p.title')
            ->from($this->_sTable, 'p')
            ->where('p.photo_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aPhoto['photo_id'])) {
            return false;
        }

        return Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
    }

    public function getNewsFeedAlbum($aRow)
    {
        if ($sPlugin = Phpfox_Plugin::get('photo.service_callback_getnewsfeedalbum_start')) {
            eval($sPlugin);
        }
        $aPart = unserialize($aRow['content']);
        $aRow['album_name'] = $aPart['name'];
        $aRow['photo_images'] = $aPart['images'];


        $aRow['text'] = _p('feed_user_add_photos', [
                'owner_full_name' => $aRow['owner_full_name'],
                'owner_link'      => Phpfox::getLib('url')->makeUrl('feed.user', ['id' => $aRow['owner_user_id']]),
                'total'           => count($aRow['photo_images']),
                'album_name'      => Phpfox::getService('feed')->shortenTitle($aRow['album_name']),
                'photo_images'    => $aRow['photo_images'],
                'link'            => $aRow['link'],
                'gender'          => Phpfox::getService('user')->gender($aRow['owner_gender'], 1)
            ]
        );

        $sImages = '';
        foreach ($aRow['photo_images'] as $aImage) {
            $sImage = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aImage['server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aImage['destination'],
                    'suffix'     => '_100',
                    'max_width'  => 100,
                    'max_height' => 100,
                    'style'      => 'vertical-align:top; padding-right:5px;'
                ]
            );

            if (preg_match('/misc\/noimage/', $sImage)) {
                continue;
            }

            if (isset($aImage['link'])) {
                $aRow['link'] = Phpfox::getLib('url')->makeUrl($aImage['link'][0], [$aImage['link'][1], $aImage['link'][2], $aImage['link'][3]]);
            }
            if (isset($aImage['mature'])) {
                $sImages .= '<a href="' . $aRow['link'] . '"' . ((($aImage['mature'] == 0 || (($aImage['mature'] == 1 || $aImage['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aRow['user_id'] == Phpfox::getUserId()) ? '' : ' class="photo-mature"') . ($aImage['mature'] == 1 ? 'onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&amp;width=350&amp;link=' . $aRow['link'] . '\')); return false;"' : '') . '>' . $sImage . '</a>';
            } else {
                $sImages .= '<a href="' . $aRow['link'] . '">' . $sImage . '</a>';
            }
        }
        $aRow['text'] .= '<div class="p_4">' . $sImages . '</div>';

        $aRow['icon'] = 'module/photo.png';
        $aRow['enable_like'] = true;

        return $aRow;
    }

    public function getNewsFeed($aRow)
    {
        if ($sPlugin = Phpfox_Plugin::get('photo.service_callback_getnewsfeed_start')) {
            eval($sPlugin);
        }
        $aImages = unserialize($aRow['content']);

        if (count($aImages) == 1) {
            $aRow['text'] = _p('a_href_profile_link_owner_full_name_a_uploaded_a_new_photo', [
                    'profile_link'    => Phpfox::getLib('url')->makeUrl('feed.user',
                        ['id' => $aRow['owner_user_id']]),
                    'owner_full_name' => $this->preParse()->clean($aRow['owner_full_name'])
                ]
            );
        } else {
            $aRow['text'] = _p('a_href_profile_link_owner_full_name_a_uploaded_new_photos', [
                    'profile_link'    => Phpfox::getLib('url')->makeUrl('feed.user',
                        ['id' => $aRow['owner_user_id']]),
                    'owner_full_name' => $this->preParse()->clean($aRow['owner_full_name'])
                ]
            );
        }

        $sImages = '';
        foreach ($aImages as $aImage) {
            $sImage = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aImage['server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aImage['destination'],
                    'suffix'     => '_100',
                    'max_width'  => 100,
                    'max_height' => 100,
                    'style'      => 'vertical-align:top; padding-right:5px;'
                ]
            );

            if (preg_match('/misc\/noimage/', $sImage)) {
                continue;
            }

            if (isset($aImage['link'])) {
                $aRow['link'] = Phpfox::getLib('url')->makeUrl(implode('.', $aImage['link']));
            }

            if (isset($aImage['mature'])) {
                $sImages .= '<a href="' . $aRow['link'] . '"' . ((($aImage['mature'] == 0 || (($aImage['mature'] == 1 || $aImage['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aRow['user_id'] == Phpfox::getUserId()) ? '' : ' class="photo-mature"') . ($aImage['mature'] == 1 ? 'onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&amp;width=350&amp;link=' . $aRow['link'] . '\')); return false;"' : '') . '>' . $sImage . '</a>';
            } else {
                $sImages .= '<a href="' . $aRow['link'] . '">' . $sImage . '</a>';
            }
        }
        $aRow['text'] .= '<div class="p_4">' . $sImages . '</div>';

        $aRow['icon'] = 'module/photo.png';
        $aRow['enable_like'] = true;

        return $aRow;
    }

    /**
     * @param int $iId photo_id
     *
     * @return array in the format:
     * array(
     *    'title' => 'item title',            <-- required
     *  'link'  => 'makeUrl()'ed link',            <-- required
     *  'paypal_msg' => 'message for paypal'        <-- required
     *  'item_id' => int                <-- required
     *    'error' => 'phrase if item doesnt exit'        <-- optional
     *    'extra' => 'description'            <-- optional
     *    'image' => 'path to an image',            <-- optional
     *  'image_dir' => 'photo.url_photo|...        <-- optional (required if image)
     *    'server_id' => database value            <-- optional (required if image)
     * )
     */
    public function getToSponsorInfo($iId)
    {
        $aImage = db()->select('p.user_id, p.title, p.photo_id as item_id, p.server_id, p.destination as image, u.user_name')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.photo_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aImage)) {
            return [
                'error' => _p('sorry_the_photo_you_are_looking_for_no_longer_exists',
                    ['link' => Phpfox::getLib('url')->makeUrl('profile.photo')])
            ];
        }

        $bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1);
        $aImage['link'] = Phpfox::permalink('photo', $aImage['item_id'], $bCanShowTitle ? $aImage['title'] : null);
        $aImage['paypal_msg'] = _p('sponsor_paypal_message_photo', ['sPhotoTitle' => $bCanShowTitle ? $aImage['title'] : '']);
        $aImage['image_dir'] = 'photo.url_photo';
        $aImage['title'] = _p($bCanShowTitle ? 'sponsor_title_photo' : 'photo_photo_number', $bCanShowTitle ? ['sPhotoTitle' => $aImage['title']] : ['number' => $iId]);

        $aImage = array_merge($aImage, [
            'redirect_completed'        => 'photo',
            'message_completed'         => _p('purchase_photo_sponsor_completed'),
            'redirect_pending_approval' => 'photo',
            'message_pending_approval'  => _p('purchase_photo_sponsor_pending_approval')
        ]);
        return $aImage;
    }

    public function getCommentNewsFeed($aRow)
    {
        $oUrl = Phpfox::getLib('url');

        $aPart = unserialize($aRow['content']);
        $aRow['content'] = $aPart['content'];
        $aRow['image_path'] = $aPart['destination'];

        if ($aRow['owner_user_id'] == $aRow['viewer_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_their_own_a_href_title_link_photo',
                [
                    'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['owner_user_id']]),
                    'full_name'  => $this->preParse()->clean($aRow['owner_full_name']),
                    'title_link' => $aRow['link']
                ]
            );
        } else {
            if ($aRow['item_user_id'] == Phpfox::getUserBy('user_id')) {
                $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_your_a_href_title_link_photo_a',
                    [
                        'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['owner_user_id']]),
                        'full_name'  => $this->preParse()->clean($aRow['owner_full_name']),
                        'title_link' => $aRow['link']
                    ]
                );
            } else {
                $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on', [
                        'user_link'      => $oUrl->makeUrl('feed.user', ['id' => $aRow['owner_user_id']]),
                        'full_name'      => $this->preParse()->clean($aRow['owner_full_name']),
                        'title_link'     => $aRow['link'],
                        'item_user_name' => $this->preParse()->clean($aRow['viewer_full_name']),
                        'item_user_link' => $oUrl->makeUrl('feed.user', ['id' => $aRow['viewer_user_id']])
                    ]
                );
            }
        }


        $sImage = '';
        if (!empty($aRow['image_path'])) {
            $sImage = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aPart['server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aRow['image_path'],
                    'suffix'     => '_50',
                    'max_width'  => 75,
                    'max_height' => 75,
                    'style'      => 'vertical-align:top; padding-right:5px;'
                ]
            );
            if (isset($aPart['mature'])) {
                $sImage = '<a href="' . $aRow['link'] . '"' . ((($aPart['mature'] == 0 || (($aPart['mature'] == 1 || $aPart['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aRow['user_id'] == Phpfox::getUserId()) ? '' : ' class="photo-mature"') . ($aPart['mature'] == 1 ? 'onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&amp;width=350&amp;link=' . $aRow['link'] . '\')); return false;"' : '') . '>' . $sImage . '</a>';
            } else {
                $sImage = '<a href="' . $aRow['link'] . '">' . $sImage . '</a>';
            }
        }

        $aRow['text'] .= '<div class="p_4"><div class="go_left">' . $sImage . '</div><div style="margin-left:75px;">' . Phpfox::getService('feed')->quote($aRow['content']) . '</div><div class="clear"></div></div>';

        return $aRow;
    }

    /**
     * @param $aRow
     *
     * @return array|bool
     */
    public function getActivityFeedComment($aRow)
    {
        if (Phpfox::isUser() && Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = db()->select('b.photo_id, b.album_id, b.module_id, b.group_id, b.server_id, b.destination, b.title, b.time_stamp, b.privacy, b.total_comment, b.total_like, c.total_like, ct.text_parsed AS text, f.friend_id AS is_friend, b.mature, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('photo'), 'b', 'c.type_id = \'photo\' AND c.item_id = b.photo_id AND c.view_id = 0')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = b.user_id AND f.friend_user_id = " . Phpfox::getUserId())
            ->where('c.comment_id = ' . (int)$aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['photo_id'])) {
            return false;
        }

        if ($aItem['module_id'] == 'groups' && !Phpfox::getService('groups')->isMember($aItem['group_id'])) {
            return false;
        }

        $sLink = Phpfox::permalink('photo', $aItem['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aItem['title'] : null);
        $sUser = '<a href="' . Phpfox::getLib('url')->makeUrl($aItem['user_name']) . '">' . $aItem['full_name'] . '</a>';
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id']) {
            $sMessage = _p('posted_a_comment_on_gender_photo', ['gender' => $sGender]);
        } else {
            $sMessage = _p('posted_a_comment_on_user_name_s_photo', ['user_name' => $sUser]);
        }

        $aFeed = [
            'no_share'        => true,
            'feed_info'       => $sMessage,
            'feed_link'       => $sLink,
            'feed_status'     => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked'   => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon'       => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/photo.png',
                'return_url' => true
            ]),
            'time_stamp'      => isset($aRow['time_stamp']) ? $aRow['time_stamp'] : time(),
            'like_type_id'    => 'feed_mini',
            'custom_rel'      => $aItem['photo_id'],
            'custom_css'      => 'photo_holder_image'
        ];

        $bCanViewItem = true;
        if ($aItem['privacy'] > 0) {
            $bCanViewItem = Phpfox::getService('privacy')->check('photo', $aItem['photo_id'], $aItem['user_id'],
                $aItem['privacy'], $aItem['is_friend'], true);
        }

        if ($bCanViewItem) {
            $aFeed['feed_image'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aItem['server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => Phpfox::getService('photo')->getPhotoUrl($aItem),
                    'suffix'     => '_100',
                    'max_width'  => 100,
                    'max_height' => 100,
                    'class'      => 'photo_holder'
                ]
            );

            if (isset($aItem['mature'])) {
                if (($aItem['mature'] == 0 || // its public
                        (
                            ($aItem['mature'] == 1 || $aItem['mature'] == 2) && // its restricted
                            Phpfox::getUserId() && // is user
                            (Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age')))) || // user is older
                    $aRow['user_id'] == Phpfox::getUserId() // owner and viewer are the same
                ) {

                } else if ($aItem['mature'] == 1) {
                    $aFeed['custom_css'] = 'no_ajax_link photo-mature';
                    $aFeed['custom_js'] = 'onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&amp;width=350&amp;link=' . $aFeed['feed_link'] . '\')); return false;"';
                }
            }
        }

        return $aFeed;
    }

    /**
     * @param      $aVals
     * @param null $iUserId
     * @param null $sUserName
     *
     * @return bool
     */
    public function addComment($aVals, $iUserId = null, $sUserName = null)
    {
        Phpfox::getUserParam('photo.can_post_on_photos', 1);
        $aRow = db()->select('u.full_name, u.gender, u.user_id, u.user_name, u.profile_page_id, p.photo_id, p.title, p.group_id, p.type_id, p.parent_user_id, pi.description, p.module_id')
            ->from($this->_sTable, 'p')
            ->join(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
            ->join(':user', 'u', 'u.user_id = p.user_id')
            ->where('p.photo_id = ' . (int)$aVals['item_id'])
            ->execute('getSlaveRow');
        if (empty($aRow)) {
            return false;
        }

        if ($aRow['profile_page_id'] && in_array($aRow['module_id'], ['pages', 'groups'])) {
            $aPage = Phpfox::getService($aRow['module_id'])->getPage($aRow['profile_page_id']);
            $aRow['user_id'] = $aPage['user_id'];
        }

        $aChecked = [];
        $aTagged = db()->select('pt.tag_user_id')
            ->from(':photo_tag', 'pt')
            ->join(':user', 'u', 'u.user_id = pt.tag_user_id')
            ->where('pt.photo_id = ' . (int)$aRow['photo_id'] . ' AND pt.tag_user_id <> ' . (int)$aRow['user_id'])
            ->execute('getSlaveRows');
        if (!empty($aTagged)) {
            $aChecked = array_map(function ($item) {
                return $item['tag_user_id'];
            }, $aTagged);
        }

        if (empty($aRow['group_id'])) {
            $iPrivacy = 0;
            $iPrivacyComment = 0;
            if (Phpfox::getParam('feed.add_feed_for_comments')) {
                $iPrivacy = $aVals['privacy_comment'];
            }
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add($aVals['type'] . '_comment',
                $aVals['comment_id'], $iPrivacy, $iPrivacyComment) : null);
        }

        if (empty($aVals['parent_id'])) {
            db()->updateCounter('photo', 'total_comment', 'photo_id', $aRow['photo_id']);
        }

        // Send the user an email
        $sLink = Phpfox::getLib('url')->permalink('photo', $aRow['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aRow['title'] : null);

        if (!isset($aVals['user_id'])) {
            $aVals['user_id'] = Phpfox::getUserId();
        }
        $aMentions = Phpfox::getService('user.process')->getIdFromMentions($aRow['description'], true);
        $aTaggedByWith = Phpfox::getService('feed.tag')->getTaggedUserIds($aRow['photo_id'], 'photo');

        Phpfox::getService('feed.tag')->filterTaggedPrivacy($aMentions, $aTaggedByWith, $aRow['photo_id'], 'photo');
        $aMentions = array_merge($aMentions, $aTaggedByWith);

        $iParentId = ($aRow['parent_user_id'] != 0 && $aRow['group_id'] == 0) ? $aRow['parent_user_id'] : 0;
        foreach ($aMentions as $iKey => $iUserId) {
            if (in_array($iUserId, $aChecked) || empty($iUserId) || $iParentId == $iUserId) {
                continue;
            }
            $aChecked[] = $iUserId;
        }
        $bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1);
        $aSubject = (Phpfox::getUserId() == $aRow['user_id'] ?
            [
                'full_name_commented_on_gender_photo', [
                'full_name' => Phpfox::getUserBy('full_name'),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1)
            ]
            ]
            :
            [
                'full_name_commented_on_other_full_name_s_photo', [
                'full_name'       => Phpfox::getUserBy('full_name'),
                'other_full_name' => $aRow['full_name']
            ]
            ]);
        $aMessage = (Phpfox::getUserId() == $aRow['user_id'] ?
            [
                $bCanShowTitle ? 'full_name_commented_on_gender_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a' : 'photo_full_name_commented_on_gender_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                    'link'      => $sLink,
                    'title'     => $aRow['title']
                ]
            ]
            :
            [
                $bCanShowTitle ? 'full_name_commented_on_other_full_name_s_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a' : 'photo_full_name_commented_on_other_full_name_s_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                [
                    'full_name'       => Phpfox::getUserBy('full_name'),
                    'other_full_name' => $aRow['full_name'],
                    'link'            => $sLink,
                    'title'           => $aRow['title']
                ]
            ]);
        $aExecutedUsers = [];
        foreach ($aChecked as $iUser) {
            Phpfox::getLib('mail')->to($iUser)
                ->subject($aSubject)
                ->message($aMessage)
                ->notification('comment.add_new_comment')
                ->send();
            if (Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('comment_photo', $aRow['photo_id'],
                    $iUser, $aVals['user_id']);
            }
            $aExecutedUsers[] = $iUser;
        }

        Phpfox::getService('comment.process')->notify([
                'user_id'            => $aRow['user_id'],
                'sender_id'          => $aVals['user_id'],
                'item_id'            => $aRow['photo_id'],
                'owner_subject'      => [
                    $bCanShowTitle ? 'full_name_commented_on_your_photo_title' : 'photo_full_name_commented_on_your_photo', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $this->preParse()->clean($aRow['title'], 100)
                    ]
                ],
                'owner_message'      =>
                    [
                        $bCanShowTitle ? 'full_name_commented_on_your_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a' : 'photo_full_name_commented_on_your_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $aRow['title'],
                            'link'      => $sLink
                        ]
                    ],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id'          => 'comment_photo',
                'mass_id'            => 'photo',
                'mass_subject'       => $aSubject,
                'mass_message'       => $aMessage,
                'exclude_users'      => $aExecutedUsers,
            ]
        );

        if ($aRow['type_id'] == 1 && $aRow['parent_user_id'] != 0 && $aRow['group_id'] == 0) {
            // Send the user an email
            Phpfox::getService('comment.process')->notify([
                    'user_id'            => $aRow['parent_user_id'],
                    'item_id'            => $aRow['photo_id'],
                    'owner_subject'      => [
                        $bCanShowTitle ? 'full_name_commented_on_your_photo_title' : 'photo_full_name_commented_on_your_photo',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                    ],
                    'owner_message'      => [
                        $bCanShowTitle ? 'full_name_commented_on_your_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a' : 'photo_full_name_commented_on_your_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'link'      => $sLink,
                            'title'     => $aRow['title']
                        ]
                    ],
                    'owner_notification' => 'comment.add_new_comment',
                    'notify_id'          => 'comment_photo',
                    'mass_id'            => 'photo',
                    'mass_subject'       => (Phpfox::getUserId() == $aRow['parent_user_id'] ? [
                        'full_name_commented_on_gender_photo',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1)
                        ]
                    ] : [
                        'full_name_commented_on_other_full_name_s_photo',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'other_full_name' => $aRow['full_name']]
                    ]),
                    'mass_message'       => (Phpfox::getUserId() == $aRow['parent_user_id'] ? [
                        $bCanShowTitle ? 'full_name_commented_on_gender_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a' : 'photo_full_name_commented_on_gender_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                            'link'      => $sLink,
                            'title'     => $aRow['title']
                        ]
                    ] : [
                        $bCanShowTitle ? 'full_name_commented_on_other_full_name_s_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a' : 'photo_full_name_commented_on_other_full_name_s_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name'       => Phpfox::getUserBy('full_name'),
                            'other_full_name' => $aRow['full_name'],
                            'link'            => $sLink,
                            'title'           => $aRow['title']
                        ]
                    ]),
                    'exclude_users'      => $aExecutedUsers,
                ]
            );
        }
    }

    /**
     * @param      $iItemId
     * @param bool $bDoNotSendEmail
     *
     * @return bool|null
     */
    public function addLike($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = db()->select('p.photo_id, p.title, p.user_id, p.group_id, p.type_id, p.parent_user_id, p.module_id, u.profile_page_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('photo_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['photo_id'])) {
            return false;
        }

        if ($aRow['profile_page_id'] && in_array($aRow['module_id'], ['pages', 'groups'])) {
            $aPage = Phpfox::getService($aRow['module_id'])->getPage($aRow['profile_page_id']);
            $aRow['user_id'] = $aPage['user_id'];
        }

        db()->updateCount('like', 'type_id = \'photo\' AND item_id = ' . (int)$iItemId . '', 'total_like', 'photo',
            'photo_id = ' . (int)$iItemId);

        if (!$bDoNotSendEmail) {
            $bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1);
            $sLink = Phpfox::permalink('photo', $aRow['photo_id'], $bCanShowTitle ? $aRow['title'] : null);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    $bCanShowTitle ? 'custom.full_name_liked_your_photo_title' : 'photo_full_name_liked_your_photo',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ])
                ->message([
                    $bCanShowTitle ? 'custom.full_name_liked_your_photo_message' : 'photo_full_name_liked_your_photo_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aRow['title']]
                ])
                ->notification('like.new_like')
                ->send();

            if (Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('photo_like', $aRow['photo_id'], $aRow['user_id']);
            }

            if ($aRow['type_id'] == 1 && $aRow['parent_user_id'] != 0 && $aRow['group_id'] == 0) {
                Phpfox::getLib('mail')->to($aRow['parent_user_id'])
                    ->subject([
                        $bCanShowTitle ? 'custom.full_name_liked_your_photo_title' : 'photo_full_name_liked_your_photo',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                    ])
                    ->message([
                        $bCanShowTitle ? 'custom.full_name_liked_your_photo_message' : 'photo_full_name_liked_your_photo_message',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'link'      => $sLink,
                            'title'     => $aRow['title']
                        ]
                    ])
                    ->notification('like.new_like')
                    ->send();

                if (Phpfox::isModule('notification')) {
                    Phpfox::getService('notification.process')->add('photo_like', $aRow['photo_id'], $aRow['parent_user_id']);
                }
            }
        }
        return null;
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationLike($aNotification)
    {
        $aRow = db()->select('b.photo_id, b.title, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('photo'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.photo_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aNotification['user_id']) || !isset($aRow['user_id'])) {
            return false;
        }

        if ($bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1)) {
            $aRow['title'] = Phpfox::getLib('locale')->convert(Phpfox::getLib('parse.output')->split($aRow['title'], 20));
        }

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p($bCanShowTitle ? 'user_name_liked_gender_own_photo_title' : 'photo_user_name_liked_gender_own_photo', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : '',
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p($bCanShowTitle ? 'user_name_liked_your_photo_title' : 'photo_user_name_liked_your_photo', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : '',
            ]);
        } else {
            $sPhrase = _p($bCanShowTitle ? 'user_name_liked_span_class_drop_data_user_full_name_s_span_photo_title' : 'photo_user_name_liked_span_class_drop_data_user_full_name_s_span_photo', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : '',
            ]);
        }

        return [
            'link'    => Phpfox::getLib('url')->permalink('photo', $aRow['photo_id'], $bCanShowTitle ? $aRow['title'] : null),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * @param $iItemId
     */
    public function deleteLike($iItemId)
    {
        db()->updateCount('like', 'type_id = \'photo\' AND item_id = ' . (int)$iItemId . '', 'total_like', 'photo',
            'photo_id = ' . (int)$iItemId);
    }

    public function updateCommentText($aVals, $sText)
    {

    }

    public function getCommentNotification($aNotification)
    {
        $aRow = db()->select('p.photo_id, p.title, u.user_id, u.gender, u.user_name, u.full_name')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.photo_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');
        if (!$aRow) {
            return false;
        }

        if ($bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1)) {
            $aRow['title'] = Phpfox::getLib('locale')->convert($aRow['title']);
        }

        if (!isset($aNotification['user_id']) || !isset($aRow['user_id'])) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users'])) {
            $sPhrase = _p($bCanShowTitle ? 'user_name_commented_on_gender_photo_title' : 'photo_user_name_commented_on_gender_photo', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : '',
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p($bCanShowTitle ? 'user_name_commented_on_your_photo_title' : 'photo_user_name_commented_on_your_photo', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : ''
            ]);
        } else {
            $sPhrase = _p($bCanShowTitle ? 'user_name_commented_on_span_class_drop_data_user_full_name_s_span_photo_title' : 'photo_user_name_commented_on_span_class_drop_data_user_full_name_s_span_photo', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : ''
            ]);
        }

        return [
            'link'    => Phpfox::getLib('url')->permalink('photo', $aRow['photo_id'], $bCanShowTitle ? $aRow['title'] : null),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getCommentNotificationTag($aNotification)
    {
        $aRow = db()
            ->select('p.photo_id, p.title, u.user_name, u.full_name')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('photo'), 'p', 'p.photo_id = c.item_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');
        if (empty($aRow)) {
            return false;
        }

        $sPhrase = _p('user_name_tagged_you_in_a_comment_in_a_photo', ['user_name' => $aRow['full_name']]);

        return [
            'link'    => Phpfox::getLib('url')
                    ->permalink('photo', $aRow['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aRow['title'] : null) . 'comment_' . $aNotification['item_id'],
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getCommentNotificationAlbumTag($aNotification)
    {
        $aRow = db()
            ->select('pa.album_id, pa.name, u.user_name, u.full_name')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = c.item_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!$aRow) {
            return false;
        }
        $sPhrase = _p('user_name_tagged_you_in_a_comment_in_a_photo_album', ['user_name' => $aRow['full_name']]);

        $aRow['name'] = Phpfox::getLib('locale')->convert($aRow['name']);

        return [
            'link'    => Phpfox::getLib('url')->permalink('photo.album', $aRow['album_id'], $aRow['name']) . 'comment_' . $aNotification['item_id'],
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getItemName($iId, $sName)
    {
        return '<a href="' . Phpfox::getLib('url')->makeUrl('comment.view',
                ['id' => $iId]) . '">' . _p('on_name_s_photo', ['name' => $sName]) . '</a>';
    }

    public function getRedirectComment($iId)
    {
        return $this->getFeedRedirect($iId);
    }

    public function getReportRedirect($iId)
    {
        return $this->getFeedRedirect($iId);
    }

    public function getRedirectCommentAlbum($iId)
    {
        return $this->getFeedRedirectAlbum($iId);
    }

    public function getReportRedirectAlbum($iId)
    {
        return $this->getFeedRedirectAlbum($iId);
    }

    public function getCommentItemName()
    {
        return 'photo';
    }

    public function getNotificationFeed_Profile($aNotification)
    {
        $aRow = db()->select('p.photo_id, u.user_id, u.gender, u.user_name, u.full_name, f.feed_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(Phpfox::getT('feed'), 'f', 'f.item_id = p.photo_id AND f.type_id =\'photo\'')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ' . Phpfox::getUserId())
            ->where('p.photo_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['user_id'])) {
            return false;
        }

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('users_commented_on_gender_wall',
                ['users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_commented_on_your_wall', ['users' => $sUsers]);
        } else {
            $sPhrase = _p('users_commented_on_one_span_class_drop_data_user_row_full_name_span_wall',
                ['users' => $sUsers, 'row_full_name' => $aRow['full_name']]);
        }

        return [
            'link'    => Phpfox::getLib('url')->makeUrl($aRow['user_name'], ['feed' => $aRow['feed_id']]),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }


    public function processCommentModeration($sAction, $iId)
    {
        // Is this comment approved?
        if ($sAction == 'approve') {
            // Update the blog comment count
            Phpfox::getService('photo.process')->updateCounter($iId, 'total_comment');

            // Get the blogs details so we can add it to our news feed
            $aPhoto = db()->select('u.full_name, u.user_id, u.user_name, p.title_url, p.destination, ct.text_parsed, p.album_id, c.comment_id, c.user_id AS comment_user_id')
                ->from($this->_sTable, 'p')
                ->join(Phpfox::getT('comment'), 'c', 'c.type_id = \'photo\' AND c.item_id = p.photo_id')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
                ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
                ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
                ->where('p.photo_id = ' . (int)$iId)
                ->execute('getSlaveRow');

            // Add to news feed
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add('comment_photo', $iId,
                $aPhoto['text_parsed'], Phpfox::getUserBy('user_name'), $aPhoto['user_id'], $aPhoto['full_name'],
                $aPhoto['destination'], $aPhoto['comment_id']) : null);

            // Send the user an email
            $sLink = ($aPhoto['album_id'] ? Phpfox::getLib('url')->makeUrl(Phpfox::getUserBy('user_name'),
                ['photo', $aPhoto['title_url']]) : Phpfox::getLib('url')->makeUrl(Phpfox::getUserBy('user_name'),
                ['photo', 'view', $aPhoto['title_url']]));

            Phpfox::getLib('mail')->to($aPhoto['comment_user_id'])
                ->subject([
                    'photo.full_name_approved_your_comment_on_site_title',
                    [
                        'full_name'  => Phpfox::getUserBy('full_name'),
                        'site_title' => Phpfox::getParam('core.site_title')
                    ]
                ])
                ->message([
                        'photo.full_name_approved_your_comment_on_site_title_message',
                        [
                            'full_name'  => Phpfox::getUserBy('full_name'),
                            'site_title' => Phpfox::getParam('core.site_title'),
                            'link'       => $sLink
                        ]
                    ]
                )
                ->notification('comment.approve_new_comment')
                ->send();
        }
    }

    /**
     * @return array
     */
    public function getWhatsNew()
    {
        return [
            'photo.photos' => [
                'ajax'  => '#photo.getNew?id=js_new_item_holder',
                'id'    => 'photo',
                'block' => 'photo.new'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getTagCloud()
    {
        return [
            'link'     => 'photo',
            'category' => 'photo'
        ];
    }

    public function globalSearch($sQuery, $bIsTagSearch = false)
    {
        $sCondition = 'p.view_id = 0 AND p.group_id = 0 AND p.privacy = 0';
        if ($bIsTagSearch === false) {
            $sCondition .= ' AND (p.title LIKE \'%' . db()->escape($sQuery) . '%\' OR pi.description LIKE \'%' . db()->escape($sQuery) . '%\')';
        }

        if ($bIsTagSearch == true) {
            db()->innerJoin(Phpfox::getT('tag'), 'tag',
                'tag.item_id = p.photo_id AND tag.category_id = \'photo\' AND tag.tag_url = \'' . db()->escape($sQuery) . '\'');
        }

        $iCnt = db()->select('COUNT(*)')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->where($sCondition)
            ->execute('getSlaveField');

        if ($bIsTagSearch == true) {
            db()->innerJoin(Phpfox::getT('tag'), 'tag',
                'tag.item_id = p.photo_id AND tag.category_id = \'photo\' AND tag.tag_url = \'' . db()->escape($sQuery) . '\'')->group('p.photo_id');
        }

        $aRows = db()->select('p.photo_id, p.title, p.title_url, p.time_stamp, p.destination, p.server_id, p.album_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where($sCondition)
            ->limit(10)
            ->order('p.photo_id DESC')
            ->execute('getSlaveRows');

        if (count($aRows)) {
            $aResults = [];
            $aResults['total'] = $iCnt;
            $aResults['menu'] = _p('photos');

            if ($bIsTagSearch == true) {
                $aResults['form'] = '<div><input type="button" value="' . _p('view_more_photos') . '" class="search_button" onclick="window.location.href = \'' . Phpfox::getLib('url')->makeUrl('photo',
                        ['tag', $sQuery]) . '\';" /></div>';
            } else {
                $aResults['form'] = '<form method="post" action="' . Phpfox::getLib('url')->makeUrl('photo') . '"><div><input type="hidden" name="' . Phpfox::getTokenName() . '[security_token]" value="' . Phpfox::getService('log.session')->getToken() . '" /></div><div><input name="search[search]" value="' . Phpfox::getLib('parse.output')->clean($sQuery) . '" size="20" type="hidden" /></div><div><input type="submit" name="submit" value="' . _p('view_more_photos') . '" class="search_button" /></div></form>';
            }

            $bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1);

            foreach ($aRows as $iKey => $aRow) {
                $aResults['results'][$iKey] = [
                    'title'      => $bCanShowTitle ? $aRow['title'] : _p('photo_photo_number', ['number' => $aRow['photo_id']]),
                    'link'       => Phpfox::getLib('url')->makeUrl($aRow['user_name'],
                        ['photo', 'view', $aRow['title_url']]),
                    'image'      => Phpfox::getLib('image.helper')->display([
                            'server_id'  => $aRow['server_id'],
                            'title'      => $bCanShowTitle ? $aRow['title'] : '',
                            'path'       => 'photo.url_photo',
                            'file'       => $aRow['destination'],
                            'suffix'     => '_50',
                            'max_width'  => 75,
                            'max_height' => 75
                        ]
                    ),
                    'extra_info' => _p('a_href_photo_section_link_photo_a_uploaded_on_time_stamp_by_a_href_user_link',
                        [
                            'photo_section_link' => Phpfox::getLib('url')->makeUrl('photo'),
                            'time_stamp'         => Phpfox::getTime(Phpfox::getParam('core.global_update_time'),
                                $aRow['time_stamp']),
                            'user_link'          => Phpfox::getLib('url')->makeUrl($aRow['user_name']),
                            'full_name'          => $this->preParse()->clean($aRow['full_name'])
                        ]
                    )
                ];
            }

            return $aResults;
        }
        return null;
    }

    public function verifyFavorite($iItemId)
    {
        $aItem = db()->select('i.photo_id')
            ->from(Phpfox::getT('photo'), 'i')
            ->where('i.photo_id = ' . (int)$iItemId . ' AND i.view_id = 0 AND i.privacy IN(0,1)')
            ->execute('getSlaveRow');

        if (!isset($aItem['photo_id'])) {
            return false;
        }

        return true;
    }

    public function verifyFavoriteAlbum($iItemId)
    {
        $aItem = db()->select('i.album_id')
            ->from(Phpfox::getT('photo_album'), 'i')
            ->where('i.album_id = ' . (int)$iItemId . ' AND i.view_id = 0 AND i.privacy IN(0,1)')
            ->execute('getSlaveRow');

        if (!isset($aItem['album_id'])) {
            return false;
        }

        return true;
    }

    public function getFavoriteAlbum($aFavorites)
    {
        $aItems = db()->select('i.name AS title, i.album_id, i.time_stamp, p.title_url, p.destination, p.server_id AS photo_server_id, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('photo_album'), 'i')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = i.user_id')
            ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = i.album_id AND i.view_id = 0 AND p.is_cover = 1')
            ->where('i.album_id IN(' . implode(',', $aFavorites) . ') AND i.view_id = 0 AND i.privacy IN(0,1)')
            ->execute('getSlaveRows');

        foreach ($aItems as $iKey => $aItem) {
            $aItems[$iKey]['image'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aItem['photo_server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aItem['destination'],
                    'suffix'     => '_50',
                    'max_width'  => 75,
                    'max_height' => 75
                ]
            );

            $aItems[$iKey]['link'] = Phpfox::getLib('url')->makeUrl($aItem['user_name'],
                ['photo', $aItem['album_url']]);
        }

        return [
            'title' => _p('photo_albums'),
            'items' => $aItems
        ];
    }

    public function getFavorite($aFavorites)
    {
        $aItems = db()->select('i.title, i.album_id, i.time_stamp, i.title_url, i.destination, i.server_id AS photo_server_id, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('photo'), 'i')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = i.user_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'p', 'p.album_id = i.album_id')
            ->where('i.photo_id IN(' . implode(',', $aFavorites) . ') AND i.view_id = 0 AND i.privacy IN(0,1)')
            ->execute('getSlaveRows');

        foreach ($aItems as $iKey => $aItem) {
            $aItems[$iKey]['image'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aItem['photo_server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aItem['destination'],
                    'suffix'     => '_50',
                    'max_width'  => 75,
                    'max_height' => 75
                ]
            );

            $aItems[$iKey]['link'] = ($aItem['album_id'] ? Phpfox::getLib('url')->makeUrl($aItem['user_name'],
                ['photo', $aItem['title_url']]) : Phpfox::getLib('url')->makeUrl($aItem['user_name'],
                ['photo', 'view', $aItem['title_url']]));
        }

        return [
            'title' => _p('photos'),
            'items' => $aItems
        ];
    }

    public function getDashboardLinks()
    {
        return [
            'submit' => [
                'phrase' => _p('upload_a_photo'),
                'link'   => 'photo.add',
                'image'  => 'misc/photo_feature.png'
            ],
            'edit'   => [
                'phrase' => _p('manage_photos'),
                'link'   => 'profile.photo',
                'image'  => 'misc/photos.png'
            ]
        ];
    }

    public function getDashboardActivity()
    {
        if (!Phpfox::getUserParam('photo.can_view_photos')) {
            return [];
        }
        $aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);
        return [
            _p('photos') => $aUser['activity_photo']
        ];
    }

    /**
     * Action to take when user cancelled their account
     *
     * @param int $iUser
     */
    public function onDeleteUser($iUser)
    {
        // get the ids for the photos of this user
        $aPhotos = db()
            ->select('p.photo_id, pa.album_id, pt.tag_id')
            ->from($this->_sTable, 'p')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.user_id = ' . (int)$iUser)
            ->leftJoin(Phpfox::getT('photo_tag'), 'pt', 'pt.user_id = ' . (int)$iUser)
            ->where('(p.user_id = ' . (int)$iUser . ' OR (p.parent_user_id = ' . (int)$iUser . ' AND p.type_id = 1 AND p.group_id = 0))')
            ->execute('getSlaveRows');

        foreach ($aPhotos as $aPhoto) {
            if (isset($aPhoto['photo_id'])) {
                Phpfox::getService('photo.process')->delete($aPhoto['photo_id'], true);
            }
            if (isset($aPhoto['album_id'])) {
                Phpfox::getService('photo.album.process')->delete($aPhoto['album_id'], '', 0, true);
            }
            if (isset($aPhoto['tag_id'])) {
                // delete tags added by this user
                Phpfox::getService('photo.tag.process')->delete($aPhoto['tag_id']);
            }
        }
    }

    public function getItemView()
    {
        if (Phpfox::getLib('request')->get('req3') == 'view') {
            return true;
        }
        return null;
    }

    public function getGroupPosting()
    {
        return [
            _p('upload_photos') => 'can_upload_photo'
        ];
    }

    public function getGroupAccess()
    {
        return [
            _p('view_photos') => 'can_use_photo'
        ];
    }

    public function getProfileSettings()
    {
        return [
            'photo.display_on_profile' => [
                'phrase'  => _p('who_can_view_photos_on_your_profile_page'),
                'default' => '0'
            ]
        ];
    }

    public function getBlockDetailsProfile()
    {
        return [
            'title' => _p('photos')
        ];
    }

    public function legacyRedirect($aRequest)
    {
        if (isset($aRequest['req2'])) {
            switch ($aRequest['req2']) {
                case 'view':
                    if (isset($aRequest['id'])) {
                        $aItem = Phpfox::getService('core')->getLegacyUrl([
                                'url_field' => 'title_url',
                                'table'     => 'photo',
                                'field'     => 'upgrade_item_id',
                                'id'        => $aRequest['id']
                            ]
                        );

                        if ($aItem !== false) {
                            return [$aItem['user_name'], ['photo', 'view', $aItem['title_url']]];
                        }
                    }
                    break;
                default:
                    return 'photo';
                    break;
            }
        }

        return false;
    }

    /**
     * @param      $iId
     * @param null $iUserId
     */
    public function addTrack($iId, $iUserId = null)
    {
        if ($iUserId == null) {
            $iUserId = Phpfox::getUserBy('user_id');
        }
        db()->insert(Phpfox::getT('track'), [
            'type_id'    => 'photo',
            'item_id'    => (int)$iId,
            'ip_address' => Phpfox::getIp(),
            'user_id'    => $iUserId,
            'time_stamp' => PHPFOX_TIME
        ]);
    }

    /**
     * @return array
     */
    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        return [
            'merge_result' => true,
            'result'       => [
                'photo'       => [
                    'phrase' => _p('photos'),
                    'value'  => db()->select('COUNT(*)')
                        ->from(Phpfox::getT('photo'))
                        ->where('view_id = 0 AND time_stamp >= ' . $iToday)
                        ->execute('getSlaveField')
                ],
                'photo_album' => [
                    'phrase' => _p('photo_albums'),
                    'value'  => $this->database()->select('COUNT(*)')
                        ->from(Phpfox::getT('photo_album'))
                        ->where('view_id = 0 AND time_stamp >= ' . $iToday)
                        ->execute('getSlaveField')
                ]
            ]
        ];
    }

    public function updateCounterList()
    {
        $aList = [];

        $aList[] = [
            'name' => _p('photo_count_for_photo_albums'),
            'id'   => 'photo-album'
        ];

        $aList[] = [
            'name' => _p('update_tags_photo'),
            'id'   => 'photo-tag-update'
        ];

        $aList[] = [
            'name' => _p('update_photo_thumbnails'),
            'id'   => 'photo-thumbnail'
        ];

        $aList[] = [
            'name' => _p('update_user_photo_count'),
            'id'   => 'photo-count'
        ];

        $aList[] = [
            'name' => _p('update_profile_photos'),
            'id'   => 'photo-profile'
        ];

        $aList[] = [
            'name' => _p('update_cover_photos'),
            'id'   => 'photo-cover'
        ];

        return $aList;
    }

    public function updateCounter($iId, $iPage, $iPageLimit)
    {
        if ($iId == 'photo-profile') {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('photo'), 'p')
                ->join(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id AND pa.profile_id > 0')
                ->execute('getSlaveField');

            $aRows = db()->select('p.photo_id')
                ->from(Phpfox::getT('photo'), 'p')
                ->join(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id AND pa.profile_id > 0')
                ->limit($iPage, $iPageLimit, $iCnt)
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                db()->update(Phpfox::getT('photo'), ['is_profile_photo' => '1'],
                    'photo_id = ' . $aRow['photo_id']);
            }

            return $iCnt;
        } else if ($iId == 'photo-cover') {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('photo'), 'p')
                ->join(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id AND pa.cover_id > 0')
                ->execute('getSlaveField');

            $aRows = db()->select('p.photo_id')
                ->from(Phpfox::getT('photo'), 'p')
                ->join(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id AND pa.cover_id > 0')
                ->limit($iPage, $iPageLimit, $iCnt)
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                db()->update(Phpfox::getT('photo'), ['is_profile_photo' => '1'],
                    'photo_id = ' . $aRow['photo_id']);
            }

            return $iCnt;
        } else if ($iId == 'photo-tag-update') {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('tag'))
                ->where('category_id = \'photo\'')
                ->execute('getSlaveField');

            $aRows = db()->select('m.tag_id, oc.photo_id AS tag_item_id')
                ->from(Phpfox::getT('tag'), 'm')
                ->where('m.category_id = \'photo\'')
                ->leftJoin(Phpfox::getT('photo'), 'oc', 'oc.photo_id = m.item_id')
                ->limit($iPage, $iPageLimit, $iCnt)
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                if (empty($aRow['tag_item_id'])) {
                    db()->delete(Phpfox::getT('tag'), 'tag_id = ' . $aRow['tag_id']);
                }
            }

            return $iCnt;
        } else if ($iId == 'photo-thumbnail') {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('photo'))
                ->where(db()->isNotNull('destination'))
                ->execute('getSlaveField');

            $aRows = db()->select('photo_id, destination')
                ->from(Phpfox::getT('photo'))
                ->where(db()->isNotNull('destination'))
                ->limit($iPage, $iPageLimit, $iCnt)
                ->execute('getSlaveRows');

            $oImage = Phpfox::getLib('image');
            foreach ($aRows as $aRow) {
                if (preg_match("/\{file\/pic\/(.*)\/(.*)\.jpg\}/i", $aRow['destination'], $aMatches)) {
                    $sPath = PHPFOX_DIR;
                    $sFileName = str_replace(['{', '.jpg}'], ['', '%s.jpg'], $aRow['destination']);
                } else {
                    $sPath = Phpfox::getParam('photo.dir_photo');
                    $sFileName = $aRow['destination'];
                }

                if (file_exists($sPath . sprintf($sFileName, ''))) {
                    foreach (Phpfox::getService('photo')->getPhotoPicSizes() as $iSize) {
                        if ($oImage->createThumbnail($sPath . sprintf($sFileName, ''),
                                $sPath . sprintf($sFileName, '_' . $iSize), $iSize, $iSize) === false
                        ) {
                            continue;
                        }
                    }
                }
            }

            return $iCnt;
        } else if ($iId == 'photo-count') {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('user'))
                ->execute('getSlaveField');

            $aRows = db()->select('u.user_id')
                ->from(Phpfox::getT('user'), 'u')
                ->limit($iPage, $iPageLimit, $iCnt)
                ->group('u.user_id')
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                $iTotalPhotos = db()->select('COUNT(f.photo_id)')
                    ->from(Phpfox::getT('photo'), 'f')
                    ->where('f.view_id = 0 AND f.group_id = 0 AND f.type_id = 0 AND f.privacy = 0 AND f.user_id = ' . $aRow['user_id'])
                    ->execute('getSlaveField');

                db()->update(Phpfox::getT('user_field'), ['total_photo' => $iTotalPhotos],
                    'user_id = ' . $aRow['user_id']);
            }
            return $iCnt;
        } else if ($iId == 'photo-album') {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('photo_album'))
                ->execute('getSlaveField');

            $aRows = db()->select('g.album_id, COUNT(gi.photo_id) AS total_items')
                ->from(Phpfox::getT('photo_album'), 'g')
                ->leftJoin(Phpfox::getT('photo'), 'gi', 'gi.album_id = g.album_id')
                ->group('g.album_id', true)
                ->limit($iPage, $iPageLimit, $iCnt)
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                db()->update(':photo_album', ['total_photo' => $aRow['total_items']],
                    'album_id = ' . (int)$aRow['album_id']);
            }
            return $iCnt;
        }

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
        $aAlbums = db()->select('album_id')->from(':photo_album')->where([
            'module_id' => $sType,
            'group_id'  => $iId
        ])->executeRows();
        foreach ($aAlbums as $aAlbum) {
            Phpfox::getService('photo.album.process')->delete($aAlbum['album_id'], '', 0, true);
        }
        // delete photos not belong to page's album
        $aPhotos = db()->select('photo_id')->from(':photo')->where([
            'module_id' => $sType,
            'group_id'  => $iId
        ])->executeRows();
        foreach ($aPhotos as $aPhoto) {
            Phpfox::getService('photo.process')->delete($aPhoto['photo_id'], true);
        }
    }

    /**
     * @param $aRow
     *
     * @return mixed
     */
    public function getNewsFeedAlbum_FeedLike($aRow)
    {
        if ($aRow['owner_user_id'] == $aRow['viewer_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_their_own_a_href_link_photo_album_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link' => Phpfox::getLib('url')->makeUrl($aRow['owner_user_name']),
                    'link'      => $aRow['link']
                ]
            );
        } else {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_a_href_view_user_link_view_full_name_a_s_a_href_link_photo_album_a',
                [
                    'full_name'      => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link'      => Phpfox::getLib('url')->makeUrl($aRow['owner_user_name']),
                    'view_full_name' => Phpfox::getLib('parse.output')->clean($aRow['viewer_full_name']),
                    'view_user_link' => Phpfox::getLib('url')->makeUrl($aRow['viewer_user_name']),
                    'link'           => $aRow['link']
                ]
            );
        }

        $aRow['icon'] = 'misc/thumb_up.png';

        return $aRow;
    }

    public function getNewsFeedFeedLike($aRow)
    {
        if ($aRow['owner_user_id'] == $aRow['viewer_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_their_own_a_href_link_photo_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link' => Phpfox::getLib('url')->makeUrl($aRow['owner_user_name']),
                    'link'      => $aRow['link']
                ]
            );
        } else {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_a_href_view_user_link_view_full_name_a_s_a_href_link_photo_a',
                [
                    'full_name'      => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link'      => Phpfox::getLib('url')->makeUrl($aRow['owner_user_name']),
                    'view_full_name' => Phpfox::getLib('parse.output')->clean($aRow['viewer_full_name']),
                    'view_user_link' => Phpfox::getLib('url')->makeUrl($aRow['viewer_user_name']),
                    'link'           => $aRow['link']
                ]
            );
        }

        $aRow['icon'] = 'misc/thumb_up.png';

        return $aRow;
    }

    public function getNotificationFeedAlbum_NotifyLike($aRow)
    {
        return [
            'message' => _p('a_href_user_link_full_name_a_liked_your_a_href_link_photo_album_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['full_name']),
                    'user_link' => Phpfox::getLib('url')->makeUrl($aRow['user_name']),
                    'link'      => Phpfox::getLib('url')->makeUrl('photo', ['aredirect' => $aRow['item_id']])
                ]
            ),
            'link'    => Phpfox::getLib('url')->makeUrl('photo', ['aredirect' => $aRow['item_id']])
        ];
    }

    public function getNotificationFeedNotifyLike($aRow)
    {
        return [
            'message' => _p('a_href_user_link_full_name_a_liked_your_a_href_link_photo_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['full_name']),
                    'user_link' => Phpfox::getLib('url')->makeUrl($aRow['user_name']),
                    'link'      => Phpfox::getLib('url')->makeUrl('photo', ['redirect' => $aRow['item_id']])
                ]
            ),
            'link'    => Phpfox::getLib('url')->makeUrl('photo', ['redirect' => $aRow['item_id']])
        ];
    }

    public function sendLikeEmailAlbum($iItemId)
    {
        return _p('a_href_user_link_full_name_a_liked_your_a_href_link_photo_album_a', [
                'full_name' => Phpfox::getLib('parse.output')->clean(Phpfox::getUserBy('full_name')),
                'user_link' => Phpfox::getLib('url')->makeUrl(Phpfox::getUserBy('user_name')),
                'link'      => Phpfox::getLib('url')->makeUrl('photo', ['aredirect' => $iItemId])
            ]
        );
    }

    public function sendLikeEmail($iItemId)
    {
        return _p('a_href_user_link_full_name_a_liked_your_a_href_link_photo_a', [
                'full_name' => Phpfox::getLib('parse.output')->clean(Phpfox::getUserBy('full_name')),
                'user_link' => Phpfox::getLib('url')->makeUrl(Phpfox::getUserBy('user_name')),
                'link'      => Phpfox::getLib('url')->makeUrl('photo', ['redirect' => $iItemId])
            ]
        );
    }

    public function getActivityPointField()
    {
        return [
            _p('photos') => 'activity_photo'
        ];
    }

    public function pendingApproval()
    {
        return [
            'phrase' => _p('photos'),
            'value'  => $this->getPendingTotal(),
            'link'   => Phpfox::getLib('url')->makeUrl('photo', ['view' => 'pending'])
        ];
    }

    public function getPendingTotal()
    {
        return db()->select('COUNT(*)')->from(Phpfox::getT('photo'))->where('view_id = 1')->execute('getSlaveField');
    }

    public function getAdmincpAlertItems()
    {
        $iTotalPending = $this->getPendingTotal();
        return [
            'message' => _p('you_have_total_pending_photos', ['total' => $iTotalPending]),
            'value'   => $iTotalPending,
            'link'    => Phpfox::getLib('url')->makeUrl('photo', ['view' => 'pending'])
        ];
    }

    public function canShareItemOnFeed()
    {
    }

    public function getActivityFeedCustomChecks($aRow)
    {
        if ((defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITgetEM_TYPE') && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null,
                    'photo.view_browse_photos'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['custom_data_cache']['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['custom_data_cache']['group_id'],
                    'photo.view_browse_photos'))
        ) {
            return false;
        }

        if (!empty($aRow['custom_data_cache']['parent_user_name']) && !defined('PHPFOX_IS_USER_PROFILE') && empty($_POST)) {
            $aRow['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow['custom_data_cache'],
                'parent_');
        }

        return $aRow;
    }

    public function getActivityFeed($aItem, $aCallback = null, $bIsChildItem = false)
    {
        //Check in case the feed with tagging user is private
        if (!empty($aItem['parent_user_id'])
            && $aItem['parent_user_id'] == Phpfox::getUserId()
            && $aItem['user_id'] != $aItem['parent_user_id']
            && Phpfox::isModule('privacy')
            && !Phpfox::getService('privacy')->check($aItem['type_id'], $aItem['item_id'], $aItem['user_id'], $aItem['privacy'], null, true)) {
            return false;
        }

        $sThickbox = '';
        $sFeedTable = 'feed';
        $iFeedId = isset($aItem['feed_id']) ? $aItem['feed_id'] : 0;

        $cacheFeedId = 0;
        if (empty($aCallback)) {
            $cache = storage()->get('photo_parent_feed_' . $iFeedId);
            if ($cache) {
                $cacheFeedId = (int)$cache->value;
            } elseif ($bIsChildItem && !empty($aItem['item_id'])) {
                $parentModule = db()->select('module_id, group_id')
                                ->from($this->_sTable)
                                ->where([
                                    'photo_id' => $aItem['item_id']
                                ])->executeRow();
                if (!empty($parentModule['module_id'])
                    && !empty($parentModule['group_id'])
                    && Phpfox::isModule($parentModule['module_id'])
                    && Phpfox::hasCallback($parentModule['module_id'], 'getFeedDetails')) {
                    $feedDetail = Phpfox::callback($parentModule['module_id'] . '.getFeedDetails', $parentModule['group_id']);
                    if (!empty($feedDetail['table_prefix'])) {
                        $cacheFeedId = (int)db()->select('feed_id')
                                        ->from(':' . $feedDetail['table_prefix'] . 'feed')
                                        ->where([
                                            'type_id' => 'photo',
                                            'item_id' => $aItem['item_id']
                                        ])->executeField();
                    }
                }
            }
        }

        $iItemId = $aItem['item_id'];
        $aPhotoItem = Phpfox::getService('photo')->getPhotoItem($iItemId);
        if (!isset($aPhotoItem['photo_id'])) {
            return false;
        }
        if (isset($aPhotoItem['module_id']) && $aPhotoItem['module_id'] && !Phpfox::isModule($aPhotoItem['module_id'])) {
            return false;
        }
        $sFeedTable = !empty($aPhotoItem['module_id']) ? $aPhotoItem['module_id'] . '_feed' : $sFeedTable;
        if ($sFeedTable == 'groups_feed') {
            $sFeedTable = 'pages_feed';
        }

        (($sPlugin = Phpfox_Plugin::get('photo.component_service_callback_getactivityfeed__get_item_before')) ? eval($sPlugin) : false);

        if ($aCallback === null) {
            db()->select(Phpfox::getUserField('u', 'parent_') . ', ')->leftJoin(Phpfox::getT('user'), 'u',
                'u.user_id = photo.parent_user_id');
        }

        if ($bIsChildItem) {
            db()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
                'u2.user_id = photo.user_id');
        }

        $sSelect = 'photo.*, pi.description, pi.location_latlng, pi.location_name, pfeed.photo_id AS extra_photo_id, pa.album_id, pa.name, pa.timeline_id';

        if (Phpfox::isModule('like')) {
            $sSelect .= ', l.like_id AS is_liked';
            db()->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'photo\' AND l.item_id = photo.photo_id AND l.user_id = ' . Phpfox::getUserId());
        }
        $aPhotoIds = [$iItemId];

        if (!empty($aItem['parent_feed_id'])) {
            $iFeedId = $aItem['parent_feed_id'];
        }
        $aRow = db()->select($sSelect)
            ->from($this->_sTable, 'photo')
            ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = photo.photo_id')
            ->leftJoin(Phpfox::getT('photo_feed'), 'pfeed', 'pfeed.feed_id = ' . ($cacheFeedId ? $cacheFeedId : (int)$iFeedId) . ' AND pfeed.feed_time = 0')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = photo.album_id')
            ->where('photo.photo_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if ($bIsChildItem) {
            $aItem = array_merge($aRow, [
                'type_id' => $aItem['type_id'],
                'item_id' => $aItem['item_id'],
            ]);
        }

        if (!isset($aRow['photo_id'])) {
            return false;
        }

        if (Phpfox::getUserParam('photo.can_view_photos')) {
            $sThickbox .= ' js_photo_item_' . $aRow['photo_id'] . ' ';
        }

        if (((defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null,
                        'photo.view_browse_photos'))
                || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages') && !Phpfox::getService('pages')->hasPerm($aRow['group_id'],
                        'photo.view_browse_photos')))
            || ($aRow['module_id'] && Phpfox::isModule($aRow['module_id']) && Phpfox::hasCallback($aRow['module_id'],
                    'canShareOnMainFeed') && !Phpfox::callback($aRow['module_id'] . '.canShareOnMainFeed',
                    $aRow['group_id'], 'photo.view_browse_photos', $bIsChildItem))
        ) {
            return false;
        }

        $bIsPhotoAlbum = false;
        if ($aRow['album_id'] && $aRow['timeline_id'] == 0) {
            $bIsPhotoAlbum = true;
        }

        $bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1);
        $iSponsorId = Phpfox::isAppActive('Core_BetterAds') && (!empty($aItem['sponsor_feed_id']) && ((int)$aItem['sponsor_feed_id'] === (int)$iFeedId)) ? (int)(Phpfox::getService('ad.get')->getFeedSponsors($iFeedId)) : 0;

        $sLink = ($iSponsorId > 0 ? Phpfox::getLib('url')->makeUrl('ad.sponsor', ['view' => $iSponsorId]) : Phpfox::permalink('photo', $aRow['photo_id'], $bCanShowTitle ? $aRow['title'] : null) . ('feed_' . ($cacheFeedId ? $cacheFeedId : (int)$iFeedId)));
        $sFeedImageOnClick = '';

        $iImageMaxSuffix = 1024;
        $sImage = Phpfox::getLib('image.helper')->display([
                'server_id' => $aRow['server_id'],
                'path'      => 'photo.url_photo',
                'file'      => Phpfox::getService('photo')->getPhotoUrl(array_merge($aRow, ['full_name' => $aItem['full_name']])),
                'suffix'    => '_' . $iImageMaxSuffix,
                'class'     => 'photo_holder',
                'defer'     => true
            ]
        );

        $sImageReturnUrl = Phpfox::getLib('image.helper')->display([
                'server_id'  => $aRow['server_id'],
                'path'       => 'photo.url_photo',
                'file'       => Phpfox::getService('photo')->getPhotoUrl(array_merge($aRow, ['full_name' => $aItem['full_name']])),
                'suffix'     => '_' . (isset($iImageMaxSuffix) ? $iImageMaxSuffix : 1024),
                'return_url' => true,
                'class'      => 'photo_holder',
                'defer'      => true
            ]
        );

        if (($aRow['mature'] == 0 || (($aRow['mature'] == 1 || $aRow['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aRow['user_id'] == Phpfox::getUserId()) {
            $sCustomCss = '' . $sThickbox . ' photo_holder_image';
        } else {
            $sFeedImageOnClick = ' onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&width=350&link=' . $sLink . '\')); return false;" ';
            $sCustomCss = 'no_ajax_link photo-mature';
        }

        $aListPhotos = [];
        $customRel = null;
        if ($aRow['extra_photo_id'] > 0 && !$iSponsorId) {
            $conditions = ['AND pfeed.feed_id = ' . ($cacheFeedId ? $cacheFeedId : (isset($iFeedId) ? (int)$iFeedId : 0)) . ' AND pfeed.feed_time = 0 AND p.album_id = ' . (int)$aRow['album_id']];
            if ($aItem['user_id'] != Phpfox::getUserId()) {
                Phpfox::getService('user')->get($aItem['user_id']);
                $oUserObject = \Phpfox::getService('user')->getUserObject($aItem['user_id']);
                $publicPrivacyValues = [0,6];
                if (isset($oUserObject->is_friend) && $oUserObject->is_friend) {
                    $privacyValues = [1,2];
                } elseif (isset($oUserObject->is_friend_of_friend) && $oUserObject->is_friend_of_friend) {
                    $privacyValues = [2];
                }
                $conditions[] = 'AND p.privacy IN(' . implode(',', !empty($privacyValues) ? array_merge($privacyValues, $publicPrivacyValues) : $publicPrivacyValues) . ')';
            }

            $limitPhotos = 4;

            $total = db()->select('COUNT(*)')
                ->from(Phpfox::getT('photo_feed'), 'pfeed')
                ->join(Phpfox::getT('photo'), 'p',
                    'p.photo_id = pfeed.photo_id' . (!empty($aRow['module_id']) ? ' AND p.module_id = \'' . db()->escape($aRow['module_id']) . '\'' : '') . ' AND pfeed.feed_table = \'' . $sFeedTable . '\'')
                ->where($conditions)
                ->executeField();
            $total = (int)$total + 1;

            db()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature')
                ->from(':photo', 'p')
                ->where(['p.photo_id' => $aRow['photo_id']])
                ->union();
            db()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature')
                ->from(Phpfox::getT('photo_feed'), 'pfeed')
                ->join(Phpfox::getT('photo'), 'p',
                    'p.photo_id = pfeed.photo_id' . (!empty($aRow['module_id']) ? ' AND p.module_id = \'' . db()->escape($aRow['module_id']) . '\'' : '') . ' AND pfeed.feed_table = \'' . $sFeedTable . '\'')
                ->where($conditions)
                ->union()
                ->unionFrom('main_photo');

            $aPhotos = db()->select('*')
                ->limit($limitPhotos)
                ->order('main_photo.photo_id DESC')
                ->group('main_photo.photo_id')
                ->execute('getSlaveRows');

            $aExtraPhotoId = array_map(function ($item) {
                return $item['photo_id'];
            }, $aPhotos);

            $iRemain = $total - count($aPhotos);
            $sMore = ($iRemain < 1000) ? $iRemain . '+' : Phpfox::getService('core.helper')->shortNumber($total - count($aPhotos));
            $aPhotoIds = $aExtraPhotoId;

            foreach ($aPhotos as $aPhoto) {
                $indexing = count($aListPhotos);
                if ($indexing >= $limitPhotos) {
                    continue;
                }

                $sPhotoImage = Phpfox::getLib('image.helper')->display([
                        'server_id'  => $aPhoto['server_id'],
                        'path'       => 'photo.url_photo',
                        'file'       => Phpfox::getService('photo')->getPhotoUrl(array_merge($aPhoto,
                            ['full_name' => $aItem['full_name']])),
                        'suffix'     => count($aPhotos) == 3 && $indexing == 0 ? '_1024' : '_500',
                        'return_url' => true,
                        'class'      => 'photo_holder',
                        'userid'     => isset($aItem['user_id']) ? $aItem['user_id'] : '',
                        'defer'      => true // Further controlled in the library image.helper.
                    ]
                );

                $photoMature = false;
                $photoLink = Phpfox::permalink('photo', $aPhoto['photo_id'], $bCanShowTitle ? $aPhoto['title'] : null) . ('feed_' . ($cacheFeedId ? $cacheFeedId : (int)$iFeedId));
                if (!(($aPhoto['mature'] == 0 || (($aPhoto['mature'] == 1 || $aPhoto['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aPhoto['user_id'] == Phpfox::getUserId())) {
                    $photoMature = true;
                }
                if ($indexing == ($limitPhotos - 1) && $total > $limitPhotos) {
                    if ($photoMature) {
                        $aListPhotos[] = '<a href="#" class="no_ajax_link photo_holder_image photo-mature" onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&width=350&link=' . $photoLink . '/\')); return false;" style="background-image: url(\'' . $sPhotoImage . '\')"><span>' . $sMore . '</span></a>';
                    } else {
                        $aListPhotos[] = '<a href="' . $photoLink . '/" class="' . $sThickbox . ' photo_holder_image" rel="' . $aPhoto['photo_id'] . '" style="background-image: url(\'' . $sPhotoImage . '\')"><span>' . $sMore . '</span></a>';
                    }
                } else {
                    if ($photoMature) {
                        $aListPhotos[] = '<a href="#" class="no_ajax_link photo_holder_image photo-mature" onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&width=350&link=' . $photoLink . '/\')); return false;" style="background-image: url(\'' . $sPhotoImage . '\')"></a>';
                    } else {
                        $aListPhotos[] = '<a href="' . $photoLink . '/" class="' . $sThickbox . ' photo_holder_image" rel="' . $aPhoto['photo_id'] . '" style="background-image: url(\'' . $sPhotoImage . '\')"></a>';
                    }
                }
            }
        }

        if (empty($aListPhotos)) {
            // in case we have only one image, use <img> instead of background-image
            $aListPhotos = [
                strtr('<a href="{href}" {other}><img src="{img_src}"></a>', [
                    '{href}'    => $sLink,
                    '{other}'   => empty($sFeedImageOnClick) ? ' class="' . $sThickbox . ' photo_holder_image photo-single-image" rel="' . $aRow['photo_id'] . '" ' : $sFeedImageOnClick . ' class="no_ajax_link photo-single-image photo-mature"',
                    '{img_src}' => $sImageReturnUrl
                ])
            ];
        }

        $sTypeId = 'photo';
        $total_feed_Tag = Phpfox::getService('feed.tag')->getTaggedUsers($iItemId, $sTypeId, true);
        if (!$total_feed_Tag) { // work on old data
            $aListTags = Phpfox::getService('photo.tag')->getTagByIds($aPhotoIds, $aItem['user_id'], true);
            $sExtraTitle = '';
            $iTotalTag = count($aListTags);
            if ($iTotalTag) {
                if ($iTotalTag == 1) {
                    $sExtraTitle .= _p('with_a_link', [
                        'link_name'  => $aListTags[0]['user_name'],
                        'link'       => Phpfox::getLib('phpfox.url')->makeUrl('profile',
                            $aListTags[0]['user_name']),
                        'link_title' => $aListTags[0]['full_name']
                    ]);
                } else if ($iTotalTag == 2) {
                    $sExtraTitle .= _p('with_a_link_and_a_link', [
                        'link_name_1'  => $aListTags[0]['user_name'],
                        'link_1'       => Phpfox::getLib('phpfox.url')->makeUrl('profile',
                            $aListTags[0]['user_name']),
                        'link_title_1' => $aListTags[0]['full_name'],
                        'link_name_2'  => $aListTags[1]['user_name'],
                        'link_2'       => Phpfox::getLib('phpfox.url')->makeUrl('profile',
                            $aListTags[1]['user_name']),
                        'link_title_2' => $aListTags[1]['full_name']
                    ]);
                } else {
                    foreach ($aListTags as $iKey => $aUser) {
                        $aUser['full_name'] = Phpfox::getLib('parse.output')->clean($aUser['full_name']);
                        if ($iKey == 0) {
                            $sExtraTitle .= _p('with') . ' <span class="user_profile_link_span" id="js_user_name_link_' . $aUser['user_name'] . '"><a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile',
                                    $aUser['user_name']) . '">' . $aUser['full_name'] . '</a></span>';
                            $sExtraTitle .= ' ' . _p('and') . ' ' . '<div class="dropdown" style="display: inline-block;"><a href="#" role="button" data-toggle="dropdown">' . ($iTotalTag - 1) . ' ' . _p('others') . '</a>';
                            $sExtraTitle .= '<ul class="dropdown-menu">';
                        } else {
                            $sExtraTitle .= '<li class="item"><span class="user_profile_link_span" id="js_user_name_link_' . $aUser['user_name'] . '"><a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile',
                                    $aUser['user_name']) . '">' . $aUser['full_name'] . '</a></span></li>';
                        }
                    }
                    $sExtraTitle .= '</ul></div>';
                }
            }
        }

        $aReturn = [
            'feed_title'        => '',
            'total_image'       => (count($aListPhotos) ? count($aListPhotos) : 1),
            'feed_image'        => (count($aListPhotos) ? $aListPhotos : $sImage),
            'feed_status'       => $aRow['description'],
            'feed_link'         => $sLink,
            'total_comment'     => $aRow['total_comment'],
            'feed_total_like'   => $aRow['total_like'],
            'feed_is_liked'     => isset($aRow['is_liked']) ? $aRow['is_liked'] : false,
            'feed_icon'         => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/photo.png',
                'return_url' => true
            ]),
            'time_stamp'        => $aRow['time_stamp'],
            'enable_like'       => true,
            'comment_type_id'   => $sTypeId,
            'like_type_id'      => $sTypeId,
            'custom_css'        => $sCustomCss,
            'custom_rel'        => isset($customRel) ? $customRel : $aRow['photo_id'],
            'custom_js'         => $sFeedImageOnClick,
            'no_target_blank'   => true,
            'custom_data_cache' => $aRow
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        if (!empty($aRow['location_name'])) {
            $aReturn['location_name'] = $aRow['location_name'];
        }
        if (!empty($aRow['location_latlng'])) {
            $aReturn['location_latlng'] = json_decode($aRow['location_latlng'], true);
        }

        // get tagged users
        if ($total_feed_Tag) {
            $aReturn['total_friends_tagged'] = $total_feed_Tag;
            $aReturn['friends_tagged'] = Phpfox::getService('feed.tag')->getTaggedUsers($iItemId, $sTypeId, false, 1, 2);
        }

        if ($aRow['module_id'] == 'pages' || $aRow['module_id'] == 'groups' || $aRow['module_id'] == 'event' || !empty($aReturn['friends_tagged'])) {
            $aRow['parent_user_id'] = '';
            $aRow['parent_user_name'] = '';
        }

        if (empty($aRow['parent_user_id'])) {
            if ($bIsPhotoAlbum) {
                $sAlbumName = Phpfox::getLib('locale')->convert(html_entity_decode($aRow['name'], ENT_QUOTES, 'UTF-8'));
                $oParseOutput = Phpfox::getLib('parse.output');
                $aReturn['feed_info'] = _p('added_new_photos_to_gender_album_a_href_link_name_a', [
                    'gender' => Phpfox::getService('user')->gender($aItem['gender'], 1),
                    'link'   => Phpfox::permalink('photo.album', $aRow['album_id'], $sAlbumName),
                    'name'   => $oParseOutput->htmlspecialchars($oParseOutput->shorten($sAlbumName,
                        (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                        '...'))
                ]);
            } else {
                $aReturn['feed_info'] = (count($aListPhotos) > 1 ? _p('shared_a_few_photos') : _p('shared_a_photo'));
            }
        }

        if ($aCallback === null && !empty($aRow['parent_user_name'])) {
            $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
        }

        $aReturn['type_id'] = $sTypeId;
        if (!defined('PHPFOX_IS_PAGES_VIEW') && (($aRow['module_id'] == 'groups' && Phpfox::isAppActive('PHPfox_Groups')) || ($aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages')))) {
            $aPage = db()->select('p.*, pu.vanity_url, ' . Phpfox::getUserField('u', 'parent_'))
                ->from(':pages', 'p')
                ->join(':user', 'u', 'p.page_id=u.profile_page_id')
                ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
                ->where('p.page_id=' . (int)$aRow['group_id'])
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
        if (!empty($sExtraTitle)) {
            $aReturn['feed_info'] = (!empty($aReturn['feed_info']) ? $aReturn['feed_info'] : '') . ' - ' . $sExtraTitle;
        }

        (($sPlugin = Phpfox_Plugin::get('photo.component_service_callback_getactivityfeed__1')) ? eval($sPlugin) : false);
        return $aReturn;
    }

    public function addLikeAlbum($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = db()->select('album_id, name, user_id')
            ->from(Phpfox::getT('photo_album'))
            ->where('album_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['album_id'])) {
            return false;
        }

        db()->updateCount('like', 'type_id = \'photo_album\' AND item_id = ' . (int)$iItemId . '', 'total_like', 'photo_album', 'album_id = ' . (int)$iItemId);

        $albumName = \Phpfox_Locale::instance()->convert($aRow['name']);

        if (!$bDoNotSendEmail) {
            $sLink = Phpfox::permalink('photo.album', $aRow['album_id'], $albumName);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'custom.full_name_liked_your_photo_album_name',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'name' => $albumName]
                ])
                ->message([
                    'custom.full_name_liked_your_photo_album_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'name' => $albumName]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('photo_album_like', $aRow['album_id'], $aRow['user_id']);
        }
        return null;
    }

    public function getNotificationAlbum_Like($aNotification)
    {
        $aRow = db()->select('b.album_id, b.name, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('photo_album'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.album_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        $aRow['name'] = Phpfox::getLib('locale')->convert($aRow['name']);

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('user_name_liked_gender_own_photo_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('user_name_liked_your_photo_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('user_name_liked_span_class_drop_data_user_full_name_s_span_photo_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        $sPhrase = preg_replace('/{phrase var=\'photo\.profile_pictures\'}/i', _p('profile_pictures'), $sPhrase);

        return [
            'link'    => Phpfox::getLib('url')->permalink('photo.album', $aRow['album_id'], $aRow['name']),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function deleteLikeAlbum($iItemId)
    {
        db()->updateCount('like', 'type_id = \'photo_album\' AND item_id = ' . (int)$iItemId . '', 'total_like',
            'photo_album', 'album_id = ' . (int)$iItemId);
    }

    /**
     * @param      $aVals
     * @param null $iUserId   remove in v4.6
     * @param null $sUserName remove in v4.6
     */
    public function addCommentAlbum($aVals, $iUserId = null, $sUserName = null)
    {
        Phpfox::getUserParam('photo.can_post_on_albums', 1);
        $aAlbum = db()->select('u.full_name, u.user_id, u.gender, u.user_name, b.name, b.album_id, b.privacy')
            ->from(Phpfox::getT('photo_album'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.album_id = ' . (int)$aVals['item_id'])
            ->execute('getSlaveRow');

        // Update the post counter if its not a comment put under moderation or if the person posting the comment is the owner of the item.
        if (empty($aVals['parent_id'])) {
            db()->updateCounter('photo_album', 'total_comment', 'album_id', $aVals['item_id']);
        }

        $albumName = \Phpfox_Locale::instance()->convert($aAlbum['name']);

        // Send the user an email
        $sLink = Phpfox::permalink('photo.album', $aAlbum['album_id'], $albumName);

        Phpfox::getService('comment.process')->notify([
                'user_id'            => $aAlbum['user_id'],
                'item_id'            => $aAlbum['album_id'],
                'owner_subject'      => ['full_name_commented_on_your_photo_album_name', ['full_name' => Phpfox::getUserBy('full_name'), 'name' => $albumName]],
                'owner_message'      =>
                    [
                        'full_name_commented_on_your_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'link'      => $sLink,
                            'title'     => $albumName
                        ]
                    ],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id'          => 'comment_photo_album',
                'mass_id'            => 'photo_album',
                'mass_subject'       => (Phpfox::getUserId() == $aAlbum['user_id'] ?
                    [
                        'full_name_commented_on_gender_photo_album', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender'    => Phpfox::getService('user')->gender($aAlbum['gender'], 1)
                    ]
                    ]
                    :
                    [
                        'full_name_commented_on_other_full_name_s_photo_album', [
                        'full_name'       => Phpfox::getUserBy('full_name'),
                        'other_full_name' => $aAlbum['full_name']
                    ]
                    ]),
                'mass_message'       => (Phpfox::getUserId() == $aAlbum['user_id'] ?
                    [
                        'full_name_commented_on_gender_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aAlbum['gender'], 1),
                            'link'      => $sLink,
                            'title'     => $albumName
                        ]
                    ]

                    :
                    [
                        'full_name_commented_on_other_full_name_s_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name'       => Phpfox::getUserBy('full_name'),
                            'other_full_name' => $aAlbum['full_name'],
                            'link'            => $sLink,
                            'title'           => $albumName
                        ]
                    ])
            ]
        );

        (($sPlugin = Phpfox_Plugin::get('blog.component_service_callback_addcomment__end')) ? eval($sPlugin) : false);
    }

    public function getAjaxCommentVarAlbum()
    {
        return 'photo.can_post_on_albums';
    }

    public function getCommentItemAlbum($iId)
    {
        $aRow = db()->select('album_id AS comment_item_id, privacy_comment, user_id AS comment_user_id')
            ->from(Phpfox::getT('photo_album'))
            ->where('album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = '0';

        if (!Phpfox::getService('comment')->canPostComment($aRow['comment_user_id'], $aRow['privacy_comment'])) {
            Phpfox_Error::set(_p('unable_to_post_a_comment_on_this_item_due_to_privacy_settings'));

            unset($aRow['comment_item_id']);
        }

        return $aRow;
    }

    public function getCommentNotificationAlbum($aNotification)
    {
        $aRow = db()->select('b.album_id, b.name, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('photo_album'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.album_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        $aRow['name'] = Phpfox::getLib('locale')->convert($aRow['name']);

        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users'])) {
            $sPhrase = _p('user_name_commented_on_gender_photo_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('user_name_commented_on_your_photo_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('user_name_commented_on_span_class_drop_data_user_full_name_s_span_photo_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        return [
            'link'    => Phpfox::getLib('url')->permalink('photo.album', $aRow['album_id'], $aRow['name']),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * @param      $aItem
     * @param null $aCallback remove in v4.6
     * @param bool $bIsChildItem
     *
     * @return array|bool
     */
    public function getActivityFeedAlbum($aItem, $aCallback = null, $bIsChildItem = false)
    {
        if (Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'photo_album\' AND l.item_id = pa.album_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if ($bIsChildItem) {
            db()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
                'u2.user_id = pa.user_id');
        }

        $albumId = (int)$aItem['item_id'];

        $aRow = db()->select('pa.*, pai.description')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->join(Phpfox::getT('photo_album_info'), 'pai', 'pai.album_id = pa.album_id')
            ->where('pa.album_id = ' . $albumId)
            ->execute('getSlaveRow');

        if (!isset($aRow['album_id'])) {
            return false;
        }

        if (((defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null,
                        'photo.view_browse_photos'))
                || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages') && !Phpfox::getService('pages')->hasPerm($aRow['group_id'],
                        'photo.view_browse_photos')))
            || ($aRow['module_id'] && Phpfox::isModule($aRow['module_id']) && Phpfox::hasCallback($aRow['module_id'],
                    'canShareOnMainFeed') && !Phpfox::callback($aRow['module_id'] . '.canShareOnMainFeed',
                    $aRow['group_id'], 'photo.view_browse_photos', $bIsChildItem))
        ) {
            return false;
        }


        if ($bIsChildItem) {
            $aItem = $aRow;
        }
        $aPhotos = db()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature')
            ->from(Phpfox::getT('photo'), 'p')
            ->where('p.album_id = ' . $aRow['album_id'] . ' AND p.view_id = 0')
            ->limit(5)
            ->order('p.photo_id DESC')
            ->execute('getSlaveRows');
        $total = count($aPhotos);
        $sImage = '';
        $sCustomCss = '';
        if ($total == 1) {
            $iImageMaxSuffix = 1024;
            $sImage = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aPhotos[0]['server_id'],
                    'path'      => 'photo.url_photo',
                    'file'      => Phpfox::getService('photo')->getPhotoUrl(array_merge($aPhotos[0],
                        ['full_name' => $aItem['full_name']])),
                    'suffix'    => '_' . $iImageMaxSuffix,
                    'class'     => 'photo_holder',
                    'defer'     => true
                ]
            );
            if (!(($aPhotos[0]['mature'] == 0 || (($aPhotos[0]['mature'] == 1 || $aPhotos[0]['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aPhotos[0]['user_id'] == Phpfox::getUserId())) {
                $sCustomCss = 'no_ajax_link photo-mature';
            }
        }

        $aListPhotos = [];

        foreach ($aPhotos as $aPhoto) {
            $indexing = count($aListPhotos);
            if ($indexing > 3) {
                continue;
            }
            $sPhotoImage = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aPhoto['server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => Phpfox::getService('photo')->getPhotoUrl(array_merge($aPhoto,
                        ['full_name' => $aItem['full_name']])),
                    'suffix'     => '_500',
                    'return_url' => true,
                    'class'      => 'photo_holder',
                    'userid'     => isset($aItem['user_id']) ? $aItem['user_id'] : '',
                    'defer'      => true // Further controlled in the library image.helper.
                ]
            );

            $iRemain = $aRow['total_photo'] - 4;
            $sMore = ($iRemain < 1000) ? $iRemain . '+' : Phpfox::getService('core.helper')->shortNumber($iRemain) . '+';
            $photoMature = false;
            if (!(($aPhoto['mature'] == 0 || (($aPhoto['mature'] == 1 || $aPhoto['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aPhoto['user_id'] == Phpfox::getUserId())) {
                $photoMature = true;
            }

            $bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1);

            if ($indexing == 3 && $total > 4) {
                $aListPhotos[] = '<a href="' . Phpfox::permalink('photo', $aPhoto['photo_id'],
                        $bCanShowTitle ? $aPhoto['title'] : null) . 'album_' . $albumId . '" class="photo_holder_image" rel="' . $aPhoto['photo_id'] . '" style="background-image: url(\'' . $sPhotoImage . '\')"><span>' . $sMore . '</span></a>';
            } else {
                if ($photoMature) {
                    $aListPhotos[] = '<a href="#" class="no_ajax_link photo_holder_image photo-mature" onclick="tb_show(\'' . _p('warning') . '\', $.ajaxBox(\'photo.warning\', \'height=300&width=350&link=' . Phpfox::permalink('photo',
                            $aPhoto['photo_id'],
                            $bCanShowTitle ? $aPhoto['title'] : null) . 'album_' . $albumId . '/\')); return false;" style="background-image: url(\'' . $sPhotoImage . '\')"></a>';
                } else {
                    $aListPhotos[] = '<a href="' . Phpfox::permalink('photo', $aPhoto['photo_id'],
                            $bCanShowTitle ? $aPhoto['title'] : null) . 'album_' . $albumId . '" class="photo_holder_image" rel="' . $aPhoto['photo_id'] . '" style="background-image: url(\'' . $sPhotoImage . '\')"></a>';
                }
            }
        }

        $sAlbumName = Phpfox::getLib('locale')->convert(html_entity_decode($aRow['name'], ENT_QUOTES, 'UTF-8'));
        $sAlbumName = Phpfox::getLib('parse.output')->clean(Phpfox::getLib('parse.output')->shorten($sAlbumName,
            (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
            '...'));

        $aReturn = [
            'feed_title'      => '',
            'feed_info'       => _p('added_new_photo_album_link_name', [
                'link' => Phpfox::permalink('photo.album', $aRow['album_id'], $aRow['name']),
                'name' => $sAlbumName,
            ]),
            'total_image'     => (count($aListPhotos) ? count($aListPhotos) : 1),
            'feed_image'      => (count($aListPhotos) ? $aListPhotos : $sImage),
            'feed_status'     => Phpfox::getLib('parse.output')->shorten($aRow['description'], 150, '...'),
            'feed_link'       => Phpfox::permalink('photo.album', $aRow['album_id'], $aRow['name']),
            'total_comment'   => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked'   => (isset($aRow['is_liked']) ? $aRow['is_liked'] : false),
            'feed_icon'       => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/photo.png',
                'return_url' => true
            ]),
            'time_stamp'      => $aRow['time_stamp'],
            'enable_like'     => true,
            'comment_type_id' => 'photo_album',
            'like_type_id'    => 'photo_album',
            'custom_css'      => $sCustomCss
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aRow);
        }


        return $aReturn;
    }

    public function getNotificationTag($aNotification)
    {
        $aRow = db()
            ->select('b.photo_id, b.title, b.user_id, pt.user_id as tagger_user_id, u.gender, u.full_name')
            ->from(':photo', 'b')
            ->join(':photo_tag', 'pt', 'b.photo_id=pt.photo_id')
            ->join(':user', 'u', 'u.user_id = b.user_id')
            ->where('b.photo_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');
        if (!isset($aRow['tagger_user_id']) || empty($aRow['tagger_user_id'])) {
            return false;
        }

        if ($bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1)) {
            $aRow['title'] = Phpfox::getLib('locale')->convert($aRow['title']);
        }

        if ($aRow['tagger_user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p($bCanShowTitle ? 'you_tagged_yourself_in_your_photo_title' : 'photo_you_tagged_yourself_in_your_photo', [
                'title' => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : ''
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $aPhraseParam = [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')
                    ->shorten($aRow['title'],
                        (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                        '...') : ''
            ];
            $sPhrase = _p($bCanShowTitle ? 'user_name_tagged_you_in_your_photo_title' : 'photo_user_name_tagged_you_in_your_photo', $aPhraseParam);
        } else {
            $sPhrase = _p($bCanShowTitle ? 'user_name_tagged_you_in_span_class_drop_data_user_full_name_s_span_photo_title' : 'photo_user_name_tagged_you_in_span_class_drop_data_user_full_name_s_span_photo', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...') : ''
            ]);
        }

        return [
            'link'    => Phpfox::getLib('url')->permalink('photo', $aRow['photo_id'], $bCanShowTitle ? $aRow['title'] : null),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getAjaxProfileController()
    {
        return 'photo.index';
    }

    public function getProfileMenu($aUser)
    {
        if (!Phpfox::getUserParam('photo.can_view_photos')) {
            return false;
        }
        $countResult = $this->getTotalItemCount($aUser['user_id']);
        if (!empty($countResult)) {
            $aUser['total_photo'] = $countResult['total'];
        }
        if (!Phpfox::getParam('profile.show_empty_tabs')) {
            if (empty($aUser['total_photo'])) {
                return false;
            }
        }

        if ($sPlugin = Phpfox_Plugin::get('photo.service_callback_getprofilemenu_1')) {
            eval($sPlugin);
            if (isset($mReturnFromPlugin)) {
                return $mReturnFromPlugin;
            }
        }
        $aSubMenu = [];

        $aMenus[] = [
            'phrase'   => _p('photos') . '',
            'url'      => 'profile.photo',
            'total'    => (int)(isset($aUser['total_photo']) ? $aUser['total_photo'] : 0),
            'icon'     => 'feed/photo.png',
            'sub_menu' => $aSubMenu
        ];

        return $aMenus;
    }

    /**
     * @param $iUserId
     *
     * @return array
     */
    public function getTotalItemCount($iUserId)
    {
        $bIsDisplayProfile = Phpfox::getParam('photo.display_profile_photo_within_gallery');
        $bIsDisplayCover = Phpfox::getParam('photo.display_cover_photo_within_gallery');
        $bIsDisplayTimeline = Phpfox::getParam('photo.display_timeline_photo_within_gallery');
        $sWhere = 'view_id = 0
                    AND group_id = 0
                    AND user_id = ' . (int)$iUserId . '
                    AND is_profile_photo IN(' . ($bIsDisplayProfile ? '0,1' : '0') . ')
                    AND is_cover_photo IN(' . ($bIsDisplayCover ? '0,1' : '0') . ')'
            . ($bIsDisplayTimeline ? '' : ' AND type_id = 0');

        if (Phpfox::getUserId() != $iUserId && !Phpfox::isAdmin()) {
            $aPrivacy = [];
            if (!Phpfox::getParam('core.friends_only_community')) {
                $aPrivacy[] = 0;
            }
            if (Phpfox::isModule('friend') && Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $iUserId)) {
                $aPrivacy[] = 1;
            }
            if (Phpfox::isModule('friend') && Phpfox::getService('friend')->isFriendOfFriend($iUserId)) {
                $aPrivacy[] = 2;
            }
            if (count($aPrivacy)) {
                $sWhere .= ' AND privacy IN (' . implode(',', $aPrivacy) . ')';
            } else {
                return [
                    'field' => 'total_photo',
                    'total' => 0
                ];
            }
        }
        return [
            'field' => 'total_photo',
            'total' => db()->select('COUNT(*)')
                ->from(Phpfox::getT('photo'))
                ->where($sWhere)
                ->execute('getSlaveField')
        ];
    }

    /**
     * @param $sSearch
     */
    public function globalUnionSearch($sSearch)
    {
        $sConds = Phpfox::getService('photo')->getConditionsForSettingPageGroup('item');
        db()->select('item.photo_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'photo\' AS item_type_id, item.destination AS item_photo, item.server_id AS item_photo_server')
            ->from(Phpfox::getT('photo'), 'item')
            ->where('item.view_id = 0 AND item.privacy = 0 AND ' . db()->searchKeywords('item.title', $sSearch) . '' . $sConds)
            ->union();
        db()->select('item.album_id AS item_id, item.name AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'photo_album\' AS item_type_id, p.destination AS item_photo, p.server_id AS item_photo_server')
            ->from(Phpfox::getT('photo_album'), 'item')
            ->leftJoin(':photo', 'p', 'p.album_id = item.album_id AND p.is_cover = 1')
            ->where('item.view_id = 0 AND item.privacy = 0 AND ' . db()->searchKeywords('item.name', $sSearch) . '' . $sConds)
            ->union();
    }

    public function getSearchInfo($aRow)
    {
        $aInfo = [];
        $aInfo['item_link'] = Phpfox::getLib('url')->permalink('photo', $aRow['item_id'], $aRow['item_title']);
        $aInfo['item_name'] = _p('photo');

        $aInfo['item_display_photo'] = Phpfox::getLib('image.helper')->display([
                'server_id'  => $aRow['item_photo_server'],
                'file'       => $aRow['item_photo'],
                'path'       => 'photo.url_photo',
                'suffix'     => '_500',
                'max_width'  => '320',
                'max_height' => '320'
            ]
        );

        return $aInfo;
    }

    public function getSearchInfoAlbum($aRow)
    {
        $aInfo = [];
        $aAlbum = Phpfox::getService('photo.album')->getForEdit($aRow['item_id'], true);
        $aInfo['item_link'] = Phpfox::getLib('url')->permalink('photo.album', $aRow['item_id'], $aRow['item_title']);
        $aInfo['item_name'] = _p('photo_albums');
        if ($aAlbum['profile_id'] > 0) {
            $aInfo['item_title'] = _p('user_profile_pictures', ['full_name' => $aAlbum['full_name']]);;
            $aInfo['item_link'] = Phpfox::permalink('photo.album.profile', $aAlbum['user_id'], $aAlbum['user_name']);
        }
        if ($aAlbum['cover_id'] > 0) {
            $aInfo['item_title'] = _p('user_cover_photo', ['full_name' => $aAlbum['full_name']]);
            $aInfo['item_link'] = Phpfox::permalink('photo.album.cover', $aAlbum['user_id'], $aAlbum['user_name']);
        }
        if ($aAlbum['timeline_id'] > 0) {
            $aInfo['item_title'] = _p('user_timeline_photos', ['full_name' => $aAlbum['full_name']]);
            $aInfo['item_link'] = Phpfox::permalink('photo.album', $aRow['item_id'], $aRow['item_title']);
        } else {
            $aInfo['item_link'] = Phpfox::permalink('photo.album', $aRow['item_id'], $aRow['item_title']);
        }
        if (!empty($aRow['item_photo'])) {
            $aInfo['item_display_photo'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aRow['item_photo_server'],
                    'file'       => $aRow['item_photo'],
                    'path'       => 'photo.url_photo',
                    'suffix'     => '_500',
                    'max_width'  => '320',
                    'max_height' => '320'
                ]
            );
        } else {
            $aInfo['item_display_photo'] = '<img src="' . Phpfox::getParam('photo.default_album_photo') . '"/>';
        }
        return $aInfo;
    }

    public function getSearchTitleInfoAlbum()
    {
        return [
            'name' => _p('photo_albums')
        ];
    }

    public function getSearchTitleInfo()
    {
        return [
            'name' => _p('photos')
        ];
    }

    public function getGlobalPrivacySettings()
    {
        return [
            'photo.default_privacy_setting' => [
                'phrase' => _p('photos')
            ],
            'photo.default_album_privacy_setting' => [
                'phrase' => _p('photo_albums')
            ],
        ];
    }

    public function getPageSubMenu($aPage)
    {
        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'photo.share_photos')
            || !Phpfox::getUserParam('photo.can_upload_photos')
            || !Phpfox::getUserParam('photo.max_images_per_upload')
            || !Phpfox::getService('photo')->checkUploadPhotoLimitation()) {
            return null;
        }

        return [
            [
                'phrase' => _p('share_photos'),
                'url'    => Phpfox::getLib('url')->makeUrl('photo.add',
                    ['module' => 'pages', 'item' => $aPage['page_id']])
            ]
        ];
    }

    public function getGroupSubMenu($aPage)
    {
        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'], 'photo.share_photos')
            || !Phpfox::getUserParam('photo.can_upload_photos')
            || !Phpfox::getUserParam('photo.max_images_per_upload')
            || !Phpfox::getService('photo')->checkUploadPhotoLimitation()) {
            return null;
        }

        return [
            [
                'phrase' => _p('share_photos'),
                'url'    => Phpfox::getLib('url')->makeUrl('photo.add',
                    ['module' => 'groups', 'item' => $aPage['page_id']])
            ]
        ];
    }

    public function getPageMenu($aPage)
    {
        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'photo.view_browse_photos') || !Phpfox::getUserParam('photo.can_view_photos')) {
            return null;
        }

        $aSubMenu = [];

        if ($this->request()->get('req3') == 'photo' || $this->request()->get('req2') == 'photo') {
            $aSubMenu[] = [
                'phrase' => _p('all_albums'),
                'url'    => 'albums/'
            ];
            $aSubMenu[] = [
                'phrase' => _p('my_albums'),
                'url'    => 'albums/view_myalbums/'
            ];
        }

        $aMenus[] = [
            'phrase'   => _p('photos'),
            'url'      => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']) . 'photo/',
            'icon'     => 'feed/photo.png',
            'landing'  => 'photo',
            'sub_menu' => $aSubMenu
        ];

        return $aMenus;
    }

    public function getGroupMenu($aPage)
    {
        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'], 'photo.view_browse_photos') || !Phpfox::getUserParam('photo.can_view_photos')) {
            return null;
        }

        $aSubMenu = [];

        if ($this->request()->get('req3') == 'photo' || $this->request()->get('req2') == 'photo') {
            $aSubMenu[] = [
                'phrase' => _p('all_albums'),
                'url'    => 'albums/'
            ];
            $aSubMenu[] = [
                'phrase' => _p('my_albums'),
                'url'    => 'albums/view_myalbums/'
            ];
        }

        $aMenus[] = [
            'phrase'   => _p('Photos'),
            'url'      => Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']) . 'photo/',
            'icon'     => 'feed/photo.png',
            'landing'  => 'photo',
            'sub_menu' => $aSubMenu
        ];

        return $aMenus;
    }

    public function getPagePerms()
    {
        $aPerms = [];

        $aPerms['photo.share_photos'] = _p('who_can_share_a_photo');
        $aPerms['photo.view_browse_photos'] = _p('who_can_view_browse_photos');

        return $aPerms;
    }

    public function getGroupPerms()
    {
        $aPerms = [
            'photo.share_photos' => _p('who_can_share_a_photo')
        ];

        return $aPerms;
    }

    public function canViewPageSection($iPage)
    {
        if (!Phpfox::getService('pages')->hasPerm($iPage, 'photo.view_browse_photos')) {
            return false;
        }

        return true;
    }

    public function checkFeedShareLink()
    {
        if (defined('PHPFOX_IS_PAGES_VIEW')
            && defined('PHPFOX_PAGES_ITEM_TYPE')
            && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null, 'photo.share_photos')) {
            return false;
        }

        if (!Phpfox::getUserParam('photo.can_upload_photos') || !Phpfox::getService('photo')->checkUploadPhotoLimitation()) {
            return false;
        }

        return null;
    }

    public function getNotificationApproved($aNotification)
    {
        $aRow = db()->select('b.photo_id, b.title, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('photo'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.photo_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['photo_id'])) {
            return false;
        }

        $bCanShowTitle = Phpfox::getParam('photo.photo_show_title', 1);
        $sPhrase = _p($bCanShowTitle ? 'your_photo_title_has_been_approved' : 'photo_your_photo_has_been_approved', [
            'title' => $bCanShowTitle ? Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...') : '',
        ]);

        return [
            'link'             => Phpfox::getLib('url')->permalink('photo', $aRow['photo_id'], $bCanShowTitle ? $aRow['title'] : null),
            'message'          => $sPhrase,
            'icon'             => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'no_profile_image' => true
        ];
    }

    /**
     * @param $iItemId
     *
     * @return bool
     */
    public function deleteFeedItem($iItemId, $sFeedTablePrefix = '')
    {
        $iPhotoType = db()->select('type_id')
            ->from(Phpfox::getT('photo'))
            ->where('photo_id = ' . (int)$iItemId)
            ->execute('getSlaveField');

        // 0 = photo section
        // 1 = feed image
        // 2 = cover image
        // Photo was uploaded from the feed, and not from the photo section:
        if ($iPhotoType == 1) {
            // get all of this feed
            $iFeedId = db()->select('feed_id')
                ->from(':' . $sFeedTablePrefix . 'feed')
                ->where('item_id = ' . (int)$iItemId . ' AND type_id = \'photo\'')
                ->execute('getSlaveField');
            $aPhotos = Phpfox::getService('photo')->getFeedPhotos($iFeedId, null, $sFeedTablePrefix);
            foreach ($aPhotos as $aPhoto) {
                Phpfox::getService('photo.process')->delete($aPhoto['photo_id']);
            }
        }

        return false;
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
        if ($sPlugin = Phpfox_Plugin::get('photo.service_callback__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }

    public function getNotificationNewItem_Groups($aNotification)
    {
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            return false;
        }
        $aItem = Phpfox::getService('photo')->getForEdit($aNotification['item_id']);
        if (empty($aItem) || empty($aItem['group_id']) || $aItem['module_id'] != 'groups') {
            return false;
        }

        $aRow = Phpfox::getService('groups')->getPage($aItem['group_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('{{ users }} add a new photo in the group "{{ title }}"', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link'    => Phpfox::getLib('url')->permalink('photo', $aItem['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aItem['title'] : null),
            'message' => $sPhrase,
            'icon'    => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'photo')
        ];
    }

    /**
     * @description: callback to check permission to view a photo
     *
     * @param $iId
     *
     * @return array|bool
     */
    public function canViewItem($iId)
    {
        return Phpfox::getService('photo')->canViewItem($iId);
    }

    public function ignoreDeleteLikesAndTagsWithFeed()
    {
        return true;
    }

    /**
     * This callback will be called when admin delete a sponsor in admincp
     *
     * @param $aParams
     */
    public function deleteSponsorItem($aParams)
    {
        db()->update(':photo', ['is_sponsor' => 0], ['photo_id' => $aParams['item_id']]);
        $this->cache()->remove('photo_sponsored');
    }

    /**
     * @param $iUserId user id of selected user
     *
     * @return array|bool
     */
    public function getUserStatsForAdmin($iUserId)
    {
        if (!$iUserId) {
            return false;
        }

        $iTotalPhoto = db()->select('COUNT(*)')
            ->from(':photo')
            ->where('user_id =' . (int)$iUserId)
            ->execute('getField');
        $iTotalAlbum = db()->select('COUNT(*)')
            ->from(':photo_album')
            ->where('user_id =' . (int)$iUserId)
            ->execute('getField');
        return [
            'merge_result' => true,
            'result'       => [
                [
                    'total_name'  => _p('photos'),
                    'total_value' => $iTotalPhoto,
                    'type'        => 'item'
                ],
                [
                    'total_name'  => _p('photo_albums'),
                    'total_value' => $iTotalAlbum,
                    'type'        => 'item'
                ]
            ]
        ];
    }

    public function getUploadParamsFeed()
    {
        return Phpfox::getService('photo')->getUploadParams(true);
    }

    public function getUploadParamsSchedule($params) {
        return Phpfox::getService('photo')->getUploadParams(true, true, $params);
    }

    public function getUploadParams()
    {
        return Phpfox::getService('photo')->getUploadParams();

    }

    /**
     * @return array
     */
    public function getNotificationSettings()
    {
        return [
            'photo.email_notification' => [
                'phrase' => _p('photos_notifications'),
                'default' => 1
            ],
        ];
    }

    public function addScheduleItemToFeed($aScheduleVals) {
        $oServicePhotoProcess = Phpfox::getService('photo.process');

        $aVals = $aScheduleVals['aVals'];
        $aFileData = $aScheduleVals['aFileData'];
        $aCallback = $aScheduleVals['aCallback'];
        $sCallbackModule = $aScheduleVals['callback_module'];
        $iParentUserId = $aScheduleVals['parent_user_id'];
        $iUserId = $aScheduleVals['iUserId'];
        $bDirectlyPublic = !Phpfox::getUserParam('photo.photo_must_be_approved');
        $bCanAddFeed = Phpfox::isModule('feed');
        if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share') {
            $aVals['description'] = $aVals['status_info'];
            $aVals['type_id'] = '1';
        }
        if (!Phpfox::getService('photo')->checkUploadPhotoLimitation()) {
            return Phpfox_Error::display(_p('photo_you_have_reached_your_limit_to_upload_new_photos'));
        }
        $aPhotos = [];
        $iTotal = 0;
        foreach($aFileData as $key => $aFileItem) {
            $iTotal++;
            $iId = $oServicePhotoProcess->add($iUserId, array_merge($aVals, $aFileItem['file_image']));
            $aPhotos[$key] = $iId;
            $sFileName = $aFileItem['file_name'];

            $aUpdate = [
                'destination'    => $sFileName,
                'width'          => $aFileItem['file_width_height'][0],
                'height'         => $aFileItem['file_width_height'][1],
                'server_id'      => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                'allow_rate'     => (empty($aVals['album_id']) ? '1' : '0'),
                'description'    => (empty($aVals['description']) ? null : $aVals['description']),
                'allow_download' => 1
            ];

            $oServicePhotoProcess->update($iUserId, $iId, $aUpdate);
            $iServerId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');

            $sFile = Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '');
            if (!file_exists($sFile)
                && !Phpfox::getParam('core.keep_files_in_server')
            ) {
                if ($iServerId > 0) {
                    $sActualFile = Phpfox::getLib('image.helper')->display([
                            'server_id'  => $iServerId,
                            'path'       => 'photo.url_photo',
                            'file'       => $sFileName,
                            'suffix'     => '',
                            'return_url' => true
                        ]
                    );

                    $aExts = preg_split("/[\/\\.]/", $sActualFile);
                    $iCnt = count($aExts) - 1;
                    $sExt = strtolower($aExts[$iCnt]);

                    $aParts = explode('/', $sFileName);
                    $sFile = Phpfox::getParam('photo.dir_photo') . $aParts[0] . '/' . $aParts[1] . '/' . md5($sFileName) . '.' . $sExt;

                    // Create a temp copy of the original file in local server
                    if (filter_var($sActualFile, FILTER_VALIDATE_URL) !== false) {
                        file_put_contents($sFile, fox_get_contents($sActualFile));
                    } else {
                        copy($sActualFile, $sFile);
                    }

                    //Delete file in local server
                    register_shutdown_function(function () use ($sFile) {
                        @unlink($sFile);
                    });
                }
            }

            if ($aFileItem['file_size'] < $aFileItem['file_image']['size']) {
                Phpfox::getService('photo.process')->updatePhotoInfo($iId, ['file_size' => $aFileItem['file_size']]);
            }
        }
        Phpfox::getService('photo.process')->updateSchedulePhotoCount($iUserId, $iTotal, true);

        if ($bDirectlyPublic) {
            $aPhoto = db()->select('*')
                ->from(':photo')
                ->where(['photo_id' => $aPhotos[0]])
                ->executeRow();
            $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('photo', $aPhoto['photo_id'], $aPhoto['privacy'], $aPhoto['privacy_comment'], $iParentUserId, $iUserId, false, 0, null, false, $aVals['schedule_timestamp']);

            if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share') {
                // notification to tagged and mentioned friends
                Phpfox::getService('photo.process')->notifyTaggedInFeed(isset($aVals['status_info']) ? $aVals['status_info'] : '', $aPhoto['photo_id'], $aPhoto['user_id'], $iFeedId, isset($aVals['tagged_friends']) ? $aVals['tagged_friends'] : '', isset($aVals['privacy']) ? $aVals['privacy'] : 0, $iParentUserId, $sCallbackModule);
            }
            if ($aCallback && Phpfox::isModule('notification') && Phpfox::isModule($aCallback['module']) && Phpfox::hasCallback($aCallback['module'], 'addItemNotification')
            ) {
                Phpfox::callback($aCallback['module'] . '.addItemNotification', [
                    'page_id'      => $aCallback['item_id'],
                    'item_perm'    => 'photo.view_browse_photos',
                    'item_type'    => 'photo',
                    'item_id'      => $aPhoto['photo_id'],
                    'owner_id'     => $aPhoto['user_id'],
                    'items_phrase' => 'photos__l'
                ]);
            }
            array_shift($aPhotos);
            foreach ($aPhotos as $key => $item) {
                db()->insert(Phpfox::getT('photo_feed'), [
                        'feed_id'    => $iFeedId,
                        'photo_id'   => $item,
                        'feed_table' => (empty($aCallback['table_prefix']) ? 'feed' : $aCallback['table_prefix'] . 'feed')
                    ]
                );
            }
        } elseif ($bCanAddFeed && count($aPhotos)) {
            Phpfox::getService('photo.process')->addPhotoFeedForPending($aPhotos, (empty($aCallback['table_prefix']) ? 'feed' : $aCallback['table_prefix'] . 'feed'));
        }
        return true;
    }

    public function getAdditionalScheduleInfo($aRow) {
        $aInfo = [];
        $data = $aRow['data'];
        $aInfo['item_title'] = $data['aVals']['status_info'];
        $aInfo['item_name'] = count($data['aFileData']) > 1 ? _p('photos') : _p('photo');
        $aInfo['item_images'] = [
            'remaining' => !empty($data['aFileData']) && count($data['aFileData']) > 2 ? count($data['aFileData']) - 1 : 0
        ];
        for ($i = 0; $i < 2; $i++) {
            if (isset($data['aFileData'][$i])) {
                $aImage = $data['aFileData'][$i];
                $aInfo['item_images']['images'][] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aImage['file_server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aImage['file_name'],
                    'suffix'     => '_500',
                    'return_url' => true
                ]);
            }
        }
        return $aInfo;
    }

    public function getAdditionalEditBlock() {
        return 'photo.block.edit-photo-schedule';
    }

    public function getExtraScheduleData($aItem) {
        $aData = $aItem['data'];
        $aVals = $aData['aVals'];
        $aData['user_status'] = $aVals['status_info'];
        $aData['tagged_friends'] = $aVals['tagged_friends'];
        $aData['privacy'] = $aVals['privacy'];
        if(!empty($aVals['location']['latlng'])) {
            $aLatLng = explode(',', $aVals['location']['latlng']);
            $aData['location_latlng']['latitude'] = $aLatLng[0];
            $aData['location_latlng']['longitude'] = $aLatLng[1];
        }
        if(!empty($aVals['location']['name'])) {
            $aData['location_name'] = $aVals['location']['name'];
        }
        $aItem['data'] = $aData;
        $aImages = [];
        $iTotalImage = is_array($aItem['data']['aFileData']) ? count($aItem['data']['aFileData']) : 0;
        foreach ($aItem['data']['aFileData'] as $key => $value) {
            $aImages[$key] = [
                'image_id'      => $value['file_image_id'],
                'image_path'    => $value['file_name'],
                'server_id'     => $value['file_server_id'],
            ];
        }
        $aItem['extra'] = [
            'iTotalImage' => $iTotalImage,
            'aScheduleImages' => $aImages
        ];
        return $aItem;
    }

    public function onUpdateScheduleItem($data, $aVals) {
        $aScheduleVals = $data['aVals'];
        $aScheduleVals['status_info'] = $aVals['user_status'];
        $aScheduleVals['tagged_friends'] = $aVals['tagged_friends'];
        $aScheduleVals['privacy'] = $aVals['privacy'];
        $aScheduleVals['privacy_list'] = $aVals['privacy_list'];
        $aScheduleVals['location'] = $aVals['location'];
        $data['aVals'] = $aScheduleVals;
        $aFileData = !empty($data['aFileData']) ? $data['aFileData'] : [];
        $iOldTotalPhoto = (int)count($aFileData);
        if (!empty($aVals['remove_photo'])) {
            $aRemovePhotos = explode(',', trim($aVals['remove_photo'], ','));
            foreach ($aFileData as $key => $aFile) {
                if (in_array($aFile['file_image_id'], $aRemovePhotos)) {
                    Phpfox::getService('photo.process')->deleteScheduleImage($aFile, $aVals['schedule_id']);
                    unset($aFileData[$key]);
                }
            }
            $aFileData = array_values($aFileData);
        }
        if (!empty($aVals['new_photo'])) {
            $aNewPhotos = explode('|', trim($aVals['new_photo'], '|'));
            foreach($aNewPhotos as $aNewFile) {
                $aFile = json_decode($aNewFile, true);
                if (!empty($aRemovePhotos) && in_array($aFile['file_image_id'], $aRemovePhotos)) {
                    continue;
                }
                $aFileData[] = $aFile;
            }
        }
        if (!($iNewTotalPhoto = count($aFileData))) {
            return Phpfox_Error::set(_p('you_can_not_schedule_a_photo_feed_without_upload_any_photo'));
        }
        if ($iNewTotalPhoto > $iOldTotalPhoto) {
            Phpfox::getService('photo.process')->updateSchedulePhotoCount($data['iUserId'], $iNewTotalPhoto - $iOldTotalPhoto);
        } elseif ($iNewTotalPhoto < $iOldTotalPhoto) {
            Phpfox::getService('photo.process')->updateSchedulePhotoCount($data['iUserId'], $iOldTotalPhoto - $iNewTotalPhoto, true);
        }
        $data['aFileData'] = $aFileData;
        return $data;
    }

    public function onDeleteScheduleItem($data) {
        if (!empty($data['aFileData'])) {
            foreach($data['aFileData'] as $key => $value) {
                if (!empty($value['file_name'])) {
                    Phpfox::getService('photo.process')->deleteFiles($value['file_name'], $data['iUserId'], $value['file_server_id']);
                }
            }
            Phpfox::getService('photo.process')->updateSchedulePhotoCount($data['iUserId'],  (int)count($data['aFileData']), true);
        }
    }

    public function onScheduleItemToQueue($aSchedule) {
        $aData = !empty($aSchedule['data']) ? unserialize($aSchedule['data']) : [];
        $iTotalImages = !empty($aData['aFileData']) ? (int)count($aData['aFileData']) : 0;
        Phpfox::getService('photo.process')->updateSchedulePhotoCount($aSchedule['user_id'], $iTotalImages);
        return true;
    }
}
