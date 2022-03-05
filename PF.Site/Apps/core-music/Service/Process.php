<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Service;

use Phpfox;
use Phpfox_File;
use Phpfox_Plugin;
use Phpfox_Request;

defined('PHPFOX') or exit('NO DICE!');

class Process extends \Phpfox_Service
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('music_song');
    }

    public function publicSong($iSongId, $sUploadHash = null)
    {
        if (!$iSongId) {
            return false;
        }

        $aSong = Phpfox::getService('music')->getSong($iSongId, false);

        if (empty($aSong) || (!empty($aSong) && (int)$aSong['view_id'] != 2)) {
            return false;
        }

        $aUpdate = [
            'view_id' => (Phpfox::getUserParam('music.music_song_approval') ? '1' : '0'),
        ];


        db()->update($this->_sTable, $aUpdate, 'song_id = ' . (int)$aSong['song_id']);

        // Update user space usage
        if (!Phpfox::getUserParam('music.music_song_approval')) {
            if ($aSong['server_id'] == 0) {
                $fileSize = filesize(Phpfox::getParam('music.dir') . sprintf($aSong['song_path'], ''));
            } else {
                $songUrl = Phpfox::getService('music')->getSongPath($aSong['song_path'], $aSong['server_id']);
                $aHeaders = get_headers($songUrl, true);
                if (preg_match('/200 OK/i', $aHeaders[0])) {
                    if (isset($aHeaders['Content-Length'])) {
                        $fileSize = $aHeaders['Content-Length'];
                    } elseif (isset($aHeaders['content-length'])) {
                        $fileSize = $aHeaders['content-length'];
                    }
                }
            }

            if (!empty($fileSize)) {
                Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'music', $fileSize);
            }
        }

        $aCallback = null;
        if (!empty($aSong['module_id']) && Phpfox::hasCallback($aSong['module_id'], 'uploadSong')) {
            $aCallback = Phpfox::callback($aSong['module_id'] . '.uploadSong', $aSong['item_id']);
        }
        $bNewFeed = isset($_SESSION['music_song_' . $sUploadHash . '_' . $aSong['album_id']]) && $_SESSION['music_song_' . $sUploadHash . '_' . $aSong['album_id']] > 0;
        if ($bNewFeed) {
            db()->insert(Phpfox::getT('music_feed'), [
                    'feed_id'    => $_SESSION['music_song_' . $sUploadHash . '_' . $aSong['album_id']],
                    'song_id'    => $aSong['song_id'],
                    'feed_table' => (empty($aCallback['table_prefix']) ? 'feed' : $aCallback['table_prefix'] . 'feed')
                ]
            );
        }

        if ((int)$aSong['album_id'] > 0) {
            if (!Phpfox::getUserParam('music.music_song_approval')) {
                $this->database()->updateCounter('music_album', 'total_track', 'album_id', $aSong['album_id']);
                if (!$bNewFeed && Phpfox::isModule('feed') && Phpfox::getParam('music.music_allow_create_feed_when_add_new_item', 1)) {
                    $_SESSION['music_song_' . $sUploadHash . '_' . $aSong['album_id']] = $iFeedId = \Phpfox::getService('feed.process')->callback($aCallback)->add('music_song',
                        $aSong['song_id'], $aSong['privacy'],
                        $aSong['privacy_comment'],
                        (!empty($aSong['item_id']) ? (int)$aSong['item_id'] : '0'));
                    if (!empty($iFeedId) && !empty($iLoopFeedId = Phpfox::getService('feed.process')->getLoopFeedId())) {
                        storage()->set('music_song_parent_feed_' . $iLoopFeedId, $iFeedId);
                    }
                }
            }
        } else {
            if (!Phpfox::getUserParam('music.music_song_approval')) {
                if (!$bNewFeed) {
                    if (Phpfox::isModule('feed') && Phpfox::getParam('music.music_allow_create_feed_when_add_new_item', 1)) {
                        $_SESSION['music_song_' . $sUploadHash . '_' . $aSong['album_id']] = $iFeedId = \Phpfox::getService('feed.process')->callback($aCallback)->add('music_song',
                            $aSong['song_id'], $aSong['privacy'],
                            $aSong['privacy_comment'],
                            (!empty($aSong['item_id']) ? (int)$aSong['item_id'] : '0'));
                        if (!empty($iFeedId) && !empty($iLoopFeedId = Phpfox::getService('feed.process')->getLoopFeedId())) {
                            storage()->set('music_song_parent_feed_' . $iLoopFeedId, $iFeedId);
                        }
                    }

                    //support add notification for parent module
                    if (!empty($aSong['module_id']) && !empty($aSong['item_id']) && Phpfox::isModule('notification') && Phpfox::isModule($aSong['module_id']) && Phpfox::hasCallback($aSong['module_id'],
                            'addItemNotification')
                    ) {
                        Phpfox::callback($aSong['module_id'] . '.addItemNotification', [
                            'page_id'      => $aSong['item_id'],
                            'item_perm'    => 'music.view_browse_music',
                            'item_type'    => 'music',
                            'item_id'      => $aSong['song_id'],
                            'owner_id'     => Phpfox::getUserId(),
                            'items_phrase' => 'songs__l'
                        ]);
                    }
                }
            }
        }

        if (!Phpfox::getUserParam('music.music_song_approval')) {
            Phpfox::getService('user.activity')->update(Phpfox::getUserId(), 'music_song');
        }

        return true;
    }


    public function upload($aVals, $iAlbumId = 0)
    {
        if (empty($aVals['name']) || !Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false)) {
            return false;
        }
        $sFileName = $aVals['name'];
        $aVals['callback_module'] = isset($aVals['callback_module']) ? $aVals['callback_module'] : null;
        $aVals['callback_item_id'] = isset($aVals['callback_item_id']) ? $aVals['callback_item_id'] : 0;

        if (empty($aVals['title'])) {
            $aVals['title'] = str_replace('.mp3', '', $aVals['file_name']);
        }

        if ($iAlbumId < 1 && isset($aVals['album_id'])) {
            $iAlbumId = (int)$aVals['album_id'];
        }

        if ($iAlbumId > 0) {
            $aAlbum = $this->database()->select('*')
                ->from(Phpfox::getT('music_album'))
                ->where('album_id = ' . (int)$iAlbumId)
                ->execute('getSlaveRow');

            $aVals['privacy'] = $aAlbum['privacy'];
            $aVals['privacy_comment'] = $aAlbum['privacy_comment'];

            if (!empty($aAlbum['module_id'])) {
                $aVals['callback_module'] = $aAlbum['module_id'];
            }
            if (!empty($aAlbum['item_id'])) {
                $aVals['callback_item_id'] = $aAlbum['item_id'];
            }
        }

        \Phpfox::getService('ban')->checkAutomaticBan($aVals['title']);

        $aInsert = [
            'view_id'            => 2,
            'privacy'            => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
            'privacy_comment'    => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
            'album_id'           => $iAlbumId,
            'genre_id'           => 0,
            'user_id'            => Phpfox::getUserId(),
            'title'              => Phpfox::getLib('parse.input')->clean($aVals['title'], 255),
            'description'        => '',
            'description_parsed' => '',
            'explicit'           => ((isset($aVals['explicit']) && $aVals['explicit']) ? 1 : 0),
            'time_stamp'         => PHPFOX_TIME,
            'module_id'          => (isset($aVals['callback_module']) ? $aVals['callback_module'] : null),
            'item_id'            => (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : '0'),
            'total_attachment'   => 0,
        ];

        $iId = $this->database()->insert($this->_sTable, $aInsert);

        if (!$iId) {
            return false;
        }

        // Return back error reporting
        \Phpfox_Error::skip(false);

        $this->database()->update($this->_sTable, [
            'song_path' => $sFileName,
            'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
        ], 'song_id = ' . (int)$iId);

        // insert genre

        if (isset($aVals['genre'][0])) {
            $aGenre = explode(',', $aVals['genre'][0]);
            foreach ($aGenre as $key => $iGenreId) {
                $data = ['song_id' => $iId, 'genre_id' => $iGenreId];
                db()->insert(':music_genre_data', $data);
            }
        }

        if ($aVals['privacy'] == '4') {
            Phpfox::getService('privacy.process')->add('music_song', $iId,
                (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
        }

        // plugin call
        if ($sPlugin = Phpfox_Plugin::get('music.service_process_upload__end')) {
            eval($sPlugin);
        }
        return $iId;
    }

    public function delete($iId, &$aSong = null)
    {
        $bSkip = true;
        $mReturn = true;
        if ($aSong === null) {
            $bSkip = false;
            $aSong = $this->database()->select('song_id, album_id, module_id, item_id, user_id, song_path, is_sponsor, is_featured, server_id, image_path, image_server_id, view_id')
                ->from($this->_sTable)
                ->where('song_id = ' . (int)$iId)
                ->execute('getSlaveRow');

            if (!isset($aSong['song_id'])) {
                return false;
            }

            if (in_array($aSong['module_id'], ['pages', 'groups']) && Phpfox::isModule($aSong['module_id']) && Phpfox::getService($aSong['module_id'])->isAdmin($aSong['item_id'])) {
                $bSkip = true;
                $mReturn = Phpfox::getService($aSong['module_id'])->getUrl($aSong['item_id']) . 'music/';
            }
        }
        if ($bSkip || (($aSong['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('music.can_delete_own_track')) || Phpfox::getUserParam('music.can_delete_other_tracks'))) {
            $this->deleteImage($iId, $aSong);
            // Update user space usage
            if ($aSong['server_id'] > 0) {
                // Get the file size stored when the photo was uploaded
                $sTempUrl = Phpfox::getLib('cdn')->getUrl(Phpfox::getParam('music.url') . sprintf($aSong['song_path'],
                        ''));

                $aHeaders = get_headers($sTempUrl, true);
                if (preg_match('/200 OK/i', $aHeaders[0])) {
                    \Phpfox::getService('user.space')->update($aSong['user_id'], 'music',
                        (int)$aHeaders["Content-Length"], '-');
                }
            } else {
                \Phpfox::getService('user.space')->update($aSong['user_id'], 'music',
                    filesize(Phpfox::getParam('music.dir') . sprintf($aSong['song_path'], '')), '-');
            }

            (($sPlugin = \Phpfox_Plugin::get('music.service_process_delete__1')) ? eval($sPlugin) : false);

            Phpfox_File::instance()->unlink(Phpfox::getParam('music.dir') . sprintf($aSong['song_path'], ''));

            $this->database()->delete($this->_sTable, 'song_id = ' . $aSong['song_id']);
            if ($aSong['album_id'] > 0) {
                $this->database()->updateCounter('music_album', 'total_track', 'album_id', $aSong['album_id'], true);
            }
            $aPlaylists = db()->select('playlist_id')
                ->from(':music_playlist_data')
                ->where('song_id =' . (int)$aSong['song_id'])
                ->execute('getSlaveRows');
            if (count($aPlaylists)) {
                foreach ($aPlaylists as $aPlaylist) {
                    $this->database()->updateCounter('music_playlist', 'total_track', 'playlist_id', $aPlaylist['playlist_id'], true);
                }
            }
            db()->delete(':music_playlist_data', 'song_id = ' . $aSong['song_id']);
            (Phpfox::isModule('attachment') ? \Phpfox::getService('attachment.process')->deleteForItem($aSong['user_id'],
                $iId, 'music_song') : null);

            (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem($aSong['user_id'], $aSong['song_id'],
                'music_song') : null);

            if (Phpfox::isModule('feed')) {
                $homeFeedId = db()->select('feed_id')
                                ->from(':feed')
                                ->where([
                                    'type_id' => 'music_song',
                                    'item_id' => $iId
                                ])->executeField(false);
                if (!empty($homeFeedId)) {
                    storage()->del('music_song_parent_feed_' . $homeFeedId);
                }
                \Phpfox::getService('feed.process')->delete('music_song', $iId);
            }

            (Phpfox::isModule('like') ? \Phpfox::getService('like.process')->delete('music_song', (int)$iId, 0,
                true) : null);
            (Phpfox::isModule('notification') ? \Phpfox::getService('notification.process')->deleteAllOfItem([
                'music_song_like',
                'music_songapproved'
            ], (int)$iId) : null);
            db()->delete(':music_genre_data', 'song_id = ' . intval($iId));
            if (Phpfox::isModule('tag')) {
                $this->database()->delete(Phpfox::getT('tag'), 'item_id = ' . $aSong['song_id'] . ' AND category_id = "music_song"', 1);
                $this->cache()->removeGroup('tag');
            }
            //close all sponsorships
            (Phpfox::isAppActive('Core_BetterAds') ? Phpfox::getService('ad.process')->closeSponsorItem('music_song',
                (int)$iId) : null);

            (($sPlugin = \Phpfox_Plugin::get('music.service_process_delete__2')) ? eval($sPlugin) : false);

            if ((int)$aSong['view_id'] == 0) {
                Phpfox::getService('user.activity')->update($aSong['user_id'], 'music_song', '-');
            }
            if ($aSong['is_sponsor'] == 1) {
                $this->cache()->remove('music_song_sponsored');
            }
            if ($aSong['is_featured'] == 1) {
                $this->cache()->remove('music_song_featured');
            }
        } else {
            $mReturn = false;
        }

        return $mReturn;
    }

    public function update($iId, $aVals)
    {
        $aSong = $this->database()->select('song_id, user_id, album_id, image_path')
            ->from($this->_sTable)
            ->where('song_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aSong['song_id'])) {
            return false;
        }

        if ((isset($aVals['album_id']) && $aVals['album_id'] > 0) || $aSong['album_id']) {
            $aAlbum = $this->database()->select('*')
                ->from(Phpfox::getT('music_album'))
                ->where('album_id = ' . (int)(isset($aVals['album_id']) ? $aVals['album_id'] : $aSong['album_id']))
                ->execute('getSlaveRow');

            if (isset($aAlbum['album_id'])) {
                $aVals['album_id'] = $aAlbum['album_id'];
                $aVals['privacy'] = $aAlbum['privacy'];
                $aVals['privacy_comment'] = $aAlbum['privacy_comment'];
            }
        }
        $bHasAttachments = (!empty($aVals['attachment']));
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->update('music_song', $iId, $aSong['user_id'], $aVals['description'],
                true);
        } else {
            if (Phpfox::isModule('tag')) {
                Phpfox::getService('tag.process')->update('music_song', $iId, $aSong['user_id'],
                    (!empty($aVals['tag_list']) ? $aVals['tag_list'] : null));
            }
        }
        $aUpdate = [
            'album_id'           => (isset($aVals['album_id']) ? (int)$aVals['album_id'] : 0),
            'genre_id'           => 0,
            'title'              => Phpfox::getLib('parse.input')->clean($aVals['title'], 255),
            'description'        => (isset($aVals['description'])) ? $this->preParse()->clean($aVals['description']) : '',
            'description_parsed' => (empty($aVals['description']) ? null : $this->preParse()->prepare($aVals['description'])),
            'total_attachment'   => (Phpfox::isModule('attachment') ? Phpfox::getService('attachment')->getCountForItem($iId,
                'music_song') : '0')
        ];

        if (empty($aVals['privacy'])) {
            $aVals['privacy'] = 0;
        }
        if (empty($aVals['privacy_comment'])) {
            $aVals['privacy_comment'] = 0;
        }

        $aUpdate['privacy'] = (isset($aVals['privacy']) ? $aVals['privacy'] : '0');
        $aUpdate['privacy_comment'] = (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0');

        db()->delete(':music_genre_data', 'song_id = ' . intval($iId));
        // insert genre
        if (isset($aVals['genre']) && count($aVals['genre'])) {

            foreach ($aVals['genre'] as $key => $iGenreId) {
                $data = ['song_id' => $iId, 'genre_id' => $iGenreId];
                db()->insert(':music_genre_data', $data);
            }
        }
        if (!empty($aSong['image_path']) && (!empty($aVals['temp_file']) || !empty($aVals['remove_photo']))) {
            if ($this->deleteImage($iId)) {
                $aUpdate['image_path'] = null;
                $aUpdate['image_server_id'] = 0;
            } else {
                return false;
            }
        }

        if (!empty($aVals['temp_file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            if (!empty($aFile)) {
                if (!Phpfox::getService('user.space')->isAllowedToUpload($aSong['user_id'], $aFile['size'])) {
                    Phpfox::getService('core.temp-file')->delete($aVals['temp_file'], true);
                    return false;
                }
                $aUpdate['image_path'] = $aFile['path'];
                $aUpdate['image_server_id'] = $aFile['server_id'];
                Phpfox::getService('user.space')->update($aSong['user_id'], 'music_image', $aFile['size']);
                Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            }
        }
        $this->database()->update($this->_sTable, $aUpdate, 'song_id = ' . (int)$iId);

        if (isset($aVals['album_id']) && $aVals['album_id'] != $aSong['album_id']) {
            // Increase the count for the new album
            $this->database()->updateCounter('music_album', 'total_track', 'album_id', $aVals['album_id'], false);

            // Decrease the count for the old album
            $this->database()->updateCounter('music_album', 'total_track', 'album_id', $aSong['album_id'], true);
        }

        if (Phpfox::isModule('privacy')) {
            if ($aVals['privacy'] == '4') {
                \Phpfox::getService('privacy.process')->update('music_song', $iId,
                    (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
            } else {
                \Phpfox::getService('privacy.process')->delete('music_song', $iId);
            }
        }

        (Phpfox::isModule('feed') ? \Phpfox::getService('feed.process')->update('music_song', $iId, $aVals['privacy'],
            (isset($aVals['privacy_comment']) ? (int)$aVals['privacy_comment'] : 0)) : null);

        (($sPlugin = \Phpfox_Plugin::get('music.service_process_update__1')) ? eval($sPlugin) : false);

        return true;
    }

    public function play($iId)
    {
        $aSong = $this->database()->select('song_id, album_id')
            ->from($this->_sTable)
            ->where('view_id != 2 AND song_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aSong['song_id'])) {
            return false;
        }

        $this->database()->updateCounter('music_song', 'total_play', 'song_id', $aSong['song_id']);

        if ($aSong['album_id']) {
            $this->database()->updateCounter('music_album', 'total_play', 'album_id', $aSong['album_id']);
        }
        return null;
    }

    public function addForProfile($iId, $iType)
    {
        Phpfox::isUser(true);

        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('music_profile'))
            ->where('user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');

        if ($iCnt >= Phpfox::getUserParam('music.total_song_on_profile')) {
            return \Phpfox_Error::set(_p('you_have_reached_your_limit_max_songs_allowed_total',
                ['total' => Phpfox::getUserParam('music.total_song_on_profile')]));
        }

        $this->database()->delete(Phpfox::getT('music_profile'),
            'song_id = ' . (int)$iId . ' AND user_id = ' . Phpfox::getUserId());

        if ($iType) {
            $this->database()->insert(Phpfox::getT('music_profile'), [
                    'song_id' => (int)$iId,
                    'user_id' => Phpfox::getUserId()
                ]
            );

            $this->database()->updateCounter('user_field', 'total_profile_song', 'user_id', Phpfox::getUserId());
        } else {
            $this->database()->updateCounter('user_field', 'total_profile_song', 'user_id', Phpfox::getUserId(), true);
        }

        return true;
    }

    public function feature($iId, $iType)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('music.can_feature_songs', true);

        $this->database()->update($this->_sTable, ['is_featured' => ($iType ? '1' : '0')],
            'song_id = ' . (int)$iId);


        return true;
    }

    public function sponsorSong($iId, $iType)
    {

        if (!Phpfox::getUserParam('music.can_sponsor_song') && !Phpfox::getUserParam('music.can_purchase_sponsor_song') && !defined('PHPFOX_API_CALLBACK')) {
            return \Phpfox_Error::set(_p('hack_attempt'));
        }
        $iType = (int)$iType;

        if ($iType != 1 && $iType != 0) {
            return false;
        }
        $this->database()->update($this->_sTable, ['is_sponsor' => $iType],
            'song_id = ' . (int)$iId);
        if ($sPlugin = \Phpfox_Plugin::get('music.service_process_sponsorsong__end')) {
            return eval($sPlugin);
        }
        return true;
    }

    public function sponsorAlbum($iId, $iType)
    {
        if (!Phpfox::getUserParam('music.can_sponsor_album') && !Phpfox::getUserParam('music.can_purchase_sponsor_album') && !defined('PHPFOX_API_CALLBACK')) {
            return \Phpfox_Error::set(_p('hack_attempt'));
        }
        $iType = (int)$iType;

        if ($iType != 1 && $iType != 0) {
            return false;
        }
        $this->database()->update(Phpfox::getT('music_album'), ['is_sponsor' => $iType],
            'album_id = ' . (int)$iId);

        if ($sPlugin = \Phpfox_Plugin::get('music.service_process_sponsoralbum__end')) {
            return eval($sPlugin);
        }
        return true;
    }

    public function approve($iId)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('music.can_approve_songs', true);

        $aSong = $this->database()->select('v.*, ma.privacy AS album_privacy, ma.privacy_comment AS album_privacy_comment, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'v')
            ->leftJoin(Phpfox::getT('music_album'), 'ma', 'ma.album_id = v.album_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.view_id != 2 AND v.song_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aSong['song_id'])) {
            return \Phpfox_Error::set(_p('unable_to_find_the_song_you_want_to_approve'));
        }

        $this->database()->update($this->_sTable, ['view_id' => '0', 'time_stamp' => PHPFOX_TIME],
            'song_id = ' . $aSong['song_id']);

        if (Phpfox::isModule('notification')) {
            \Phpfox::getService('notification.process')->add('music_songapproved', $aSong['song_id'],
                $aSong['user_id']);
        }

        $bAddFeed = true;
        (($sPlugin = \Phpfox_Plugin::get('music.service_process_approve__1')) ? eval($sPlugin) : false);

        // Send the user an email
        $sLink = \Phpfox_Url::instance()->permalink('music', $aSong['song_id'], $aSong['title']);
        Phpfox::getLib('mail')->to($aSong['user_id'])
            ->subject([
                'your_song_title_has_been_approved_on_site_title',
                ['title' => $aSong['title'], 'site_title' => Phpfox::getParam('core.site_title')]
            ])
            ->message([
                'your_song_title_has_been_approved_on_site_title_to_view_this_song',
                ['title' => $aSong['title'], 'site_title' => Phpfox::getParam('core.site_title'), 'link' => $sLink]
            ])
            ->notification('music.song_email_notification')
            ->send();

        if ($aSong['album_id']) {
            $this->database()->updateCounter('music_album', 'total_track', 'album_id', $aSong['album_id']);

            (Phpfox::isModule('feed') && $bAddFeed && Phpfox::getParam('music.music_allow_create_feed_when_add_new_item', 1) ? \Phpfox::getService('feed.process')->add('music_album',
                $aSong['song_id'], $aSong['album_privacy'],
                (isset($aSong['album_privacy_comment']) ? (int)$aSong['album_privacy_comment'] : 0), 0,
                $aSong['user_id']) : null);
        } else {
            if ($aSong['module_id'] && $aSong['item_id'] && Phpfox::isModule($aSong['module_id']) && Phpfox::hasCallback($aSong['module_id'],
                    'getFeedDetails')
            ) {
                if (Phpfox::isModule('feed') && Phpfox::getParam('music.music_allow_create_feed_when_add_new_item', 1)) {
                    $iFeedId = \Phpfox::getService('feed.process')->callback(Phpfox::callback($aSong['module_id'] . '.getFeedDetails',
                        $aSong['item_id']))->add('music_song', $aSong['song_id'], $aSong['privacy'],
                        (isset($aSong['privacy_comment']) ? (int)$aSong['privacy_comment'] : 0), $aSong['item_id'],
                        $aSong['user_id']);
                    if (!empty($iFeedId) && !empty($iLoopFeedId = Phpfox::getService('feed.process')->getLoopFeedId())) {
                        storage()->set('music_song_parent_feed_' . $iLoopFeedId, $iFeedId);
                        Phpfox::getService('feed.process')->resetLoopFeedId();
                    }
                }
            } else {
                (Phpfox::isModule('feed') && $bAddFeed && Phpfox::getParam('music.music_allow_create_feed_when_add_new_item', 1) ? \Phpfox::getService('feed.process')->add('music_song',
                    $aSong['song_id'], $aSong['privacy'],
                    (isset($aSong['privacy_comment']) ? (int)$aSong['privacy_comment'] : 0), 0,
                    $aSong['user_id']) : null);
            }

            //support add notification for parent module
            if (Phpfox::isModule('notification') && $aSong['module_id'] && Phpfox::isModule($aSong['module_id']) && Phpfox::hasCallback($aSong['module_id'],
                    'addItemNotification')
            ) {
                Phpfox::callback($aSong['module_id'] . '.addItemNotification', [
                    'page_id'      => $aSong['item_id'],
                    'item_perm'    => 'music.view_browse_music',
                    'item_type'    => 'music',
                    'item_id'      => $iId,
                    'owner_id'     => $aSong['user_id'],
                    'items_phrase' => 'songs__l'
                ]);
            }
        }
        Phpfox::getService('user.activity')->update($aSong['user_id'], 'music_song');
        return true;
    }

    /**
     * @param $sPath
     *
     * @return bool
     */
    public function removeTempFile($sPath)
    {
        Phpfox::getLib('file')->unlink(Phpfox::getParam('core.dir_file') . 'static' . PHPFOX_DS . $sPath);
        return true;
    }

    public function deleteImage($iId, &$aSong = null)
    {
        $bSkip = true;
        if ($aSong === null) {
            $bSkip = false;
            $aSong = $this->database()->select('song_id, user_id, image_path, image_server_id')
                ->from($this->_sTable)
                ->where('view_id != 2 AND song_id = ' . (int)$iId)
                ->execute('getSlaveRow');

            if (!isset($aSong['song_id'])) {
                return false;
            }
        }

        if (empty($aSong['image_path'])) {
            return null;
        }

        if ($bSkip || (($aSong['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('music.can_edit_own_albums')) || Phpfox::getUserParam('music.can_edit_other_music_albums'))) {
            $aParams = Phpfox::getService('music')->getUploadPhotoParams();
            $aParams['type'] = 'music_image';
            $aParams['path'] = $aSong['image_path'];
            $aParams['user_id'] = $aSong['user_id'];
            $aParams['update_space'] = ($aSong['user_id'] ? true : false);
            $aParams['server_id'] = $aSong['image_server_id'];

            (($sPlugin = \Phpfox_Plugin::get('music.service_process_deleteimage__1')) ? eval($sPlugin) : false);
            return Phpfox::getService('user.file')->remove($aParams);

        }

        return \Phpfox_Error::set(_p('not_allowed_to_edit_this_photo_song_art'));
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
        if ($sPlugin = \Phpfox_Plugin::get('music.service_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        \Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}