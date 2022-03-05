<?php

namespace Apps\Core_Music\Service\Playlist;
defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('music_playlist');
    }

    public function add($aVals)
    {
        if (!Phpfox::getService('music.playlist')->canCreateNewPlaylist()) {
            return false;
        }
        $bHasAttachments = (!empty($aVals['attachment']));
        Phpfox::getService('ban')->checkAutomaticBan($aVals['name'] . ' ' . $aVals['description']);
        $aInsert = [
            'user_id'            => Phpfox::getUserId(),
            'name'               => $this->preParse()->clean($aVals['name'], 255),
            'description'        => (empty($aVals['description']) ? null : $this->preParse()->clean($aVals['description'])),
            'description_parsed' => (empty($aVals['description']) ? null : $this->preParse()->prepare($aVals['description'])),
            'time_stamp'         => PHPFOX_TIME,
            'view_id'            => 0,
            'privacy'            => !empty($aVals['privacy']) ? $aVals['privacy'] : 0,
            'privacy_comment'    => !empty($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0',
        ];
        if (!empty($aVals['temp_file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            if (!empty($aFile)) {
                if (!Phpfox::getService('user.space')->isAllowedToUpload($aInsert['user_id'], $aFile['size'])) {
                    Phpfox::getService('core.temp-file')->delete($aVals['temp_file'], true);
                    return false;
                }
                $aInsert['image_path'] = $aFile['path'];
                $aInsert['server_id'] = $aFile['server_id'];
                Phpfox::getService('user.space')->update($aInsert['user_id'], 'music_image', $aFile['size']);
                Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            }
        }
        $iId = $this->database()->insert($this->_sTable, $aInsert);

        if (!$iId) {
            return false;
        }
        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }

        (Phpfox::isModule('feed') && Phpfox::getParam('music.music_allow_create_feed_when_add_new_item', 1) ? $iFeedId = Phpfox::getService('feed.process')->add('music_playlist',
            $iId, $aVals['privacy'], (isset($aVals['privacy_comment']) ? (int)$aVals['privacy_comment'] : 0),
            (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : '0')) : null);

        return $iId;
    }

    public function update($iId, $aVals)
    {
        $aPlaylist = db()->select('mp.*')
            ->from($this->_sTable, 'mp')
            ->join(':user', 'u', 'u.user_id = mp.user_id')
            ->where('mp.playlist_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aPlaylist['playlist_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_playlist_you_want_to_edit'));
        }

        if (($aPlaylist['user_id'] != Phpfox::getUserId() || !Phpfox::getUserParam('music.can_edit_own_playlists')) && !Phpfox::getUserParam('music.can_edit_other_music_playlists')) {
            return Phpfox_Error::set(_p('you_do_not_have_permission_to_edit_this_playlist'));
        }

        $bHasAttachments = (!empty($aVals['attachment']));
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }
        $aUpdate = [
            'name'               => $this->preParse()->clean($aVals['name'], 255),
            'total_attachment'   => (Phpfox::isModule('attachment') ? Phpfox::getService('attachment')->getCountForItem($iId,
                'music_playlist', true) : '0'),
            'description'        => (empty($aVals['description']) ? null : $this->preParse()->clean($aVals['description'])),
            'description_parsed' => (empty($aVals['description']) ? null : $this->preParse()->prepare($aVals['description'])),
            'privacy'            => !empty($aVals['privacy']) ? $aVals['privacy'] : 0,
            'privacy_comment'    => !empty($aVals['privacy_comment']) ? $aVals['privacy_comment'] : 0,
        ];

        if (!empty($aPlaylist['image_path']) && (!empty($aVals['temp_file']) || !empty($aVals['remove_photo']))) {
            if ($this->deleteImage($iId)) {
                $aUpdate['image_path'] = null;
                $aUpdate['server_id'] = 0;
            } else {
                return false;
            }
        }

        if (!empty($aVals['temp_file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            if (!empty($aFile)) {
                if (!Phpfox::getService('user.space')->isAllowedToUpload($aPlaylist['user_id'], $aFile['size'])) {
                    Phpfox::getService('core.temp-file')->delete($aVals['temp_file'], true);
                    return false;
                }
                $aUpdate['image_path'] = $aFile['path'];
                $aUpdate['server_id'] = $aFile['server_id'];
                Phpfox::getService('user.space')->update($aPlaylist['user_id'], 'music_image', $aFile['size']);
                Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            }
        }

        $this->database()->update($this->_sTable, $aUpdate, 'playlist_id = ' . $aPlaylist['playlist_id']);

        return true;
    }

    public function deleteImage($iId, &$aPlaylist = null)
    {
        $bSkip = true;
        if ($aPlaylist === null) {
            $bSkip = false;
            $aPlaylist = $this->_getPlaylist($iId);

            if (!isset($aPlaylist['playlist_id'])) {
                return false;
            }
        }

        if (empty($aPlaylist['image_path'])) {
            return null;
        }

        if ($bSkip || $aPlaylist['user_id'] == Phpfox::getUserId()) {
            $aParams = Phpfox::getService('music')->getUploadPhotoParams();
            $aParams['type'] = 'music_image';
            $aParams['path'] = $aPlaylist['image_path'];
            $aParams['user_id'] = $aPlaylist['user_id'];
            $aParams['update_space'] = ($aPlaylist['user_id'] ? true : false);
            $aParams['server_id'] = $aPlaylist['server_id'];

            (($sPlugin = Phpfox_Plugin::get('music.service_playlist_process_deleteimage__1')) ? eval($sPlugin) : false);
            return Phpfox::getService('user.file')->remove($aParams);
        }

        return false;
    }

    public function delete($iId)
    {
        $mReturn = true;
        $aPlaylist = $this->_getPlaylist($iId);

        if (!isset($aPlaylist['playlist_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_playlist_you_are_looking_for'));
        }

        if (($aPlaylist['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('music.can_delete_own_music_playlist')) || Phpfox::getUserParam('music.can_delete_other_music_playlists')) {
            $this->deleteImage($aPlaylist['playlist_id'], $aPlaylist);

            (Phpfox::isModule('attachment') ? Phpfox::getService('attachment.process')->deleteForItem($aPlaylist['user_id'],
                $iId, 'music_playlist') : null);
            (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem($aPlaylist['user_id'], $aPlaylist['playlist_id'],
                'music_playlist') : null);
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('music_playlist',
                $iId) : null);
            (Phpfox::isModule('like') ? Phpfox::getService('like.process')->delete('music_playlist', (int)$iId, 0,
                true) : null);
            (Phpfox::isModule('notification') ? Phpfox::getService('notification.process')->deleteAllOfItem([
                'music_playlist_like',
                'comment_music_playlist',
            ], (int)$iId) : null);

            $this->database()->delete($this->_sTable, 'playlist_id = ' . $aPlaylist['playlist_id']);
            $this->database()->delete(':music_playlist_data', 'playlist_id = ' . $aPlaylist['playlist_id']);
            (($sPlugin = Phpfox_Plugin::get('music.service_playlist_process_delete__1')) ? eval($sPlugin) : false);
        } else {
            $mReturn = false;
        }

        return $mReturn;
    }

    public function removeSong($iSongId, $iPlaylistId)
    {
        $aPlaylist = $this->_getPlaylist($iPlaylistId);

        if ($aPlaylist['user_id'] != Phpfox::getUserId()) {
            return false;
        }
        $iCnt = db()->select('COUNT(*)')
            ->from(':music_playlist_data')
            ->where('song_id =' . (int)$iSongId . ' AND playlist_id =' . (int)$iPlaylistId)
            ->execute('getField');
        if (!$iCnt) {
            return false;
        }
        db()->updateCounter('music_playlist', 'total_track', 'playlist_id', $iPlaylistId, true);
        return db()->delete(':music_playlist_data', [
            'song_id'     => $iSongId,
            'playlist_id' => $iPlaylistId
        ]);
    }

    public function addSong($iSongId, $iPlaylistId)
    {
        $aPlaylist = $this->_getPlaylist($iPlaylistId);

        if ($aPlaylist['user_id'] != Phpfox::getUserId()) {
            return false;
        }
        $iCnt = db()->select('COUNT(*)')
            ->from(':music_playlist_data')
            ->where('song_id =' . (int)$iSongId . ' AND playlist_id =' . (int)$iPlaylistId)
            ->execute('getField');
        if ($iCnt) {
            return false;
        }
        db()->updateCounter('music_playlist', 'total_track', 'playlist_id', $iPlaylistId);
        return db()->insert(':music_playlist_data', [
            'song_id'     => $iSongId,
            'playlist_id' => $iPlaylistId,
            'time_stamp'  => PHPFOX_TIME
        ]);
    }

    private function _getPlaylist($iPlaylistId)
    {
        return db()->select('*')
            ->from($this->_sTable)
            ->where('playlist_id = ' . (int)$iPlaylistId)
            ->execute('getSlaveRow');
    }

    public function updateManageSongs($iId, $aVals)
    {
        $aPlaylist = db()->select('mp.*')
            ->from($this->_sTable, 'mp')
            ->join(':user', 'u', 'u.user_id = mp.user_id')
            ->where('mp.playlist_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aPlaylist['playlist_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_playlist_you_want_to_edit'));
        }

        if (($aPlaylist['user_id'] != Phpfox::getUserId() || !Phpfox::getUserParam('music.can_edit_own_playlists')) && !Phpfox::getUserParam('music.can_edit_other_music_playlists')) {
            return Phpfox_Error::set(_p('you_do_not_have_permission_to_edit_this_playlist'));
        }
        if (!empty($aVals['remove_song'])) {
            $sRemoveIds = trim($aVals['remove_song'], ',');
            $iTotalRemove = count(explode(',', $sRemoveIds));
            if ($iTotalRemove) {
                db()->delete(':music_playlist_data', 'song_id IN (' . $sRemoveIds . ') AND playlist_id =' . (int)$iId);
                db()->update($this->_sTable, ['total_track' => $aPlaylist['total_track'] - $iTotalRemove],
                    'playlist_id = ' . (int)$iId);
            }
        }
        if (!empty($aVals['ordering'])) {
            $i = 1;
            foreach ($aVals['ordering'] as $key => $value) {
                db()->update(':music_playlist_data', ['ordering' => $i], 'song_id = ' . (int)$key . ' AND playlist_id =' . (int)$iId);
                $i++;
            }
        }
        return true;
    }
}