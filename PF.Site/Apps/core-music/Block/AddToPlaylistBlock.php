<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Block;

use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

class AddToPlaylistBlock extends Phpfox_Component
{
    /**
     * Controller
     */

    public function process()
    {
        $iSongId = $this->getParam('song_id');
        if (!$iSongId) {
            return false;
        }
        $this->template()->assign([
            'aSong'        => [
                'song_id' => $iSongId
            ],
            'isDetailPage' => true
        ]);
        return 'block';
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = \Phpfox_Plugin::get('music.component_block_add_to_playlist_clean')) ? eval($sPlugin) : false);
    }
}