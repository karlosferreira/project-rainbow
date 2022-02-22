<?php

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Music\MusicPlaylistAddSongForm;
use Apps\Core_MobileApi\Api\Form\Music\MusicPlaylistForm;
use Apps\Core_MobileApi\Api\Form\Music\MusicPlaylistSearchForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\MusicPlaylistResource;
use Apps\Core_MobileApi\Api\Resource\MusicSongResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\Music\MusicPlaylistAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Music\Service\Music;
use Apps\Core_Music\Service\Playlist\Playlist;
use Apps\Core_Music\Service\Playlist\Process;
use Phpfox;

class MusicPlaylistApi extends AbstractResourceApi implements ActivityFeedInterface
{
    /**
     * @var Music
     */
    private $musicService;
    /**
     * @var Playlist
     */
    private $playlistService;
    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->musicService = Phpfox::getService('music');
        $this->playlistService = Phpfox::getService('music.playlist');
        $this->processService = Phpfox::getService('music.playlist.process');
        $this->userService = Phpfox::getService('user');
    }

    public function __naming()
    {
        return [
            'music-playlist/search-form'   => [
                'get' => 'searchForm'
            ],
            'music-playlist/song'          => [
                'post'   => 'addSong',
                'get'    => 'getSongsByPlaylist',
                'delete' => 'removeSong',
                'where'  => [
                    'id' => '(\d+)'
                ]
            ],
            'music-playlist/song-form/:id' => [
                'get'   => 'getAddToPlaylistForm',
                'where' => [
                    'id' => '(\d+)'
                ]
            ]
        ];
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'limit', 'page', 'when', 'profile_id'
        ])
            ->setAllowedValues('sort', ['latest', 'most_liked', 'most_viewed', 'most_song'])
            ->setAllowedValues('view', ['my', 'all'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->permissionError();
        }
        $user = [];
        $isProfile = $params['profile_id'];
        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
        }
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'music.playlist',
            'alias'     => 'mp',
            'field'     => 'playlist_id',
            'table'     => Phpfox::getT('music_playlist'),
            'hide_view' => [],
            'service'   => 'music.playlist.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'mp'
        ]);
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND mp.name LIKE "%' . Phpfox::getLib('parse.input')->clean($params['q']) . '%"');
        }
        switch ($params['view']) {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND mp.user_id = ' . (int)Phpfox::getUserId());
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition("AND mp.view_id IN(" . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND mp.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($user)) . ") AND mp.user_id = " . $user['user_id'] . "");
                } else {
                    $this->search()->setCondition("AND mp.view_id = 0 AND mp.privacy IN(%PRIVACY%)");
                }
                break;
        }
        switch ($params['sort']) {
            case 'most_liked':
                $sort = 'mp.total_like DESC';
                break;
            case 'most_viewed':
                $sort = 'mp.total_view DESC';
                break;
            case 'most_song':
                $sort = 'mp.total_track DESC';
                break;
            default:
                $sort = 'mp.time_stamp DESC';
                break;
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);
        $this->browse()->params($browseParams)->execute();
        $items = $this->browse()->getRows();
        $this->processRows($items);
        return $this->success($items);
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        if (!Phpfox::getUserParam('music.can_access_music')) {
            return $this->permissionError();
        }
        $item = $this->playlistService->getPlaylist($id);
        if (!$item) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::VIEW, MusicPlaylistResource::populate($item));

        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$item['is_viewed']) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('music', 'playlist_' . $item['playlist_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('music', 'playlist_' . $item['playlist_id']);
                } else {
                    Phpfox::getService('track.process')->update('music_playlist', $item['playlist_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            $this->database()->updateCounter('music_playlist', 'total_view', 'playlist_id', $item['playlist_id']);
        }

        $item['is_detail'] = true;
        $resource = $this->populateResource(MusicPlaylistResource::class, $item);
        $this->setHyperlinks($resource, true);
        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->loadFeedParam()
            ->toArray());
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        $form = $this->createForm(MusicPlaylistForm::class, [
            'title'  => 'create_playlist',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('music-playlist')
        ]);
        $playlist = $this->loadResourceById($editId, true);
        if ($editId && empty($playlist)) {
            return $this->notFoundError();
        }

        if ($playlist) {
            $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::EDIT, $playlist);
            $form->setTitle('update_playlist')
                ->setAction(UrlUtility::makeApiUrl('music-playlist/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($playlist);
        } else {
            $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::ADD);
        }

        return $this->success($form->getFormStructure());
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::ADD);
        /** @var MusicPlaylistForm $form */
        $form = $this->createForm(MusicPlaylistForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MusicPlaylistResource::populate([])->getResourceName(),
                    'module_name'   => 'music'
                ], [], $this->localization->translate('music_playlist_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        if (!empty($values['file']) && !empty($values['file']['temp_file'])) {
            $values['temp_file'] = $values['file']['temp_file'];
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        if (!empty($values['text'])) {
            $values['description'] = $values['text'];
        }
        return $this->processService->add($values);
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var MusicPlaylistForm $form */
        $form = $this->createForm(MusicPlaylistForm::class);
        $playlist = $this->loadResourceById($id, true);
        if (empty($playlist)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::EDIT, $playlist);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MusicPlaylistResource::populate([])->getResourceName(),
                    'module_name'   => 'music'
                ], [], $this->localization->translate('music_playlist_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        if (!empty($values['file'])) {
            if ($values['file']['status'] == FileType::NEW_UPLOAD || $values['file']['status'] == FileType::CHANGE) {
                $values['temp_file'] = $values['file']['temp_file'];
            } else if ($values['file']['status'] == FileType::REMOVE) {
                $values['remove_photo'] = 1;
            }
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        if (!empty($values['text'])) {
            $values['description'] = $values['text'];
        }
        return $this->processService->update($id, $values);
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    function delete($params)
    {
        $itemId = $this->resolver->resolveId($params);
        $item = $this->loadResourceById($itemId, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::DELETE, $item);
        if ($this->processService->delete($itemId) !== false) {
            return $this->success([], [], $this->getLocalization()->translate('playlist_successfully_deleted'));
        }

        return $this->permissionError();
    }

    /**
     * @param      $id
     * @param bool $returnResource
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->database()->select('mp.*, u.user_name')
            ->from(':music_playlist', 'mp')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = mp.user_id')
            ->where('mp.playlist_id = ' . (int)$id)
            ->execute('getSlaveRow');
        if (empty($item['playlist_id'])) {
            return null;
        }

        if ($returnResource) {
            $item['is_detail'] = true;
            return MusicPlaylistResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        $resource = $this->populateResource(MusicPlaylistResource::class, $item);
        $this->setHyperlinks($resource, true);
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray();
    }

    /**
     * Get for display on activity feed
     *
     * @param array $feed
     * @param array $item detail data from database
     *
     * @return array
     */
    function getFeedDisplay($feed, $item)
    {
        if (empty($item) && !$item = $this->loadResourceById($feed['item_id'])) {
            return null;
        }
        $resource = $this->populateResource(MusicPlaylistResource::class, $item);

        return $resource->getFeedDisplay();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MusicPlaylistAccessControl($this->getSetting(), $this->getUser());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::VIEW);
        /** @var MusicPlaylistSearchForm $form */
        $form = $this->createForm(MusicPlaylistSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('music-playlist')
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(MusicPlaylistResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            MusicPlaylistAccessControl::VIEW   => $this->createHyperMediaLink(MusicPlaylistAccessControl::VIEW, $resource,
                HyperLink::GET, 'music-playlist/:id', ['id' => $resource->getId()]),
            MusicPlaylistAccessControl::EDIT   => $this->createHyperMediaLink(MusicPlaylistAccessControl::EDIT, $resource,
                HyperLink::GET, 'music-playlist/form/:id', ['id' => $resource->getId()]),
            MusicPlaylistAccessControl::DELETE => $this->createHyperMediaLink(MusicPlaylistAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'music-playlist/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(MusicPlaylistAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'music_playlist']),
                'comments' => $this->createHyperMediaLink(MusicPlaylistAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'music_playlist'])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', MusicPlaylistResource::RESOURCE_NAME);
        $module = 'music';
        return [
            [
                'path'      => 'music/playlist/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'music/browse/playlist(/*)',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function addSong($params)
    {
        $params = $this->resolver
            ->setRequired(['song_id', 'playlist_id'])
            ->setAllowedTypes('song_id', 'int')
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $playlistIds = is_array($params['playlist_id']) ? $params['playlist_id'] : [$params['playlist_id']];
        $songId = $params['song_id'];
        $songItem = (new MusicSongApi())->loadResourceById($songId);
        if (!$songItem) {
            return $this->notFoundError();
        }
        //Get old playlists by song
        $playlists = $this->playlistService->getAllPlaylist($this->getUser()->getId(), $songId);

        $pass = $this->processAddSong([
            'song_id'     => $songId,
            'playlist_id' => $playlistIds
        ], $playlists);

        if (!$pass) {
            return $this->error($this->getLocalization()->translate('cannot_add_song_to_this_playlist'));
        }
        return $this->success([
            'module_name'   => 'music',
            'resource_name' => 'music_song',
            'id'            => $songId
        ], [], $this->getLocalization()->translate('updated_playlists_of_song_successfully'));
    }

    public function processAddSong($values, $playlists)
    {
        $pass = true;
        $addSuccess = [];
        foreach ($playlists as $playlist) {
            if (!$pass) break;
            if (in_array($playlist['playlist_id'], $values['playlist_id'])) {
                if ($playlist['id'] > 0) {
                    //Song in playlist already
                    continue;
                }
                $item = $this->loadResourceById($playlist['playlist_id'], true);
                $grant = $this->getAccessControl()->isGranted(MusicPlaylistAccessControl::OWNER, $item);
                if (empty($item) || !$grant) {
                    $pass = false;
                    continue;
                }
                $this->database()->updateCounter('music_playlist', 'total_track', 'playlist_id', $playlist['playlist_id']);
                $this->database()->insert(':music_playlist_data', [
                    'song_id'     => $values['song_id'],
                    'playlist_id' => $playlist['playlist_id'],
                    'time_stamp'  => PHPFOX_TIME
                ]);
                $addSuccess[] = $playlist['playlist_id'];
            } else if ($playlist['id'] > 0) {
                //Remove from playlist
                $this->processService->removeSong($values['song_id'], $playlist['playlist_id']);
            }
        }
        if (!$pass) {
            foreach ($addSuccess as $added) {
                //Remove if failed
                $this->processService->removeSong($values['song_id'], $added);
            }
        }
        return $pass;
    }

    public function removeSong($params)
    {
        $id = $this->resolver->resolveId($params);
        $item = $this->loadResourceById($id, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::OWNER, $item);
        $songId = (int)$this->resolver
            ->setRequired(['song_id'])
            ->resolveSingle($params, 'song_id', 'int', ['min' => 1]);
        $songItem = (new MusicSongApi())->loadResourceById($songId);
        if (empty($songItem)) {
            return $this->error($this->getLocalization()->translate('cannot_remove_song_from_this_playlist'));
        }
        if ($this->processService->removeSong($songId, $id)) {
            $playlist = $this->playlistService->getPlaylist($id);
            $playlist['is_detail'] = true;
            return $this->success([
                'playlist' => $this->populateResource(MusicPlaylistResource::class, $playlist)->toArray(),
            ], [], $this->getLocalization()->translate('the_song_has_been_removed_from_this_playlist'));
        }
    }

    function approve($params)
    {

    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
    }


    public function getAddToPlaylistForm($params)
    {
        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::VIEW);

        $songId = $this->resolver->resolveSingle($params, 'id');
        /** @var MusicPlaylistAddSongForm $form */
        $form = $this->createForm(MusicPlaylistAddSongForm::class, [
            'title'  => 'add_to_playlists',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('music-playlist/song')
        ]);
        $song = (new MusicSongApi())->loadResourceById($songId, true);
        $playlists = $this->playlistService->getAllPlaylist($this->getUser()->getId(), $songId);
        if (empty($playlists)) {
            return $this->error($this->getLocalization()->translate('no_playlists_found'));
        }
        $songPlaylists = [];
        foreach ($playlists as $playlist) {
            if (!empty($playlist['id'])) {
                $songPlaylists[] = (int)$playlist['playlist_id'];
            }
        }
        $form->setPlaylists($playlists);
        $form->setSongId($songId);
        $form->assignValues(['playlist_id' => $songPlaylists]);
        if (empty($song)) {
            return $this->notFoundError();
        }

        return $this->success($form->getFormStructure());
    }

    /**
     * @param      $params
     * @param bool $noSuccess
     *
     * @return array|bool
     */
    public function getSongsByPlaylist($params, $noSuccess = false)
    {
        $id = $this->resolver
            ->setRequired(['playlist_id'])
            ->resolveSingle($params, 'playlist_id', 'int');
        if (empty($id)) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::EDIT, $item);
        $songs = $this->playlistService->getAllSongs($id);
        $result = [];
        if (count($songs)) {
            foreach ($songs as $key => $song) {
                $result[$key] = MusicSongResource::populate($song)->displayShortFields()->toArray();
                $result[$key]['playlist_id'] = $id;
            }
        }
        if ($noSuccess) {
            return $result;
        }
        return $this->success($result);
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'music.browse.playlist',
            ScreenSetting::MODULE_LISTING => 'music.browse.playlist',
            ScreenSetting::MODULE_DETAIL  => 'music.view-playlist'
        ];
    }

    /**
     * Moderation items
     *
     * @param $params
     *
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    public function moderation($params)
    {
        $this->resolver
            ->setAllowedValues('action', [Screen::ACTION_DELETE_ITEMS]);
        $action = $this->resolver->resolveSingle($params, 'action', 'string', [], '');
        $ids = $this->resolver->resolveSingle($params, 'ids', 'array', [], []);
        if (!count($ids)) {
            return $this->missingParamsError(['ids']);
        }

        $sMessage = '';
        switch ($action) {
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(MusicPlaylistAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('playlist_s_successfully_deleted');
                break;
        }
        return $this->success(['ids' => $ids], [], $sMessage);
    }
}