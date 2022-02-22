<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_BetterAds\Service\Process as BetterAdsProcess;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Photo\PhotoAlbumForm;
use Apps\Core_MobileApi\Api\Form\Photo\PhotoAlbumSearchForm;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\PhotoAlbumResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Photo\PhotoAlbumAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Photos\Service\Album\Album;
use Apps\Core_Photos\Service\Album\Browse;
use Apps\Core_Photos\Service\Album\Process as AlbumProcess;
use Phpfox;

class PhotoAlbumApi extends AbstractResourceApi implements ActivityFeedInterface
{
    /**
     * @var Album
     */
    private $albumService;

    /**
     * @var AlbumProcess
     */
    private $processService;

    /**
     * @var AlbumProcess
     */
    private $processPhotoService;

    /**
     * @var Browse
     */
    private $browserService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    /**
     * @var BetterAdsProcess
     */
    private $adProcessService = null;

    public function __construct()
    {
        parent::__construct();
        $this->albumService = Phpfox::getService('photo.album');
        $this->processService = Phpfox::getService('photo.album.process');
        $this->browserService = Phpfox::getService('photo.album.browse');
        $this->processPhotoService = Phpfox::getService('photo.process');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'photo-album/search-form' => [
                'get' => 'searchForm'
            ],
        ];
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        // Security checking
        $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::VIEW);

        $params = $this->resolver->setDefined([
            'view', 'module_id', 'item_id', 'q', 'sort', 'profile_id', 'limit', 'page', 'when'
        ])
            ->setAllowedValues('sort', ['latest', 'most_photos', 'most_liked', 'most_discussed'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedValues('view', ['my', 'sponsor', 'feature'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $sort = $params['sort'];
        $view = $params['view'];
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
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'photo.album',
            'alias'     => 'pa',
            'field'     => 'album_id',
            'table'     => Phpfox::getT('photo_album'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'photo.album.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'pa'
        ]);
        $isProfile = $params['profile_id'];
        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
            $this->search()->setCondition('AND pa.view_id ' . ($user['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND pa.group_id = 0 AND pa.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ') AND pa.user_id = ' . (int)$user['user_id']);
        } else {
            switch ($view) {
                case 'my':
                    if (Phpfox::isUser()) {
                        $condition = ' AND pa.user_id = ' . Phpfox::getUserId();
                        //hide special album if have no photos
                        $condition .= ' AND (((pa.profile_id > 0 OR pa.cover_id > 0 OR pa.timeline_id > 0) AND pa.total_photo > 0) OR (pa.profile_id = 0 AND pa.cover_id = 0 AND pa.timeline_id = 0))';
                        $modules = [];
                        if (!Phpfox::isAppActive('PHPfox_Groups')) {
                            $modules[] = 'groups';
                        }
                        if (!Phpfox::isAppActive('Core_Pages')) {
                            $modules[] = 'pages';
                        }
                        if (count($modules)) {
                            $condition .= ' AND (pa.module_id NOT IN ("' . implode('","',
                                    $modules) . '") OR pa.module_id IS NULL)';
                        }
                        $this->search()->setCondition($condition);
                    } else {
                        return $this->permissionError();
                    }
                    break;
                default:
                    if (!empty($parentModule)) {
                        // support new pages setting "Display pages profile photo within gallery" and "Display pages cover photo within gallery" (gallery of pages)
                        $aHiddenAlbums = [];
                        if (Phpfox::hasCallback($parentModule['module_id'], 'getHiddenAlbums')) {
                            $aHiddenAlbums = Phpfox::callback($parentModule['module_id'] . '.getHiddenAlbums', $parentModule['item_id']);
                        }
                        $this->search()->setCondition('AND pa.module_id = \'' . $parentModule['module_id'] . '\' AND pa.group_id = ' . (int)$parentModule['item_id'] . (count($aHiddenAlbums) ? ' AND pa.album_id NOT IN (' . implode(',', $aHiddenAlbums) . ')' : ''));
                    } else {
                        $condition = 'AND pa.view_id = 0 AND pa.total_photo > 0';
                        if (Phpfox::getParam('photo.display_photo_album_created_in_group') || Phpfox::getParam('photo.display_photo_album_created_in_page')) {
                            $modules = [];
                            if (Phpfox::getParam('photo.display_photo_album_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                                $modules[] = 'groups';
                            }
                            if (Phpfox::getParam('photo.display_photo_album_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                                $modules[] = 'pages';
                            }
                            if (count($modules)) {
                                $condition .= ' AND (pa.module_id IN ("' . implode('","',
                                        $modules) . '") OR pa.module_id is NULL)';
                            } else {
                                $condition .= ' AND pa.module_id is NULL';
                            }
                        } else {
                            $condition .= ' AND pa.group_id = 0';
                        }
                        if (!Phpfox::getUserParam('privacy.can_view_all_items')) {
                            $condition .= ' AND pa.privacy IN(%PRIVACY%)';
                        }
                        $this->search()->setCondition($condition);
                    }
                    break;
            }
        }
        // not use this setting in pages view, because pages have seperate settings about this.
        if ($view != 'my' && !$parentModule) {
            if (!Phpfox::getParam('photo.display_profile_photo_within_gallery')) {
                $this->search()->setCondition('AND pa.profile_id = 0');
            }
            if (!Phpfox::getParam('photo.display_cover_photo_within_gallery')) {
                $this->search()->setCondition('AND pa.cover_id = 0');
            }
            if (!Phpfox::getParam('photo.display_timeline_photo_within_gallery')) {
                $this->search()->setCondition('AND pa.timeline_id = 0');
            }
        }

        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND pa.name LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }

        // sort
        switch ($sort) {
            case 'most_photos':
                $sort = 'pa.total_photo DESC';
                break;
            case 'most_liked':
                $sort = 'pa.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'pa.total_comment DESC';
                break;
            default:
                $sort = 'pa.time_stamp DESC';
                break;
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);
        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $items = $this->browse()->getRows();
        $this->processRows($items);
        return $this->success($items);
    }

    public function getById($id)
    {
        $where = ['AND pa.album_id = ' . (int)$id];
        $item = $this->database()->select('pa.*, p.destination, p.server_id, p.mature, pai.description, ' . Phpfox::getUserField())
            ->from(':photo_album', 'pa')
            ->leftjoin(':photo', 'p', 'p.album_id = pa.album_id AND p.is_cover = 1')
            ->join(':user', 'u', 'u.user_id = pa.user_id')
            ->join(':photo_album_info', 'pai', 'pai.album_id = pa.album_id')
            ->where($where)
            ->execute('getRow');
        if (empty($item['album_id'])) {
            return null;
        }
        $isProfilePictureAlbum = $isCoverPhotoAlbum = $isTimelinePhotoAlbum = false;
        if ($item['profile_id'] > 0) {
            $isProfilePictureAlbum = true;
        } else if ($item['cover_id'] > 0) {
            $isCoverPhotoAlbum = true;
        } else if ($item['timeline_id'] > 0) {
            $isTimelinePhotoAlbum = true;
        }
        if ($isProfilePictureAlbum) {
            $item['name'] = $this->getLocalization()->translate('user_profile_pictures', ['full_name' => $item['full_name']]);
        } else if ($isCoverPhotoAlbum) {
            $item['name'] = $this->getLocalization()->translate('user_cover_photo', ['full_name' => $item['full_name']]);
        } else if ($isTimelinePhotoAlbum) {
            $item['name'] = $this->getLocalization()->translate('user_timeline_photos', ['full_name' => $item['full_name']]);
        }
        return PhotoAlbumResource::populate($item)->toArray(['resource_name', 'name', 'image', 'privacy', 'user', 'id', 'description', 'text']);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $params = $this->resolver
            ->setDefined(['page', 'limit'])
            ->setRequired(['id'])
            ->resolve(array_merge(['page' => 1, 'limit' => 10], $params))
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }

        $item = $this->albumService->getForView($params['id']);
        // Make sure this is a valid album
        if ($item === false) {
            return $this->notFoundError();
        }

        $isProfilePictureAlbum = $isCoverPhotoAlbum = $isTimelinePhotoAlbum = false;
        if ($item['profile_id'] > 0) {
            $isProfilePictureAlbum = true;
        } else if ($item['cover_id'] > 0) {
            $isCoverPhotoAlbum = true;
        } else if ($item['timeline_id'] > 0) {
            $isTimelinePhotoAlbum = true;
        }
        if ($isProfilePictureAlbum) {
            $item['name'] = $this->getLocalization()->translate('user_profile_pictures', ['full_name' => $item['full_name']]);
        } else if ($isCoverPhotoAlbum) {
            $item['name'] = $this->getLocalization()->translate('user_cover_photo', ['full_name' => $item['full_name']]);
        } else if ($isTimelinePhotoAlbum) {
            $item['name'] = $this->getLocalization()->translate('user_timeline_photos', ['full_name' => $item['full_name']]);
        }
        $item['name'] = Phpfox::getLib('locale')->convert($item['name']);
        $cover = $this->database()
            ->select('p.destination, p.server_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->where('p.is_cover = 1 AND p.album_id = ' . (int)$item['album_id'])
            ->execute('getRow');
        if (!empty($cover)) {
            $item = array_merge($item, $cover);
        }
        $page = isset($params['page']) ? intval($params['page']) : $this->request()->get('page', 1);
        $limit = isset($params['limit']) ? intval($params['limit']) : $this->request()->get('limit', 10);
        // Create the SQL condition array
        $conditions = [];
        $conditions[] = 'p.album_id = ' . $item['album_id'] . '';
        list(, $photos) = Phpfox::getService('photo')->get($conditions, 'p.ordering ASC, p.photo_id DESC', $page, $limit);
        $item['photos'] = $photos;
        /** @var PhotoAlbumResource $resource */
        $resource = $this->populateResource(PhotoAlbumResource::class, $item);

        $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::VIEW, $resource);
        $this->setHyperlinks($resource, true);
        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->lazyLoad(['user'])
            ->loadFeedParam()
            ->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::ADD);
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var PhotoAlbumForm $form */
        $form = $this->createForm(PhotoAlbumForm::class, [
            'title'  => 'create_a_new_photo_album',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('photo-album')
        ]);
        /** @var PhotoAlbumResource $album */
        $album = $this->loadResourceById($editId, true, true);
        if ($editId && empty($album)) {
            return $this->notFoundError();
        }
        if ($album) {
            $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::EDIT, $album);
            $form->setTitle('update_album')
                ->setAction(UrlUtility::makeApiUrl('photo-album/:id', $editId))
                ->setMethod('PUT');
            $form->setCanEditName($this->canEditAlbumName($album->toArray()));
            $form->assignValues($album);
        }
        return $this->success($form->getFormStructure());
    }

    private function canEditAlbumName($album)
    {
        $result = true;
        if ($album['profile_id'] > 0) {
            $result = false;
        } else if ($album['cover_id'] > 0) {
            $result = false;
        } else if ($album['timeline_id'] > 0) {
            $result = false;
        }
        return $result;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::ADD);
        /** @var PhotoAlbumForm $form */
        $form = $this->createForm(PhotoAlbumForm::class);
        if ($form->isValid()) {
            $id = $this->processCreate($form->getValues());
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => PhotoAlbumResource::populate([])->getResourceName(),
                    'module_name'   => 'photo',
                ], [], $this->localization->translate('photo_album_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }

    }

    private function processCreate($values)
    {
        if (!empty($values['item_id'])) {
            $values['group_id'] = $values['item_id'];
        }
        $values['description'] = isset($values['text']) ? $values['text'] : '';
        return $this->processService->add($values);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var PhotoAlbumForm $form */
        $form = $this->createForm(PhotoAlbumForm::class);
        /** @var PhotoAlbumResource $album */
        $album = $this->loadResourceById($id, true);
        if (empty($album)) {
            return $this->notFoundError();
        }
        $form->setCanEditName($this->canEditAlbumName($album->toArray()));

        $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::EDIT, $album);
        if ($form->isValid() && ($values = $form->getValues())) {
            if (!$form->isCanEditName()) {
                $values['name'] = $album->getName();
            }
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $success,
                    'resource_name' => PhotoAlbumResource::populate([])->getResourceName(),
                    'module_name'   => 'photo',
                ], [], $this->localization->translate('photo_album_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        $values['album_id'] = $id;
        $values['description'] = isset($values['text']) ? $values['text'] : '';
        return $this->processService->add($values, true);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $itemId = $params['id'];
        if ($itemId < 1) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('photo.can_view_photos')) {
            $mResult = Phpfox::getService('photo.album.process')->delete($itemId);
            if ($mResult !== false) {
                return $this->success([], [], $this->getLocalization()->translate('photo_album_successfully_deleted'));
            }
        }
        return $this->permissionError();

    }


    /**
     * @param      $id
     * @param bool $returnResource
     * @param bool $forEdit
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false, $forEdit = false)
    {
        $item = $this->albumService->getForEdit($id, true);
        if (empty($item['album_id'])) {
            return null;
        }
        if ($forEdit) {
            $item['is_edit'] = true;
        }
        if ($returnResource) {
            return PhotoAlbumResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        /** @var PhotoAlbumResource $resource */
        $resource = $this->populateResource(PhotoAlbumResource::class, $item);
        $this->setHyperlinks($resource);

        $view = $this->request()->get('view');
        $shortFields = [];

        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'name', 'image', 'statistic', 'id'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->toArray($shortFields);
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
        $limitPhoto = 4;
        $aAlbum = $this->database()->select('pa.total_photo, pa.privacy, pa.album_id')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->where('pa.album_id = ' . (int)$feed['item_id'])
            ->execute('getRow');
        $iFeedId = isset($feed['feed_id']) ? $feed['feed_id'] : 0;
        $totalPhoto = $aAlbum['total_photo'];
        $aRows = $this->database()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
            ->where('p.album_id = ' . $feed['item_id'] . ' AND p.view_id = 0')
            ->limit($limitPhoto)
            ->order('p.time_stamp DESC')
            ->execute('getSlaveRows');
        $aPhotos = array_map(function ($aPhoto) {
            if ($aPhoto['mature'] == 0 || ($this->getUser()->getId() && $this->getSetting()->getUserSetting('photo.photo_mature_age_limit') <= $this->getUser()->getAge()) || $aPhoto['user_id'] == Phpfox::getUserId()) {
                $photoUrl = UrlUtility::getPhotoUrl('photo.url_photo', $aPhoto['server_id'],
                    $aPhoto['destination'], '1024');
            } else {
                $photoUrl = Phpfox::getLib('image.helper')->display([
                    'theme'      => 'misc/mature.jpg',
                    'return_url' => true
                ]);
            }
            return [
                'id'            => intval($aPhoto['photo_id']),
                'mature'        => intval($aPhoto['mature']),
                'module_name'   => 'photo',
                'resource_name' => 'photo',
                'width'         => isset($aPhoto['width']) ? (int)$aPhoto['width'] : 0,
                'height'        => isset($aPhoto['height']) ? (int)$aPhoto['height'] : 0,
                'image'         => $photoUrl,
            ];
        }, $aRows);
        return [
            'module_name'   => 'photo',
            'resource_name' => str_replace('-', '_', PhotoAlbumResource::RESOURCE_NAME),
            'privacy'       => intval($aAlbum['privacy']),
            'total_photo'   => intval($totalPhoto),
            'album_id'      => intval($aAlbum['album_id']),
            'feed_id'       => intval($iFeedId),
            'remain_photo'  => $totalPhoto > count($aRows) ? intval($totalPhoto - count($aRows)) : 0,
            'photos'        => $aPhotos,
        ];
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new PhotoAlbumAccessControl($this->getSetting(), $this->getUser());

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
        $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::VIEW);
        /** @var PhotoAlbumSearchForm $form */
        $form = $this->createForm(PhotoAlbumSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('photo-album')
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(PhotoAlbumResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            PhotoAlbumAccessControl::VIEW   => $this->createHyperMediaLink(PhotoAlbumAccessControl::VIEW, $resource,
                HyperLink::GET, 'photo-album/:id', ['id' => $resource->getId()]),
            PhotoAlbumAccessControl::EDIT   => $this->createHyperMediaLink(PhotoAlbumAccessControl::EDIT, $resource,
                HyperLink::GET, 'photo-album/form/:id', ['id' => $resource->getId()]),
            PhotoAlbumAccessControl::DELETE => $this->createHyperMediaLink(PhotoAlbumAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'photo-album/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(PhotoAlbumAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'photo_album']),
                'comments' => $this->createHyperMediaLink(PhotoAlbumAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'photo_album'])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', PhotoAlbumResource::RESOURCE_NAME);
        $module = 'photo';
        return [
            [
                'path'      => 'photo/album/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'photo/albums(/*)',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
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
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('photo_album_feature_successfully') : $this->getLocalization()->translate('photo_album_unfeature_successfully'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        /** @var PhotoAlbumResource $item */
        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if (!$this->getAccessControl()->isGranted(PhotoAlbumAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(PhotoAlbumAccessControl::PURCHASE_SPONSOR, $item)) {
            return $this->permissionError();
        }
        if ($this->processService->sponsor($id, $sponsor)) {
            if ($sponsor == 1) {
                $sModule = $this->getLocalization()->translate('photo_album');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'photo',
                    'section' => 'album',
                    'item_id' => $id,
                    'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getName()])
                ], false);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('photo_album', $id);
            }
            return $this->success([
                'is_sponsor' => !!$sponsor
            ], [], $sponsor ? $this->getLocalization()->translate('photo_album_sponsor_successfully') : $this->getLocalization()->translate('photo_album_unsponsor_successfully'));
        }
        return $this->error();
    }

    /**
     * Get sponsored items
     *
     * @param $params
     *
     * @return array
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->albumService->getRandomSponsoredAlbum($limit, $cacheTime);

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
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'photo.album');
            }
        }
    }

    /**
     * @param $params
     *
     * @return array
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

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'photo.albums',
            ScreenSetting::MODULE_LISTING => 'photo.albums',
            ScreenSetting::MODULE_DETAIL  => 'photo.album'
        ];
    }

    /**
     * Moderation items
     *
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
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
                $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('albums_s_successfully_featured') : $this->getLocalization()->translate('albums_s_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->delete($id, '', 0, true)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('albums_s_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}