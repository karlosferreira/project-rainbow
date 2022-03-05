<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Controller\Browse;

use Phpfox;
use Phpfox_Pager;

defined('PHPFOX') or exit('NO DICE!');

class AlbumController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::getUserParam('music.can_access_music', true);

        if (($iDeleteAlbum = $this->request()->getInt('id')) && $mDeleteReturn = Phpfox::getService('music.album.process')->delete($iDeleteAlbum)) {
            if (is_bool($mDeleteReturn)) {
                if ($mDeleteReturn) {
                    $this->url()->send('music.browse.album', null, _p('album_successfully_deleted'));
                } else {
                    $this->url()->send('music.browse.album', null, _p('not_allowed_to_delete_this_album'));
                }
            } else {
                $this->url()->forward($mDeleteReturn, _p('album_successfully_deleted'));
            }
        }

        $sView = $this->request()->get('view');

        $this->template()->setTitle(_p('music_albums'))
            ->setBreadCrumb($sView == 'my-album' ? _p('my_albums') : _p('all_albums'), $sView == 'my-album' ? $this->url()->makeUrl('music.browse.album', ['view' => $sView]) : $this->url()->makeUrl('music.browse.album'));
        $aParentModule = $this->getParam('aParentModule');
        if ($aParentModule === null) {
            \Phpfox::getService('music')->getSectionMenu();
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
        $aParentModule = $this->getParam('aParentModule');

        $this->search()->set([
                'type'           => 'music_album',
                'field'          => 'm.album_id',
                'ignore_blocked' => true,
                'search_tool'    => [
                    'table_alias' => 'm',
                    'search'      => [
                        'action'        => ($aParentModule !== null ? $aParentModule['url'] . 'music/album' : ($bIsProfile === true ? $this->url()->makeUrl($aUser['user_name'],
                            [
                                'music/album',
                                'view' => $this->request()->get('view')
                            ]) : $this->url()->makeUrl('music.browse.album',
                            ['view' => $this->request()->get('view')]))),
                        'default_value' => _p('search_albums'),
                        'name'          => 'search',
                        'field'         => 'm.name'
                    ],
                    'sort'        => [
                        'latest'      => ['m.time_stamp', _p('latest')],
                        'most-liked'  => ['m.total_like', _p('most_liked')],
                        'most-talked' => ['m.total_comment', _p('most_discussed')]
                    ],
                    'show'        => [10, 20, 30]
                ]
            ]
        );

        $aBrowseParams = [
            'module_id' => 'music.album',
            'alias'     => 'm',
            'field'     => 'album_id',
            'table'     => Phpfox::getT('music_album'),
            'hide_view' => ['pending', 'my', 'my-album']
        ];

        switch ($sView) {
            case 'my-album':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                break;
            default:
                if ($bIsProfile === true) {
                    $this->search()->setCondition("AND m.view_id IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND m.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($aUser)) . ") AND m.user_id = " . $aUser['user_id'] . "");
                } else {
                    $this->search()->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)");
                    if ($sView == 'featured') {
                        $this->search()->setCondition('AND m.is_featured = 1');
                    }
                }
                break;
        }
        $iFromUser = $this->request()->get('user');
        if ($iFromUser) {
            $this->search()->setCondition(' AND m.user_id=' . intval($iFromUser));
        }
        if ($aParentModule !== null) {
            $this->search()->setCondition("AND m.module_id = '" . Phpfox::getLib('database')->escape($aParentModule['module_id']) . "' AND m.item_id = " . (int)$aParentModule['item_id']);
        } else {
            if ($sView != 'my-album') {
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
                ->setBreadCrumb(_p('all_albums'), $aParentModule['url'] . 'music/album')
                ->setTitle(_p('music_albums') . ' &raquo; ' . $sTitle, true);
        }

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('music.music_paging_mode', 'loadmore'))
            ->execute();

        Phpfox_Pager::instance()->set([
            'page'        => $this->search()->getPage(),
            'size'        => $this->search()->getDisplay(),
            'count'       => $this->search()->browse()->getCount(),
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ]);

        $albums = $this->search()->browse()->getRows();
        foreach ($albums as $key => $album) {
            $albums[$key]['songs'] = \Phpfox::getService('music')->getSongs($album['user_id'], $album['album_id']);
        }
        if ($aParentModule === null && Phpfox::getService('music.album')->canCreateNewAlbum(Phpfox::getUserId(), false) && (!defined('PHPFOX_IS_USER_PROFILE') || (defined('PHPFOX_IS_USER_PROFILE') && Phpfox::getUserId() == $aUser['user_id']))) {
            sectionMenu(_p('add_an_album'), url('/music/album/add'));
        }
        $this->template()
            ->assign([
                    'aAlbums'           => $albums,
                    'sDefaultThumbnail' => Phpfox::getParam('music.default_album_photo')
                ]

            )
            ->setMeta('keywords', Phpfox::getParam('music.music_meta_keywords'))
            ->setMeta('description', Phpfox::getParam('music.music_meta_description'));

        if (defined('PHPFOX_IS_USER_PROFILE') || defined('PHPFOX_IS_PAGES_VIEW')) {
            $aTplParam = ['bSpecialMenu' => true];
            if (defined('PHPFOX_IS_PAGES_VIEW')) {
                $aTplParam['bShowSongs'] = false;
            }
            $this->template()->assign($aTplParam);
        } else {
            $this->template()->assign(['bSpecialMenu' => false]);
        }
        $aModerationMenu = [];
        $bShowModerator = $bIsAdmin;
        if (Phpfox::getUserParam('music.can_feature_music_albums')) {
            $aModerationMenu[] = [
                'phrase' => _p('feature'),
                'action' => 'feature'
            ];
            $aModerationMenu[] = [
                'phrase' => _p('un_feature'),
                'action' => 'un-feature'
            ];
        }
        if (Phpfox::getUserParam('music.can_delete_other_music_albums') || $bIsAdmin) {
            $aModerationMenu[] = [
                'phrase' => _p('delete'),
                'action' => 'delete',
                'message' => _p('are_you_sure_you_want_to_delete_selected_albums_permanently')
            ];
        }
        if (count($aModerationMenu)) {
            $this->setParam('global_moderation', [
                    'name' => 'musicalbum',
                    'ajax' => 'music.moderationAlbum',
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
        (($sPlugin = \Phpfox_Plugin::get('music.component_controller_browse_album_clean')) ? eval($sPlugin) : false);
    }
}
