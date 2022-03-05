<?php
namespace Apps\Core_Music\Service;

use Core\Api\ApiServiceBase;
use Phpfox;

class Api extends ApiServiceBase
{
    /*--------------------------Song-----------------------*/

    /**
     * @description: get detail info of a photo
     *
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function get($params, $messages = [])
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.', [
                'action' => _p('view__l'),
                'item' => _p('song')
            ]), true);
        } elseif (empty($params['id'])
            || empty($song = Phpfox::getService('music')->getSong($params['id']))
            || ($song['view_id'] && !Phpfox::getUserParam('music.can_approve_songs') && $song['user_id'] != Phpfox::getUserId())
            || (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $song['user_id']))) {
            return $this->error(_p('the_song_you_are_looking_for_cannot_be_found'), true);
        }

        if (!empty($song['module_id'])
            && !empty(Phpfox::callback($song['module_id'] . '.getMusicDetails', $song))
            && Phpfox::isModule($song['module_id'])
            && Phpfox::hasCallback($song['module_id'], 'checkPermission')
            && !Phpfox::callback($song['module_id'] . '.checkPermission', $song['item_id'],
                'music.view_browse_music')) {
            return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $song['song_id'], $song['user_id'], $song['privacy'],
                $song['is_friend'], true)) {
            return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
        }

        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$song['is_viewed']) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('music', 'song_' . $song['song_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('music', 'song_' . $song['song_id']);
                } else {
                    Phpfox::getService('track.process')->update('music_song', $song['song_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            db()->updateCounter('music_song', 'total_view', 'song_id', $song['song_id']);
        }

        if (!empty($song['image_path'])) {
            $song['song_image_url'] = Phpfox::getLib('image.helper')->display([
                'server_id' => $song['image_server_id'],
                'path' => 'music.url_image',
                'file' => $song['image_path'],
                'suffix' => '_200_square',
                'return_url' => true,
            ]);
        } else {
            $song['song_image_url'] = Phpfox::getParam('music.default_song_photo');
        }

        $song = array_merge($song, [
            'song_url' => $song['song_path'],
        ]);

        $this->setPublicFields($this->_getPublicFields());

        return $this->success($this->getItem($song), $messages);
    }

    /**
     * @description: update info for a photo
     *
     * @param $params
     *
     * @return array|bool
     */
    public function put($params)
    {
        $this->isUser();

        if (empty($params['id']) || empty($songItem = Phpfox::getService('music')->getForEdit((int)$params['id']))) {
            return $this->error(_p('This {{ item }} cannot be found.', ['item' => _p('song')]), true);
        } elseif (!Phpfox::getService('music')->canUpdate($songItem)) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                ['action' => _p('edit__l'), 'item' => _p('song')]));
        }

        $validateObject = \Phpfox_Validator::instance()->set([
            'sFormName' => 'js_music_form',
            'aParams'   => [
                'title' => _p('provide_a_name_for_this_song')
            ]
        ]);
        
        $vals = $this->request()->getArray('val');

        if ($validateObject->isValid($vals)) {
            if (Phpfox::getService('music.process')->update($songItem['song_id'], $vals)) {
                return $this->get(['id' => $songItem['song_id']], [_p('Song successfully updated.')]);
            }
        }

        return $this->error();
    }

    /**
     * @description: delete a photo
     *
     * @param $params
     *
     * @return array|bool
     */
    public function delete($params)
    {
        $this->isUser();

        if (empty($params['id']) || empty($song = Phpfox::getService('music')->getSong((int)$params['id']))) {
            return $this->error(_p('This {{ item }} cannot be found.', ['item' => _p('song')]), true);
        } elseif (!Phpfox::getService('music')->canDelete($song) || !Phpfox::getService('music.process')->delete($params['id'])) {
            return $this->error(_p('Cannot {{ action }} this {{ item }}.',
                ['action' => _p('delete__l'), 'item' => _p('song')]), true);
        }

        return $this->success([], [_p('song_successfully_deleted')]);
    }

    /**
     * @description: browse photos
     * @return array|bool
     */
    public function gets()
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('songs__l')]));
        }

        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $userId = $this->request()->get('user_id');
        $view = $this->request()->get('view');
        $genreId = $this->request()->getInt('genre_id');
        $user = [];

        if (!empty($userId)) {
            if (!is_numeric($userId)) {
                return $this->error(_p('music_invalid_parameter_name', ['name' => 'user_id']));
            }
            $user = Phpfox::getService('user')->get($userId);
            if (empty($user['user_id'])) {
                return $this->error();
            }
        } elseif (!empty($moduleId) && !empty($itemId)) {
            if (in_array($moduleId, ['pages', 'groups'])) {
                if (Phpfox::hasCallback($moduleId, 'checkPermission') && !Phpfox::callback($moduleId . '.checkPermission', $itemId, 'music.view_browse_music')) {
                    return $this->error(_p('Cannot display this section due to privacy.'));
                }
            }
        }

        if (!empty($view)
            && (($view == 'my' && !Phpfox::isUser()) || ($view == 'pending' && (!Phpfox::isUser() || !Phpfox::getUserParam('music.can_approve_songs'))))) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('songs__l')]));
        }

        $isProfile = !empty($user['user_id']);

        $this->search()->set([
                'type'           => 'music_song',
                'field'          => 'm.song_id',
                'ignore_blocked' => true,
                'search_tool'    => [
                    'table_alias' => 'm',
                    'search'      => [
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

        switch ($view) {
            case 'my':
                $this->search()->setCondition('AND (m.user_id = ' . Phpfox::getUserId() . ' AND m.view_id != 2)');
                break;
            case 'pending':
                $this->search()->setCondition('AND m.view_id = 1');
                break;
            default:
                if ($isProfile === true) {
                    $this->search()->setCondition("AND m.item_id = 0 AND m.view_id IN(" . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND m.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($user)) . ") AND m.user_id = " . $user['user_id'] . "");
                } else {
                    $this->search()->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)");
                    if ($view == 'featured') {
                        $this->search()->setCondition('AND m.is_featured = 1');
                    }
                }
                break;
        }

        if ($genreId && Phpfox::getService('music.genre')->getGenre($genreId)) {
            $this->search()->setCondition('AND mgd.genre_id = ' . (int)$genreId);
        }

        if (!empty($moduleId) && !empty($itemId)) {
            $this->search()->setCondition("AND m.module_id = '" . Phpfox::getLib('database')->escape($moduleId) . "' AND m.item_id = " . (int)$itemId);
        } else {
            if ($view != 'pending' && $view != 'my' && !$isProfile) {
                if ((Phpfox::getParam('music.music_display_music_created_in_group') || Phpfox::getParam('music.music_display_music_created_in_page')) && $isProfile !== true) {
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

        $this->search()->setContinueSearch(true);
        $this->search()->browse()
            ->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('music.music_paging_mode', 'loadmore'))
            ->execute();

        $songs = $this->search()->browse()->getRows();
        $parsedSongs = [];

        $this->setPublicFields($this->_getPublicFields());

        foreach ($songs as $key => $song) {
            if (!empty($song['image_path'])) {
                $song['song_image_url'] = Phpfox::getLib('image.helper')->display([
                    'server_id' => $song['image_server_id'],
                    'path' => 'music.url_image',
                    'file' => $song['image_path'],
                    'suffix' => '_200_square',
                    'return_url' => true,
                ]);
            } else {
                $song['song_image_url'] = Phpfox::getParam('music.default_song_photo');
            }

            $song = array_merge($song, [
                'song_url' => $song['song_path'],
            ]);

            $parsedSongs[] = $this->getItem($song);
        }

        return $this->success($parsedSongs);
    }

    /**
     * @description: upload photos/ upload cover photo
     * @return array|bool
     */
    public function post()
    {
        $this->isUser();

        if (!Phpfox::getService('music')->canUploadNewSong()) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('song')]));
        }

        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->getInt('item_id');
        $vals = $this->request()->get('val');
        $callback = false;

        if ($moduleId && $itemId && Phpfox::hasCallback($moduleId, 'getMusicDetails')) {
            if ((Phpfox::callback($moduleId . '.getMusicDetails', ['item_id' => $itemId]))) {
                if (Phpfox::hasCallback($moduleId, 'checkPermission') && !Phpfox::callback($moduleId . '.checkPermission', $itemId, 'music.share_music')) {
                    return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
                }
            } else {
                return $this->error(_p('Cannot find the parent item.'));
            }
        } else {
            if ($moduleId && $itemId && $callback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        if (empty($_FILES['file'])) {
            return $this->error(_p('cannot_find_the_uploaded_file_please_try_again'));
        }

        $uploadParams = Phpfox::getService('music')->getUploadParams();
        $uploadParams['user_id'] = Phpfox::getUserId();
        $uploadParams['type'] = 'music';
        $songFile = Phpfox::getService('user.file')->load('file', $uploadParams);

        if (!$songFile) {
            return $this->error(_p('cannot_find_the_uploaded_file_please_try_again'));
        }

        if (!empty($songFile['error'])) {
            return $this->error($songFile['error']);
        }

        $song = Phpfox::getService('user.file')->upload('file', $uploadParams, true);

        if (empty($song) || !empty($song['error'])) {
            if (empty($song)) {
                return $this->error(_p('cannot_find_the_uploaded_file_please_try_again'));
            }

            if (!empty($song['error'])) {
                return $this->error($song['error']);
            }
        }

        $vals = array_merge($vals, $song);
        $vals['file_name'] = $songFile['name'];
        if ($moduleId && $itemId) {
            $vals = array_merge($vals, [
                'callback_module' => $moduleId,
                'callback_item_id' => $itemId,
            ]);
        }

        if ($songId = Phpfox::getService('music.process')->upload($vals, $vals['album_id'])) {
            Phpfox::getService('music.process')->publicSong($songId);
            return $this->get(['id' => $songId], [_p('song_successfully_uploaded')]);
        }

        return $this->error();
    }

    /*----------------------------------Album-------------------------------*/

    /**
     * @description: get detail info of a photo
     *
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function getAlbum($params, $messages = [])
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.', [
                'action' => _p('view__l'),
                'item' => _p('album')
            ]), true);
        }


        $album = \Phpfox::getService('music.album')->getAlbum((int)$params['id']);

        if (empty($album['album_id'])) {
            return $this->error(_p('unable_to_find_the_album_you_are_looking_for'));
        }

        if (Phpfox::isUser() && \Phpfox::getService('user.block')->isBlocked(null, $album['user_id'])) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.', [
                'action' => _p('view__l'),
                'item' => _p('album')
            ]), true);
        }

        if (!empty($album['module_id'])) {
            if ($callback = Phpfox::callback($album['module_id'] . '.getMusicDetails', $album)) {
                if ($album['module_id'] == 'pages'
                    && !Phpfox::getService('pages')->hasPerm($callback['item_id'], 'music.view_browse_music')) {
                    return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
                }
            }
        }

        if (Phpfox::isModule('privacy')
            && !Phpfox::getService('privacy')->check('music_album', $album['album_id'], $album['user_id'], $album['privacy'], $album['is_friend'], true)) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.', [
                'action' => _p('view__l'),
                'item' => _p('album')
            ]), true);
        }

        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (empty($album['is_viewed'])) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('music', 'album_' . $album['album_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('music', 'album_' . $album['album_id']);
                } else {
                    Phpfox::getService('track.process')->update('music_album', $album['album_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            db()->updateCounter('music_album', 'total_view', 'album_id', $album['album_id']);
        }

        $album['image_url'] = !empty($album['image_path']) ? Phpfox::getLib('image.helper')->display([
            'server_id' => $album['server_id'],
            'path' => 'music.url_image',
            'file' => $album['image_path'],
            'suffix' => '',
            'return_url' => true,
        ]) : Phpfox::getParam('music.default_album_photo');

        $this->setPublicFields($this->_getPublicFields('album'));

        return $this->success($this->getItem($album), $messages);
    }

    /**
     * @description: update info for a photo
     *
     * @param $params
     *
     * @return array|bool
     */
    public function putAlbum($params)
    {
        $this->isUser();

        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                ['action' => _p('edit__l'), 'item' => _p('album')]));
        }

        if (empty($params['id']) || empty($album = Phpfox::getService('music.album')->getForEdit($params['id']))) {
            return $this->error(_p('This {{ item }} cannot be found.', ['item' => _p('album')]), true);
        }

        $validateObject = \Phpfox_Validator::instance()->set([
                'sFormName' => 'js_music_add_album_form',
                'aParams'   => [
                    'name' => _p('provide_a_name_for_this_album'),
                    'year' => [
                        'def' => 'year'
                    ]
                ]
            ]
        );

        $vals = $this->request()->getArray('val');

        if ($validateObject->isValid($vals)) {
            if (Phpfox::getService('music.album.process')->update($album['album_id'], $vals)) {
                return $this->getAlbum(['id' => $album['album_id']], [_p('album_successfully_updated')]);
            }
        } else {
            return $this->error();
        }

        return $this->error(_p('Cannot {{ action }} this {{ item }}.',
            ['action' => _p('edit__l'), 'item' => _p('album')]), true);
    }

    /**
     * @description: delete a photo
     *
     * @param $params
     *
     * @return array|bool
     */
    public function deleteAlbum($params)
    {
        $this->isUser();

        if (!empty($params['id']) && Phpfox::getService('music.album.process')->delete((int)$params['id'])) {
            return $this->success([], _p('album_successfully_deleted'));
        }

        return $this->error(_p('Cannot {{ action }} this {{ item }}.',
            ['action' => _p('delete__l'), 'item' => _p('album')]), true);
    }

    /**
     * @description: browse photos
     * @return array|bool
     */
    public function getAlbums()
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('albums')]));
        }

        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $view = $this->request()->get('view');
        $userId = $this->request()->get('user_id');
        $isProfile = false;
        $user = [];

        if ($userId) {
            if (!is_numeric($userId)) {
                return $this->error(_p('music_invalid_parameter_name', ['name' => 'user_id']));
            }
            $user = Phpfox::getService('user')->get($userId);
            if (empty($user['user_id'])) {
                return $this->error();
            }
            $isProfile = true;
        }

        if ($view == 'my-album' && !Phpfox::isUser()) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('albums')]));
        }

        $this->search()->set([
                'type'           => 'music_album',
                'field'          => 'm.album_id',
                'ignore_blocked' => true,
                'search_tool'    => [
                    'table_alias' => 'm',
                    'search'      => [
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

        switch ($view) {
            case 'my-album':
                $this->search()->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                break;
            default:
                if ($isProfile === true) {
                    $this->search()->setCondition("AND m.view_id IN(" . (!empty($user) && $user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND m.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($user)) . ") AND m.user_id = " . $user['user_id'] . "");
                } else {
                    $this->search()->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)");
                    if ($view == 'featured') {
                        $this->search()->setCondition('AND m.is_featured = 1');
                    }
                }
                break;
        }

        if ($moduleId && $itemId) {
            $this->search()->setCondition("AND m.module_id = '" . Phpfox::getLib('database')->escape($moduleId) . "' AND m.item_id = " . (int)$itemId);
        } else {
            if ($view != 'my-album') {
                if ((Phpfox::getParam('music.music_display_music_created_in_group') || Phpfox::getParam('music.music_display_music_created_in_page')) && $isProfile !== true) {
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

        if (in_array($moduleId, ['pages', 'groups']) && $itemId) {
            if (Phpfox::hasCallback($moduleId, 'checkPermission') && !Phpfox::callback($moduleId . '.checkPermission', $itemId, 'music.view_browse_music')) {
                return $this->error(_p('Cannot display this section due to privacy.'));
            }
        }

        Phpfox::getService('music.album.browse')->setIsApi(true);

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('music.music_paging_mode', 'loadmore'))
            ->execute();

        $albums = $this->search()->browse()->getRows();
        $parsedAlbums = [];

        if ($albums) {
            $this->setPublicFields($this->_getPublicFields('album'));
            foreach ($albums as $album) {
                $album['image_url'] = !empty($album['image_path']) ? Phpfox::getLib('image.helper')->display([
                    'server_id' => $album['server_id'],
                    'path' => 'music.url_image',
                    'file' => $album['image_path'],
                    'suffix' => '',
                    'return_url' => true,
                ]) : Phpfox::getParam('music.default_album_photo');
                $parsedAlbums[] = $this->getItem($album);
            }
        }

        return $this->success($parsedAlbums);
    }

    /**
     * @description: upload photos/ upload cover photo
     * @return array|bool
     */
    public function postAlbum()
    {
        $this->isUser();

        if (!Phpfox::getUserParam('music.can_access_music') || !Phpfox::getService('music.album')->canCreateNewAlbum()) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('album')]));
        }
        
        $vals = $this->request()->getArray('val');
        $moduleId = $this->request()->get('module_id', false);
        $itemId = $this->request()->getInt('item_id', false);
        $callback = false;

        if ($moduleId !== false && $itemId !== false && Phpfox::hasCallback($moduleId, 'getMusicDetails')) {
            if ((Phpfox::callback($moduleId . '.getMusicDetails', ['item_id' => $itemId]))) {
                if (Phpfox::hasCallback($moduleId, 'checkPermission')
                    && !Phpfox::callback($moduleId . '.checkPermission', $itemId, 'music.share_music')) {
                    return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
                }
            } else {
                return $this->error(_p('Cannot find the parent item.'));
            }
        } else {
            if ($moduleId && $itemId && $callback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        $validateObject = \Phpfox_Validator::instance()->set([
                'sFormName' => 'js_music_add_album_form',
                'aParams'   => [
                    'name' => _p('provide_a_name_for_this_album'),
                    'year' => [
                        'def' => 'year'
                    ]
                ]
            ]
        );

        if ($validateObject->isValid($vals)) {
            if ($albumId = Phpfox::getService('music.album.process')->add($vals)) {
                return $this->getAlbum(['id' => $albumId], [_p('album_successfully_added')]);
            }
        }

        return $this->error(_p('Cannot add new {{ item }}.', ['item' => _p('album')]), true);
    }

    /*-----------------------------Playlist---------------------------------*/

    /**
     * @description: get detail info of a photo
     *
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function getPlaylist($params, $messages = [])
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.', [
                'action' => _p('view__l'),
                'item' => _p('music_playlist_single_word')
            ]), true);
        }

        if (empty($params['id']) || empty($playlist = Phpfox::getService('music.playlist')->getPlaylist($params['id']))) {
            return $this->error(_p('unable_to_find_the_album_you_are_looking_for'));
        }

        if (Phpfox::isModule('privacy')) {
            if ($playlist['user_id'] != Phpfox::getUserId() && !Phpfox::getService('privacy')->check('music', $playlist['playlist_id'], $playlist['user_id'], $playlist['privacy'], null, true)) {
                return $this->error(_p('you_do_not_have_permission_to_view_this_playlist'));
            }
        } else {
            if ($playlist['user_id'] != Phpfox::getUserId()) {
                return $this->error(_p('you_do_not_have_permission_to_view_this_playlist'));
            }
        }

        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$playlist['is_viewed']) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('music', 'playlist_' . $playlist['playlist_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('music', 'playlist_' . $playlist['playlist_id']);
                } else {
                    Phpfox::getService('track.process')->update('music_playlist', $playlist['playlist_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            db()->updateCounter('music_playlist', 'total_view', 'playlist_id', $playlist['playlist_id']);
        }

        if (!empty($playlist['image_path'])) {
            $playlist['image_url'] = Phpfox::getLib('image.helper')->display([
                'server_id' => $playlist['server_id'],
                'path' => 'music.url_image',
                'file' => $playlist['image_path'],
                'suffix' => '_500_square',
                'return_url' => true,
            ]);
        } else {
            $playlist['image_url'] = Phpfox::getParam('music.default_playlist_photo');
        }

        $this->setPublicFields($this->_getPublicFields('playlist'));

        return $this->success($this->getItem($playlist), $messages);
    }

    /**
     * @description: update info for a photo
     *
     * @param $params
     *
     * @return array|bool
     */
    public function putPlaylist($params)
    {
        $this->isUser();

        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('music_playlist_single_word')]));
        }

        if (empty($params['id']) || empty($playlist = Phpfox::getService('music.playlist')->getForEdit($params['id']))) {
            return $this->error(_p('This {{ item }} cannot be found.', ['item' => _p('music_playlist_single_word')]), true);
        }

        $vals = $this->request()->getArray('val');

        $validateObject = \Phpfox_Validator::instance()->set([
                'sFormName' => 'js_music_playlist_form',
                'aParams'   => [
                    'name' => _p('provide_a_name_for_this_playlist'),
                ]
            ]
        );

        if ($validateObject->isValid($vals)) {
            if (Phpfox::getService('music.playlist.process')->update($params['id'], $vals)) {
                return $this->getPlaylist(['id' => $params['id']], [_p('successfully_updated_playlist')]);
            }
        } else {
            return $this->error();
        }

        return $this->error(_p('Cannot {{ action }} this {{ item }}.',
            ['action' => _p('edit__l'), 'item' => _p('music_playlist_single_word')]), true);
    }

    /**
     * @description: delete a photo
     *
     * @param $params
     *
     * @return array|bool
     */
    public function deletePlaylist($params)
    {
        $this->isUser();

        if (Phpfox::getUserParam('music.can_access_music')
            && !empty($params['id'])
            && Phpfox::getService('music.playlist.process')->delete((int)$params['id'])) {
            return $this->success([], _p('playlist_successfully_deleted'));
        }

        return $this->error(_p('Cannot {{ action }} this {{ item }}.',
            ['action' => _p('delete__l'), 'item' => _p('music_playlist_single_word')]), true);
    }

    /**
     * @description: browse photos
     * @return array|bool
     */
    public function getPlaylists()
    {
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('music_playlists')]));
        }

        $view = $this->request()->get('view');

        if ($view == 'my-playlist' && !Phpfox::isUser()) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('music_playlists')]));
        }

        $this->search()->set([
                'type'           => 'music_playlist',
                'field'          => 'mp.playlist_id',
                'ignore_blocked' => true,
                'search_tool'    => [
                    'table_alias' => 'mp',
                    'search'      => [
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

        switch ($view) {
            case 'my-playlist':
                $this->search()->setCondition('AND mp.user_id = ' . (int)Phpfox::getUserId());
                break;
            default:
                $this->search()->setCondition("AND mp.view_id = 0 AND mp.privacy IN(%PRIVACY%)");
                break;
        }

        $this->search()->browse()->params($aBrowseParams)
            ->setPagingMode(Phpfox::getParam('music.music_paging_mode', 'loadmore'))
            ->execute();

        $playlists = $this->search()->browse()->getRows();
        $parsedPlaylists = [];

        $this->setPublicFields($this->_getPublicFields('playlist'));

        foreach ($playlists as $playlist) {
            if (!empty($playlist['image_path'])) {
                $playlist['image_url'] = Phpfox::getLib('image.helper')->display([
                    'server_id' => $playlist['server_id'],
                    'path' => 'music.url_image',
                    'file' => $playlist['image_path'],
                    'suffix' => '_500_square',
                    'return_url' => true,
                ]);
            } else {
                $playlist['image_url'] = Phpfox::getParam('music.default_playlist_photo');
            }

            $parsedPlaylists[] = $this->getItem($playlist);
        }

        return $this->success($parsedPlaylists);
    }

    /**
     * @description: upload photos/ upload cover photo
     * @return array|bool
     */
    public function postPlaylist()
    {
        $this->isUser();

        if (!Phpfox::getUserParam('music.can_access_music') || !Phpfox::getService('music.playlist')->canCreateNewPlaylist()) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('music_playlist_single_word')]));
        }

        $vals = $this->request()->getArray('val');

        $validateObject = \Phpfox_Validator::instance()->set([
                'sFormName' => 'js_music_playlist_form',
                'aParams'   => [
                    'name' => _p('provide_a_name_for_this_playlist'),
                ]
            ]
        );

        if ($validateObject->isValid($vals)) {
            if ($playlistId = Phpfox::getService('music.playlist.process')->add($vals)) {
                return $this->getPlaylist(['id' => $playlistId], [_p('successfully_added_playlist')]);
            }
        } else {
            return $this->error();
        }

        return $this->error(_p('Cannot add new {{ item }}.', ['item' => _p('music_playlist_single_word')]), true);
    }

    /*-----------------------------------*/

    private function _getPublicFields($type = 'song')
    {
        switch ($type) {
            case 'album':
                $fields = [
                    'album_id',
                    'view_id',
                    'privacy',
                    'is_featured',
                    'is_sponsor',
                    'user_id',
                    'name',
                    'year',
                    'image_url',
                    'total_track',
                    'total_play',
                    'total_comment',
                    'total_view',
                    'total_like',
                    'total_attachment',
                    'time_stamp',
                    'module_id',
                    'item_id',
                    'text'
                ];
                break;
            case 'playlist':
                $fields = [
                    'playlist_id',
                    'user_id',
                    'name',
                    'description',
                    'image_url',
                    'total_track',
                    'total_view',
                    'total_attachment',
                    'time_stamp',
                    'view_id',
                    'privacy',
                    'total_comment',
                    'total_like',
                ];
                break;
            default:
                $fields = [
                    'song_id',
                    'view_id',
                    'privacy',
                    'is_featured',
                    'is_sponsor',
                    'album_id',
                    'user_id',
                    'title',
                    'description',
                    'song_url',
                    'song_image_url',
                    'duration',
                    'total_play',
                    'total_view',
                    'total_comment',
                    'total_like',
                    'total_attachment',
                    'time_stamp',
                    'module_id',
                    'item_id',
                    'genres',
                ];
                break;
        }

        return $fields;
    }
}