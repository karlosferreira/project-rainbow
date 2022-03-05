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
use Apps\Core_MobileApi\Api\Form\Photo\PhotoForm;
use Apps\Core_MobileApi\Api\Form\Photo\PhotoSearchForm;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\PhotoAlbumResource;
use Apps\Core_MobileApi\Api\Resource\PhotoCategoryResource;
use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Photo\PhotoAccessControl;
use Apps\Core_MobileApi\Api\Security\Photo\PhotoAlbumAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Photos\Service\Browse;
use Apps\Core_Photos\Service\Category\Category;
use Apps\Core_Photos\Service\Photo;
use Apps\Core_Photos\Service\Process;
use Phpfox;

class PhotoApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    const ERROR_PHOTO_NOT_FOUND = "Photo not found";

    /**
     * @var Photo
     */
    private $photoService;

    /**
     * @var Category
     */
    private $categoryService;

    /**
     * @var Process
     */
    private $processService;
    /**
     * @var Browse
     */
    private $browserService;
    /**
     * @var \User_Service_User
     */
    private $userService;

    private $bIsFeed;

    public function __construct()
    {
        parent::__construct();
        $this->photoService = Phpfox::getService('photo');
        $this->categoryService = Phpfox::getService('photo.category');
        $this->processService = Phpfox::getService('photo.process');
        $this->browserService = Phpfox::getService('mobile.photo_browse_helper');
        $this->userService = Phpfox::getService('user');
    }

    public function __naming()
    {
        return [
            'photo/search-form'        => [
                'get' => 'searchForm'
            ],
            'photo/album-upload/:id'   => [
                'get' => 'getUploadFormByAlbum'
            ],
            'photo/album-cover/:id'    => [
                'put' => 'setAlbumCover'
            ],
            'photo/profile-cover/:id'  => [
                'put' => 'setProfileCover'
            ],
            'photo/profile-avatar/:id' => [
                'put' => 'setProfileAvatar'
            ],
            'photo/parent-cover/:id'   => [
                'put' => 'setParentCover'
            ]
        ];
    }

    /**
     * Get list photo
     *
     * @param array $params
     *
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    function findAll($params = [])
    {
        // Security checking
        $this->denyAccessUnlessGranted(PhotoAccessControl::VIEW);

        $params = $this->resolver->setDefined([
            'view', 'module_id', 'item_id', 'category', 'q', 'sort', 'profile_id', 'limit', 'page', 'album_id', 'when', 'feed_id', 'tag'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'pending', 'friend', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('feed_id', 'int')
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('category', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('album_id', 'int')
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

        $this->bIsFeed = false;
        $bAlbumSearch = false;
        if (!empty($params['album_id'])) {
            /** @var PhotoAlbumResource $album */
            $album = (new PhotoAlbumApi())->loadResourceById((int)$params['album_id'], true);
            if (!$album) {
                return $this->notFoundError($this->getLocalization()->translate('photo_album_not_found'));
            }
            $this->denyAccessUnlessGranted(PhotoAlbumAccessControl::VIEW, $album);
            $this->search()->setCondition('AND photo.album_id = ' . (int)$params['album_id']);
            if (!empty($album->module_id) && !empty($album->group_id)) {
                $this->browse()->changeParentView($album->module_id, $album->group_id);
            }
            $bAlbumSearch = true;
        }
        $isProfile = $params['profile_id'];
        if ($isProfile) {
            if (Phpfox::getService('user.block')->isBlocked($isProfile, $this->getUser()->getId())) {
                return $this->success([]);
            }

            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
            $this->search()->setCondition('AND photo.view_id = 0 AND photo.group_id = 0 AND photo.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ') AND photo.user_id = ' . (int)$user['user_id']);
        }
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'photo',
            'alias' => 'photo',
            'field' => 'photo_id',
            'table' => Phpfox::getT('photo'),
            'hide_view' => ['pending', 'my'],
            'service' => 'mobile.photo_browse_helper',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'photo'
        ]);
        switch ($view) {
            case 'pending':
                if (!Phpfox::getUserParam('photo.can_approve_photos')) {
                    return $this->permissionError();
                }
                $condition = 'AND photo.view_id = 1';
                $modules = [];
                if (!Phpfox::isAppActive('PHPfox_Groups')) {
                    $modules[] = 'groups';
                }
                if (!Phpfox::isAppActive('Core_Pages')) {
                    $modules[] = 'pages';
                }
                if (count($modules)) {
                    $condition .= ' AND (photo.module_id NOT IN ("' . implode('","',
                            $modules) . '") OR photo.module_id IS NULL)';
                }
                $this->search()->setCondition($condition);
                break;
            case 'my':
                if (Phpfox::isUser()) {
                    $condition = 'AND (photo.type_id = 0 OR (photo.type_id = 1 AND (photo.parent_user_id = 0 OR photo.group_id != 0))) AND photo.user_id = ' . Phpfox::getUserId();
                    $modules = [];
                    if (!Phpfox::isAppActive('PHPfox_Groups')) {
                        $modules[] = 'groups';
                    }
                    if (!Phpfox::isAppActive('Core_Pages')) {
                        $modules[] = 'pages';
                    }
                    if (count($modules)) {
                        $condition .= ' AND (photo.module_id NOT IN ("' . implode('","',
                                $modules) . '") OR photo.module_id IS NULL)';
                    }

                    $this->search()->setCondition($condition);
                } else {
                    return $this->permissionError();
                }
                break;
            default:
                $condition = 'AND photo.view_id = 0';
                if (!$bAlbumSearch) {
                    if (!empty($parentModule)) {
                        $condition .= ' AND photo.module_id = \'' . db()->escape($parentModule['module_id']) . '\' AND photo.group_id = ' . (int)$parentModule['item_id'];
                        if (!Phpfox::getUserParam('privacy.can_view_all_items')) {
                            $condition .= ' AND photo.privacy IN(%PRIVACY%)';
                        }

                        // support new pages setting "Display pages profile photo within gallery" and "Display pages cover photo within gallery" (gallery of pages)
                        $aHiddenAlbums = [];
                        if (isset($parentModule['module_id']) && Phpfox::hasCallback($parentModule['module_id'],
                                'getHiddenAlbums')
                        ) {
                            $aHiddenAlbums = Phpfox::callback($parentModule['module_id'] . '.getHiddenAlbums',
                                $parentModule['item_id']);
                        }
                        if (count($aHiddenAlbums)) {
                            $condition .= ' AND photo.album_id NOT IN (' . implode(',', $aHiddenAlbums) . ')';
                        }
                    } else {
                        $condition .= $this->photoService->getConditionsForSettingPageGroup('photo');
                        if (!Phpfox::getUserParam('privacy.can_view_all_items')) {
                            $condition .= ' AND photo.privacy IN(%PRIVACY%)';
                        }
                    }
                }
                $this->search()->setCondition($condition);
                break;
        }


        if ($params['category']) {
            $sWhere = 'AND pcd.category_id = ' . (int)$params['category'];

            // Get sub-categories
            $aSubCategories = $this->categoryService->getForBrowse($params['category']);

            if (!empty($aSubCategories) && is_array($aSubCategories)) {
                $aSubIds = $this->categoryService->extractCategories($aSubCategories);
                if (!empty($aSubIds)) {
                    $sWhere = 'AND pcd.category_id IN (' . (int)$params['category'] . ',' . join(',',
                            $aSubIds) . ')';
                }
            }

            $this->search()->setCondition($sWhere);
            $this->browserService->category($params['category']);
        }
        if (!$parentModule && $view != 'pending' && !$bAlbumSearch && empty($params['feed_id'])) {
            if (!Phpfox::getParam('photo.display_profile_photo_within_gallery')) {
                $this->search()->setCondition('AND photo.is_profile_photo IN (0)');
            }
            if (!Phpfox::getParam('photo.display_cover_photo_within_gallery')) {
                $this->search()->setCondition('AND photo.is_cover_photo IN (0)');
            }
            if (!Phpfox::getParam('photo.display_timeline_photo_within_gallery') && empty($params['feed_id'])) {
                $this->search()->setCondition('AND (photo.type_id = 0 OR (photo.type_id = 1 AND photo.group_id != 0))');
            }
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND photo.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }
        // Search By tag
        if ($params['tag']) {
            if (Phpfox::isModule('tag') && $aTag = Phpfox::getService('tag')->getTagInfo('photo', urldecode($params['tag']))) {
                $this->search()->setCondition('AND tag.tag_text = \'' . urldecode(db()->escape($aTag['tag_text'])) . '\'');
            } else {
                $this->search()->setCondition('AND 0');
            }
        }
        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'photo.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'photo.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'photo.total_comment DESC';
                break;
            default:
                $sort = 'photo.photo_id DESC';
                break;
        }

        if (!empty($params['feed_id'])) {
            $this->bIsFeed = true;
            //Get photo on feed
            $feedTable = '';
            if (!empty($params['module_id']) && !empty($params['item_id']) && Phpfox::hasCallback($params['module_id'], 'getPhotoDetails')) {
                $callback = Phpfox::callback($params['module_id'] . '.getPhotoDetails', ['group_id' => $params['item_id']]);
                $feedTable = isset($callback['feed_table_prefix']) ? $callback['feed_table_prefix'] : '';
            }
            $aFeed = Phpfox::getService('feed')->getFeed($params['feed_id'], $feedTable);
            if ($aFeed) {
                $this->search()->setCondition(' AND ((pfeed.feed_id = ' . $params['feed_id'] . ' AND  pfeed.feed_table = \'' . $feedTable . 'feed\') OR photo.photo_id = ' . $aFeed['item_id'] . ')');
            } else {
                return $this->notFoundError();
            }
        }
        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);

        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $items = $this->browse()->getRows();
        $this->processRows($items);
        return $this->success($items);
    }

    /**
     * Get feed photos
     *
     * @param        $iFeedId
     * @param null   $iLimit
     * @param string $sFeedTablePrefix
     *
     * @return array|int|string
     * @deprecated Remove in the next version
     * @codeCoverageIgnore
     */
    public function getFeedPhotos($iFeedId, $iLimit = null, $sFeedTablePrefix = '')
    {
        $aFeed = Phpfox::getService('feed')->getFeed($iFeedId, $sFeedTablePrefix);
        if (!$aFeed) {
            return [];
        }
        $aCondition[] = '(pfeed.feed_id = ' . $iFeedId . ' AND  pfeed.feed_table = \'' . $sFeedTablePrefix . 'feed\') OR p.photo_id = ' . $aFeed['item_id'];
        if ($iLimit) {
            $aPhotos = db()
                ->select('p.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('photo'), 'p')
                ->join(':user', 'u', 'u.user_id = p.user_id')
                ->leftJoin(Phpfox::getT('photo_feed'), 'pfeed', 'p.photo_id = pfeed.photo_id')
                ->where($aCondition)
                ->limit($iLimit)
                ->order('pfeed.feed_id ASC, p.photo_id DESC')
                ->execute('getSlaveRows');
        } else {
            $aPhotos = db()
                ->select('p.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('photo'), 'p')
                ->join(':user', 'u', 'u.user_id = p.user_id')
                ->leftJoin(Phpfox::getT('photo_feed'), 'pfeed', 'p.photo_id = pfeed.photo_id')
                ->where($aCondition)
                ->order('pfeed.feed_id ASC, p.photo_id DESC')
                ->execute('getSlaveRows');
        }
        return $aPhotos;
    }

    /**
     * Get photo info
     *
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $item = $this->photoService->getPhoto($id);

        if (!isset($item['photo_id']) || ($item['view_id'] && !Phpfox::getUserParam('photo.can_approve_photos') && $item['user_id'] != Phpfox::getUserId())) {
            return $this->notFoundError();
        }
        
        $this->denyAccessUnlessGranted(PhotoAccessControl::VIEW, PhotoResource::populate($item));

        if ($item['mature'] != 0) {
            if (Phpfox::getUserId()) {
                if ($item['user_id'] != Phpfox::getUserId()) {
                    if ($item['mature'] == 2 && Phpfox::getUserParam(['photo.photo_mature_age_limit' => ['>', (int)$this->getUser()->getAge()]])
                    ) {
                        return $this->permissionError($this->getLocalization()->translate('sorry_this_photo_can_only_be_viewed_by_those_older_than_the_age_of_limit', ['limit' => $this->getSetting()->getUserSetting('photo.photo_mature_age_limit')]));
                    }
                }
            }
        }
        /** @var PhotoResource $resource */
        $item['user_tags'] = Phpfox::getService('photo.tag')->getTagByIds($item['photo_id']);
        $item['is_detail'] = true;
        $resource = $this->populateResource(PhotoResource::class, $item);
        $this->setHyperlinks($resource, true);
        $this->denyAccessUnlessGranted(PhotoAccessControl::VIEW, $resource);
        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$item['is_viewed']) {
                $updateCounter = true;
                Phpfox::getService('track.process')->add('photo', $item['photo_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('photo', $item['photo_id']);
                } else {
                    Phpfox::getService('track.process')->update('photo', $item['photo_id']);
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            Phpfox::getService('photo.process')->updateCounter($item['photo_id'], 'total_view');
        }

        $resource->setItemId();

        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->lazyLoad(['user'])
            ->loadFeedParam()
            ->toArray());
    }

    /**
     * @param array $params
     *
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    function form($params = [])
    {
        $this->denyAccessUnlessGranted(PhotoAccessControl::ADD);
        $params = $this->resolver
            ->setDefined(['module_id', 'item_id', 'id', 'album_id'])
            ->resolve($params)->getParameters();
        $editId = $params['id'];
        /** @var PhotoForm $form */
        $form = $this->createForm(PhotoForm::class, [
            'title'  => 'share_photos',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('photo')
        ]);
        /** @var PhotoResource $photo */
        $photo = $this->loadResourceById($editId);
        if (!empty($params['album_id']) && $album = (new PhotoAlbumApi())->loadResourceById($params['album_id'])) {
            $params['module_id'] = $album['module_id'];
            $params['item_id'] = $album['group_id'];
        }
        if ($editId && empty($photo)) {
            return $this->notFoundError();
        }
        if ($photo) {
            $photo['is_form'] = true;
            if (isset($photo['can_add_mature'])) {
                $form->setCanMature($photo['can_add_mature']);
            }
            $form->setEditing(true);
            $photo = PhotoResource::populate($photo);
            $this->denyAccessUnlessGranted(PhotoAccessControl::EDIT, $photo);
            $form->setTitle('editing_photo')
                ->setAction(UrlUtility::makeApiUrl('photo/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($photo);
            if ($photo->user->getId() == $this->getUser()->getId()) {
                $form->setAlbumId($photo->album_id);
                $form->setAlbums($this->getAlbums($photo->module_id, $photo->group_id, $photo));
            }
        } else {
            if (($iFlood = $this->getSetting()->getUserSetting('photo.flood_control_photos')) !== 0) {
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field'      => 'time_stamp', // The time stamp field
                        'table'      => Phpfox::getT('photo'), // Database table we plan to check
                        'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];

                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error($this->getLocalization()->translate('uploading_photos_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }
            if (!empty($params['album_id'])) {
                $form->setAlbumId($params['album_id']);
            }
            $form->assignValues([
                'module_id' => $params['module_id'],
                'item_id'   => $params['item_id'] ? $params['item_id'] : null
            ]);
            $form->setAlbums($this->getAlbums($params['module_id'], $params['item_id'], null));
        }

        $form->setCategories($this->getCategories());

        return $this->success($form->getFormStructure());
    }


    public function getUploadFormByAlbum($params)
    {
        $id = $this->resolver->resolveId($params);
        if ($id) {
            return $this->form(['album_id' => $id]);
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(PhotoAccessControl::ADD);
        $params = $this->resolver
            ->setDefined(['module_id', 'item_id', 'album'])
            ->resolve($params)->getParameters();
        /** @var PhotoForm $form */
        $form = $this->createForm(PhotoForm::class);
        $form->setCategories($this->getCategories());
        if (!empty($params['album']) && $album = (new PhotoAlbumApi())->loadResourceById($params['album'])) {
            $params['module_id'] = $album['module_id'];
            $params['item_id'] = $album['group_id'];
        }
        $form->setAlbums($this->getAlbums($params['module_id'], $params['item_id'], null));
        if ($form->isValid()) {
            $ids = $this->processCreate($form->getValues());
            if ($ids) {
                return $this->success([
                    'ids'           => $ids,
                    'resource_name' => PhotoResource::populate([])->getResourceName()
                ], [], $this->localization->translate('photo_s_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    /**
     * Process create Photo post
     *
     * @param      $values
     * @param bool $checkPerm
     *
     * @return array|bool|string
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    public function processCreate($values, $checkPerm = false)
    {
        if ($checkPerm) {
            $this->denyAccessUnlessGranted(PhotoAccessControl::ADD);
        }
        if (($iFlood = $this->getSetting()->getUserSetting('photo.flood_control_photos')) !== 0) {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp', // The time stamp field
                    'table'      => Phpfox::getT('photo'), // Database table we plan to check
                    'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];

            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->error($this->getLocalization()->translate('uploading_photos_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }
        $this->convertSubmitForm($values);
        $file_list = $this->executeImagesUploaded($values);
        if (empty($file_list)) {
            return false;
        }
        return $this->add($file_list);
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var PhotoForm $form */
        $form = $this->createForm(PhotoForm::class);
        $form->setEditing(true);
        $photo = $this->loadResourceById($id);
        if (empty($photo)) {
            return $this->notFoundError();
        }
        if (isset($photo['can_add_mature'])) {
            $form->setCanMature($photo['can_add_mature']);
        }
        $form->setCategories($this->getCategories());
        $resource = PhotoResource::populate($photo);
        $form->setAlbums($this->getAlbums($photo['module_id'], $photo['group_id'], $resource));
        $this->denyAccessUnlessGranted(PhotoAccessControl::EDIT, $resource);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($photo, $values);
            if ($success) {
                return $this->success([
                    'id'            => $success,
                    'resource_name' => PhotoResource::populate([])->getResourceName()
                ], [], $this->localization->translate('photo_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    /**
     * @param $values
     * @param $item
     *
     * @return int
     */
    private function processUpdate($item, $values)
    {
        $this->convertSubmitForm($values, true);
        $values['album_id'] = $item['album_id'];
        //Should not move to currently album
        if (!empty($values['move_to']) && $values['move_to'] == $item['album_id']) {
            $values['move_to'] = 0;
        }
        $values['photo_id'] = $item['photo_id'];
        $values['description'] = isset($values['text']) ? $values['text'] : '';
        if (!empty($values['module_id']) && !empty($values['item_id'])) {
            $values['parent_user_id'] = $values['item_id'];
        }
        return $this->processService->add(Phpfox::getUserId(), $values, true);
    }

    /**
     * @param      $vals
     * @param bool $edit
     */
    private function convertSubmitForm(&$vals, $edit = false)
    {
        if (isset($vals['categories'])) {
            $vals['category_id'] = $vals['categories'];
            unset($vals['categories']);
        }
        if (isset($vals['tags'])) {
            $vals['tag_list'] = $vals['tags'];
            unset($vals['tags']);
        }
        if (!empty($vals['item_id'])) {
            $vals['group_id'] = $vals['item_id'];
        }
        if (!empty($vals['album'])) {
            if (!$edit) {
                $vals['album_id'] = $vals['album'];
            } else {
                $vals['move_to'] = $vals['album'];
            }
        }
    }

    private function executeImagesUploaded($vals)
    {
        $finalVals = [];
        $uploadList = $vals['files']['new'];
        unset($vals['files']);
        foreach ((array)$uploadList as $key => $file) {
            $uploadedFile = Phpfox::getService('core.temp-file')->get($file);
            if (!$uploadedFile || empty($uploadedFile['extra_info'])) {
                continue;
            }
            $extraInfo = json_decode($uploadedFile['extra_info'], true);
            $data = [
                'name'           => $extraInfo['name'],
                'destination'    => $uploadedFile['path'],
                'width'          => isset($extraInfo['width']) ? $extraInfo['width'] : null,
                'height'         => isset($extraInfo['height']) ? $extraInfo['height'] : null,
                'server_id'      => $uploadedFile['server_id'],
                'ext'            => $extraInfo['ext'],
                'size'           => $extraInfo['size'],
                'type'           => $extraInfo['type'],
                'allow_rate'     => (empty($vals['album_id']) ? '1' : '0'),
                'description'    => (empty($vals['description']) ? null : $vals['description']),
                'allow_download' => 1,
                'tagged_friends' => (empty($vals['tagged_friends']) ? null : $vals['tagged_friends'])
            ];
            $finalVals[] = array_merge($vals, $data);
            //Remove from table temp file
            Phpfox::getService('core.temp-file')->delete($file);
        }
        return $finalVals;
    }

    private function add($vals)
    {
        $ids = [];
        $moduleId = isset($vals[0]['module_id']) ? $vals[0]['module_id'] : '';
        $itemId = isset($vals[0]['item_id']) ? $vals[0]['item_id'] : '';
        $parentUserId = !$itemId && !empty($vals[0]['parent_user_id']) ? $vals[0]['parent_user_id'] : 0;
        if ($parentUserId > 0) {
            $itemId = $parentUserId;
        }
        $albumId = 0;
        $isInFeed = $vals[0]['type_id'] == 1;
        foreach ($vals as $values) {
            if (!empty($values['module_id']) && !empty($values['item_id'])) {
                $values['parent_user_id'] = $values['item_id'];
            }
            $id = $this->processService->add(Phpfox::getUserId(), $values);
            if ($id) {
                unset($values['type_id']);
                $this->processService->update(Phpfox::getUserId(), $id, $values);
                // Have we posted an album for these set of photos?
                if (isset($values['album_id']) && !empty($values['album_id'])) {
                    $albumId = $values['album_id'];
                    // Set the album privacy
                    Phpfox::getService('photo.album.process')->setPrivacy($values['album_id']);

                    // Check if we already have an album cover
                    if (!Phpfox::getService('photo.album.process')->hasCover($values['album_id'])) {
                        // Set the album cover
                        Phpfox::getService('photo.album.process')->setCover($values['album_id'], $id);
                    }
                }
            }
            $ids[] = $id;
        }
        // Update the album photo count
        if (!Phpfox::getUserParam('photo.photo_must_be_approved')) {
            Phpfox::getService('photo.album.process')->updateCounter($albumId, 'total_photo');
        }
        //Reverse last photo to first
        $ids = array_reverse($ids);
        //Add feed
        $callback = null;
        foreach ($ids as $iKey => $id) {
            if (Phpfox::isModule('feed') && !Phpfox::getUserParam('photo.photo_must_be_approved') && Phpfox::getParam('photo.photo_allow_create_feed_when_add_new_item')) {
                if ($iKey == 0) {
                    $photo = $this->photoService->getForProcess($id, Phpfox::getUserId());
                    $callback = ((!empty($moduleId) && Phpfox::hasCallback($moduleId, 'addPhoto')) ? Phpfox::callback($moduleId . '.addPhoto', $itemId) : null);
                    $feedId = Phpfox::getService('feed.process')->callback($callback)->add('photo',
                        $photo['photo_id'], $photo['privacy'], $photo['privacy_comment'], $itemId);
                    if ($callback && defined('PHPFOX_NEW_FEED_LOOP_ID') && PHPFOX_NEW_FEED_LOOP_ID) {
                        storage()->set('photo_parent_feed_' . PHPFOX_NEW_FEED_LOOP_ID, $feedId);
                    }
                    if ($isInFeed) {
                        $this->processService->notifyTaggedInFeed(isset($vals[0]['description']) ? $vals[0]['description'] : '', $id, $this->getUser()->getId(), $feedId, $vals[0]['tagged_friends'], isset($vals[0]['privacy']) ? $vals[0]['privacy'] : 0, $itemId, $moduleId);
                    }
                    if ($callback && Phpfox::isModule('notification') && Phpfox::isModule($callback['module'])
                        && Phpfox::hasCallback($callback['module'], 'addItemNotification')) {
                        Phpfox::callback($callback['module'] . '.addItemNotification', [
                            'page_id'      => $callback['item_id'],
                            'item_perm'    => 'photo.view_browse_photos',
                            'item_type'    => 'photo',
                            'item_id'      => $photo['photo_id'],
                            'owner_id'     => $photo['user_id'],
                            'items_phrase' => $this->getLocalization()->translate('photos__l')
                        ]);
                    }
                    if (!empty($parentUserId) && $parentUserId != $this->getUser()->getId()) {
                        if (Phpfox::isModule('notification')) {
                            Phpfox::getService('notification.process')->add('photo_feed_profile', $photo['photo_id'], $parentUserId);
                        }
                        $link = Phpfox::getLib('url')->makeUrl($this->getUser()->getUserName(), ['feed' => $feedId]);
                        $ownerName = $this->getUser()->getFullName();
                        Phpfox::getLib('mail')->to($parentUserId)
                            ->subject([
                                'full_name_post_some_images_on_your_wall', ['full_name' => $ownerName]
                            ])
                            ->message([
                                'full_name_post_some_images_on_your_wall_message', ['full_name' => $ownerName, 'link' => $link]
                            ])
                            ->notification('comment.add_new_comment')
                            ->send();
                    }
                } else if (isset($feedId)) {
                    $this->database()->insert(Phpfox::getT('photo_feed'), [
                            'feed_id'    => $feedId,
                            'photo_id'   => $id,
                            'feed_table' => (empty($callback['table_prefix']) ? 'feed' : $callback['table_prefix'] . 'feed')
                        ]
                    );
                }
            }
        }
        $this->parametersBag->add('feed_id', isset($feedId) ? $feedId : true);
        return implode(',', $ids);
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
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    function delete($params)
    {

        $itemId = $this->resolver->resolveId($params);
        if ($itemId < 1) {
            return $this->notFoundError();
        }

        $item = $this->photoService->getPhotoItem($itemId);
        if (!isset($item['photo_id'])) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('photo.can_view_photos')) {
            $mResult = Phpfox::getService('photo.process')->delete($itemId);
            if ($mResult !== false) {
                return $this->success([], [], $this->getLocalization()->translate('photo_successfully_deleted'));
            }
        }
        return $this->permissionError();
    }

    private function getCategories()
    {
        return $this->categoryService->getForBrowse();
    }

    /**
     * @param string             $module
     * @param array              $item
     * @param PhotoResource|null $photo
     *
     * @return array
     */
    private function getAlbums($module, $item, $photo)
    {
        $albums = Phpfox::getService('photo.album')->getAll(Phpfox::getUserId(), !empty($module) ? $module : false, !empty($item) ? $item : false);
        $photoAlbum = $photo !== null ? $photo->getAlbum() : null;
        $photoAlbumId = $photoAlbum !== null && isset($photoAlbum['id']) ? $photoAlbum['id'] : 0;
        $user = $photo !== null ? $this->userService->getUser($photo->getAuthor()->getId()) : null;
        $fullName = isset($user['full_name']) ? $user['full_name'] : '';
        foreach ($albums as $key => $album) {
            if ($album['profile_id'] > 0) {
                if ($photoAlbumId != $album['album_id']) {
                    unset($albums[$key]);
                } else {
                    $albums[$key]['name'] = $this->getLocalization()->translate('user_profile_pictures', ['full_name' => $fullName]);
                }
            }
            if ($album['cover_id'] > 0) {
                if ($photoAlbumId != $album['album_id']) {
                    unset($albums[$key]);
                } else {
                    $albums[$key]['name'] = $this->getLocalization()->translate('user_cover_photo', ['full_name' => $fullName]);
                }
            }
            if ($album['timeline_id'] > 0) {
                if ($photoAlbumId != $album['album_id']) {
                    unset($albums[$key]);
                } else {
                    $albums[$key]['name'] = $this->getLocalization()->translate('user_timeline_photos', ['full_name' => $fullName]);
                }
            }
        }
        return array_values($albums);
    }

    /**
     * @param      $id
     * @param bool $returnResource
     *
     * @return null|\Phpfox_Database_Dba|static|object
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     */
    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->photoService->getPhotoItem($id);
        if (empty($item['photo_id'])) {
            return null;
        }
        if ($returnResource) {
            return PhotoResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        /** @var PhotoResource $resource */
        $resource = $this->populateResource(PhotoResource::class, $item);
        $this->setHyperlinks($resource);

        $shortFields = [];
        $view = $this->request()->get('view');
        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'image', 'statistic', 'id'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
                $shortFields[] = 'is_sponsor';
                $resource->is_sponsor = true;
            } else {
                $shortFields[] = 'is_featured';
                $resource->is_featured = true;
            }
        }

        if ($this->bIsFeed) {
            return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->lazyLoad(['user'])->displayShortFields()->toArray($shortFields);
        }
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
    }

    /**
     * @param      $item
     * @param null $context
     *
     * @return bool
     */
    function canView($item, $context = null)
    {
        return $item['can_view'];
    }

    function canEdit($item, $context = null)
    {
        return $item['canEdit'];
    }

    /**
     * Get for display on activity feed
     *
     * @param array $feed
     * @param array $item detail data from database
     *
     * @return array|bool
     */
    function getFeedDisplay($feed, $item)
    {
        $extraPhotoId = intval($item['extra_photo_id']);
        $sFeedTable = 'feed';
        $iFeedId = isset($feed['feed_id']) ? $feed['feed_id'] : 0;
        $cache = storage()->get('photo_parent_feed_' . $iFeedId);
        if ($cache) {
            $iFeedId = $cache->value;
        } elseif (!empty($item['photo_id'])) {
            $parentModule = db()->select('module_id, group_id')
                ->from(':photo')
                ->where([
                    'photo_id' => $item['photo_id']
                ])->executeRow();
            if (!empty($parentModule['module_id'])
                && !empty($parentModule['group_id'])
                && Phpfox::isModule($parentModule['module_id'])
                && Phpfox::hasCallback($parentModule['module_id'], 'getFeedDetails')) {
                $feedDetail = Phpfox::callback($parentModule['module_id'] . '.getFeedDetails', $parentModule['group_id']);
                if (!empty($feedDetail['table_prefix'])) {
                    $iFeedId = (int)db()->select('feed_id')
                        ->from(':' . $feedDetail['table_prefix'] . 'feed')
                        ->where([
                            'type_id' => 'photo',
                            'item_id' => $item['photo_id']
                        ])->executeField();
                }
            }
        }

        if (!empty($feed['parent_feed_id'])) {
            $iFeedId = $feed['parent_feed_id'];
        }
        $aPhotos = [];
        $limitPhoto = 4;
        $totalPhoto = 1;
        $aPhotoIte = db()->select('p.photo_id, p.module_id, p.group_id')
            ->from(':photo', 'p')
            ->where('p.photo_id = ' . (int)$feed['item_id'])
            ->execute('getSlaveRow');
        if (empty($aPhotoIte['photo_id'])) {
            return [];
        }
        if (isset($aPhotoIte['module_id']) && $aPhotoIte['module_id'] && !Phpfox::isModule($aPhotoIte['module_id'])) {
            return [];
        }

        (($sPlugin = \Phpfox_Plugin::get('photo.component_service_callback_getactivityfeed__get_item_before')) ? eval($sPlugin) : false);

        if ($extraPhotoId) {
            $totalPhoto = $this->database()->select('count(*)')
                ->from(Phpfox::getT('photo_feed'), 'pfeed')
                ->join(Phpfox::getT('photo'), 'p',
                    'p.photo_id = pfeed.photo_id' . (!empty($feed['module_id']) ? ' AND p.module_id = \'' . db()->escape($feed['module_id']) . '\'' : '') . ' AND pfeed.feed_table = \'' . $sFeedTable
                    . '\'')
                ->where('pfeed.feed_id = ' . (isset($iFeedId) ? (int)$iFeedId : 0) . ' AND p.album_id = ' . (int)$item['album_id'])
                ->executeField();

            $totalPhoto = intval($totalPhoto) + 1;

            db()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature, pi.width, pi.height')
                ->from(':photo', 'p')
                ->join(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
                ->where(['p.photo_id' => $item['photo_id']])
                ->union();
            db()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature, pi.width, pi.height')
                ->from(Phpfox::getT('photo_feed'), 'pfeed')
                ->join(Phpfox::getT('photo'), 'p',
                    'p.photo_id = pfeed.photo_id' . (!empty($item['module_id']) ? ' AND p.module_id = \'' . db()->escape($item['module_id']) . '\'' : '') . ' AND pfeed.feed_table = \'' . $sFeedTable . '\'')
                ->join(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
                ->where('pfeed.feed_id = ' . (isset($iFeedId) ? (int)$iFeedId : 0) . ' AND pfeed.feed_time = 0 AND p.album_id = ' . (int)$item['album_id'])
                ->union()
                ->unionFrom('main_photo');
            $aRows = db()->select('*')
                ->limit($limitPhoto)
                ->order('main_photo.photo_id DESC')
                ->group('main_photo.photo_id')
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
                    'resource_name' => 'photo',
                    'module_name'   => 'photo',
                    'href'          => "photo/{$aPhoto['photo_id']}",
                    'mature'        => intval($aPhoto['mature']),
                    'width'         => isset($aPhoto['width']) ? (int)$aPhoto['width'] : 0,
                    'height'        => isset($aPhoto['height']) ? (int)$aPhoto['height'] : 0,
                    'user'          => [
                        'id'            => intval($aPhoto['user_id']),
                        'resource_name' => 'user'
                    ],
                    'image'         => $photoUrl,
                ];
            }, $aRows);
        } else {
            if (($item['mature'] == 0 || (($item['mature'] == 1 || $item['mature'] == 2) && $this->getUser()->getId() && $this->getSetting()->getUserSetting('photo.photo_mature_age_limit') <= $this->getUser()->getAge())) || $item['user_id'] == Phpfox::getUserId()) {
                $itemUrl = UrlUtility::getPhotoUrl('photo.url_photo', $item['server_id'],
                    $item['destination'], '1024');
            } else {
                $itemUrl = Phpfox::getLib('image.helper')->display([
                    'theme'      => 'misc/mature.jpg',
                    'return_url' => true
                ]);
            }
            $photoInfo = $this->database()->select('width, height')->from(':photo_info')->where('photo_id =' . (int)$item['photo_id'])->execute('getRow');

            $aItemPhoto = [
                'id'            => intval($item['photo_id']),
                'module_name'   => 'photo',
                'resource_name' => 'photo',
                'mature'        => intval($item['mature']),
                'width'         => isset($photoInfo['width']) ? (int)$photoInfo['width'] : 0,
                'height'        => isset($photoInfo['height']) ? (int)$photoInfo['height'] : 0,
                'user'          => [
                    'id'            => $item['user_id'],
                    'resource_name' => 'user'
                ],
                'image'         => $itemUrl,
            ];

            array_unshift($aPhotos, $aItemPhoto);
        }

        return [
            'module_name'   => 'photo',
            'resource_name' => PhotoResource::RESOURCE_NAME,
            'total_photo'   => $totalPhoto,
            'module_id'     => $aPhotoIte['module_id'],
            'item_id'       => intval($aPhotoIte['group_id']),
            'feed_item_id'  => intval($feed['item_id']),
            'feed_id'       => intval($iFeedId),
            'remain_photo'  => $totalPhoto > $limitPhoto ? $totalPhoto - $limitPhoto : 0,
            'photos'        => $aPhotos
        ];
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new PhotoAccessControl($this->getSetting(), $this->getUser());

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
        $this->denyAccessUnlessGranted(PhotoAccessControl::VIEW);
        /** @var PhotoSearchForm $form */
        $form = $this->createForm(PhotoSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('photo')
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(PhotoResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            PhotoAccessControl::VIEW   => $this->createHyperMediaLink(PhotoAccessControl::VIEW, $resource,
                HyperLink::GET, 'photo/:id', ['id' => $resource->getId()]),
            PhotoAccessControl::EDIT   => $this->createHyperMediaLink(PhotoAccessControl::EDIT, $resource,
                HyperLink::GET, 'photo/form/:id', ['id' => $resource->getId()]),
            PhotoAccessControl::DELETE => $this->createHyperMediaLink(PhotoAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'photo/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(PhotoAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'photo']),
                'comments' => $this->createHyperMediaLink(PhotoAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'photo'])
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', PhotoResource::RESOURCE_NAME);
        $module = 'photo';
        return [
            [
                'path'      => 'photo/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'photo/category/:category(/*), photo/tag/:tag',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'photo/add',
                'routeName' => ROUTE_MODULE_ADD,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'photo(/*)',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $showAddMenu = false;
        $app = new MobileApp('photo', [
            'home_view'         => 'menu',
            'title'             => $l->translate('photos'),
            'main_resource'     => new PhotoResource([]),
            'category_resource' => new PhotoCategoryResource([]),
            'other_resources'   => [
                new PhotoAlbumResource([])
            ]
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $photoResourceName = (new PhotoResource([]))->getResourceName();
        $albumResourceName = (new PhotoAlbumResource([]))->getResourceName();
        $headerButtons[$photoResourceName] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ]
        ];
        $addMenu = [];

        if ($this->getAccessControl()->isGranted(PhotoAccessControl::ADD)) {
            $addMenu[] = [
                'icon'   => 'plus',
                'label'  => $l->translate('add_photos'),
                'value'  => Screen::ACTION_ADD,
                'params' => [
                    'resource_name' => $photoResourceName,
                    'module_name'   => 'photo'
                ]
            ];
        }
        if ($this->getSetting()->getUserSetting('photo.can_create_photo_album')) {
            $addMenu[] = [
                'icon'   => 'plus',
                'value'  => Screen::ACTION_ADD,
                'label'  => $l->translate('add_album'),
                'params' => [
                    'resource_name' => $albumResourceName,
                    'module_name'   => 'photo',
                ]
            ];
        }
        if (count($addMenu)) {
            $app->addSetting('home.add_menu', $addMenu);
            $showAddMenu = true;
        }
        if ($showAddMenu) {
            $headerButtons[$photoResourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_SHOW_APP_MENU,
                'params' => [
                    'module'    => 'photo',
                    'menu_name' => 'home.add_menu',
                ]
            ];
        }
        $headerButtons[$albumResourceName] = [
            [
                'icon'   => 'plus',
                'action' => Screen::ACTION_SHOW_APP_MENU,
                'params' => [
                    'module'    => 'photo',
                    'menu_name' => 'home.add_menu',
                ]
            ]
        ];
        $app->addSetting('home.header_buttons', $headerButtons);

        return $app;
    }

    public function getActions()
    {
        $l = $this->getLocalization();
        return [
            'photo-album/upload'       => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'photo',
                    'resource_name' => 'photo_album',
                    'formType'      => 'uploadPhotos',
                ]
            ],
            'photo/set_album_cover'    => [
                'url'             => 'mobile/photo/album-cover/:id',
                'method'          => 'put',
                'data'            => 'id',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
                'new_state'       => 'can_set_album_cover=false',
            ],
            'photo/set_profile_avatar' => [
                'url'             => 'mobile/photo/profile-avatar/:id',
                'method'          => 'put',
                'data'            => 'id',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'photo/set_profile_cover'  => [
                'url'             => 'mobile/photo/profile-cover/:id',
                'method'          => 'put',
                'data'            => 'id',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'photo/set_parent_cover'   => [
                'url'             => 'mobile/photo/parent-cover/:id',
                'method'          => 'put',
                'data'            => 'id',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
        ];
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var PhotoResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PhotoAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('photo_has_been_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = $this->resolver->resolveSingle($params, 'feature', 'int', ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PhotoAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('photo_successfully_featured') : $this->getLocalization()->translate('photo_successfully_un_featured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $isSponsorFeed = $this->resolver->resolveSingle($params, 'is_sponsor_feed', 'int', [], 0);
        $sponsor = $this->resolver->resolveSingle($params, 'sponsor', 'int', ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($isSponsorFeed) {
            //Support un-sponsor in feed
            $this->denyAccessUnlessGranted(PhotoAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('photo', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(PhotoAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(PhotoAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }

            if ($this->processService->sponsor($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('photo');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module' => 'photo',
                        'item_id' => $id,
                        'name' => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('photo', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('photo_successfully_sponsored') : $this->getLocalization()->translate('photo_successfully_un_sponsored'));
            }
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->photoService->getRandomSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * Get featured items
     *
     * @param $params
     *
     * @return mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        list(, $featuredItems) = $this->photoService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $apiVersion = isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile';
        $screenSetting = new ScreenSetting('photo', []);
        $resourcePhoto = PhotoResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourcePhoto, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourcePhoto, ScreenSetting::MODULE_LISTING);

        $resourceAlbum = PhotoAlbumResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceAlbum, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceAlbum, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceAlbum, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component'  => 'item_header',
                'transition' => 'transparent'
            ],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'             => 'photo_album_list',
                'contentContainerStyle' => [],
                'headerComponent'       => ['component' => 'photo_album_info']
            ],
            'screen_title'                 => $l->translate('photos') . ' > ' . $l->translate('photo_album') . ' - ' . $l->translate('mobile_detail_page')
        ]);

        $screenSetting->addBlock($resourcePhoto, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_photos'),
                'resource_name' => $resourcePhoto,
                'module_name'   => 'photo',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_photos'),
                'resource_name' => $resourcePhoto,
                'module_name'   => 'photo',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);
        $embedComponents = [
            [
                'component'       => 'item_image',
                'imageAutoHeight' => true
            ],
            'item_title',
            'item_author',
            'item_stats',
            'item_user_tags',
            'item_like_phrase',
            ['component' => 'item_pending', 'message' => 'photo_is_pending_approval'],
            'item_html_content',
            'item_category',
            'item_tags'
        ];
        if ($apiVersion != 'mobile' && version_compare($apiVersion, 'v1.7.3', '>=')) {
            array_splice($embedComponents, 8, 0, ['item_album']);
        }
        $screenSetting->addSetting($resourcePhoto, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'item_header',
            ],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => $embedComponents,
            ],
            'no_ads'                       => true
        ]);
        $screenSetting->addBlock($resourceAlbum, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_albums'),
                'resource_name' => $resourceAlbum,
                'module_name'   => 'photo',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_albums'),
                'resource_name' => $resourceAlbum,
                'module_name'   => 'photo',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'photo.index',
            ScreenSetting::MODULE_LISTING => 'photo.index',
            ScreenSetting::MODULE_DETAIL  => 'photo.view'
        ];
    }

    public function setAlbumCover($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var PhotoResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PhotoAccessControl::SET_ALBUM_COVER, $item);

        $this->database()->update(Phpfox::getT('photo'), ['is_cover' => '0'], "album_id = $item->album_id");
        $this->database()->update(Phpfox::getT('photo'), ['is_cover' => '1'], "album_id = $item->album_id AND photo_id = $id");

        return $this->success([], [], $this->getLocalization()->translate('photo_set_as_album_cover_successfully'));
    }

    public function setProfileCover($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var PhotoResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PhotoAccessControl::SET_PROFILE_COVER, $item);
        if ($this->processService->makeCoverPicture($id)) {
            return $this->success([], [], $this->getLocalization()->translate('cover_photo_successfully_updated'));
        }
        return $this->error();
    }

    public function setProfileAvatar($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var PhotoResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PhotoAccessControl::SET_PROFILE_AVATAR, $item);
        if ($this->processService->makeProfilePicture($id)) {
            return $this->success([], [], $this->getLocalization()->translate('profile_photo_successfully_updated'));
        }
        return $this->error();
    }

    public function setParentCover($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var PhotoResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PhotoAccessControl::SET_PARENT_COVER, $item);
        if (Phpfox::getService($item->module_id . '.process')->setCoverPhoto($item->group_id, $id)) {
            return $this->success([], [], $this->getLocalization()->translate('cover_photo_successfully_updated'));
        }
        return $this->error();
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
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
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
                $this->denyAccessUnlessGranted(PhotoAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->approve($id)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_pending' => false];
                $sMessage = $this->getLocalization()->translate('photo_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(PhotoAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('photo_s_successfully_featured') : $this->getLocalization()->translate('photo_s_successfully_unfeatured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(PhotoAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('photo_s_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}
