<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Music\MusicAlbumForm;
use Apps\Core_MobileApi\Api\Form\Music\MusicAlbumSearchForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\MusicAlbumResource;
use Apps\Core_MobileApi\Api\Resource\MusicGenreResource;
use Apps\Core_MobileApi\Api\Resource\MusicPlaylistResource;
use Apps\Core_MobileApi\Api\Resource\MusicSongResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Music\MusicAlbumAccessControl;
use Apps\Core_MobileApi\Api\Security\Music\MusicPlaylistAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Music\Service\Album\Album;
use Apps\Core_Music\Service\Music;
use Apps\Core_Music\Service\Process;
use Phpfox;

class MusicAlbumApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    /**
     * @var Music
     */
    private $musicService;

    /**
     * @var Album
     */
    private $albumService;

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
        $this->albumService = Phpfox::getService('music.album');
        $this->processService = Phpfox::getService('music.album.process');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'music-album/search-form' => [
                'get' => 'searchForm'
            ],
        ];
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'profile_id', 'limit', 'page', 'when', 'module_id', 'item_id'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'friend', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('item_id', 'int')
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
        $sort = $params['sort'];
        $view = $params['view'];
        $isProfile = $params['profile_id'];
        $parentModule = null;

        if (in_array($view, ['sponsor', 'feature'])) {
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
            'module_id' => 'music.album',
            'alias'     => 'm',
            'field'     => 'album_id',
            'table'     => Phpfox::getT('music_album'),
            'hide_view' => ['my'],
            'service'   => 'music.album.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'm'
        ]);
        switch ($view) {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition("AND m.view_id IN(" . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND m.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($user)) . ") AND m.user_id = " . $user['user_id'] . "");
                } else {
                    $this->search()->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)");
                }
                break;
        }
        if ($parentModule !== null) {
            $this->search()->setCondition("AND m.module_id = '" . Phpfox::getLib('database')->escape($parentModule['module_id']) . "' AND m.item_id = " . (int)$parentModule['item_id']);
        } else {
            if ($view != 'my') {
                if ((Phpfox::getParam('music.music_display_music_created_in_group') || Phpfox::getParam('music.music_display_music_created_in_page')) && !$isProfile) {
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
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND m.name LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
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
        $item = $this->albumService->getAlbum($id);
        if (!$item) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(MusicAlbumAccessControl::VIEW, MusicAlbumResource::populate($item));

        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!isset($item['is_viewed']) || !$item['is_viewed']) {
                $updateCounter = true;
                Phpfox::getService('track.process')->add('music', 'album_' . $item['album_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('music', 'album_' . $item['album_id']);
                } else {
                    Phpfox::getService('track.process')->update('music_album', $item['album_id']);
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->database()->updateCounter('music_album', 'total_view', 'album_id', $item['album_id']);
        }
        $item['is_detail'] = true;
        /** @var MusicAlbumResource $resource */
        $resource = $this->populateResource(MusicAlbumResource::class, $item);
        $this->setHyperlinks($resource, true);
        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->lazyLoad(['user'])
            ->loadFeedParam()
            ->toArray());
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var MusicAlbumForm $form */
        $form = $this->createForm(MusicAlbumForm::class, [
            'title'  => 'create_album',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('music-album')
        ]);
        $album = $this->loadResourceById($editId, true);
        if ($editId && empty($album)) {
            return $this->notFoundError();
        }

        if ($album) {
            $this->denyAccessUnlessGranted(MusicAlbumAccessControl::EDIT, $album);
            $form->setTitle('update_album')
                ->setAction(UrlUtility::makeApiUrl('music-album/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($album);
        } else {
            $this->denyAccessUnlessGranted(MusicAlbumAccessControl::ADD);
        }

        return $this->success($form->getFormStructure());
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(MusicAlbumAccessControl::ADD);
        /** @var MusicAlbumForm $form */
        $form = $this->createForm(MusicAlbumForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MusicAlbumResource::populate([])->getResourceName(),
                    'module_name'   => 'music'
                ], [], $this->localization->translate('music_album_successfully_created'));
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
        return $this->processService->add($values);
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var MusicAlbumForm $form */
        $form = $this->createForm(MusicAlbumForm::class);
        $album = $this->loadResourceById($id, true);
        if (empty($album)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicAlbumAccessControl::EDIT, $album);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MusicAlbumResource::populate([])->getResourceName(),
                    'module_name'   => 'music'
                ], [], $this->localization->translate('music_album_successfully_updated'));
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
            return $this->success([], [], $this->getLocalization()->translate('album_successfully_deleted'));
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
        $item = $this->database()->select('ma.*, mat.text,' . Phpfox::getUserField())
            ->from(':music_album', 'ma')
            ->join(Phpfox::getT('music_album_text'), 'mat', 'mat.album_id = ma.album_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id')
            ->where('ma.album_id = ' . (int)$id)
            ->execute('getSlaveRow');

        if (empty($item['album_id'])) {
            return null;
        }
        if ($returnResource) {
            $item['is_detail'] = true;
            return MusicAlbumResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        /** @var MusicAlbumResource $resource */
        $resource = $this->populateResource(MusicAlbumResource::class, $item);
        $this->setHyperlinks($resource);

        $view = $this->request()->get('view');
        $shortFields = [];

        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'name', 'image', 'statistic', 'user', 'id'
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
        $resource = $this->populateResource(MusicAlbumResource::class, $item);

        return $resource->getFeedDisplay();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MusicAlbumAccessControl($this->getSetting(), $this->getUser());

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
        $this->denyAccessUnlessGranted(MusicAlbumAccessControl::VIEW);
        /** @var MusicAlbumSearchForm $form */
        $form = $this->createForm(MusicAlbumSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('music-album')
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(MusicAlbumResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            MusicAlbumAccessControl::VIEW   => $this->createHyperMediaLink(MusicAlbumAccessControl::VIEW, $resource,
                HyperLink::GET, 'music-album/:id', ['id' => $resource->getId()]),
            MusicAlbumAccessControl::EDIT   => $this->createHyperMediaLink(MusicAlbumAccessControl::EDIT, $resource,
                HyperLink::GET, 'music-album/form/:id', ['id' => $resource->getId()]),
            MusicAlbumAccessControl::DELETE => $this->createHyperMediaLink(MusicAlbumAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'music-album/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(MusicAlbumAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'music_album']),
                'comments' => $this->createHyperMediaLink(MusicAlbumAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'music_album'])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', MusicAlbumResource::RESOURCE_NAME);
        $module = 'music';
        return [
            [
                'path'      => 'music/album/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'music/browse/album(/*)',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function getAppSetting($param)
    {
        $app = new MobileApp('music', [
            'title'             => 'Music',
            'home_view'         => 'menu',
            'main_resource'     => new MusicSongResource([]),
            'category_resource' => new MusicGenreResource([]),
            'other_resources'   => [
                new MusicAlbumResource([]),
                new MusicPlaylistResource([])
            ],
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $headerButtons[(new MusicSongResource([]))->getResourceName()] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ],
        ];
        if ($this->getAccessControl()->isGranted(MusicPlaylistAccessControl::ADD)) {
            $headerButtons[(new MusicPlaylistResource([]))->getResourceName()] = [
                [
                    'icon'   => 'plus',
                    'action' => Screen::ACTION_ADD,
                    'params' => ['resource_name' => (new MusicPlaylistResource([]))->getResourceName()]
                ],
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MusicAlbumAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('album_successfully_featured') : $this->getLocalization()->translate('album_successfully_un_featured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        /** @var MusicAlbumResource $item */
        $item = $this->loadResourceById($id, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        if (!$this->getAccessControl()->isGranted(MusicAlbumAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(MusicAlbumAccessControl::PURCHASE_SPONSOR, $item)) {
            return $this->permissionError();
        }

        if (Phpfox::getService('music.process')->sponsorAlbum($id, $sponsor)) {
            if ($sponsor == 1) {
                $sModule = $this->getLocalization()->translate('music_album');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'music',
                    'section' => 'album',
                    'item_id' => $id,
                    'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getName()])
                ], false);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('music_album', $id);
            }
            return $this->success([
                'is_sponsor' => !!$sponsor
            ], [], $sponsor ? $this->getLocalization()->translate('album_successfully_sponsored') : $this->getLocalization()->translate('album_successfully_un_sponsored'));
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

        $sponsoredItems = $this->musicService->getRandomSponsoredAlbum($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * Update view count for sponsored items
     *
     * @param $sponsorItems
     */
    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'music', 'sponsorAlbum');
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

        $featuredItems = $this->albumService->getFeaturedAlbums($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return [
            'music-song/add-to-playlist' => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'music',
                    'resource_name' => 'music_song',
                    'formType'      => 'addToPlaylists'
                ]
            ],
            'music-playlist/songs'       => [
                'routeName' => 'music-playlist/songs',
                'params'    => [
                    'headerTitle'   => $this->getLocalization()->translate('manage_songs'),
                    'module_name'   => 'music',
                    'resource_name' => 'music_playlist',
                    'apiUrl'        => 'mobile/music-playlist/song',
                    'use_query'     => [
                        'playlist_id' => ':id'
                    ]
                ]
            ],
            'music-song/remove-playlist' => [
                'method'        => 'delete',
                'url'           => 'mobile/music-playlist/song',
                'data'          => 'id=:playlist_id, song_id=:id',
                'actionFlow'    => 'delete_reference',
                'actionSuccess' => [
                    ['action' => 'loadDetail', 'module_name' => 'music', 'resource_name' => 'music_playlist', 'data' => 'id=:playlist_id']
                ],
            ]
        ];
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('music', []);
        $resourceAlbum = MusicAlbumResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceAlbum, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceAlbum, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceAlbum, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component'  => 'item_header',
                'transition' => 'transparent'
            ],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'             => 'item_simple_detail',
                'contentContainerStyle' => [],
                'embedComponents'       => [
                    [
                        'component'   => 'music_album_header_info',
                        'aspectRatio' => 1
                    ],
                    [
                        'component' => 'smart_tabs',
                        'tabs'      => [
                            [
                                'component' => 'music_album_info',
                                'label'     => 'information'
                            ],
                            [
                                'component' => 'music_album_songs',
                                'label'     => 'songs'
                            ]
                        ]
                    ]
                ]
            ],
            'screen_title'                 => $l->translate('music') . ' > ' . $l->translate('music_album') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addBlock($resourceAlbum, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_music_album'),
                'resource_name' => $resourceAlbum,
                'module_name'   => 'music',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_albums'),
                'resource_name' => $resourceAlbum,
                'module_name'   => 'music',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ]
        ]);
        $resourceSong = MusicSongResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceSong, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceSong, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceSong, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component'  => 'item_header',
                'transition' => 'transparent'
            ],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    'music_song_header_info',
                    'item_author',
                    [
                        'component' => 'item_stats',
                        'stats'     => ['play' => 'total_play', 'view' => 'total_view']
                    ],
                    'item_like_phrase',
                    [
                        'component' => 'item_pending',
                        'message' => 'song_is_pending_approval'
                    ],
                    'item_html_content',
                    'item_genres',
                    'item_tags'
                ]
            ],
            'screen_title'                 => $l->translate('music') . ' > ' . $l->translate('music_song') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addBlock($resourceSong, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_music_songs'),
                'resource_name' => $resourceSong,
                'module_name'   => 'music',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_songs'),
                'resource_name' => $resourceSong,
                'module_name'   => 'music',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ]
        ]);
        $resourcePlaylist = MusicPlaylistResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourcePlaylist, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourcePlaylist, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourcePlaylist, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component'  => 'item_header',
                'transition' => 'transparent'
            ],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'             => 'item_simple_detail',
                'contentContainerStyle' => [],
                'embedComponents'       => [
                    [
                        'component'   => 'music_playlist_header_info',
                        'aspectRatio' => 1
                    ],
                    [
                        'component' => 'smart_tabs',
                        'tabs'      => [
                            [
                                'component' => 'music_album_info',
                                'label'     => 'information'
                            ],
                            [
                                'component' => 'music_album_songs',
                                'label'     => 'songs'
                            ]
                        ]
                    ]
                ]
            ],
            'screen_title'                 => $l->translate('music') . ' > ' . $l->translate('music_playlist') . ' - ' . $l->translate('mobile_detail_page')
        ]);

        $screenSetting->addSetting($resourcePlaylist, 'music-playlist/songs', [
            ScreenSetting::LOCATION_HEADER => ['component' => 'simple_header'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'      => ScreenSetting::SMART_RESOURCE_LIST,
                'module_name'    => 'music',
                'resource_name'  => $resourceSong,
                'use_query'      => ['playlist_id' => ':id'],
                'list_view_name' => 'manage_playlist_song'
            ],
            'screen_title'                 => $l->translate('music') . ' > ' . $l->translate('music_playlist') . ' - ' . $l->translate('manage_songs_page')
        ]);

        $screenSetting->addSetting($resourceAlbum, 'music/playing', [
            ScreenSetting::LOCATION_TOP  => [
                'component'  => ScreenSetting::SIMPLE_HEADER,
                'title'      => 'now_playing',
                'transition' => 'transparent',
                'cancelIcon' => 'close'
            ],
            ScreenSetting::LOCATION_MAIN => [
                'component'   => 'music_now_playing',
                'aspectRatio' => 1
            ],
            'screen_title'               => $l->translate('music') . ' > ' . $l->translate('now_playing'),
            'no_ads'                     => true
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'music.browse.album',
            ScreenSetting::MODULE_LISTING => 'music.browse.album',
            ScreenSetting::MODULE_DETAIL  => 'music.view-album'
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
            ->setAllowedValues('action', [Screen::ACTION_DELETE_ITEMS, Screen::ACTION_FEATURE_ITEMS, Screen::ACTION_REMOVE_FEATURE_ITEMS]);
        $action = $this->resolver->resolveSingle($params, 'action', 'string', [], '');
        $ids = $this->resolver->resolveSingle($params, 'ids', 'array', [], []);
        if (!count($ids)) {
            return $this->missingParamsError(['ids']);
        }

        $data = [];
        $sMessage = '';
        switch ($action) {
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(MusicAlbumAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = array_merge($data, ['is_featured' => !!$value]);
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('albums_s_successfully_featured') : $this->getLocalization()->translate('albums_s_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(MusicAlbumAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('albums_s_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}