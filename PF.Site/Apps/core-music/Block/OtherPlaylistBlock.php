<?php

namespace Apps\Core_Music\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class OtherPlaylistBlock extends Phpfox_Component
{
    public function process()
    {
        if (defined('PHPFOX_IS_USER_PROFILE')) {
            return false;
        }
        $aPlaylist = $this->getParam('aPlaylist');
        if (!$aPlaylist) {
            return false;
        }

        $iLimit = $this->getParam('limit', 4);

        if (!(int)$iLimit) {
            return false;
        }
        $aConditions = [
            'AND mp.playlist_id <>' . (int)$aPlaylist['playlist_id']
        ];
        $aOther = Phpfox::getService('music.playlist')->getPlaylists($aConditions, $iLimit);

        if (!count($aOther)) {
            return false;
        }

        $this->template()->assign([
                'sHeader'         => _p('other_playlists'),
                'aOtherPlaylists' => $aOther,
                'aFooter'         => [_p('view_more') => url('music.browse.playlist')]
            ]
        );

        return 'block';
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            [
                'info'        => _p('Other Playlists Limit'),
                'description' => _p('Define the limit of how many other playlists can be displayed when viewing the playlist detail. Set 0 will hide this block'),
                'value'       => 4,
                'type'        => 'integer',
                'var_name'    => 'limit',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getValidation()
    {
        return [
            'limit' => [
                'def'   => 'int',
                'min'   => 0,
                'title' => _p('"Other Playlists Limit" must be greater than or equal to 0')
            ],
        ];
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = \Phpfox_Plugin::get('music.component_block_other_playlist_clean')) ? eval($sPlugin) : false);
    }
}