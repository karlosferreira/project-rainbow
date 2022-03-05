<?php

namespace Apps\Core_Music\Ajax;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Validator;

class Ajax extends Phpfox_Ajax
{
    public function deleteImage()
    {
        $iAlbumId = $this->get('id');
        if (\Phpfox::getService('music.album.process')->deleteImage($iAlbumId)) {
            $this->call('$(\'#js_music_album_upload_image\').show();');
            $this->call('$(\'#js_music_album_current_image, .js_hide_upload_album_image\').remove();');
        } else {
            $this->call('$(\'#js_music_album_current_image\').after(\'' . _p('an_error_occured_and_your_image_could_not_be_deleted_please_try_again') . '\');');
        }
    }

    public function deleteSongImage()
    {
        $iSongId = $this->get('id');
        if (Phpfox::getService('music.process')->deleteImage($iSongId)) {
            $this->call('$(\'#js_music_upload_image_' . $iSongId . '\').show();');
            $this->call('$(\'#js_song_image_' . $iSongId . '\').removeClass(\'has-image\').html(\'<i class="ico ico-music-note-o"></i>\');');
            $this->call('$(\'#js_music_current_image_' . $iSongId . ', .js_hide_upload_image_' . $iSongId . '\').remove();');
        } else {
            $this->call('$(\'#js_music_current_image_' . $iSongId . '\').after(\'' . _p('an_error_occured_and_your_image_could_not_be_deleted_please_try_again') . '\');');
        }
    }

    public function deleteSong()
    {
        if (\Phpfox::getService('music.process')->delete($this->get('id'))) {
            if ($this->get('inline')) {
                $this->call('$(\'#js_file_holder_' . $this->get('id') . '\').remove(); $Core.music.iValidFile--;$(\'#js_total_success\').html($Core.music.iValidFile);');
                if (Phpfox::isModule('feed') && $aFeed = Phpfox::getService('feed')->getForItem('music_song', $this->get('id'))) {
                    $_SESSION['music_song_' . $this->get('time_stamp') . '_' . $this->get('album_id')] = 0;
                }
                $this->call('delete ($Core.music.aUploadedSong[' . $this->get('id') . ']);');
                $this->call('if ($Core.music.canCheckValidate){$Core.music.reloadMusicValidation.validate();}');
            }
        }
    }

    public function play()
    {
        \Phpfox::getService('music.process')->play($this->get('id'));

        $this->removeClass('.js_music_track', 'isSelected')
            ->addClass('#js_music_track_' . $this->get('id'), 'isSelected');
    }

    public function playInFeed()
    {
        $aSong = \Phpfox::getService('music')->getSong($this->get('id'));
        $bAutoPlay = $this->get('auto_play', 0);
        if (!isset($aSong['song_id'])) {
            $this->alert(_p('unable_to_find_the_song_you_are_trying_to_play'));
            return false;
        }

        \Phpfox::getService('music.process')->play($aSong['song_id']);

        $sSongPath = $aSong['song_path'];

        $sWidth = '425px';
        if ($this->get('track')) {
            $sWidth = '100%';
        }

        if ($this->get('is_player')) {
            $sDivId = 'js_music_player_all';
        } else {
            $sDivId = 'js_tmp_music_player_' . $aSong['song_id'] . '_' . time();
            if ($this->get('feed_id') && $this->get('id')) {
                $this->call('$(\'#js_play_music_song_' . $this->get('feed_id') . $aSong['song_id'] . '\').find(\'.activity_feed_content_link:first\').addClass("audio-player").html(\'<div style="width:' . $sWidth . ';"><audio style="display: none;" id="' . $sDivId . '" src="' . $sSongPath . '" type="audio/mp3" controls="controls"></audio></div>\');');
            } else if ($this->get('feed_id')) {
                $this->call('$(\'#js_item_feed_' . $this->get('feed_id') . '\').find(\'.activity_feed_content_link:first\').addClass("audio-player").html(\'<div style="width:' . $sWidth . ';"><audio style="display: none;" id="' . $sDivId . '" src="' . $sSongPath . '" type="audio/mp3" controls="controls"></audio></div>\');');
            } else {
                $this->call('$(\'.js_block_track_player\').removeClass("audio-player").html(\'\');');
                $this->call('$(\'#' . ($this->get('track') ? $this->get('track') . '_' . $this->get('id') : 'js_controller_music_play_' . $this->get('id') . '') . '\').addClass("audio-player").html(\'<div style="width:' . $sWidth . ';"><audio style="display: none;" id="' . $sDivId . '" src="' . $sSongPath . '" type="audio/mp3" controls="controls"></audio></div>\');');
            }
        }

        $this->call('setTimeout(function(){$Core.music.initPlayInFeed("#' . $sDivId . '",' . $bAutoPlay . ');}, 200);');
    }

