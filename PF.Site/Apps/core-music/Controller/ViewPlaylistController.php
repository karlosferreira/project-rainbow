<?php

namespace Apps\Core_Music\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

class ViewPlaylistController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::getUserParam('music.can_access_music', true);

        $aPlaylist = Phpfox::getService('music.playlist')->getPlaylist($this->request()->getInt('req3'));

        if (!isset($aPlaylist['playlist_id'])) {
            return Phpfox_Error::display(_p('unable_to_find_the_playlist_you_are_looking_for'));
        }

        if (Phpfox::isModule('privacy')) {
            if ($aPlaylist['user_id'] != Phpfox::getUserId() && !Phpfox::getService('privacy')->check('music', $aPlaylist['playlist_id'], $aPlaylist['user_id'], $aPlaylist['privacy'], null, true)) {
                return Phpfox_Error::display(_p('you_do_not_have_permission_to_view_this_playlist'));
            }
        } else {
            if ($aPlaylist['user_id'] != Phpfox::getUserId()) {
                return Phpfox_Error::display(_p('you_do_not_have_permission_to_view_this_playlist'));
            }
        }

        $this->setParam('aPlaylist', $aPlaylist);

        $this->setParam('aFeed', [
                'comment_type_id' => 'music_playlist',
                'privacy'         => $aPlaylist['privacy'],
                'comment_privacy' => Phpfox::getUserParam('music.can_add_comment_on_music_playlist') ? 0 : 3,
                'like_type_id'    => 'music_playlist',
                'feed_is_liked'   => $aPlaylist['is_liked'],
                'feed_is_friend'  => $aPlaylist['is_friend'],
                'item_id'         => $aPlaylist['playlist_id'],
                'user_id'         => $aPlaylist['user_id'],
                'total_comment'   => $aPlaylist['total_comment'],
                'total_like'      => $aPlaylist['total_like'],
                'feed_link'       => $this->url()->makeUrl('music.playlist.' . $aPlaylist['playlist_id'] . '.' . $aPlaylist['name']),
                'feed_title'      => $aPlaylist['name'],
                'feed_display'    => 'view',
                'feed_total_like' => $aPlaylist['total_like'],
                'report_module'   => 'music_playlist',
                'report_phrase'   => _p('report_this_song_lowercase')
            ]
        );

        Phpfox::getService('music.playlist')->checkPermission($aPlaylist);

        $this->template()
            ->setTitle($aPlaylist['name'])
            ->setBreadCrumb(_p('music_playlist'), $this->url()->makeUrl('music.browse.playlist'))
            ->setBreadCrumb($aPlaylist['name'],
                $this->url()->permalink('music.playlist', $aPlaylist['playlist_id'], $aPlaylist['name']), true)
            ->assign([
                    'aPlaylist'         => $aPlaylist,
                    'sDefaultThumbnail' => Phpfox::getParam('music.default_playlist_photo'),
                    'sShareDescription' => str_replace(["\n", "\r", "\r\n"], '', $aPlaylist['description'])
                ]
            )->setHeader([
                'jscript/mediaelementplayer/mediaelement-and-player.js' => 'app_core-music'
            ]);

        \Phpfox::getService('music')->getSectionMenu();

        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$aPlaylist['is_viewed']) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('music', 'playlist_' . $aPlaylist['playlist_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('music', 'playlist_' . $aPlaylist['playlist_id']);
                } else {
                    Phpfox::getService('track.process')->update('music_playlist', $aPlaylist['playlist_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            db()->updateCounter('music_playlist', 'total_view', 'playlist_id', $aPlaylist['playlist_id']);
        }
        return 'controller';
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = \Phpfox_Plugin::get('music.component_controller_view_playlist_clean')) ? eval($sPlugin) : false);
    }
}