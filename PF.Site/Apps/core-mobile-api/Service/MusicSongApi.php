<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Music\MusicSongForm;
use Apps\Core_MobileApi\Api\Form\Music\MusicSongSearchForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\MusicAlbumResource;
use Apps\Core_MobileApi\Api\Resource\MusicSongResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Music\MusicSongAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_MobileApi\Service\Helper\TextHelper;
use Apps\Core_Music\Service\Music;
use Apps\Core_Music\Service\Process;
use Phpfox;

class MusicSongApi extends AbstractResourceApi implements ActivityFeedInterface
{
    /**
     * @var Music
     */
    private $musicService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    /**
     * @var \Apps\Core_BetterAds\Service\Process
     */
    private $adProcessService = null;

    public function __construct()
    {
        parent::__construct();
        $this->musicService = Phpfox::getService('music');
        $this->processService = Phpfox::getService('music.process');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'music-song/search-form' => [
                'get' => 'searchForm'
            ],
            'music-song/play/:id'    => [
                'put'   => 'playSong',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
        ];
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'genre', 'q', 'sort', 'profile_id', 'limit', 'page', 'when', 'module_id', 'item_id', 'playlist_id'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'pending', 'friend', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('item_id', 'int')
            ->setAllowedTypes('playlist_id', 'int')
            ->setAllowedTypes('genre', 'int')
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
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

        if (!empty($params['playlist_id'])) {
            return $this->success((new MusicPlaylistApi())->getSongsByPlaylist($params, true));
        }
        $sort = $params['sort'];
        $view = $params['view'];
        $isProfile = $params['profile_id'];
        $parentModule = null;

        if (in_array($view, ['feature', 'sponsor'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }

        if (!empty($params['module_id']) && !empty($params['item_id'])) {
            $parentModule = [
                'module_id' => $params['module_id'],
                'item_id'   => $params['item_id'],
            ];
        }
        $user = [];
        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
        }
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'music.song',
            'alias'     => 'm',
            'alias_select' => 'm.song_id, m.view_id, m.privacy, m.privacy_comment, m.is_featured, m.is_sponsor, m.album_id, m.user_id, m.title, m.song_path, m.server_id, m.explicit, m.duration, m.ordering, m.image_path, m.image_server_id, m.total_play, m.total_view, m.total_comment, m.total_like, m.total_dislike, m.total_score, m.total_rating, m.total_attachment, m.time_stamp, m.module_id, m.item_id',
            'field'     => 'song_id',
            'table'     => Phpfox::getT('music_song'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'music.song.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'm'
        ]);
        switch ($view) {
            case 'my':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                }
                break;
            case 'pending':
                if (Phpfox::isUser() && Phpfox::getUserParam('music.can_approve_songs')) {
                    $this->search()->setCondition('AND m.view_id = 1');
                } else {
                    return $this->permissionError();
                }
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition("AND m.item_id = 0 AND m.view_id IN(" . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND m.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($user)) . ") AND m.user_id = " . $user['user_id'] . "");
                } else {
                    $this->search()->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)");
                }
                break;
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND m.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }
        //category
        if ($params['genre']) {
            $this->search()->setCondition('AND mgd.genre_id = ' . (int)$params['genre']);
            \Phpfox_Request::instance()->set('req2', 'genre');
            \Phpfox_Request::instance()->set('req3', $params['genre']);
        }

        if ($parentModule !== null) {
            $this->search()->setCondition("AND m.module_id = '" . Phpfox::getLib('database')->escape($parentModule['module_id']) . "' AND m.item_id = " . (int)$parentModule['item_id']);
        } else {
            if ($view != 'pending' && $view != 'my') {
                if ((Phpfox::getParam('music.music_display_music_created_in_group') || Phpfox::getParam('music.music_display_music_created_in_page')) && $isProfile !== true) {
                    $modules = [];
                    if (Phpfox::getParam('music.music_display_music_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                        $modules[] = 'groups';
                    }
                    if (Phpfox::getParam('music.music_display_music_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                        $modules[] = 'pages';
                    }
                    if (count($modules)) {
                        $this->search()->setCondition('AND (m.module_id IN ("' . implode('","',
                                $modules) . '") OR m.module_id is NULL)');
                    } else {
                        $this->search()->setCondition('AND m.module_id is NULL');
                    }
                } else {
                    $this->search()->setCondition('AND m.item_id = 0');
                }
            }
        }
        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'm.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'm.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'm.total_comment DESC';
                break;
            default:
                $sort = 'm.time_stamp DESC';
                break;
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);
        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

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
        $item = $this->musicService->getSong($id);
        if (!$item || ($item['view_id'] && !Phpfox::getUserParam('music.can_approve_songs') && $item['user_id'] != Phpfox::getUserId())) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(MusicSongAccessControl::VIEW, MusicSongResource::populate($item));

        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$item['is_viewed']) {
                $updateCounter = true;
                Phpfox::getService('track.process')->add('music', 'song_' . $item['song_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('music', 'song_' . $item['song_id']);
                } else {
                    Phpfox::getService('track.process')->update('music_song', $item['song_id']);
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->database()->updateCounter('music_song', 'total_view', 'song_id', $item['song_id']);
        }
        $item['is_detail'] = true;
        $resource = $this->populateResource(MusicSongResource::class, $item);
        $this->setHyperlinks($resource, true);
        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->loadFeedParam()
            ->toArray());
    }


    function form($params = [])
    {
        $params = $this->resolver
            ->setDefined(['module_id', 'item_id', 'album_id', 'id'])
            ->resolve($params)->getParameters();
        $editId = $params['id'];
        /** @var MusicSongForm $form */
        $form = $this->createForm(MusicSongForm::class, [
            'title'  => 'share_songs',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('music-song')
        ]);
        /** @var MusicSongResource $photo */
        $song = $this->loadResourceById($editId, true);
        $album = NameResource::instance()->getApiServiceByResourceName(MusicAlbumResource::RESOURCE_NAME)->loadResourceById($params['album_id']);

        if ($params['album_id'] && empty($album)) {
            return $this->notFoundError();
        }
        if ($editId && empty($song)) {
            return $this->notFoundError();
        }
        if ($song) {
            $this->denyAccessUnlessGranted(MusicSongAccessControl::EDIT, $song);
            $form->setEditing(true);
            $form->setTitle('update_song')
                ->setAction(UrlUtility::makeApiUrl('music-song/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($song);
            $form->setAlbums($this->getAlbums($song->module_id, $song->item_id));
        } else {
            $this->denyAccessUnlessGranted(MusicSongAccessControl::ADD);
            $form->setAlbums($this->getAlbums($params['module_id'], $params['item_id']));
        }

        $form->setAlbumId($params['album_id']);
        $form->setGenres($this->getGenres());

        return $this->success($form->getFormStructure());
    }

    private function getGenres()
    {
        return Phpfox::getService('music.genre')->getList(1);
    }

    private function getAlbums($module, $item)
    {
        return Phpfox::getService('music.album')->getForUpload(['module_id' => $module, 'item_id' => $item]);
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(MusicSongAccessControl::ADD);
        $params = $this->resolver
            ->setDefined(['module_id', 'item_id', 'album_id', 'id'])
            ->resolve($params)->getParameters();
        /** @var MusicSongForm $form */
        $form = $this->createForm(MusicSongForm::class);
        $form->setGenres($this->getGenres());
        $form->setAlbums($this->getAlbums($params['module_id'], $params['item_id']));
        if ($form->isValid()) {
            $ids = $this->processCreate($form->getValues());
            if ($ids) {
                return $this->success([
                    'ids'           => $ids,
                    'resource_name' => MusicSongResource::populate([])->getResourceName(),
                    'module_name'   => 'music'
                ], [], $this->localization->translate('music_song_successfully_uploaded'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        if (!empty($values['album'])) {
            $values['album_id'] = $values['album'];
        } else {
            $values['album_id'] = 0;
        }
        if (!is_array($values['genres'])) {
            $values['genres'] = [];
        }
        $finalVals = [];
        $uploadList = $values['files']['new'];
        unset($values['files']);
        foreach ($uploadList as $file) {
            $uploadedFile = Phpfox::getService('core.temp-file')->get($file);
            if (empty($uploadedFile['file_id'])) {
                continue;
            }
            if (!empty($uploadedFile['extra_info'])) {
                $extraInfo = json_decode($uploadedFile['extra_info'], true);
            }
            $finalVals[] = [
                'name'             => $uploadedFile['path'],
                'file_name'        => isset($extraInfo['name']) ? $extraInfo['name'] : 'Unknown',
                'album_id'         => $values['album_id'],
                'genre'            => [implode(',', $values['genres'])],
                'privacy'          => isset($values['privacy']) ? $values['privacy'] : 0,
                'privacy_list'     => isset($values['privacy_list']) ? $values['privacy_list'] : [],
                'time_stamp'       => time(),
                'callback_module'  => isset($values['module_id']) ? $values['module_id'] : null,
                'callback_item_id' => isset($values['item_id']) ? $values['item_id'] : null,
            ];
            //Remove from table temp file
            Phpfox::getService('core.temp-file')->delete($uploadedFile['file_id']);
        }
        if (empty($finalVals)) {
            return $this->error();
        }
        $ids = [];
        foreach ($finalVals as $vals) {
            $ids[] = $this->processService->upload($vals, $vals['album_id']);
        }
        return implode(',', $ids);
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var MusicSongForm $form */
        $form = $this->createForm(MusicSongForm::class);
        $form->setEditing(true);
        /** @var MusicSongResource $song */
        $song = $this->loadResourceById($id, true);
        if (empty($song)) {
            return $this->notFoundError();
        }
        $form->setGenres($this->getGenres());
        $form->setAlbums($this->getAlbums($song->module_id, $song->item_id));
        $this->denyAccessUnlessGranted(MusicSongAccessControl::EDIT, $song);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MusicSongResource::populate([])->getResourceName(),
                    'module_name'   => 'music'
                ], [], $this->localization->translate('music_song_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        if (!empty($values['album'])) {
            $values['album_id'] = $values['album'];
        }
        if (!empty($values['genres'])) {
            $values['genre'] = $values['genres'];
        }
        if (!empty($values['text'])) {
            $values['description'] = $values['text'];
        }
        if (!empty($values['file'])) {
            if ($values['file']['status'] == FileType::NEW_UPLOAD || $values['file']['status'] == FileType::CHANGE) {
                $values['temp_file'] = $values['file']['temp_file'];
            } else if ($values['file']['status'] == FileType::REMOVE) {
                $values['remove_photo'] = 1;
            }
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
        $item = $this->loadResourceById($itemId);
        if (!$item) {
            return $this->notFoundError();
        }

        if (Phpfox::getUserParam('music.can_access_music') && $this->processService->delete($itemId) !== false) {
            return $this->success([], [], $this->getLocalization()->translate('song_successfully_deleted'));
        }

        return $this->permissionError();
    }

    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->musicService->getForEdit($id, true);
        if (empty($item['song_id'])) {
            return null;
        }
        if ($returnResource) {
            if (isset($item['genres'])) {
                unset($item['genres']);
            }
            $item['is_detail'] = true;
            return MusicSongResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        /** @var MusicSongResource $resource */
        $resource = $this->populateResource(MusicSongResource::class, $item);
        $this->setHyperlinks($resource);

        $view = $this->request()->get('view');
        $shortFields = [];

        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'image', 'statistic', 'user', 'song_path', 'genres', 'id'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
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
        $extraSongId = isset($item['extra_song_id']) ? intval($item['extra_song_id']) : 0;
        $iFeedId = isset($feed['feed_id']) ? $feed['feed_id'] : 0;
        $aSongs = [];
        $limitSong = 2;
        $totalSong = 1;
        if ($extraSongId) {
            $aCond[] = 'AND mf.feed_id =' . (int)$iFeedId;
            if ($item['user_id'] == Phpfox::getUserId()) {
                $aCond[] = 'AND ms.privacy IN(0,1,2,3,4)';
            } else {
                $oUserObject = \Phpfox::getService('user')->getUserObject($item['user_id']);
                if (isset($oUserObject->is_friend) && $oUserObject->is_friend) {
                    $aCond[] = 'AND ms.privacy IN(0,1,2)';
                } else {
                    if (isset($oUserObject->is_friend_of_friend) && $oUserObject->is_friend_of_friend) {
                        $aCond[] = 'AND ms.privacy IN(0,2)';
                    } else {
                        $aCond[] = 'AND ms.privacy IN(0)';
                    }
                }
            }
            $totalSong = $this->database()->select('COUNT(*)')
                ->from(':music_feed', 'mf')
                ->join(':music_song', 'ms', 'ms.song_id = mf.song_id')
                ->where('mf.feed_id =' . (int)$iFeedId)
                ->execute('getField');

            $totalSong = intval($totalSong) + 1;

            $aRows = $this->database()->select('ms.*')
                ->from(':music_feed', 'mf')
                ->join(':music_song', 'ms', 'ms.song_id = mf.song_id')
                ->where($aCond)
                ->limit($limitSong)
                ->execute('getSlaveRows');
            $aSongs = array_map(function ($row) {
                return [
                    'id'            => $row['song_id'],
                    'title'         => TextHelper::cleanHtml($row['title']),
                    'href'          => "music-song/{$row['song_id']}",
                    'resource_name' => 'music_song',
                    'image'         => null, //Don't need image for this case
                    'total_play'    => (int)$row['total_play'],
                    'song_path'     => $this->musicService->getSongPath($row['song_path'], $row['server_id'])
                ];
            }, $aRows);
        }
        /** @var MusicSongResource $firstSong */
        $firstSong = MusicSongResource::populate($item);
        array_unshift($aSongs, [
            'id'            => $firstSong->getId(),
            'title'         => $firstSong->getTitle(),
            'href'          => "music-song/{$firstSong->getId()}",
            'resource_name' => 'music_song',
            'image'         => isset($firstSong->getImage()->sizes['500']) ? $firstSong->getImage()->sizes['500'] : $firstSong->getImage(),
            'total_play'    => (int)$item['total_play'],
            'song_path'     => $item['song_path']
        ]);
        return [
            'resource_name' => str_replace('-', '_', MusicSongResource::RESOURCE_NAME),
            'total_song'    => $totalSong,
            'songs'         => $aSongs,
        ];
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MusicSongAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get("module_id");
        $itemId = $this->request()->get("item_id");

        if ($moduleId) {
            $context = AppContextFactory::create($moduleId, $itemId);
            if ($context === null) {
                return $this->notFoundError();
            }
            $this->accessControl->setAppContext($context);
        }
        return true;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(MusicSongAccessControl::VIEW);
        /** @var MusicSongSearchForm $form */
        $form = $this->createForm(MusicSongSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('music-song')
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(MusicSongResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            MusicSongAccessControl::VIEW   => $this->createHyperMediaLink(MusicSongAccessControl::VIEW, $resource,
                HyperLink::GET, 'music-song/:id', ['id' => $resource->getId()]),
            MusicSongAccessControl::EDIT   => $this->createHyperMediaLink(MusicSongAccessControl::EDIT, $resource,
                HyperLink::GET, 'music-song/form/:id', ['id' => $resource->getId()]),
            MusicSongAccessControl::DELETE => $this->createHyperMediaLink(MusicSongAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'music-song/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(MusicSongAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'music_song']),
                'comments' => $this->createHyperMediaLink(MusicSongAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'music_song'])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', MusicSongResource::RESOURCE_NAME);
        $module = 'music';
        return [
            [
                'path'      => 'music/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'music/genre/:genre(/*)',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'music(/*)',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function playSong($params)
    {
        $id = $this->resolver->resolveId($params);
        if (!$id) {
            return false;
        }
        $this->processService->play($id);
        return $this->success([
            'id' => $id
        ]);
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var MusicSongResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicSongAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            return $this->success([
                'is_pending' => false
            ], [], $this->getLocalization()->translate('song_has_been_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicSongAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('song_successfully_featured') : $this->getLocalization()->translate('song_successfully_un_featured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $isSponsorFeed = $this->resolver->resolveSingle($params, 'is_sponsor_feed', null, [], 0);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        if ($isSponsorFeed) {
            //Support un-sponsor in feed
            $this->denyAccessUnlessGranted(MusicSongAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('music_song', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(MusicSongAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(MusicSongAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }

            if ($this->processService->sponsorSong($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('music_song');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module' => 'music',
                        'section' => 'song',
                        'item_id' => $id,
                        'name' => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('music_song', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('song_successfully_sponsored') : $this->getLocalization()->translate('song_successfully_un_sponsored'));
            }
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->musicService->getRandomSponsoredSongs($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'music', 'sponsorSong');
            }
        }
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    protected function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $featuredItems = $this->musicService->getFeaturedSongs($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'music.index',
            ScreenSetting::MODULE_LISTING => 'music.index',
            ScreenSetting::MODULE_DETAIL  => 'music.view'
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
            ->setAllowedValues('action', [Screen::ACTION_APPROVE_ITEMS, Screen::ACTION_DELETE_ITEMS, Screen::ACTION_FEATURE_ITEMS, Screen::ACTION_REMOVE_FEATURE_ITEMS]);
        $action = $this->resolver->resolveSingle($params, 'action', 'string', [], '');
        $ids = $this->resolver->resolveSingle($params, 'ids', 'array', [], []);
        if (!count($ids)) {
            return $this->missingParamsError(['ids']);
        }

        $data = [];
        $sMessage = '';
        switch ($action) {
            case Screen::ACTION_APPROVE_ITEMS:
                $this->denyAccessUnlessGranted(MusicSongAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->approve($id)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_pending' => false];
                $sMessage = $this->getLocalization()->translate('songs_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(MusicSongAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('songs_s_successfully_featured') : $this->getLocalization()->translate('songs_s_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(MusicSongAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('songs_s_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}