    public function userProfile()
    {
        if (\Phpfox::getService('music.process')->addForProfile($this->get('id'), $this->get('type'))) {
            if ($this->get('type')) {
                $this->show('#js_music_profile_remove_' . $this->get('id'))->hide('#js_music_profile_add_' . $this->get('id'))->alert(_p('this_song_has_been_added_to_your_profile'));
            } else {
                if ($this->get('remove')) {
                    $this->remove('#js_music_track_' . $this->get('id'));
                }

                $this->show('#js_music_profile_add_' . $this->get('id'))->hide('#js_music_profile_remove_' . $this->get('id'))->alert(_p('this_song_has_been_removed_from_your_profile'));
            }
        }
    }

    public function featureSong()
    {
        if (\Phpfox::getService('music.process')->feature($this->get('song_id'), $this->get('type'))) {
            if ($this->get('type')) {
                $this->addClass('#js_controller_music_track_' . $this->get('song_id'), 'row_featured');
                $this->alert(_p('song_successfully_featured'), _p('feature'), 300, 150, true);
            } else {
                $this->removeClass('#js_controller_music_track_' . $this->get('song_id'), 'row_featured');
                $this->alert(_p('song_successfully_un_featured'), _p('un_feature'), 300, 150, true);
            }
        }
    }

    public function featureAlbum()
    {
        if (\Phpfox::getService('music.album.process')->feature($this->get('album_id'), $this->get('type'))) {
            if ($this->get('type')) {
                $this->addClass('#js_album_' . $this->get('album_id'), 'row_featured');
                $this->alert(_p('album_successfully_featured'), _p('feature'), 300, 150, true);
            } else {
                $this->removeClass('#js_album_' . $this->get('album_id'), 'row_featured');
                $this->alert(_p('album_successfully_un_featured'), _p('un_feature'), 300, 150, true);
            }
        }
    }

