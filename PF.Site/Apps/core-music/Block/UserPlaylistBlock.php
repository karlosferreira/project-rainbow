<?php

namespace Apps\Core_Music\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class UserPlaylistBlock extends Phpfox_Component
{
    public function process()
    {
        $iSongId = $this->getParam('song_id');
        if (!Phpfox::getUserId() || !$iSongId) {
            return false;
        }
        $this->template()->assign([
            'aItems'  => Phpfox::getService('music.playlist')->getAllPlaylist(Phpfox::getUserId(), $iSongId),
            'iSongId' => $iSongId
        ]);

        return 'block';
    }
}