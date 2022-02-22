<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;

class IndexController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (defined('PHPFOX_IS_USER_PROFILE') && ($sLegacyTitle = $this->request()->get('req4')) && !empty($sLegacyTitle)) {
            \Phpfox::getService('core')->getLegacyItem([
                    'field'    => ['song_id', 'title'],
                    'table'    => 'music_song',
                    'redirect' => 'music',
                    'title'    => $sLegacyTitle
                ]
            );
        }
        Phpfox::getUserParam('music.can_access_music', true);

        if (defined('PHPFOX_IS_USER_PROFILE') || defined('PHPFOX_IS_PAGES_VIEW')) {
            $aUser = (!defined('PHPFOX_IS_PAGES_VIEW')) ? $this->getParam('aUser') : $this->getParam('aPage');
            $bShowSongs = $this->request()->get('req3') != 'album' || $this->request()->get('req4') != 'album';
            if (defined('PHPFOX_IS_PAGES_VIEW')) {
                if (empty($aUser['vanity_url'])) {
                    $sStyle = defined('PHPFOX_PAGES_ITEM_TYPE') ? PHPFOX_PAGES_ITEM_TYPE : 'pages';
                    $aUser['user_name'] = $sStyle . '.' . $aUser['page_id'];
                } else {
                    $aUser['user_name'] = $aUser['vanity_url'];
                }
                $aUser['profile_page_id'] = 0;
            }
            $bSpecialMenu = (!defined('PHPFOX_IS_AJAX_CONTROLLER'));
            $this->template()->assign([
                'bSpecialMenu' => $bSpecialMenu,
                'bShowSongs'   => $bShowSongs,
                'sSongLink'    => $this->url()->makeUrl($aUser['user_name'] . '.music'),
                'sAlbumLink'   => $this->url()->makeUrl($aUser['user_name'] . '.music.album')
            ]);
        } else {
            $this->template()->assign(['bSpecialMenu' => false]);
        }

        if (!$this->request()->get('delete') && defined('PHPFOX_IS_PAGES_VIEW') && ($this->request()->get('req3') == 'album' || $this->request()->get('req4') == 'album')) {
            Phpfox::getComponent('music.browse.album', ['bNoTemplate' => true], 'controller');
            return null;
        }
        $aParentModule = $this->getParam('aParentModule');

        if ($aParentModule === null && (!defined('PHPFOX_IS_USER_PROFILE') || (defined('PHPFOX_IS_USER_PROFILE') && isset($aUser['user_id']) && Phpfox::getUserId() == $aUser['user_id']))) {
            if (Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false)) {
                sectionMenu(_p('share_songs'), url('/music/upload'));
            }
        }
        if ($this->request()->get('req2') == 'delete' && ($iDeleteId = $this->request()->getInt('id'))) {
            $mDeleteReturn = \Phpfox::getService('music.process')->delete($iDeleteId);
            if ($iAlbumId = $this->request()->getInt('album', 0)) {
                $this->url()->send('music.album.manage', ['id' => $iAlbumId], _p('song_successfully_deleted'));
            }
            if (is_bool($mDeleteReturn)) {
                if ($mDeleteReturn) {
                    $this->url()->send('music', null, _p('song_successfully_deleted'));
                } else {
                    $this->url()->send('music', null, _p('you_do_not_have_permission_to_delete_this_song'));
                }
            } else {
                $this->url()->forward($mDeleteReturn, _p('song_successfully_deleted'));
            }
        }

        $sView = $this->request()->get('view');

        if (($sRedirect = $this->request()->getInt('redirect')) && ($aSong = \Phpfox::getService('music')->getSong(Phpfox::getUserId(), $sRedirect))
        ) {
            $this->url()->send($aSong['user_name'],
                ['music', ($aSong['album_id'] ? $aSong['album_url'] : 'view'), $aSong['title_url']]);
        }

        if ($aParentModule === null && $this->request()->getInt('req2')) {
            return \Phpfox_Module::instance()->setController('music.view');
        }
        $aUser = [];

        if (defined('PHPFOX_IS_AJAX_CONTROLLER')) {
            $bIsProfile = true;
            $aUser = \Phpfox::getService('user')->get($this->request()->get('profile_id'));
            $this->setParam('aUser', $aUser);
        } else {
            $bIsProfile = $this->getParam('bIsProfile');
            if ($bIsProfile === true) {
                $aUser = $this->getParam('aUser');
            }
        }

        $this->template()->setTitle(($bIsProfile ? _p('fullname_s_songs',
            ['full_name' => $aUser['full_name']]) : _p('music')))->setBreadCrumb(_p('all_songs'),
            ($bIsProfile ? $this->url()->makeUrl($aUser['user_name'], 'music') : $this->url()->makeUrl('music')))
            ->setMeta('keywords', Phpfox::getParam('music.music_meta_keywords'))
            ->setMeta('description', Phpfox::getParam('music.music_meta_description'));

        if ($aParentModule === null) {
            \Phpfox::getService('music')->getSectionMenu();
        }

        $this->search()->set([
                'type'           => 'music_song',
                'field'          => 'm.song_id',
                'ignore_blocked' => true,
                'search_tool'    => [
                    'table_alias' => 'm',
                    'search'      => [
                        'action'        => (defined('PHPFOX_IS_PAGES_VIEW') ? $aParentModule['url'] . 'music/' : ($bIsProfile === true ? $this->url()->makeUrl($aUser['user_name'],
                            ['music', 'view' => $this->request()->get('view')]) : $this->url()->makeUrl('music',
                            ['view' => $this->request()->get('view')]))),
                        'default_value' => _p('search_songs'),
                        'name'          => 'search',
                        'field'         => 'm.title'
                    ],
                    'sort'        => [
                        'latest'      => ['m.time_stamp', _p('latest')],
                        'most-viewed' => ['m.total_view', _p('most_viewed')],
                        'most-played' => ['m.total_play', _p('most_played')],
                        'most-liked'  => ['m.total_like', _p('most_liked')],
                        'most-talked' => ['m.total_comment', _p('most_discussed')]
                    ],
                    'show'        => [10, 20, 30]
                ]
            ]
        );

        $aBrowseParams = [
            'module_id' => 'music.song',
            'alias'     => 'm',
            'alias_select' => 'm.song_id, m.view_id, m.privacy, m.privacy_comment, m.is_featured, m.is_sponsor, m.album_id, m.user_id, m.title, m.song_path, m.server_id, m.explicit, m.duration, m.ordering, m.image_path, m.image_server_id, m.total_play, m.total_view, m.total_comment, m.total_like, m.total_dislike, m.total_score, m.total_rating, m.total_attachment, m.time_stamp, m.module_id, m.item_id',
            'field'     => 'song_id',
            'table'     => Phpfox::getT('music_song'),
            'hide_view' => ['pending', 'my']
        ];

        $iGenre = $this->request()->getInt('req3');

        switch ($sView) {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND (m.user_id = ' . Phpfox::getUserId() . ' AND m.view_id != 2)');
                break;
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('music.can_approve_songs', true);
                $this->search()->setCondition('AND m.view_id = 1');
                $this->template()->assign('bIsInPendingMode', true);
                break;
            default:
                if ($bIsProfile === true) {
                    $this->search()->setCondition("AND m.item_id = 0 AND m.view_id IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND m.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($aUser)) . ") AND m.user_id = " . $aUser['user_id'] . "");
                } else {
                    $this->search()->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)");
                    if ($sView == 'featured') {
                        $this->search()->setCondition('AND m.is_featured = 1');
                    }
                }
                break;
        }

        if ($iGenre && ($aGenre = \Phpfox::getService('music.genre')->getGenre($iGenre))) {
            $this->search()->setCondition('AND mgd.genre_id = ' . (int)$iGenre);
            $this->search()->setFormUrl($this->url()->permalink([
                'music.genre',
                'view' => $sView
            ], $iGenre, _p($aGenre['name'])));
            $this->template()->setBreadCrumb(_p($aGenre['name']),
                $this->url()->permalink('music.genre', $aGenre['genre_id'], _p($aGenre['name'])),
                true);
        }
        $iFromUser = $this->request()->get('user');
        if ($iFromUser) {
            $this->search()->setCondition(' AND m.user_id=' . intval($iFromUser));
        }

        if ($aParentModule !== null) {
            $this->search()->setCondition("AND m.module_id = '" . Phpfox::getLib('database')->escape($aParentModule['module_id']) . "' AND m.item_id = " . (int)$aParentModule['item_id']);
        } else {
            if ($sView != 'pending' && $sView != 'my' && !$bIsProfile) {
                if ((Phpfox::getParam('music.music_display_music_created_in_group') || Phpfox::getParam('music.music_display_music_created_in_page')) && $bIsProfile !== true) {
                    $aModules = [];
                    if (Phpfox::getParam('music.music_display_music_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                        $aModules[] = 'groups';
                    }
                    if (Phpfox::getParam('music.music_display_music_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                        $aModules[] = 'pages';
                    }
                    if (count($aModules)) {
                        $this->search()->setCondition('AND (m.module_id IN ("' . implode('","',
                                $aModules) . '") OR m.module_id is NULL)');
                    } else {
                        $this->search()->setCondition('AND m.module_id is NULL');
                    }
                } else {
                    $this->search()->setCondition('AND m.item_id = 0');
                }
            }
        }

        // PARENT MODULE: PRIVACY AND BREADCRUMB
        $bIsAdmin = false;
        if (!empty($aParentModule) && Phpfox::hasCallback($aParentModule['module_id'], 'isAdmin')) {
            $bIsAdmin = Phpfox::callback($aParentModule['module_id'] . '.isAdmin', $aParentModule['item_id']);
        }

        if (defined('PHPFOX_IS_PAGES_VIEW') && PHPFOX_IS_PAGES_VIEW && defined('PHPFOX_PAGES_ITEM_TYPE') && $aParentModule) {
            $sService = PHPFOX_PAGES_ITEM_TYPE ? PHPFOX_PAGES_ITEM_TYPE : 'pages';
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $aParentModule['item_id'], 'music.view_browse_music')) {
                $this->template()->assign(['aSearchTool' => []]);
                return \Phpfox_Error::display(_p('Cannot display this section due to privacy.'));
            }

            if (Phpfox::getService($sService)->isAdmin($aParentModule['item_id'])) {
                $bIsAdmin = true;
                $this->request()->set('view', 'pages_admin');
            } else if (Phpfox::getService($sService)->isMember($aParentModule['item_id'])) {
                $this->request()->set('view', 'pages_member');
            }

            $sTitle = Phpfox::getService($sService)->getTitle($aParentModule['item_id']);
            $this->template()
                ->clearBreadCrumb()
                ->setBreadCrumb($sTitle, $aParentModule['url'])
                ->setBreadCrumb(_p('all_songs'), $aParentModule['url'] . 'music/')
                ->setTitle(_p('music') . ' &raquo; ' . $sTitle, true);
        }

        $this->search()->setContinueSearch(true);
        $this->search()->browse()
            ->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('music.music_paging_mode', 'loadmore'))
            ->execute();

        $aSongs = $this->search()->browse()->getRows();
        \Phpfox_Pager::instance()->set([
            'page'        => $this->search()->getPage(),
            'size'        => $this->search()->getDisplay(),
            'count'       => $this->search()->browse()->getCount(),
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ]);

        if ($sPlugin = \Phpfox_Plugin::get('music.component_controller_music_index')) {
            eval($sPlugin);
        }

        $this->template()
            ->assign([
                    'aSongs'     => $aSongs,
                    'sMusicView' => $sView,
                ]
            );

        $aModerationMenu = [];
        $bShowModerator = $bIsAdmin;
        if ($sView == 'pending') {
            if (Phpfox::getUserParam('music.can_approve_songs')) {
                $aModerationMenu[] = [
                    'phrase' => _p('approve'),
                    'action' => 'approve'
                ];
            }
        } else if (Phpfox::getUserParam('music.can_feature_songs')) {
            $aModerationMenu[] = [
                'phrase' => _p('feature'),
                'action' => 'feature'
            ];
            $aModerationMenu[] = [
                'phrase' => _p('un_feature'),
                'action' => 'un-feature'
            ];
        }
        if (Phpfox::getUserParam('music.can_delete_other_tracks') || $bIsAdmin) {
            $aModerationMenu[] = [
                'phrase' => _p('delete'),
                'action' => 'delete',
                'message' => _p('are_you_sure_you_want_to_delete_selected_songs_permanently'),
            ];
        }
        if (count($aModerationMenu)) {
            $this->setParam('global_moderation', [
                    'name' => 'musicsong',
                    'ajax' => 'music.moderation',
                    'menu' => $aModerationMenu
                ]
            );
            $bShowModerator = true;
        }

        $this->template()->assign(['bShowModerator' => $bShowModerator]);
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = \Phpfox_Plugin::get('music.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}
