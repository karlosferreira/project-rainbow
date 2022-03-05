<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Block;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class TrackBlock extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if ($this->getParam('playlist_id') !== null) {
            $aSongs = Phpfox::getService('music.playlist')->getAllSongs($this->getParam('playlist_id'), true);
        } else {
            if (!$this->getParam('inline_album')) {
                return false;
            }

            if ($this->getParam('album_user_id', null) === null) {
                return false;
            }
            $aSongs = Phpfox::getService('music.album')->getTracks($this->getParam('album_user_id'),
                $this->getParam('album_id'), $this->getParam('album_view_all', false));
        }
        $this->template()->assign([
                'aTracks'        => $aSongs,
                'iTotalSong'     => count($aSongs),
                'bIsMusicPlayer' => ($this->getParam('is_player') ? true : false),
                'bFixPopup'      => true
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
        (($sPlugin = \Phpfox_Plugin::get('music.component_block_track_clean')) ? eval($sPlugin) : false);

        $this->clearParam('inline_album');
    }
}