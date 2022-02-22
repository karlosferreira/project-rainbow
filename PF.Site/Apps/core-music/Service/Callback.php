<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Service;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Template;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Callback extends \Phpfox_Service
{
    private $_iFallbackLength;

    /**
     * Class constructor
     */
    public function __construct()
    {
        // if the notification module is disabled we fallback the length to shorten to _iFallbackLength
        $this->_iFallbackLength = 50;
    }

    public function getFeedRedirectPlaylist($iId)
    {
        $aRow = $this->database()->select('m.playlist_id, m.name')
            ->from(Phpfox::getT('music_playlist'), 'm')
            ->where('m.playlist_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aRow['playlist_id'])) {
            return false;
        }

        return Phpfox::permalink('music.playlist', $aRow['playlist_id'], $aRow['name']);
    }

    public function getRedirectCommentPlaylist($iId)
    {
        return $this->getFeedRedirectPlaylist($iId);
    }

    public function getReportRedirectPlaylist($iId)
    {
        return $this->getFeedRedirectPlaylist($iId);
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

        $iCntSong = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('music_song'))
            ->where($aCond)
            ->execute('getSlaveField');
        $iCntAlbum = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('music_album'))
            ->where($aCond)
            ->execute('getSlaveField');

        return [
            'merge_result' => true,
            'result'       => [
                'music'       => [
                    'phrase' => 'music.songs',
                    'total'  => $iCntSong
                ],
                'music_album' => [
                    'phrase' => 'music.music_albums',
                    'total'  => $iCntAlbum
                ]
            ]
        ];
    }

    public function enableSponsor($aParams)
    {
        if ($aParams['section'] == 'album') {
            return Phpfox::getService('music.process')->sponsorAlbum($aParams['item_id'], 1);
        }
        if ($aParams['section'] == 'song') {
            return Phpfox::getService('music.process')->sponsorSong($aParams['item_id'], 1);
        }
        return null;
    }

    public function enableSponsorAlbum($aParams)
    {
        return Phpfox::getService('music.process')->sponsorAlbum($aParams['item_id'], 1);
    }

    public function enableSponsorSong($aParams)
    {
        return Phpfox::getService('music.process')->sponsorSong($aParams['item_id'], 1);
    }


    public function getDashboardActivity()
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return [];
        }
        $aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);
        return [
            _p('music_songs') => $aUser['activity_music_song']
        ];
    }


    public function getLink($aParams)
    {
        if ($aParams['section'] == 'song') {
            $sTitle = $this->database()->select('title')
                ->from(Phpfox::getT('music_song'))
                ->where('view_id != 2 AND song_id = ' . (int)$aParams['item_id'])
                ->executeField();

            if (empty($sTitle)) {
                return false;
            }

            return Phpfox_Url::instance()->makeUrl("music.$aParams[item_id].$sTitle");
        }
        if ($aParams['section'] == 'album') {
            $sTitle = $this->database()->select('name')
                ->from(Phpfox::getT('music_album'))
                ->where('album_id = ' . (int)$aParams['item_id'])
                ->executeField();

            if (empty($sTitle)) {
                return false;
            }

            return Phpfox_Url::instance()->makeUrl("music.album.$aParams[item_id].$sTitle");
        }

        return null;
    }

    public function getLinkSong($aParams)
    {
        $sTitle = $this->database()->select('title')
            ->from(Phpfox::getT('music_song'))
            ->where('view_id != 2 AND song_id = ' . (int)$aParams['item_id'])
            ->executeField();

        if (empty($sTitle)) {
            return false;
        }

        return Phpfox_Url::instance()->makeUrl("music.$aParams[item_id].$sTitle");
    }

    public function getLinkAlbum($aParams)
    {
        $sTitle = $this->database()->select('name')
            ->from(Phpfox::getT('music_album'))
            ->where('album_id = ' . (int)$aParams['item_id'])
            ->executeField();

        if (empty($sTitle)) {
            return false;
        }

        return Phpfox_Url::instance()->makeUrl("music.album.$aParams[item_id].$sTitle");
    }

    public function getProfileLink()
    {
        return 'profile.music';
    }

    public function getAjaxCommentVarPlaylist()
    {
        return 'music.can_add_comment_on_music_playlist';
    }

    public function getAjaxCommentVarAlbum()
    {
        return 'music.can_add_comment_on_music_album';
    }

    public function getAjaxCommentVarSong()
    {
        return 'music.can_add_comment_on_music_song';
    }

    public function getCommentNewsFeedSong($aRow)
    {
        $oUrl = \Phpfox_Url::instance();

        if ($aRow['owner_user_id'] == $aRow['item_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_their_own_a_href_title_link_song',
                [
                    'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                    'full_name'  => $this->preParse()->clean($aRow['owner_full_name']),
                    'title_link' => $aRow['link']
                ]
            );
        } else {
            if ($aRow['item_user_id'] == Phpfox::getUserBy('user_id')) {
                $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_your_a_href_title_link_song_a',
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

    public function getCommentItemSong($iId)
    {
        $aRow = $this->database()->select('song_id AS comment_item_id, user_id AS comment_user_id, module_id AS parent_module_id')
            ->from(Phpfox::getT('music_song'))
            ->where('song_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = 1;

        return $aRow;
    }

    public function getActivityFeedSong_Comment($aRow)
    {
        if (Phpfox::isUser() && Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = $this->database()->select('b.song_id, b.title, b.time_stamp, b.privacy, b.total_comment, b.total_like, c.total_like, ct.text_parsed AS text,  f.friend_id AS is_friend, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('music_song'), 'b',
                'c.type_id = \'music_song\' AND c.item_id = b.song_id AND c.view_id = 0')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = b.user_id AND f.friend_user_id = " . Phpfox::getUserId())
            ->where('c.comment_id = ' . (int)$aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['song_id'])) {
            return false;
        }

        $sLink = Phpfox::permalink('music', $aItem['song_id'], $aItem['title']);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aItem['title'],
            (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : 50));
        $sUser = '<a href="' . \Phpfox_Url::instance()->makeUrl($aItem['user_name']) . '">' . $aItem['full_name'] . '</a>';
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id']) {
            $sMessage = _p('posted_a_comment_on_gender_song_a_href_link_title_a',
                ['gender' => $sGender, 'link' => $sLink, 'title' => $sTitle]);
        } else {
            $sMessage = _p('posted_a_comment_on_user_name_s_song_a_href_link_title_a',
                ['user_name' => $sUser, 'link' => $sLink, 'title' => $sTitle]);
        }

        $aReturn = [
            'no_share'        => true,
            'feed_info'       => $sMessage,
            'feed_link'       => $sLink,
            'feed_status'     => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked'   => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon'       => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/music.png',
                'return_url' => true
            ]),
            'time_stamp'      => $aRow['time_stamp'],
            'like_type_id'    => 'feed_mini'
        ];

        return $aReturn;
    }

    public function addCommentSong($aVals, $iUserId = null, $sUserName = null)
    {
        $aRow = $this->database()->select('m.song_id, m.item_id, m.title, u.full_name, u.user_id, u.gender, u.user_name')
            ->from(Phpfox::getT('music_song'), 'm')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
            ->where('m.song_id = ' . (int)$aVals['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['song_id'])) {
            return \Phpfox_Error::trigger('Invalid callback on a song.');
        }

        if (empty($aRow['item_id'])) {
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add($aVals['type'] . '_comment',
                $aVals['comment_id']) : null);
        }

        // Update the post counter if its not a comment put under moderation or if the person posting the comment is the owner of the item.
        if (empty($aVals['parent_id'])) {
            $this->database()->updateCounter('music_song', 'total_comment', 'song_id', $aRow['song_id']);
        }

        // Send the user an email
        $sLink = \Phpfox_Url::instance()->permalink('music', $aRow['song_id'], $aRow['title']);

        Phpfox::getService('comment.process')->notify([
                'user_id'            => $aRow['user_id'],
                'item_id'            => $aRow['song_id'],
                'owner_subject'      => [
                    'full_name_commented_on_your_song_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $this->preParse()->clean($aRow['title'], 100)
                    ]
                ],
                'owner_message'      => [
                    'name_commented_on_your_song',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aRow['title']]
                ],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id'          => 'comment_music_song',
                'mass_id'            => 'music_song',
                'mass_subject'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_song', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1)
                    ]
                    ]
                    :
                    [
                        'full_name_commented_on_other_full_name_s_song',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'other_full_name' => $aRow['full_name']]
                    ]),
                'mass_message'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_song_a_href_link_title_a_to_see_the_comment_thread_folow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                            'title'     => $aRow['title'],
                            'link'      => $sLink
                        ]
                    ]

                    :
                    [
                        'full_name_commented_on_other_full_names_song', [
                        'full_name'       => Phpfox::getUserBy('full_name'),
                        'other_full_name' => $aRow['full_name'],
                        'link'            => $sLink,
                        'title'           => $aRow['title']
                    ]
                    ]
                )
            ]
        );
        return null;
    }

    public function updateCommentTextSong($aVals, $sText)
    {
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('comment_music_song', $aVals['item_id'],
            $sText, $aVals['comment_id']) : null);
    }

    public function getCommentItemAlbum($iId)
    {
        $aRow = $this->database()->select('album_id AS comment_item_id, user_id AS comment_user_id, module_id AS parent_module_id')
            ->from(Phpfox::getT('music_album'))
            ->where('album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = 1;

        return $aRow;
    }

    public function getCommentItemPlaylist($iId)
    {
        $aRow = db()->select('playlist_id AS comment_item_id, privacy_comment, user_id AS comment_user_id')
            ->from(Phpfox::getT('music_playlist'))
            ->where('playlist_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = '0';

        if (!Phpfox::getService('comment')->canPostComment($aRow['comment_user_id'], $aRow['privacy_comment'])) {
            Phpfox_Error::set(_p('unable_to_post_a_comment_on_this_item_due_to_privacy_settings'));
            unset($aRow['comment_item_id']);
        }

        return $aRow;
    }

    public function addCommentPlaylist($aVals, $iUserId = null, $sUserName = null)
    {
        $aRow = db()->select('u.full_name, u.user_id, u.gender, u.user_name, p.name, p.playlist_id, p.privacy, p.privacy_comment')
            ->from(Phpfox::getT('music_playlist'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.playlist_id = ' . (int)$aVals['item_id'])
            ->execute('getSlaveRow');

        if ($iUserId === null) {
            $iUserId = Phpfox::getUserId();
        }
        if (empty($aRow['playlist_id'])) {
            return \Phpfox_Error::trigger('Invalid callback on music playlist.');
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add($aVals['type'] . '_comment',
            $aVals['comment_id'], 0, 0, 0, $iUserId) : null);

        // Update the post counter if its not a comment put under moderation or if the person posting the comment is the owner of the item.
        db()->updateCounter('music_playlist', 'total_comment', 'playlist_id', $aVals['item_id']);

        // Send the user an email
        $sLink = \Phpfox_Url::instance()->permalink('music.playlist', $aRow['playlist_id'], $aRow['name']);

        Phpfox::getService('comment.process')->notify([
                'user_id'            => $aRow['user_id'],
                'item_id'            => $aRow['playlist_id'],
                'owner_subject'      => [
                    'full_name_commented_on_your_music_playlist_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $this->preParse()->clean($aRow['name'], 100)
                    ]
                ],
                'owner_message'      => [
                    'full_name_commented_on_your_music_playlist_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['name'], 'link' => $sLink]
                ],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id'          => 'comment_music_playlist',
                'mass_id'            => 'music_playlist',
                'mass_subject'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_music_playlist', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1)
                    ]
                    ]
                    :
                    [
                        'full_name_commented_on_other_full_name_s_music_playlist',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'other_full_name' => $aRow['full_name']]
                    ]),
                'mass_message'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                            'user_name' => $aRow['name'],
                            'link'      => $sLink
                        ]
                    ]
                    :
                    [
                        'full_name_commented_on_other_full_name_s_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name'       => Phpfox::getUserBy('full_name'),
                            'other_full_name' => $aRow['full_name'],
                            'user_name'       => $aRow['name'],
                            'link'            => $sLink
                        ]
                    ]
                )
            ]
        );
        return null;
    }


    public function addCommentAlbum($aVals, $iUserId = null, $sUserName = null)
    {
        $aRow = $this->database()->select('m.album_id, m.name, u.full_name, u.user_id, u.gender, u.user_name')
            ->from(Phpfox::getT('music_album'), 'm')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
            ->where('m.album_id = ' . (int)$aVals['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['album_id'])) {
            return \Phpfox_Error::trigger('Invalid callback on music album.');
        }

        // Update the post counter if its not a comment put under moderation or if the person posting the comment is the owner of the item.
        if (empty($aVals['parent_id'])) {
            $this->database()->updateCounter('music_album', 'total_comment', 'album_id', $aRow['album_id']);
        }

        // Send the user an email
        $sLink = \Phpfox_Url::instance()->permalink('music.album', $aRow['album_id'], $aRow['name']);

        Phpfox::getService('comment.process')->notify([
                'user_id'            => $aRow['user_id'],
                'item_id'            => $aRow['album_id'],
                'owner_subject'      => [
                    'full_name_commented_on_your_album_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $this->preParse()->clean($aRow['name'], 100)
                    ]
                ],
                'owner_message'      => [
                    'full_name_commented_on_your_album_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['name'], 'link' => $sLink]
                ],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id'          => 'comment_music_album',
                'mass_id'            => 'music_album',
                'mass_subject'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_album', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1)
                    ]
                    ]
                    :
                    [
                        'full_name_commented_on_other_full_name_s_album',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'other_full_name' => $aRow['full_name']]
                    ]),
                'mass_message'       => (Phpfox::getUserId() == $aRow['user_id'] ?
                    [
                        'full_name_commented_on_gender_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                            'user_name' => $aRow['name'],
                            'link'      => $sLink
                        ]
                    ]
                    :
                    [
                        'full_name_commented_on_other_full_name_s_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                        [
                            'full_name'       => Phpfox::getUserBy('full_name'),
                            'other_full_name' => $aRow['full_name'],
                            'user_name'       => $aRow['name'],
                            'link'            => $sLink
                        ]
                    ]
                )
            ]
        );
        return null;
    }

    public function updateCommentTextAlbum($aVals, $sText)
    {
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('comment_music_album',
            $aVals['item_id'], $sText, $aVals['comment_id']) : null);
    }

    public function getItemNameSong($iId, $sName)
    {
        return _p('a_href_link_on_user_name_s_song_a', [
            'link'      => \Phpfox_Url::instance()->makeUrl('comment.view', ['id' => $iId]),
            'user_name' => $sName
        ]);
    }

    public function getItemNameAlbum($iId, $sName)
    {
        return _p('a_href_link_on_user_name_s_album_a', [
            'link'      => \Phpfox_Url::instance()->makeUrl('comment.view', ['id' => $iId]),
            'user_name' => $sName
        ]);
    }

    public function getCommentNewsFeedAlbum($aRow)
    {
        $oUrl = \Phpfox_Url::instance();

        if ($aRow['owner_user_id'] == $aRow['item_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_their_own_a_href_title_link_music',
                [
                    'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                    'full_name'  => $this->preParse()->clean($aRow['owner_full_name']),
                    'title_link' => $aRow['link']
                ]
            );
        } else {
            if ($aRow['item_user_id'] == Phpfox::getUserBy('user_id')) {
                $aRow['text'] = _p('a_href_user_link_full_name_a_added_a_new_comment_on_your_a_href_title_link_music_album',
                    [
                        'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                        'full_name'  => $this->preParse()->clean($aRow['owner_full_name']),
                        'title_link' => $aRow['link']
                    ]
                );
            } else {
                $aRow['text'] = _p('added_a_new_comment_on_a_href_item_user_link_item_user_name_s_album', [
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

    public function getFeedRedirectAlbum($iId)
    {
        $aRow = $this->database()->select('m.album_id, m.name')
            ->from(Phpfox::getT('music_album'), 'm')
            ->where('m.album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aRow['album_id'])) {
            return false;
        }

        return Phpfox::permalink('music.album', $aRow['album_id'], $aRow['name']);
    }

    public function getReportRedirectAlbum($iId)
    {
        return $this->getFeedRedirectAlbum($iId);
    }

    public function getReportRedirectSong($iId)
    {
        return $this->getFeedRedirectSong($iId);
    }

    public function getFeedRedirectSong($iId)
    {
        $aRow = $this->database()->select('m.song_id, m.title')
            ->from(Phpfox::getT('music_song'), 'm')
            ->where('m.view_id != 2 AND m.song_id = ' . (int)$iId)
            ->execute('getSlaveRow');;

        if (!isset($aRow['song_id'])) {
            return false;
        }

        return Phpfox::permalink('music', $aRow['song_id'], $aRow['title']);
    }

    public function deleteCommentSong($iId)
    {
        $this->database()->updateCounter('music_song', 'total_comment', 'song_id', $iId, true);
    }

    public function deleteCommentAlbum($iId)
    {
        $this->database()->updateCounter('music_album', 'total_comment', 'album_id', $iId, true);
    }

    public function getBlockDetailsSong()
    {
        return [
            'title' => _p('songs')
        ];
    }

    public function getBlockDetailsProfile()
    {
        return [
            'title' => _p('favorite_songs')
        ];
    }

    public function hideBlockSong($sType)
    {
        return [
            'table' => 'user_design_order'
        ];
    }

    /**
     * Action to take when user cancelled their account
     *
     * @param int $iUser
     */
    public function onDeleteUser($iUser)
    {
        // delete albums (it runs a delete on the songs as well)
        $aAlbums = $this->database()
            ->select('album_id')
            ->from(Phpfox::getT('music_album'))
            ->where('user_id = ' . (int)$iUser)
            ->execute('getSlaveRows');
        foreach ($aAlbums as $aAlbum) {
            Phpfox::getService('music.album.process')->delete($aAlbum['album_id']);
        }

        // delete songs
        $aSongs = $this->database()
            ->select('song_id')
            ->from(Phpfox::getT('music_song'))
            ->where('user_id = ' . (int)$iUser)
            ->execute('getSlaveRows');

        foreach ($aSongs as $aSong) {
            Phpfox::getService('music.process')->delete($aSong['song_id']);
        }
    }

    /**
     * This callback will be called when a page or group be deleted
     *
     * @param $iId
     * @param $sType
     */

    public function onDeletePage($iId, $sType)
    {
        $aSongs = db()->select('song_id')->from(':music_song')->where([
            'module_id' => $sType,
            'item_id'   => $iId
        ])->executeRows();
        foreach ($aSongs as $aSong) {
            Phpfox::getService('music.process')->delete($aSong['song_id']);
        }
    }

    public function getNotificationFeedSongApproved($aRow)
    {
        return [
            'message' => _p('your_song_title_has_been_approved',
                ['title' => Phpfox::getLib('parse.output')->shorten($aRow['item_title'], 20, '...')]),
            'link'    => \Phpfox_Url::instance()->makeUrl('music.browse.song', ['redirect' => $aRow['item_id']])
        ];
    }

    public function getNotificationFeedSong_Album($aRow)
    {
        return [
            'message' => _p('a_href_user_link_full_name_a_likes_your_a_href_link_music_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['full_name']),
                    'user_link' => \Phpfox_Url::instance()->makeUrl($aRow['user_name']),
                    'link'      => \Phpfox_Url::instance()->makeUrl('music', ['redirect' => $aRow['item_id']])
                ]
            ),
            'link'    => \Phpfox_Url::instance()->makeUrl('music', ['redirect' => $aRow['item_id']])
        ];
    }

    public function getItemView()
    {
        if (Phpfox_Request::instance()->get('req3') != '') {
            return true;
        }

        return false;
    }

    public function pendingApproval()
    {
        return [
            'phrase' => _p('music'),
            'value'  => Phpfox::getService('music')->getPendingTotal(),
            'link'   => \Phpfox_Url::instance()->makeUrl('music', ['view' => 'pending'])
        ];
    }

    public function getAdmincpAlertItems()
    {
        $iTotalPending = Phpfox::getService('music')->getPendingTotal();
        return [
            'message' => _p('you_have_total_pending_songs', ['total' => $iTotalPending]),
            'value'   => $iTotalPending,
            'link'    => \Phpfox_Url::instance()->makeUrl('music', ['view' => 'pending'])
        ];
    }

    public function getDashboardLinks()
    {
        if (!Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false)) {
            return false;
        }

        return [
            'submit' => [
                'phrase' => _p('share_songs'),
                'link'   => 'music.upload',
                'image'  => 'module/music_add.png'
            ],
            'edit'   => [
                'phrase' => _p('manage_songs'),
                'link'   => 'music.browse.song.view_my',
                'image'  => 'module/music_edit.png'
            ]
        ];
    }

    public function reparserList()
    {
        return [
            'name'       => _p('music_album_text'),
            'table'      => 'music_album_text',
            'original'   => 'text',
            'parsed'     => 'text_parsed',
            'item_field' => 'album_id'
        ];
    }

    public function getNewsFeedSong($aRow)
    {
        if ($sPlugin = \Phpfox_Plugin::get('music.service_callback_getnewsfeedsong_start')) {
            eval($sPlugin);
        }
        $aRow['text'] = _p('full_name_uploaded_a_new_song', [
                'full_name'    => $this->preParse()->clean($aRow['owner_full_name']),
                'profile_link' => \Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                'title'        => Phpfox::getService('feed')->shortenTitle($aRow['content']),
                'link'         => $aRow['link']
            ]
        );
        $aRow['icon'] = 'module/music.png';
        $aRow['enable_like'] = true;

        return $aRow;
    }

    public function getNewsFeedSong_Album($aRow)
    {
        if ($sPlugin = \Phpfox_Plugin::get('music.song_album_service_callback_getnewsfeed_start')) {
            eval($sPlugin);
        }
        $aContent = unserialize($aRow['content']);

        $aRow['text'] = _p('full_name_uploaded_a_new_song_to_the_album', [
                'full_name'    => $this->preParse()->clean($aRow['owner_full_name']),
                'profile_link' => \Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                'title'        => Phpfox::getService('feed')->shortenTitle($aContent['title']),
                'album_title'  => Phpfox::getService('feed')->shortenTitle($aContent['album']['name']),
                'album_link'   => \Phpfox_Url::instance()->makeUrl($aRow['owner_user_name'], ['music']),
                'link'         => $aRow['link']
            ]
        );
        $aRow['icon'] = 'module/music.png';
        $aRow['enable_like'] = true;

        return $aRow;
    }

    public function getFeedRedirectSong_Feedlike($iId, $iChild)
    {
        return $this->getFeedRedirectSong($iChild);
    }

    public function getFeedRedirectSong_Album_FeedLike($iId, $iChild)
    {
        return $this->getFeedRedirectSong($iChild);
    }

    public function getFeedRedirectSong_Album($iId)
    {
        return $this->getFeedRedirectSong($iId);
    }

    public function verifyFavoriteSong($iItemId)
    {
        return true;
    }

    public function verifyFavoriteAlbum($iItemId)
    {
        return true;
    }

    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        return [
            'merge_result' => true,
            'result'       => [
                'music'       => [
                    'phrase' => _p('songs'),
                    'value'  => $this->database()->select('COUNT(*)')
                        ->from(Phpfox::getT('music_song'))
                        ->where('view_id = 0 AND time_stamp >= ' . $iToday)
                        ->execute('getSlaveField')
                ],
                'music_album' => [
                    'phrase' => _p('music_albums'),
                    'value'  => $this->database()->select('COUNT(*)')
                        ->from(Phpfox::getT('music_album'))
                        ->where('view_id = 0 AND time_stamp >= ' . $iToday)
                        ->execute('getSlaveField')
                ]
            ]
        ];
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
     *    'error' => 'phrase if item doesnt exit'        <-- optional
     *    'extra' => 'description'            <-- optional
     *    'image' => 'path to an image',            <-- optional
     *    'image_dir' => 'photo.url_photo|...        <-- optional (required if image)
     *    'server_id' => value from DB            <-- optional (required if image)
     * )
     */
    public function getToSponsorInfoAlbum($iId)
    {
        $aAlbum = $this->database()->select('ma.name, ma.image_path as image, ma.server_id, ma.album_id, album_id as item_id, ma.user_id')
            ->from(Phpfox::getT('music_album'), 'ma')
            ->where('ma.album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aAlbum)) {
            return ['error' => _p('sponsor_error_album_not_found')];
        }

        $aAlbum['title'] = _p('album_sponsor_title', ['sAlbumTitle' => $aAlbum['name']]);
        $aAlbum['paypal_msg'] = _p('album_sponsor_paypal_message', ['sAlbumTitle' => $aAlbum['name']]);
        $aAlbum['link'] = Phpfox::permalink('music', $aAlbum['item_id'], $aAlbum['title']);
        $aAlbum['image_dir'] = 'music.url_image';
        $aAlbum['image'] = sprintf($aAlbum['image'], '_200_square');
        $aAlbum = array_merge($aAlbum, [
            'redirect_completed'        => 'music',
            'message_completed'         => _p('purchase_album_sponsor_completed'),
            'redirect_pending_approval' => 'music.browse.album',
            'message_pending_approval'  => _p('purchase_album_sponsor_pending_approval')
        ]);
        return $aAlbum;
    }

    public function getToSponsorInfoSong($iId)
    {
        $aSong = $this->database()->select('ms.user_id, ms.title, ms.song_id as item_id, ma.name, ma.image_path as image, ma.server_id, ma.album_id')
            ->from(Phpfox::getT('music_song'), 'ms')
            ->leftJoin(Phpfox::getT('music_album'), 'ma', 'ms.album_id = ma.album_id')
            ->where('ms.view_id != 2 AND ms.song_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aSong)) {
            return ['error' => _p('sponsor_error_song_not_found')];
        }

        $aSong['title'] = _p('song_sponsor_title', ['sSongTitle' => $aSong['title']]);
        $aSong['paypal_msg'] = _p('song_sponsor_paypal_message', ['sSongTitle' => $aSong['title']]);
        $aSong['link'] = Phpfox::permalink('music', $aSong['item_id'], $aSong['title']);

        $aSong = array_merge($aSong, [
            'redirect_completed'        => 'music',
            'message_completed'         => _p('purchase_song_sponsor_completed'),
            'redirect_pending_approval' => 'music',
            'message_pending_approval'  => _p('purchase_song_sponsor_pending_approval')
        ]);

        return $aSong;
    }

    public function updateCounterList()
    {
        $aList = [];

        $aList[] = [
            'name' => _p('music_album_track_count'),
            'id'   => 'music_album'
        ];

        $aList[] = [
            'name' => _p('update_user_song_count'),
            'id'   => 'user-count'
        ];

        return $aList;
    }

    public function updateCounter($iId, $iPage, $iPageLimit)
    {
        if ($iId == 'user-count') {
            $iCnt = $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('user'))
                ->execute('getSlaveField');

            $aRows = $this->database()->select('u.user_id')
                ->from(Phpfox::getT('user'), 'u')
                ->limit($iPage, $iPageLimit, $iCnt)
                ->group('u.user_id')
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                $iTotalPhotos = $this->database()->select('COUNT(m.song_id)')
                    ->from(Phpfox::getT('music_song'), 'm')
                    ->where('m.view_id = 0 AND m.user_id = ' . $aRow['user_id'])
                    ->execute('getSlaveField');

                $this->database()->update(Phpfox::getT('user_field'), ['total_song' => $iTotalPhotos],
                    'user_id = ' . $aRow['user_id']);
            }

            return $iCnt;
        }

        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('music_album'))
            ->execute('getSlaveField');

        $aRows = $this->database()->select('g.album_id, COUNT(gi.song_id) AS total_items')
            ->from(Phpfox::getT('music_album'), 'g')
            ->leftJoin(Phpfox::getT('music_song'), 'gi', 'gi.album_id = g.album_id AND gi.view_id != 2')
            ->group('g.album_id')
            ->limit($iPage, $iPageLimit, $iCnt)
            ->execute('getSlaveRows');

        foreach ($aRows as $aRow) {
            $this->database()->update(Phpfox::getT('music_album'), ['total_track' => $aRow['total_items']],
                'album_id = ' . (int)$aRow['album_id']);
        }

        return $iCnt;
    }

    public function getNewsFeedSong_Album_Feedlike($aRow)
    {
        if ($aRow['owner_user_id'] == $aRow['viewer_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_their_own_a_href_link_song_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link' => \Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                    'link'      => $aRow['link']
                ]
            );
        } else {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_a_href_view_user_link_view_full_name_a_s_a_href_link_song_a',
                [
                    'full_name'      => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link'      => \Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                    'view_full_name' => Phpfox::getLib('parse.output')->clean($aRow['viewer_full_name']),
                    'view_user_link' => \Phpfox_Url::instance()->makeUrl($aRow['viewer_user_name']),
                    'link'           => $aRow['link']
                ]
            );
        }

        $aRow['icon'] = 'misc/thumb_up.png';

        return $aRow;
    }

    public function getNewsFeedSong_FeedLike($aRow)
    {
        if ($aRow['owner_user_id'] == $aRow['viewer_user_id']) {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_their_own_a_href_link_song_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link' => \Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                    'link'      => $aRow['link']
                ]
            );
        } else {
            $aRow['text'] = _p('a_href_user_link_full_name_a_liked_a_href_view_user_link_view_full_name_a_s_a_href_link_song_a',
                [
                    'full_name'      => Phpfox::getLib('parse.output')->clean($aRow['owner_full_name']),
                    'user_link'      => \Phpfox_Url::instance()->makeUrl($aRow['owner_user_name']),
                    'view_full_name' => Phpfox::getLib('parse.output')->clean($aRow['viewer_full_name']),
                    'view_user_link' => \Phpfox_Url::instance()->makeUrl($aRow['viewer_user_name']),
                    'link'           => $aRow['link']
                ]
            );
        }

        $aRow['icon'] = 'misc/thumb_up.png';

        return $aRow;
    }

    public function getNotificationFeedSong_Album_NotifyLike($aRow)
    {
        return [
            'message' => _p('a_href_user_link_full_name_a_liked_your_a_href_link_song_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean($aRow['full_name']),
                    'user_link' => \Phpfox_Url::instance()->makeUrl($aRow['user_name']),
                    'link'      => \Phpfox_Url::instance()->makeUrl('music.browse.song',
                        ['redirect' => $aRow['item_id']])
                ]
            ),
            'link'    => \Phpfox_Url::instance()->makeUrl('music.browse.song', ['redirect' => $aRow['item_id']])
        ];
    }

    public function sendLikeEmailSong_Album($iItemId, $aFeed = [])
    {
        if (isset($aFeed['user_name']) && $aFeed['user_name'] != '' && isset($aFeed['feed_id']) && $aFeed['feed_id'] != '') {
            return _p('a_href_user_link_full_name_a_liked_your_a_href_link_song_a', [
                    'full_name' => Phpfox::getLib('parse.output')->clean(Phpfox::getUserBy('full_name')),
                    'user_link' => \Phpfox_Url::instance()->makeUrl(Phpfox::getUserBy('user_name')),
                    'link'      => \Phpfox_Url::instance()->makeUrl($aFeed['user_name'],
                            ['feed' => $aFeed['feed_id']]) . '#feed'
                ]
            );
        }
        return _p('a_href_user_link_full_name_a_liked_your_a_href_link_song_a', [
                'full_name' => Phpfox::getLib('parse.output')->clean(Phpfox::getUserBy('full_name')),
                'user_link' => \Phpfox_Url::instance()->makeUrl(Phpfox::getUserBy('user_name')),
                'link'      => \Phpfox_Url::instance()->makeUrl('music.browse.song', ['redirect' => $iItemId])
            ]
        );
    }

    public function getNotificationFeedSong_NotifyLike($aRow)
    {
        return $this->getNotificationFeedSong_Album_NotifyLike($aRow);
    }

    public function sendLikeEmailSong($iItemId, $aFeed)
    {
        return $this->sendLikeEmailSong_Album($iItemId, $aFeed);
    }

    public function getRedirectCommentSong($iId)
    {
        return $this->getFeedRedirectSong($iId);
    }

    public function getRedirectCommentAlbum($iId)
    {
        return $this->getFeedRedirectAlbum($iId);
    }

    public function canShareItemOnFeed()
    {
    }

    public function getActivityFeedCustomChecksSong($aRow)
    {
        if ((defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null,
                    'music.view_browse_music'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['custom_data_cache']['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['custom_data_cache']['item_id'],
                    'music.view_browse_music'))
        ) {
            return false;
        }

        return $aRow;
    }

    public function getActivityFeedSong($aItem, $aCallback = null, $bIsChildItem = false)
    {
        $aSong = Phpfox::getService('music')->getSong($aItem['item_id']);
        $iFeedId = isset($aItem['feed_id']) ? $aItem['feed_id'] : 0;

        /**
         * Check active parent module
         */
        if (!empty($aSong['module_id']) && !Phpfox::isModule($aSong['module_id'])) {
            return false;
        }

        $iCacheFeedId = $iFeedId;
        $aFeedCallback = null;
        $sFeedTable = (!empty($aCallback['table_prefix']) ? $aCallback['table_prefix'] : '') . 'feed';
        if (empty($aCallback) && !empty($aSong['module_id']) && !empty($aSong['item_id'])
            && Phpfox::hasCallback($aSong['module_id'], 'getFeedDetails')) {
            $aFeedCallback = Phpfox::callback($aSong['module_id'] . '.getFeedDetails', $aSong['item_id']);
            $sFeedTable = $aFeedCallback['table_prefix'] . 'feed';
            $oCache = storage()->get('music_song_parent_feed_' . $iFeedId);
            if (is_object($oCache) && !empty($oCache->value)) {
                $iCacheFeedId = $oCache->value;
            } else {
                $iCacheFeedId = (int)db()->select('mf.feed_id')
                    ->from(':feed', 'f')
                    ->join(':' . $aFeedCallback['table_prefix'] . 'feed', 'mf', 'mf.type_id = f.type_id AND mf.item_id = f.item_id')
                    ->where([
                        'f.feed_id' => $iFeedId,
                    ])->executeField();
            }
        }

        $this->database()->select('ma.name AS album_name, ma.album_id, u.gender, ')
            ->leftJoin(':music_album', 'ma', 'ma.album_id = ms.album_id')
            ->leftJoin(':user', 'u', 'u.user_id = ma.user_id');

        $this->database()->select('mp.play_id AS is_on_profile, ')->leftJoin(Phpfox::getT('music_profile'), 'mp',
            'mp.song_id = ms.song_id AND mp.user_id = ' . Phpfox::getUserId());

        if ($bIsChildItem) {
            $this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
                'u2.user_id = ms.user_id');
        }

        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(':like', 'l',
                    'l.type_id = \'music_song\' AND l.item_id = ms.song_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aRow = $this->database()->select('ms.*,mf.song_id as extra_song_id')
            ->from(':music_song', 'ms')
            ->leftJoin(':music_feed', 'mf', 'mf.feed_id =' . (int)$iCacheFeedId)
            ->where('ms.song_id = ' . (int)$aItem['item_id'])
            ->execute('getSlaveRow');
        if (!isset($aRow['song_id'])) {
            return false;
        }

        if ($bIsChildItem) {
            $aItem = array_merge($aRow, $aItem);
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null,
                    'music.view_browse_music'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages') && !Phpfox::getService('pages')->hasPerm($aRow['item_id'],
                    'music.view_browse_music'))
            || ($aRow['module_id'] && Phpfox::isModule($aRow['module_id']) && Phpfox::hasCallback($aRow['module_id'],
                    'canShareOnMainFeed') && !Phpfox::callback($aRow['module_id'] . '.canShareOnMainFeed',
                    $aRow['item_id'], 'music.view_browse_music', $bIsChildItem))
        ) {
            return false;
        }

        $bShowAlbumTitle = false;
        if (!empty($aRow['album_name'])) {
            $bShowAlbumTitle = true;
        }
        $aRow['is_in_feed'] = true;
        $aRow['song_path'] = Phpfox::getService('music')->getSongPath($aRow['song_path'], $aRow['server_id']);

        $aRows[] = $aRow;
        $iTotalUploaded = 1;
        if ($aRow['extra_song_id'] > 0) {
            $aCond[] = 'AND (mf.feed_id =' . (int)$iCacheFeedId . ' AND mf.feed_table = "' . $sFeedTable . '")';

            if ($aItem['user_id'] == Phpfox::getUserId()) {
                $aCond[] = 'AND ms.privacy IN(0,1,2,3,4,6)';
            } else {
                $sExtraPrivacy = Phpfox::isUser() ? ',6' : '';
                $oUserObject = Phpfox::getService('user')->getUserObject($aItem['user_id']);
                if (isset($oUserObject->is_friend) && $oUserObject->is_friend) {
                    $aCond[] = 'AND ms.privacy IN(0,1,2' . $sExtraPrivacy . ')';
                } else {
                    if (isset($oUserObject->is_friend_of_friend) && $oUserObject->is_friend_of_friend) {
                        $aCond[] = 'AND ms.privacy IN(0,2' . (!Phpfox::getParam('core.friends_only_community') ? $sExtraPrivacy : '') . ')';
                    } else {
                        $aCond[] = 'AND ms.privacy IN(0' . (!Phpfox::getParam('core.friends_only_community') ? $sExtraPrivacy : '') . ')';
                    }
                }
            }

            $aListSongs = db()->select('ma.name AS album_name, ma.album_id, u.gender, ms.*')
                ->from(':music_feed', 'mf')
                ->join(':music_song', 'ms', 'ms.song_id = mf.song_id')
                ->leftJoin(':music_album', 'ma', 'ma.album_id = ms.album_id')
                ->leftJoin(':user', 'u', 'u.user_id = ma.user_id')
                ->where($aCond)
                ->execute('getSlaveRows');
            $iTotalUploaded = $iTotalUploaded + count($aListSongs);
            if ($iTotalUploaded - 1) {
                for ($i = 0; $i < 2; $i++) {
                    if (!isset($aListSongs[$i])) {
                        continue;
                    }
                    $aListSongs[$i]['is_in_feed'] = true;
                    $aListSongs[$i]['song_path'] = Phpfox::getService('music')->getSongPath($aListSongs[$i]['song_path'],
                        $aListSongs[$i]['server_id']);
                }
                $aRows = ($iTotalUploaded > 2) ? [$aRow, $aListSongs[0], $aListSongs[1]] : [
                    $aRow,
                    $aListSongs[0]
                ];
            }
        }
        \Phpfox_Template::instance()->assign('aSongs', $aRows);
        \Phpfox_Component::setPublicParam('custom_param_' . $aItem['feed_id'], $aRows);
        if ($bShowAlbumTitle) {
            if ($iTotalUploaded > 1) {
                $sTitle = _p('shared_number_songs_from_gender_album_a_href_album_link_album_name_a', [
                    'number'     => $iTotalUploaded,
                    'gender'     => Phpfox::getService('user')->gender($aRow['gender'], 1),
                    'album_link' => \Phpfox_Url::instance()->permalink('music.album', $aRow['album_id'],
                        $aRow['album_name']),
                    'album_name' => Phpfox::getLib('parse.output')->shorten($aRow['album_name'],
                        (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                        '...')
                ]);
            } else {
                $sTitle = _p('shared_a_song_from_gender_album_a_href_album_link_album_name_a', [
                    'gender'     => Phpfox::getService('user')->gender($aRow['gender'], 1),
                    'album_link' => \Phpfox_Url::instance()->permalink('music.album', $aRow['album_id'],
                        $aRow['album_name']),
                    'album_name' => Phpfox::getLib('parse.output')->shorten($aRow['album_name'],
                        (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                        '...')
                ]);
            }
        } else {
            if ($iTotalUploaded > 1) {
                $sTitle = _p('shared_number_songs', [
                    'number' => '<a href=\'' . Phpfox::getLib('url')->makeUrl('music',
                            ['user' => $aItem['user_id']]) . '\'>' . $iTotalUploaded . '</a>'
                ]);
            } else {
                $sTitle = _p('shared_a_song');
            }
        }
        $aReturn = [
            'feed_title'        => '',
            'feed_info'         => $sTitle,
            'feed_link'         => Phpfox::permalink('music', $aRow['song_id'], $aRow['title']),
            'total_comment'     => $aRow['total_comment'],
            'feed_total_like'   => $aRow['total_like'],
            'feed_is_liked'     => (isset($aRow['is_liked']) ? $aRow['is_liked'] : false),
            'feed_icon'         => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/music.png',
                'return_url' => true
            ]),
            'time_stamp'        => $aRow['time_stamp'],
            'enable_like'       => true,
            'comment_type_id'   => 'music_song',
            'like_type_id'      => 'music_song',
            'load_block'        => 'music.rows',
            'custom_data_cache' => $aRow
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
        (($sPlugin = \Phpfox_Plugin::get('music.component_service_callback_getactivityfeedsong__1')) ? eval($sPlugin) : false);
        return $aReturn;
    }

    public function checkFeedShareLink()
    {
        if (!Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false)) {
            return false;
        }
        return null;
    }

    public function addLikeSong($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = $this->database()->select('song_id, title, user_id')
            ->from(Phpfox::getT('music_song'))
            ->where('view_id != 2 AND song_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['song_id'])) {
            return false;
        }

        $this->database()->updateCount('like', 'type_id = \'music_song\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'music_song', 'song_id = ' . (int)$iItemId);

        if (!$bDoNotSendEmail) {
            $sLink = Phpfox::permalink('music', $aRow['song_id'], $aRow['title']);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'full_name_liked_your_song_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ])
                ->message([
                    'full_name_liked_your_song_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aRow['title']]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('music_song_like', $aRow['song_id'], $aRow['user_id']);
        }
        return null;
    }

    public function deleteLikeSong($iItemId)
    {
        $this->database()->updateCount('like', 'type_id = \'music_song\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'music_song', 'song_id = ' . (int)$iItemId);
    }

    public function getNotificationSong_Like($aNotification)
    {
        $aRow = $this->database()->select('ms.song_id, ms.title, ms.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('music_song'), 'ms')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ms.user_id')
            ->where('ms.view_id != 2 AND ms.song_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['song_id'])) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('user_name_liked_gender_own_song_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_liked_your_song_title', [
                'users' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('user_name_liked_span_class_drop_data_user_full_name_s_span_song_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music', $aRow['song_id'], $aRow['title']),
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getCommentNotificationSong($aNotification)
    {
        $aRow = $this->database()->select('l.song_id, l.title, u.user_id, u.gender, u.user_name, u.full_name')
            ->from(Phpfox::getT('music_song'), 'l')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
            ->where('l.view_id != 2 AND l.song_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (empty($aRow)) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users'])) {
            $sPhrase = _p('users_commented_on_gender_song_title', [
                'users'  => Phpfox::getService('notification')->getUsers($aNotification),
                'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'  => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_commented_on_your_song_title', [
                'users' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('user_name_commented_on_span_class_drop_data_user_full_name_s_span_song_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music', $aRow['song_id'], $aRow['title']),
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function addLikeAlbum($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = $this->database()->select('album_id, name, user_id')
            ->from(Phpfox::getT('music_album'))
            ->where('album_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['album_id'])) {
            return false;
        }

        $this->database()->updateCount('like', 'type_id = \'music_album\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'music_album', 'album_id = ' . (int)$iItemId);

        if (!$bDoNotSendEmail) {
            $sLink = Phpfox::permalink('music.album', $aRow['album_id'], $aRow['name']);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'full_name_liked_your_album_name',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'name' => $aRow['name']]
                ])
                ->message([
                    'full_name_liked_your_album_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'name' => $aRow['name']]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('music_album_like', $aRow['album_id'], $aRow['user_id']);
        }
        return null;
    }


    public function deleteLikeAlbum($iItemId)
    {
        $this->database()->updateCount('like', 'type_id = \'music_album\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'music_album', 'album_id = ' . (int)$iItemId);
    }


    public function getNotificationAlbum_Like($aNotification)
    {
        $aRow = $this->database()->select('ms.album_id, ms.name, ms.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('music_album'), 'ms')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ms.user_id')
            ->where('ms.album_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['album_id'])) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('user_name_liked_gender_own_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('user_name_liked_your_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('user_name_liked_span_class_drop_data_user_full_name_s_span_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music.album', $aRow['album_id'], $aRow['name']),
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getCommentNotificationAlbum($aNotification)
    {
        $aRow = $this->database()->select('l.album_id, l.name, u.user_id, u.gender, u.user_name, u.full_name')
            ->from(Phpfox::getT('music_album'), 'l')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
            ->where('l.album_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (empty($aRow)) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users'])) {
            $sPhrase = _p('user_name_commented_on_gender_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('user_name_commented_on_your_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('user_name_commented_on_span_class_drop_data_user_full_name_s_album_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music.album', $aRow['album_id'], $aRow['name']),
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }


    public function addLikePlaylist($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = $this->database()->select('playlist_id, name, user_id')
            ->from(Phpfox::getT('music_playlist'))
            ->where('playlist_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['playlist_id'])) {
            return false;
        }

        $this->database()->updateCount('like', 'type_id = \'music_playlist\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'music_playlist', 'playlist_id = ' . (int)$iItemId);

        if (!$bDoNotSendEmail) {
            $sLink = Phpfox::permalink('music.playlist', $aRow['playlist_id'], $aRow['name']);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'full_name_liked_your_music_playlist_name',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'name' => $aRow['name']]
                ])
                ->message([
                    'full_name_liked_your_music_playlist_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'name' => $aRow['name']]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('music_playlist_like', $aRow['playlist_id'], $aRow['user_id']);
        }
        return null;
    }


    public function deleteLikePlaylist($iItemId)
    {
        $this->database()->updateCount('like', 'type_id = \'music_playlist\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'music_playlist', 'playlist_id = ' . (int)$iItemId);
    }

    public function getNotificationPlaylist_Like($aNotification)
    {
        $aRow = $this->database()->select('p.playlist_id, p.name, p.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('music_playlist'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.playlist_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['playlist_id'])) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('music_user_name_liked_gender_own_playlist_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('music_user_name_liked_your_playlist_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('music_user_name_liked_span_class_drop_data_user_full_name_s_span_playlist_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music.playlist', $aRow['playlist_id'], $aRow['name']),
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getCommentNotificationPlaylist($aNotification)
    {
        $aRow = $this->database()->select('l.playlist_id, l.name, u.user_id, u.gender, u.user_name, u.full_name')
            ->from(Phpfox::getT('music_playlist'), 'l')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
            ->where('l.playlist_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (empty($aRow)) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users'])) {
            $sPhrase = _p('music_user_name_commented_on_gender_playlist_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'gender'    => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('music_user_name_commented_on_your_playlist_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        } else {
            $sPhrase = _p('music_user_name_commented_on_span_class_drop_data_user_full_name_s_playlist_title', [
                'user_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'full_name' => $aRow['full_name'],
                'title'     => Phpfox::getLib('parse.output')->shorten($aRow['name'],
                    (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                    '...')
            ]);
        }

        return [
            'link'    => Phpfox_Url::instance()->permalink('music.playlist', $aRow['playlist_id'], $aRow['name']),
            'message' => $sPhrase,
            'icon'    => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }


    public function getActivityFeedAlbum($aItem, $aCallback = null, $bIsChildItem = false)
    {
        $aAlbum = Phpfox::getService('music.album')->getAlbum($aItem['item_id']);
        /**
         * Check active parent module
         */
        if (!empty($aAlbum['module_id']) && !Phpfox::isModule($aAlbum['module_id'])) {
            return false;
        }

        if (Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'music_album\' AND l.item_id = ma.album_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if ($bIsChildItem) {
            db()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
                'u2.user_id = ma.user_id');
        }
        $aRow = db()->select('ma.*, mat.*')
            ->from(Phpfox::getT('music_album'), 'ma')
            ->join(Phpfox::getT('music_album_text'), 'mat', 'mat.album_id = ma.album_id')
            ->where('ma.album_id = ' . (int)$aItem['item_id'])
            ->execute('getSlaveRow');
        if (!isset($aRow['album_id'])) {
            return false;
        }

        $aRow['is_in_feed'] = true;

        if ((defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null,
                    'music.view_browse_music'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages') && !Phpfox::getService('pages')->hasPerm($aRow['item_id'],
                    'music.view_browse_music'))
            || ($aRow['module_id'] && Phpfox::isModule($aRow['module_id']) && Phpfox::hasCallback($aRow['module_id'],
                    'canShareOnMainFeed') && !Phpfox::callback($aRow['module_id'] . '.canShareOnMainFeed',
                    $aRow['item_id'], 'music.view_browse_music', $bIsChildItem))
        ) {
            return false;
        }
        $sImage = '<img src="' . Phpfox::getParam('music.default_album_photo') . '"</img>';
        if (!empty($aRow['image_path'])) {
            $sImage = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aRow['server_id'],
                    'path'      => 'music.url_image',
                    'file'      => $aRow['image_path'],
                    'suffix'    => '_200_square',
                    'class'     => 'photo_holder',
                    'userid'    => isset($aRow['user_id']) ? $aRow['user_id'] : '',
                ]
            );
        }
        Phpfox_Template::instance()->assign('aAlbum', $aRow);
        Phpfox_Component::setPublicParam('custom_param_' . $aItem['feed_id'], $aRow);
        $aReturn = [
            'feed_title'      => $aRow['name'],
            'feed_info'       => _p('added_new_music_album'),
            'feed_status'     => '',
            'feed_link'       => Phpfox::permalink('music.album', $aRow['album_id'], $aRow['name']),
            'feed_content'    => $aRow['text_parsed'],
            'total_comment'   => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked'   => (isset($aRow['is_liked']) ? $aRow['is_liked'] : false),
            'feed_icon'       => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/music.png',
                'return_url' => true
            ]),
            'time_stamp'      => $aRow['time_stamp'],
            'enable_like'     => true,
            'comment_type_id' => 'music_album',
            'like_type_id'    => 'music_album',
            'load_block'      => 'music.album-rows'
        ];
        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
            $aReturn['feed_image'] = $sImage;
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
        (($sPlugin = \Phpfox_Plugin::get('music.component_service_callback_getactivityfeedalbum__1')) ? eval($sPlugin) : false);
        return $aReturn;
    }

    public function getActivityFeedPlaylist($aItem, $aCallback = null, $bIsChildItem = false)
    {
        if (Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'music_playlist\' AND l.item_id = ma.playlist_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if ($bIsChildItem) {
            db()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
                'u2.user_id = ma.user_id');
        }
        $aRow = db()->select('ma.*')
            ->from(Phpfox::getT('music_playlist'), 'ma')
            ->where('ma.playlist_id = ' . (int)$aItem['item_id'])
            ->execute('getSlaveRow');
        if (!isset($aRow['playlist_id'])) {
            return false;
        }

        $aRow['is_in_feed'] = true;

        $sImage = '<img src="' . Phpfox::getParam('music.default_playlist_photo') . '"></img>';
        if (!empty($aRow['image_path'])) {
            $sImage = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aRow['server_id'],
                    'path'      => 'music.url_image',
                    'file'      => $aRow['image_path'],
                    'suffix'    => '_200_square',
                    'class'     => 'photo_holder',
                    'userid'    => isset($aRow['user_id']) ? $aRow['user_id'] : '',
                ]
            );
        }
        Phpfox_Template::instance()->assign('aPlaylist', $aRow);
        Phpfox_Component::setPublicParam('custom_param_' . $aItem['feed_id'], $aRow);
        $aReturn = [
            'feed_title'      => $aRow['name'],
            'feed_info'       => _p('added_new_music_playlist'),
            'feed_status'     => '',
            'feed_link'       => Phpfox::permalink('music.playlist', $aRow['playlist_id'], $aRow['name']),
            'feed_content'    => $aRow['description_parsed'],
            'total_comment'   => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked'   => (isset($aRow['is_liked']) ? $aRow['is_liked'] : false),
            'feed_icon'       => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/music.png',
                'return_url' => true
            ]),
            'time_stamp'      => $aRow['time_stamp'],
            'enable_like'     => true,
            'comment_type_id' => 'music_playlist',
            'like_type_id'    => 'music_playlist',
            'load_block'      => 'music.playlist-feed'
        ];
        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aRow);
            $aReturn['feed_image'] = $sImage;
            $aReturn['feed_id'] = $aItem['feed_id'];
        }
        (($sPlugin = \Phpfox_Plugin::get('music.component_service_callback_getactivityfeedalbum__1')) ? eval($sPlugin) : false);
        return $aReturn;
    }


    public function getNotificationSongapproved($aNotification)
    {
        $aRow = $this->database()->select('b.song_id, b.title, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('music_song'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.song_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['song_id'])) {
            return false;
        }

        $sPhrase = _p('your_song_title_has_been_approved', [
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength),
                '...')
        ]);

        return [
            'link'             => \Phpfox_Url::instance()->permalink('music', $aRow['song_id'], $aRow['title']),
            'message'          => $sPhrase,
            'icon'             => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog'),
            'no_profile_image' => true
        ];
    }

    public function getAjaxProfileController()
    {
        return 'music.index';
    }

    public function getProfileMenu($aUser)
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return false;
        }
        $countResult = $this->getTotalItemCountSong($aUser['user_id']);
        if (!empty($countResult)) {
            $aUser['total_song'] = $countResult['total'];
        }
        if (!Phpfox::getParam('profile.show_empty_tabs')) {
            if (empty($aUser['total_song'])) {
                return false;
            }

            if (isset($aUser['total_song']) && (int)$aUser['total_song'] === 0) {
                return false;
            }
        }

        $aMenus[] = [
            'phrase' => _p('music'),
            'url'    => 'profile.music',
            'total'  => (int)(isset($aUser['total_song']) ? $aUser['total_song'] : 0),
            'icon'   => 'feed/music.png'
        ];

        return $aMenus;
    }

    public function getTotalItemCountSong($iUserId)
    {
        return [
            'field' => 'total_song',
            'total' => $this->database()->select('COUNT(*)')->from(Phpfox::getT('music_song'))->where('view_id = 0 AND user_id = ' . (int)$iUserId . ' AND item_id = 0')->execute('getSlaveField')
        ];
    }

    public function globalUnionSearch($sSearch)
    {
        $sConds = Phpfox::getService('music')->getConditionsForSettingPageGroup('item');
        $this->database()->select('item.song_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'music\' AS item_type_id, item.image_path AS item_photo, item.image_server_id AS item_photo_server')
            ->from(Phpfox::getT('music_song'), 'item')
            ->where('item.view_id = 0 AND item.privacy = 0 AND ' . $this->database()->searchKeywords('item.title',
                    $sSearch) . $sConds)
            ->union();
        $this->database()->select('item.album_id AS item_id, item.name AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'music_album\' AS item_type_id, item.image_path AS item_photo, item.server_id AS item_photo_server')
            ->from(Phpfox::getT('music_album'), 'item')
            ->where('item.view_id = 0 AND item.privacy = 0 AND ' . $this->database()->searchKeywords('item.name',
                    $sSearch) . $sConds)
            ->union();
    }

    public function getSearchInfo($aRow)
    {
        $aInfo = [];
        $aInfo['item_link'] = \Phpfox_Url::instance()->permalink('music', $aRow['item_id'], $aRow['item_title']);
        $aInfo['item_name'] = _p('song');
        if (!empty($aRow['item_photo'])) {
            $aInfo['item_display_photo'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aRow['item_photo_server'],
                    'file'       => $aRow['item_photo'],
                    'path'       => 'music.url_image',
                    'suffix'     => '_200_square',
                    'max_width'  => '320',
                    'max_height' => '320'
                ]
            );
        } else {
            $aInfo['item_display_photo'] = '<img src="' . Phpfox::getParam('music.default_song_photo') . '"/>';
        }
        return $aInfo;
    }

    public function getSearchInfoAlbum($aRow)
    {
        $aInfo = [];
        $aInfo['item_link'] = \Phpfox_Url::instance()->permalink('music.album', $aRow['item_id'], $aRow['item_title']);
        $aInfo['item_name'] = _p('music_albums');
        if (!empty($aRow['item_photo'])) {
            $aInfo['item_display_photo'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aRow['item_photo_server'],
                    'file'       => $aRow['item_photo'],
                    'path'       => 'music.url_image',
                    'suffix'     => '_200_square',
                    'max_width'  => '320',
                    'max_height' => '320'
                ]
            );
        } else {
            $aInfo['item_display_photo'] = '<img src="' . Phpfox::getParam('music.default_album_photo') . '"/>';
        }
        return $aInfo;
    }

    public function getSearchTitleInfo()
    {
        return [
            'name' => _p('songs')
        ];
    }

    public function getSearchTitleInfoAlbum()
    {
        return [
            'name' => _p('music_albums')
        ];
    }

    public function getGlobalPrivacySettings()
    {
        return [
            'music.default_privacy_setting_song'     => [
                'phrase' => _p('music_songs')
            ],
            'music.default_privacy_setting_album'    => [
                'phrase' => _p('music_albums')
            ],
            'music.default_privacy_setting_playlist' => [
                'phrase' => _p('music_playlist')
            ]
        ];
    }

    public function getPageMenu($aPage)
    {
        (($sPlugin = \Phpfox_Plugin::get('music.service_callback_getpagemenu')) ? eval($sPlugin) : null);

        if (isset($bForceNoMusicOnPages)) {
            return false;
        }

        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'music.view_browse_music') || !Phpfox::getUserParam('music.can_access_music')) {
            return null;
        }

        $aMenus[] = [
            'phrase'  => _p('music'),
            'url'     => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']) . 'music/',
            'icon'    => 'feed/music.png',
            'landing' => 'music'
        ];

        return $aMenus;
    }

    public function getGroupMenu($aPage)
    {
        (($sPlugin = \Phpfox_Plugin::get('music.service_callback_getgroupmenu')) ? eval($sPlugin) : null);

        if (isset($bForceNoMusicOnGroups)) {
            return false;
        }

        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'], 'music.view_browse_music') || !Phpfox::getUserParam('music.can_access_music')) {
            return null;
        }

        $aMenus[] = [
            'phrase'  => _p('Music'),
            'url'     => Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']) . 'music/',
            'icon'    => 'feed/music.png',
            'landing' => 'music'
        ];

        return $aMenus;
    }

    public function canViewPageSection($iPage)
    {
        if (!Phpfox::getService('pages')->hasPerm($iPage, 'music.view_browse_music')) {
            return false;
        }

        return true;
    }

    public function getPageSubMenu($aPage)
    {
        $bCanUploadSong = Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false);
        $bCanUploadAlbum = Phpfox::getService('music.album')->canCreateNewAlbum(Phpfox::getUserId(), false);
        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'music.share_music') || (!$bCanUploadSong && !$bCanUploadAlbum)) {
            return null;
        }
        $aMenu = [];
        if ($bCanUploadSong) {
            $aMenu[] = [
                'phrase' => _p('share_songs'),
                'url'    => \Phpfox_Url::instance()->makeUrl('music.upload',
                    ['module' => 'pages', 'item' => $aPage['page_id']])
            ];
        }
        if ($bCanUploadAlbum) {
            $aMenu[] = [
                'phrase' => _p('add_an_album'),
                'url'    => \Phpfox_Url::instance()->makeUrl('music.album',
                    ['module' => 'pages', 'item' => $aPage['page_id']])
            ];
        }
        return $aMenu;
    }

    public function getGroupSubMenu($aPage)
    {
        $bCanUploadSong = Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false);
        $bCanUploadAlbum = Phpfox::getService('music.album')->canCreateNewAlbum(Phpfox::getUserId(), false);
        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'], 'music.share_music') || (!$bCanUploadSong && !$bCanUploadAlbum)) {
            return null;
        }
        $aMenu = [];
        if ($bCanUploadSong) {
            $aMenu[] = [
                'phrase' => _p('share_songs'),
                'url'    => \Phpfox_Url::instance()->makeUrl('music.upload',
                    ['module' => 'groups', 'item' => $aPage['page_id']])
            ];
        }
        if ($bCanUploadAlbum) {
            $aMenu[] = [
                'phrase' => _p('add_an_album'),
                'url'    => \Phpfox_Url::instance()->makeUrl('music.album',
                    ['module' => 'groups', 'item' => $aPage['page_id']])
            ];
        }
        return $aMenu;
    }

    public function getCommentNotificationSongTag($aNotification)
    {
        $aRow = $this->database()->select('ms.song_id, ms.title, u.user_name, u.full_name')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('music_song'), 'ms', 'ms.song_id = c.item_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');
        if (empty($aRow)) {
            return false;
        }


        $sPhrase = _p('user_name_tagged_you_in_a_comment_in_a_song', ['user_name' => $aRow['full_name']]);

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music', $aRow['song_id'],
                    $aRow['title']) . 'comment_' . $aNotification['item_id'],
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getCommentNotificationAlbumTag($aNotification)
    {
        $aRow = $this->database()->select('ma.album_id, ma.name, u.user_name, u.full_name')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('music_album'), 'ma', 'ma.album_id = c.item_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');
        if (empty($aRow)) {
            return false;
        }


        $sPhrase = _p('user_name_tagged_you_in_a_comment_in_a_music_album', [
            'user_name' => Phpfox::getService('notification')->getUsers($aNotification)
        ]);

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music.album', $aRow['album_id'],
                    $aRow['name']) . 'comment_' . $aNotification['item_id'],
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getCommentNotificationPlaylistTag($aNotification)
    {
        $aRow = $this->database()->select('ml.playlist_id, ml.name, u.user_name, u.full_name')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('music_playlist'), 'ml', 'ml.playlist_id = c.item_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');
        if (empty($aRow)) {
            return false;
        }


        $sPhrase = _p('user_name_tagged_you_in_a_comment_in_a_music_playlist', [
            'user_name' => Phpfox::getService('notification')->getUsers($aNotification)
        ]);

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music.playlist', $aRow['playlist_id'],
                    $aRow['name']) . 'comment_' . $aNotification['item_id'],
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * callback to add music settings in pages
     */
    public function getPagePerms()
    {
        $aPerms = [];

        $aPerms['music.share_music'] = _p('who_can_share_music');
        $aPerms['music.view_browse_music'] = _p('who_can_view_music');

        return $aPerms;
    }

    public function getGroupPerms()
    {
        $aPerms = [
            'music.share_music' => _p('who_can_share_music')
        ];

        return $aPerms;
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
        if ($sPlugin = \Phpfox_Plugin::get('music.service_callback__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        \Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);

        return false;
    }

    public function getNotificationNewItem_Groups($aNotification)
    {
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            return false;
        }
        $aItem = Phpfox::getService('music')->getSong($aNotification['item_id']);
        if (empty($aItem) || empty($aItem['item_id']) || $aItem['module_id'] != 'groups') {
            return false;
        }

        $aRow = Phpfox::getService('groups')->getPage($aItem['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('{{ users }} add a new song in the group "{{ title }}"', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link'    => \Phpfox_Url::instance()->permalink('music', $aItem['song_id'], $aItem['title']),
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'music')
        ];
    }

    public function ignoreDeleteLikesAndTagsWithFeedSong()
    {
        return true;
    }

    /**
     * @param      $iId
     * @param null $iUserId
     *
     * @return bool
     */
    public function addTrack($iId, $iUserId = null)
    {
        $aId = explode('_', $iId);
        if (count($aId) != 2) {
            return false;
        }
        if ($iUserId === null) {
            $iUserId = Phpfox::getUserBy('user_id');
        }

        db()->insert(Phpfox::getT('track'), [
            'type_id'    => 'music_' . $aId[0],
            'item_id'    => (int)$aId[1],
            'ip_address' => Phpfox::getIp(),
            'user_id'    => $iUserId,
            'time_stamp' => PHPFOX_TIME
        ]);

        return true;
    }

    /**
     * @return array
     */
    public function getAttachmentFieldAlbum()
    {
        return [
            'music_album',
            'album_id'
        ];
    }

    /**
     * @return array
     */
    public function getAttachmentFieldSong()
    {
        return [
            'music_song',
            'song_id'
        ];
    }

    /**
     * @return array
     */
    public function getAttachmentFieldPlaylist()
    {
        return [
            'music_playlist',
            'playlist_id'
        ];
    }

    /**
     * @param array  $aConds
     * @param string $sSort
     *
     * @return array
     */
    public function getTagSearchAlbum($aConds, $sSort)
    {
        (($sPlugin = Phpfox_Plugin::get('music.component_service_callback_gettagsearchalbum__start')) ? eval($sPlugin) : false);
        $aRows = $this->database()->select("ma.album_id AS id")
            ->from(Phpfox::getT('music_album'), 'ma')
            ->innerJoin(Phpfox::getT('tag'), 'tag', "tag.item_id = ma.album_id")
            ->join(Phpfox::getT('music_album_text'), 'mat', 'mat.album_id = ma.album_id')
            ->where($aConds)
            ->group('ma.album_id', true)
            ->order($sSort)
            ->execute('getSlaveRows');

        $aSearchIds = [];
        foreach ($aRows as $aRow) {
            $aSearchIds[] = $aRow['id'];
        }
        (($sPlugin = Phpfox_Plugin::get('music.component_service_callback_gettagsearchalbum__end')) ? eval($sPlugin) : false);
        return $aSearchIds;
    }

    /**
     * @return array
     */
    public function getTagCloudAlbum()
    {
        (($sPlugin = Phpfox_Plugin::get('music.component_service_callback_gettagcloudalbum__start')) ? eval($sPlugin) : false);
        return [
            'link'     => 'music/album',
            'category' => 'music_album'
        ];
    }

    /**
     * @return string
     */
    public function getTagTypeProfile()
    {
        return 'music';
    }

    /**
     * @return string
     */
    public function getTagType()
    {
        return 'music';
    }

    public function deleteSponsorItemSong($aParams)
    {
        if (!isset($aParams['item_id'])) {
            return;
        }
        db()->update(':music_song', ['is_sponsor' => 0], ['song_id' => $aParams['item_id']]);
        $this->cache()->remove('music_song_sponsored');
    }

    public function deleteSponsorItemAlbum($aParams)
    {
        if (!isset($aParams['item_id'])) {
            return;
        }
        db()->update(':music_album', ['is_sponsor' => 0], ['album_id' => $aParams['item_id']]);
        $this->cache()->remove('music_album_sponsored');
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

        $iTotalSong = db()->select('COUNT(*)')
            ->from(':music_song')
            ->where('user_id =' . (int)$iUserId)
            ->execute('getField');
        $iTotalAlbum = db()->select('COUNT(*)')
            ->from(':music_album')
            ->where('user_id =' . (int)$iUserId)
            ->execute('getField');
        return [
            'merge_result' => true,
            'result'       => [
                [
                    'total_name'  => _p('music_songs'),
                    'total_value' => $iTotalSong,
                    'type'        => 'item'
                ],
                [
                    'total_name'  => _p('music_albums'),
                    'total_value' => $iTotalAlbum,
                    'type'        => 'item'
                ]
            ]
        ];
    }

    public function getUploadParamsSong()
    {
        return Phpfox::getService('music')->getUploadParams();
    }

    public function getUploadParamsImage($aParams = null)
    {
        return Phpfox::getService('music')->getUploadPhotoParams($aParams);
    }

    public function getUploadParamsAlbum_Image()
    {
        return Phpfox::getService('music')->getUploadPhotoParams();
    }

    public function getUploadParamsPlaylist_Image()
    {
        return Phpfox::getService('music')->getUploadPhotoParams();
    }

    public function getNotificationSettings()
    {
        return [
            'music.song_email_notification' => [
                'phrase' => _p('music_songs_notifications'),
                'default' => 1
            ]
        ];
    }
}
