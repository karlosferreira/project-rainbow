<?php

namespace Apps\Core_Music\Controller\Browse;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class PlaylistController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::getUserParam('music.can_access_music', true);

        if ($iDelete = $this->request()->getInt('id')) {
            $mDeleteReturn = Phpfox::getService('music.playlist.process')->delete($iDelete);
            if ($mDeleteReturn) {
                $this->url()->send('music.browse.playlist', null, _p('playlist_successfully_deleted'));
            } else {
                $this->url()->send('music.browse.playlist', null, _p('not_allowed_to_delete_this_playlist'));
            }
        }

        $sView = $this->request()->get('view');

        $this->template()->setTitle($sView == 'my-playlist' ? _p('my_playlists') : _p('all_music_playlists'))
            ->setBreadCrumb($sView == 'my-playlist' ? _p('my_playlists') : _p('all_music_playlists'), $sView == 'my-playlist' ? $this->url()->makeUrl('music.browse.playlist.view_my-playlist') : $this->url()->makeUrl('music.browse.playlist'));

        $this->search()->set([
                'type'           => 'music_playlist',
                'field'          => 'mp.playlist_id',
                'ignore_blocked' => true,
                'search_tool'    => [
                    'table_alias' => 'mp',
                    'search'      => [
                        'action'        => ($sView == 'my-playlist') ? $this->url()->makeUrl('music.browse.playlist.view_my-playlist') : $this->url()->makeUrl('music.browse.playlist'),
                        'default_value' => _p('search_playlists_dot'),
                        'name'          => 'search',
                        'field'         => 'mp.name'
                    ],
                    'sort'        => [
                        'latest'      => ['mp.time_stamp', _p('latest')],
                        'most-viewed' => ['mp.total_view', _p('most_viewed')],
                        'most-songs'  => ['mp.total_track', _p('most_songs')]
                    ],
                    'show'        => [10, 20, 30]
                ]
            ]
        );

        $aBrowseParams = [
            'module_id' => 'music.playlist',
            'alias'     => 'mp',
            'field'     => 'playlist_id',
            'table'     => Phpfox::getT('music_playlist'),
            'hide_view' => []
        ];

        switch ($sView) {
            case 'my-playlist':
                {
                    Phpfox::isUser(true);
                    $this->search()->setCondition('AND mp.user_id = ' . (int)Phpfox::getUserId());
                    break;
                }
            default:
                {
                    $this->search()->setCondition("AND mp.view_id = 0 AND mp.privacy IN(%PRIVACY%)");
                    break;
                }
        }


        $this->search()->browse()->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('music.music_paging_mode', 'loadmore'))
            ->execute();

        \Phpfox_Pager::instance()->set([
            'page'        => $this->search()->getPage(),
            'size'        => $this->search()->getDisplay(),
            'count'       => $this->search()->browse()->getCount(),
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ]);

        $playlists = $this->search()->browse()->getRows();
        if (!empty($playlists)) {
            foreach ($playlists as $iKey => $playlist) {
                Phpfox::getService('music.playlist')->checkPermission($playlists[$iKey]);
            }
        }


        //Section menu
        Phpfox::getService('music')->getSectionMenu();

        if (Phpfox::getService('music.playlist')->canCreateNewPlaylist(Phpfox::getUserId(), false)) {
            sectionMenu(_p('add_a_playlist'), url('/music/playlist'));
        }


        $this->template()
            ->assign([
                    'aPlaylists'        => $playlists,
                    'sDefaultThumbnail' => Phpfox::getParam('music.default_playlist_photo')
                ]

            )
            ->setMeta('keywords', Phpfox::getParam('music.music_meta_keywords'))
            ->setMeta('description', Phpfox::getParam('music.music_meta_description'));

        $this->setParam('global_moderation', [
                'name' => 'musicplaylist',
                'ajax' => 'music.moderationPlaylist',
                'menu' => [
                    [
                        'phrase' => _p('delete'),
                        'action' => 'delete',
                        'message' => _p('are_you_sure_you_want_to_delete_selected_playlists_permanently'),
                    ]
                ]
            ]
        );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = \Phpfox_Plugin::get('music.component_controller_browse_playlist_clean')) ? eval($sPlugin) : false);
    }
}