    public function sponsorSong()
    {

        Phpfox::isUser(true);

        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return $this->alert('your_request_is_invalid');
        }
        $iSongId = $this->get('song_id');
        if (\Phpfox::getService('music.process')->sponsorSong($iSongId, $this->get('type'))) {
            $aSong = Phpfox::getService('music')->getSong($iSongId);
            if ($this->get('type') == '1') {
                $sModule = _p('music_song');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'music',
                    'section' => 'song',
                    'item_id' => $this->get('song_id'),
                    'name'    => _p('default_campaign_custom_name', ['module' => $sModule, 'name' => $aSong['title']])
                ]);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('music_song', $iSongId);
            }

            if (Phpfox::getUserParam('music.can_purchase_sponsor_song') && !Phpfox::getUserParam('music.can_sponsor_song')) {
                $sHtml = '<a href="' . \Phpfox_Url::instance()->makeUrl('ad.sponsor.' . $iSongId . '.section_music_song') . '"><i class="ico ico-sponsor"></i>' . _p('sponsor_this_song') . '</a>';
                $this->html('#js_sponsor_purchase_music_song_' . $iSongId, $sHtml);
            }

            $this->alert($this->get('type') == '1' ? _p('song_successfully_sponsored') : _p('song_successfully_un_sponsored'));
            if ($this->get('type') == '1') {
                $this->call('$("#js_controller_music_track_' . $iSongId . '").addClass("row_sponsored");');
            } else {
                $this->call('$("#js_controller_music_track_' . $this->get('song_id') . '").removeClass("row_sponsored");');
            }
        }

    }

    public function sponsorAlbum()
    {
        Phpfox::isUser(true);

        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return $this->alert('your_request_is_invalid');
        }
        $iAlbumId = $this->get('album_id');
        if (true == \Phpfox::getService('music.process')->sponsorAlbum($iAlbumId, $this->get('type'))) {
            $sHtml = '';
            $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
            if ($this->get('type') == '1') {
                $sModule = _p('music_album');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'music',
                    'section' => 'album',
                    'item_id' => $iAlbumId,
                    'name'    => _p('default_campaign_custom_name', ['module' => $sModule, 'name' => $aAlbum['name']])
                ]);
                //item was sponsored
                $sHtml = '<a href="#" title="' . _p('unsponsor_this_album') . '" onclick="$.ajaxCall(\'music.sponsorAlbum\', \'album_id=' . $iAlbumId . '&amp;type=0\'); return false;"><i class="ico ico-sponsor mr-1"></i>' . _p('unsponsor_this_album') . '</a>';
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('music_album', $iAlbumId);
                if (Phpfox::getUserParam('music.can_sponsor_album')) {
                    $sHtml = '<a href="#" title="' . _p('sponsor_this_album') . '" onclick="$.ajaxCall(\'music.sponsorAlbum\', \'album_id=' . $iAlbumId . '&amp;type=1\'); return false;"><i class="ico ico-sponsor mr-1"></i>' . _p('sponsor_this_album') . '</a>';
                } else if (Phpfox::getUserParam('music.can_purchase_sponsor_album')) {
                    $sHtml = '<a href="' . \Phpfox_Url::instance()->makeUrl('ad.sponsor.' . $iAlbumId . '.section_music_album') . '"><i class="ico ico-sponsor mr-1"></i>' . _p('encourage_sponsor_album') . '</a>';
                }
            }
            $this->html('#js_sponsor_album_' . $iAlbumId, $sHtml)
                ->alert($this->get('type') == '1' ? _p('album_successfully_sponsored') : _p('album_successfully_un_sponsored'));
            if ($this->get('type') == '1') {
                $this->addClass('#js_album_' . $iAlbumId, 'row_sponsored');
            } else {
                $this->removeClass('#js_album_' . $iAlbumId, 'row_sponsored');
            }
        }
    }

    public function approveSong()
    {
        if (\Phpfox::getService('music.process')->approve($this->get('id'))) {
            $this->alert(_p('song_has_been_approved'), _p('song_approved'), 300, 100, true);
            $this->hide('#js_item_bar_approve_image');
            $this->hide('.js_moderation_off');
            $this->show('.js_moderation_on');
            $this->call('window.location.reload();');
        }
    }

    public function setName()
    {
        $sName = $this->get('sTitle');
        $iSong = (int)$this->get('iSong');
        $sTitle = Phpfox::getService('music.song.process')->setName($iSong, $sName, true);
        if (!empty($sTitle)) {
            Phpfox::addMessage(_p('your_song_was_named_successfully'));
            $this->call('location.href = "' . \Phpfox_Url::instance()->makeUrl('music.' . $iSong . '.' . $sTitle) . '";');
        }
    }

    public function moderation()
    {
        Phpfox::isUser(true);
        switch ($this->get('action')) {
            case 'approve':
                Phpfox::getUserParam('music.can_approve_songs', true);
                foreach ((array)$this->get('item_moderate') as $iId) {
                    \Phpfox::getService('music.process')->approve($iId);
                }
                Phpfox::addMessage(_p('songs_s_successfully_approved'));
                $this->call('window.location.reload();');
                break;
            case 'delete':
                foreach ((array)$this->get('item_moderate') as $iId) {
                    if (!Phpfox::getService('music')->isAdminOfParentItem($iId)) {
                        Phpfox::getUserParam('music.can_delete_other_tracks', true);
                    }
                    \Phpfox::getService('music.process')->delete($iId);
                    $this->call('$("#js_controller_music_track_' . $iId . '").prev().remove();');
                    $this->remove('#js_controller_music_track_' . $iId);
                }
                Phpfox::addMessage(_p('songs_s_successfully_deleted'));
                $this->call('window.location.reload();');
                break;
            case 'feature':
                Phpfox::getUserParam('music.can_feature_songs', true);
                foreach ((array)$this->get('item_moderate') as $iId) {
                    \Phpfox::getService('music.process')->feature($iId, 1);
                    $this->addClass('#js_controller_music_track_' . $iId, 'row_featured');
                }
                $sMessage = _p('songs_s_successfully_featured');
                break;
            case 'un-feature':
                Phpfox::getUserParam('music.can_feature_songs', true);
                foreach ((array)$this->get('item_moderate') as $iId) {
                    \Phpfox::getService('music.process')->feature($iId, 0);
                    $this->removeClass('#js_controller_music_track_' . $iId, 'row_featured');
                }
                $sMessage = _p('songs_s_successfully_un_featured');
                break;
            default:
                $sMessage = '';
                break;
        }
        if (!empty($sMessage)) {
            $this->alert($sMessage, _p('moderation'), 300, 150, true);
        }
        $this->hide('.moderation_process');
        $this->call('setTimeout(function() {$Core.reloadPage();}, 1800);');
    }

    public function moderationAlbum()
    {
        Phpfox::isUser(true);
        $sMessage = '';
        switch ($this->get('action')) {
            case 'delete':
                foreach ((array)$this->get('item_moderate') as $iId) {
                    if (!Phpfox::getService('music.album')->isAdminOfParentItem($iId)) {
                        Phpfox::getUserParam('music.can_delete_other_music_albums', true);
                    }
                    \Phpfox::getService('music.album.process')->delete($iId);
                    $this->call('$("#js_album_' . $iId . '").prev().remove();');
                    $this->remove('#js_album_' . $iId);
                }
                Phpfox::addMessage(_p('albums_s_successfully_deleted'));
                $this->call('window.location.reload();');
                break;
            case 'feature':
                Phpfox::getUserParam('music.can_feature_music_albums', true);
                foreach ((array)$this->get('item_moderate') as $iId) {
                    \Phpfox::getService('music.album.process')->feature($iId, 1);
                    $this->addClass('#js_album_' . $iId, 'row_featured');
                }
                $sMessage = _p('albums_s_successfully_featured');
                break;
            case 'un-feature':
                Phpfox::getUserParam('music.can_feature_music_albums', true);
                foreach ((array)$this->get('item_moderate') as $iId) {
                    \Phpfox::getService('music.album.process')->feature($iId, 0);
                    $this->removeClass('#js_album_' . $iId, 'row_featured');
                }
                $sMessage = _p('albums_s_successfully_un_featured');
                break;
            default:
                $sMessage = '';
                break;
        }

        $this->alert($sMessage, _p('moderation'), 300, 150, true);
        $this->hide('.moderation_process');
        $this->call('setTimeout(function() {$Core.reloadPage();}, 1800);');
    }

    public function displayFeed()
    {
        \Phpfox::getService('feed')->processAjax($this->get('id'));
    }

    /**
     * Be used on adminCP
     * Toggle Genre
     */
    public function toggleGenre()
    {
        $iGenreID = $this->get('id');
        $iActive = $this->get('active');
        Phpfox::getService('music.genre.process')->toggleGenre($iGenreID, $iActive);
    }

    public function updateSong()
    {
        $aFullVals = $this->get('val');
        $iSongId = $this->get('song_id');
        if ($iSongId) {
            $aVals = $aFullVals[$iSongId];
            $aVals['temp_file'] = $this->get('temp_file');
            $aVals['remove_photo'] = $this->get('remove_photo');
            if (empty($aVals['title'])) {
                $this->alert(_p('provide_a_name_for_this_song'));
                return false;
            }
            $aVals['description'] = (!empty($aFullVals['description_' . $iSongId])) ? $aFullVals['description_' . $iSongId] : $this->get('description',
                '');
            $aVals['attachment'] = $aFullVals['attachment_' . $iSongId];

            if (Phpfox::getService('music.process')->update($iSongId, $aVals)) {
                if ($this->get('temp_file')) {
                    $aSong = Phpfox::getService('music')->getForEdit($iSongId);
                    $this->template()->
                    assign([
                        'aForms' => $aSong,
                    ])->getTemplate('music.block.upload-photo');
                    $this->call('$(\'#js_upload_photo_section_' . $iSongId . '\').html(\'' . $this->getContent() . '\');');
                    $this->call('$(\'#js_song_image_' . $iSongId . '\').addClass(\'has-image\').html(\'<img src="' . $aSong['current_image'] . '"/>\');');
                } else {
                    $this->call('$(\'#js_song_image_' . $iSongId . '\').removeClass(\'has-image\').html(\'<i class="ico ico-music-note-o"></i>\');');
                }
                $this->call('$(\'#js_music_song_submit_' . $iSongId . '\').removeClass(\'disabled\');');
                $this->alert(_p('Song successfully updated.'));
            }
        }
    }

    public function appendAddedSong()
    {
        $iSongId = $this->get('id');
        $aSong = Phpfox::getService('music')->getForEdit($iSongId, true);
        $aSong['canEdit'] = Phpfox::getUserParam('music.can_edit_own_song') || Phpfox::getUserParam('music.can_edit_other_song');
        $aSong['canDelete'] = Phpfox::getService('music')->canDelete($aSong);
        Phpfox::getBlock('music.upload', ['aSong' => $aSong, 'iAlbumId' => $this->get('album_id')]);
        $this->call('$(\'#js_music_uploaded_section\').append(\'' . $this->getContent() . '\');');
        $this->call('$Core.music.iValidFile++;$(\'#js_total_success\').html($Core.music.iValidFile);');
        $this->call('$Core.music.iAlbumId =' . $aSong['album_id'] . ';$(\'#js_done_upload\').show();');
        $this->call('$Core.music.aUploadedSong[' . $iSongId . '] = 1;');
        $this->call('if(!$Core.music.iTotalTimePer){$Core.music.doneAllFile();}');
        $this->call('$Core.loadInit(true);');
    }

    public function moderationPlaylist()
    {
        Phpfox::isUser(true);
        switch ($this->get('action')) {
            case 'delete':
                foreach ((array)$this->get('item_moderate') as $iId) {
                    Phpfox::getService('music.playlist.process')->delete($iId);
                }
                $sMessage = _p('playlist_s_successfully_deleted');
                $this->call('setTimeout(function(){window.location.reload();},500);');
                break;
            default:
                $sMessage = '';
                break;
        }
        if (!empty($sMessage)) {
            $this->alert($sMessage, _p('moderation'), 300, 150, true);
        }
        $this->hide('.moderation_process');
    }

    public function getPlaylistByUser()
    {
        $iSongId = $this->get('song_id');

        if (!$iSongId) {
            return false;
        }
        Phpfox::getBlock('music.user-playlist', ['song_id' => $iSongId]);
        $this->call('$(".js_music_list_playlist_' . $iSongId . '").html("' . $this->getContent() . '")');
        return true;
    }

    public function updateSongInPlaylist()
    {
        $iSongId = $this->get('song_id');
        $iPlaylistId = $this->get('playlist_id');
        $bIsAdd = $this->get('is_checked');
        if (!$iSongId || !$iPlaylistId) {
            return false;
        }
        if ($bIsAdd) {
            if (Phpfox::getService('music.playlist.process')->addSong($iSongId, $iPlaylistId)) {
                return true;
            } else {
                $this->call('$(".js_music_add_to_playlist_error_' . $iSongId . '").html("' . _p('cannot_add_song_to_this_playlist') . '").fadeIn().fadeOut(2000);');
            }
        } else {
            if (Phpfox::getService('music.playlist.process')->removeSong($iSongId, $iPlaylistId)) {
                return true;
            } else {
                $this->call('$(".js_music_add_to_playlist_error_' . $iSongId . '").html("' . _p('cannot_remove_song_from_this_playlist') . '").fadeIn().fadeOut(2000);');
                $this->call('$(".music_playlist_checklist_' . $iSongId . '_' . $iPlaylistId . '").prop("checked", true);');
            }
        }
        return false;
    }

    public function addPlaylistOnEntry()
    {
        $iSongId = $this->get('song_id');
        $sName = $this->get('name');

        if (empty($iSongId) || empty($sName)) {
            return false;
        }
        if ($iId = Phpfox::getService('music.playlist.process')->add(['name' => $sName])) {
            Phpfox::getService('music.playlist.process')->addSong($iSongId, $iId);
            $this->call('$(".js_music_add_to_playlist_success_' . $iSongId . '").html("' . _p('successfully_added_playlist') . '").css("display", "flex").hide().fadeIn().fadeOut(2000);');
            $this->call('oParent = $(\'.js_music_add_to_playlist_' . $iSongId . '\');oParent.prop(\'opened-form\', false);oParent.find(\'.js_down\').show();oParent.find(\'.js_up\').hide();oParent.find(\'.js_music_quick_add_playlist_form\').slideUp(200);oParent.find(\'.js_submit\').removeClass(\'disabled\');');

            Phpfox::getBlock('music.user-playlist', ['song_id' => $iSongId]);
            $this->call('$(".js_music_list_playlist_' . $iSongId . '").html("' . $this->getContent() . '")');
        } else {
            $this->call('$(".js_music_add_to_playlist_error_' . $iSongId . '").html("' . _p('cannot_add_new_playlist') . '").css("display", "flex").fadeIn().fadeOut(2000);');
            $this->call('oParent = $(\'.js_music_add_to_playlist_' . $iSongId . '\');oParent.find(\'.js_submit\').removeClass(\'disabled\');');
        }
        return false;
    }

    public function loadUserPlaylistInDetail()
    {
        $iSongId = $this->get('song_id');
        if (empty($iSongId)) {
            return false;
        }
        Phpfox::getBlock('music.add-to-playlist', ['song_id' => $iSongId]);

        $this->call('$("#js_music_add_to_playlist_dropdown #js_music_playlist_dropdown_menu").html("' . $this->getContent() . '");');
        $this->call('if(!$Core.music.isMobile){$(".music-quick-list-playlist").mCustomScrollbar({theme: "minimal-dark",mouseWheel: {preventDefault: true}}).addClass(\'dont-unbind-children\')};');
        return true;
    }

    /**
     * Displays the form that adds a new photo album.
     *
     */
    public function newAlbum()
    {
        $this->setTitle(_p('create_album'));
        // Only users can view this form.
        Phpfox::isUser(true);
        // Only users with this specific user group perm. can view this form.
        Phpfox::getUserParam('music.can_access_music', true);
        Phpfox::getService('music.album')->canCreateNewAlbum();
        // Display the block form
        Phpfox::getComponent('music.album', ['popup' => 1], 'controller');

        $this->call('<script type="text/javascript">$Core.loadInit();</script>');
    }

    public function addAlbumInline()
    {
        Phpfox::getUserParam('music.can_access_music', true);
        Phpfox::getService('music.album')->canCreateNewAlbum();
        $aVals = $this->get('val');
        $aValidation = [
            'name' => _p('provide_a_name_for_this_album'),
            'year' => [
                'def' => 'year'
            ]
        ];

        $oValidator = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_music_add_album_form',
                'aParams'   => $aValidation
            ]
        );

        if ($aVals) {
            if ($oValidator->isValid($aVals)) {
                if ($iId = Phpfox::getService('music.album.process')->add($aVals)) {
                    $this->call('$Core.music.onAddAlbumSuccess(' . $iId . ', "' . Phpfox::getLib('parse.output')->clean(html_entity_decode($aVals['name'], ENT_QUOTES, 'UTF-8')) . '");');
                }
            } else {
                $this->call('$("#js_music_add_album_form").find("#js_music_album_submit").removeClass("disabled");');
            }
        }
    }
}