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
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Video\VideoForm;
use Apps\Core_MobileApi\Api\Form\Video\VideoSearchForm;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\VideoCategoryResource;
use Apps\Core_MobileApi\Api\Resource\VideoResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Video\VideoAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\PHPfox_Videos\Service\Process;
use Apps\PHPfox_Videos\Service\Video;
use Exception;
use Phpfox;

class VideoApi extends AbstractResourceApi implements MobileAppSettingInterface, ActivityFeedInterface
{
    /**
     * @var Video
     */
    private $videoService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    private $categoryService;

    public function __construct()
    {
        parent::__construct();
        $this->videoService = Phpfox::getService('v.video');
        $this->processService = Phpfox::getService('v.process');
        $this->categoryService = Phpfox::getService('v.category');
        $this->userService = Phpfox::getService('user');
    }

    public function __naming()
    {
        return [
            'video/validate'    => [
                'get' => 'validateUrl',
            ],
            'video/search-form' => [
                'get' => 'searchForm'
            ],
        ];
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'profile_id', 'limit', 'page', 'when', 'module_id', 'item_id', 'category', 'is_uploaded'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'friend', 'pending', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('category', 'int')
            ->setAllowedTypes('item_id', 'int')
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
        if (!Phpfox::getUserParam('pf_video_view')) {
            return $this->permissionError();
        }
        $sort = $params['sort'];
        $view = $params['view'];

        if (in_array($view, ['feature', 'sponsor'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }

        $parentModule = null;
        if (!empty($params['module_id']) && !empty($params['item_id'])) {
            $parentModule = [
                'module_id' => $params['module_id'],
                'item_id'   => $params['item_id'],
            ];
        }

        $isProfile = $params['profile_id'];
        $user = [];
        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
        }

        $browseParams = [
            'module_id' => 'video',
            'alias'     => 'video',
            'field'     => 'video_id',
            'table'     => Phpfox::getT('video'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'v.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'video'
        ]);

        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'video.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'video.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'video.total_comment DESC';
                break;
            default:
                $sort = 'video.time_stamp DESC';
                break;
        }

        $view = trim($view, '/');
        switch ($view) {
            case 'my':
                if (Phpfox::isUser()) {
                    $condition = ' AND video.user_id = ' . Phpfox::getUserId();
                    $modules = ['user'];
                    if (!Phpfox::isAppActive('PHPfox_Groups')) {
                        $modules[] = 'groups';
                    }
                    if (!Phpfox::isAppActive('Core_Pages')) {
                        $modules[] = 'pages';
                    }
                    $condition .= ' AND video.module_id NOT IN ("' . implode('","', $modules) . '")';
                    $this->search()->setCondition($condition);
                } else {
                    return $this->permissionError();
                }
                break;
            case 'pending':
                if (Phpfox::isUser() && Phpfox::getUserParam('pf_video_approve')) {
                    $condition = ' AND video.view_id = 2';
                    $modules = [];
                    if (!Phpfox::isAppActive('PHPfox_Groups')) {
                        $modules[] = 'groups';
                    }
                    if (!Phpfox::isAppActive('Core_Pages')) {
                        $modules[] = 'pages';
                    }
                    $condition .= ' AND video.module_id NOT IN ("' . implode('","', $modules) . '")';
                    $this->search()->setCondition($condition);
                } else {
                    return $this->permissionError();
                }
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition(' AND video.in_process = 0 AND video.view_id ' . ($user['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND video.item_id = 0 AND video.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ') AND video.user_id = ' . (int)$user['user_id']);
                } else {
                    $condition = ' AND video.in_process = 0 AND video.view_id = 0';
                    if (defined('PHPFOX_IS_PAGES_VIEW') || $parentModule) {
                        $condition .= ' AND video.module_id = \'' . Phpfox::getLib('database')->escape($parentModule['module_id']) . '\' AND video.item_id = ' . (int)$parentModule['item_id'];
                        if (!Phpfox::getUserParam('privacy.can_view_all_items')) {
                            $condition .= ' AND video.privacy IN(%PRIVACY%)';
                        }
                    } else {
                        if (Phpfox::getParam('v.pf_video_display_video_created_in_group') || Phpfox::getParam('v.pf_video_display_video_created_in_page')) {
                            $modules = ['video'];
                            if (Phpfox::getParam('v.pf_video_display_video_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                                $modules[] = 'groups';
                            }
                            if (Phpfox::getParam('v.pf_video_display_video_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                                $modules[] = 'pages';
                            }
                            $condition .= ' AND video.module_id IN ("' . implode('","', $modules) . '")';
                        } else {
                            $condition .= ' AND video.item_id = 0';
                        }
                        if (!Phpfox::getUserParam('privacy.can_view_all_items')) {
                            $condition .= ' AND video.privacy IN(%PRIVACY%)';
                        }
                    }
                    $this->search()->setCondition($condition);
                }
                break;
        }
        //get uploaded video only
        if (!empty($params['is_uploaded'])) {
            $this->search()->setCondition('AND video.is_stream = 0');
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND video.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }
        //category
        if ($params['category']) {
            $category = Phpfox::getService('v.category')->getCategory($params['category']);
            if (!$category || (!$category['is_active'] && !Phpfox::isAdmin())) {
                return $this->permissionError();
            }
            $childIds = Phpfox::getService('v.category')->getChildIds($params['category']);
            $categoryIds = $params['category'];
            if ($childIds) {
                $categoryIds .= ',' . $childIds;
            }
            $this->search()->setCondition('AND vcd.category_id IN (' . $categoryIds . ')');
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);

        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $items = $this->browse()->getRows();

        $this->processRows($items);
        return $this->success($items);
    }

    function findOne($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }

        $item = $this->videoService->getVideo($params['id']);
        if (!$item) {
            return $this->notFoundError();
        }

        if (isset($item['module_id']) && !empty($item['item_id']) && !Phpfox::isModule($item['module_id'])) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(VideoAccessControl::VIEW, VideoResource::populate($item));

        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$item['video_is_viewed']) {
                $updateCounter = true;
                Phpfox::getService('track.process')->add('v', $item['video_id']);
            } else {
                if (!Phpfox::getParam('track.unique_viewers_counter')) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('v', $item['video_id']);
                } else {
                    Phpfox::getService('track.process')->update('v', $item['video_id']);
                }
            }
        } else {
            $updateCounter = true;
        }

        if ($updateCounter) {
            $this->database()->updateCounter('video', 'total_view', 'video_id', $item['video_id']);
        }

        /** @var VideoResource $resource */
        $resource = $this->populateResource(VideoResource::class, $item);
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
        /** @var VideoForm $form */
        $form = $this->createForm(VideoForm::class, [
            'title'  => 'share_a_video',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('video')
        ]);
        $form->setCategories($this->getCategories());
        /** @var VideoResource $video */
        $video = $this->loadResourceById($editId, true);
        if ($editId && empty($video)) {
            return $this->notFoundError();
        }

        if ($video) {
            $this->denyAccessUnlessGranted(VideoAccessControl::EDIT, $video);
            $form->setTitle('editing_video')
                ->setAction(UrlUtility::makeApiUrl('video/:id', $editId))
                ->setMethod('PUT');
            $form->setEditing(true);
            $form->assignValues($video);
        } else {
            $this->denyAccessUnlessGranted(VideoAccessControl::ADD);
        }

        return $this->success($form->getFormStructure());
    }

    private function getCategories()
    {
        return $this->categoryService->getForUsers(0, 1, 1, 0);
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(VideoAccessControl::ADD);
        /** @var VideoForm $form */
        $form = $this->createForm(VideoForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                if ($id !== true) {
                    if ($this->getSetting()->getUserSetting('v.pf_video_approve_before_publicly')) {
                        return $this->success([
                            'resource_name' => VideoResource::populate([])->getResourceName(),
                        ], [], $this->getLocalization()->translate('video_is_pending_approval'));
                    }
                    return $this->success([
                        'id'            => $id,
                        'resource_name' => VideoResource::populate([])->getResourceName(),
                    ], [], $this->getLocalization()->translate('video_successfully_added'));
                } else {
                    return $this->success([
                        'resource_name' => VideoResource::populate([])->getResourceName(),
                    ], [], $this->getLocalization()->translate('your_video_has_successfully_been_saved_and_will_be_published_when_we_are_done_processing_it'));
                }
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    /**
     * Create Video post
     *
     * @param $values
     * @param $checkPerm bool
     *
     * @return mixed
     */
    public function processCreate($values, $checkPerm = false)
    {
        if ($checkPerm) {
            $this->denyAccessUnlessGranted(VideoAccessControl::ADD);
        }
        if (!empty($values['file'])) {
            //Upload file
            $iMethodUpload = $this->getSetting()->getAppSetting('v.pf_video_method_upload');
            if (empty($values['file']['temp_file'])) {
                return $this->notFoundError($this->getLocalization()->translate('we_could_not_find_a_video_there_please_try_again'));
            }
            $videoId = $values['file']['temp_file'];
            $encoding = storage()->get('pf_video_' . $videoId);
            if (isset($_REQUEST['custom_pages_post_as_page']) && (int)$_REQUEST['custom_pages_post_as_page'] > 0) {
                $iUserId = Phpfox::getPageUserId();
            } else {
                $iUserId = $encoding->value->user_id;
            }
            if (!empty($encoding->value->encoded)) {
                $values = array_merge($values, [
                    'is_stream'       => 0,
                    'user_id'         => $iUserId,
                    'server_id'       => $encoding->value->server_id,
                    'path'            => $encoding->value->video_path,
                    'ext'             => $encoding->value->ext,
                    'default_image'   => isset($encoding->value->default_image) ? $encoding->value->default_image : '',
                    'image_path'      => isset($encoding->value->image_path) ? $encoding->value->image_path : '',
                    'image_server_id' => $encoding->value->image_server_id,
                    'duration'        => $encoding->value->duration,
                    'resolution_x'    => isset($encoding->value->resolution_x) ? $encoding->value->resolution_x : null,
                    'resolution_y'    => isset($encoding->value->resolution_y) ? $encoding->value->resolution_y : null,
                    'video_size'      => $encoding->value->video_size,
                    'photo_size'      => $encoding->value->photo_size,
                    'asset_id'        => isset($encoding->value->asset_id) ? $encoding->value->asset_id : null
                ]);
                $iId = Phpfox::getService('v.process')->addVideo($values);

                if (Phpfox::isModule('notification')) {
                    Phpfox::getService('notification.process')->add('v_ready', $iId,
                        $encoding->value->user_id, $encoding->value->user_id, true);
                }

                \Phpfox_Mail::instance()->to($encoding->value->user_id)
                    ->subject(['video_is_ready'])
                    ->message([
                            'your_video_is_ready_url',
                            ['url' => url('/video/play/' . $iId)]
                        ]
                    )
                    ->send();

                $file = PHPFOX_DIR_FILE . 'static/' . $encoding->value->id . '.' . $encoding->value->ext;
                if (file_exists($file)) {
                    @unlink($file);
                }

                storage()->del('pf_video_' . $videoId);
                return $iId;

            } else {
                if ($this->getSetting()->getAppSetting('v.pf_video_allow_compile_on_storage_system') && version_compare(Phpfox::getCurrentVersion(), '4.8.0', '>=')) {
                    storage()->update('pf_video_' . $videoId, [
                        'encoding_id'      => '',
                        'is_ready'         => 1,
                        'privacy'          => (isset($values['privacy']) ? (int)$values['privacy'] : 0),
                        'privacy_list'     => json_encode(isset($values['privacy_list']) ? $values['privacy_list'] : []),
                        'callback_module'  => (isset($values['module_id']) ? $values['module_id'] : ''),
                        'callback_item_id' => (isset($values['item_id']) ? (int)$values['item_id'] : 0),
                        'parent_user_id'   => (isset($values['parent_user_id']) ? $values['parent_user_id'] : 0),
                        'title'            => $values['title'],
                        'category'         => json_encode(isset($values['categories']) ? $values['categories'] : []),
                        'text'             => isset($values['text']) ? $values['text'] : '',
                        'status_info'      => isset($values['status_info']) ? text()->clean($values['status_info']) : '',
                        'updated_info'     => 1,
                        'location_name'    => (!empty($values['location_name'])) ? $values['location_name'] : null,
                        'location_latlng'  => (!empty($values['location_latlng'])) ? $values['location_latlng'] : null,
                        'feed_values'      => json_encode($values),
                        'tagged_friends'   => isset($values['tagged_friends']) ? $values['tagged_friends'] : null,
                        'user_id'          => $iUserId
                    ]);
                } elseif ($iMethodUpload == 0 && $this->getSetting()->getAppSetting('v.pf_video_ffmpeg_path')) {
                    $iJobId = \Phpfox_Queue::instance()->addJob('videos_ffmpeg_encode', []);
                    storage()->set('pf_video_' . $iJobId, [
                        'encoding_id'      => $iJobId,
                        'id'               => $encoding->value->id,
                        'user_id'          => $iUserId,
                        'view_id'          => $encoding->value->view_id,
                        'path'             => $encoding->value->path,
                        'ext'              => $encoding->value->ext,
                        'privacy'          => (isset($values['privacy']) ? (int)$values['privacy'] : 0),
                        'privacy_list'     => json_encode(isset($values['privacy_list']) ? $values['privacy_list'] : []),
                        'callback_module'  => (isset($values['module_id']) ? $values['module_id'] : ''),
                        'callback_item_id' => (isset($values['item_id']) ? (int)$values['item_id'] : 0),
                        'parent_user_id'   => (isset($values['parent_user_id']) ? $values['parent_user_id'] : 0),
                        'title'            => $values['title'],
                        'category'         => json_encode(isset($values['categories']) ? $values['categories'] : []),
                        'text'             => isset($values['text']) ? $values['text'] : '',
                        'status_info'      => isset($values['status_info']) ? text()->clean($values['status_info']) : '',
                        'feed_values'      => json_encode($values),
                        'location_name'    => (!empty($values['location_name'])) ? $values['location_name'] : null,
                        'location_latlng'  => (!empty($values['location_latlng'])) ? $values['location_latlng'] : null,
                        'tagged_friends'   => isset($values['tagged_friends']) ? $values['tagged_friends'] : null,
                    ]);
                    storage()->del('pf_video_' . $videoId);
                } else {
                    storage()->update('pf_video_' . $videoId, [
                        'privacy'          => (isset($values['privacy']) ? (int)$values['privacy'] : 0),
                        'privacy_list'     => json_encode(isset($values['privacy_list']) ? $values['privacy_list'] : []),
                        'callback_module'  => (isset($values['module_id']) ? $values['module_id'] : ''),
                        'callback_item_id' => (isset($values['item_id']) ? (int)$values['item_id'] : 0),
                        'parent_user_id'   => (isset($values['parent_user_id']) ? $values['parent_user_id'] : 0),
                        'title'            => $values['title'],
                        'category'         => json_encode(isset($values['categories']) ? $values['categories'] : []),
                        'text'             => isset($values['text']) ? $values['text'] : '',
                        'status_info'      => isset($values['status_info']) ? text()->clean($values['status_info']) : '',
                        'updated_info'     => 1,
                        'location_name'    => (!empty($values['location_name'])) ? $values['location_name'] : null,
                        'location_latlng'  => (!empty($values['location_latlng'])) ? $values['location_latlng'] : null,
                        'feed_values'      => json_encode($values),
                        'tagged_friends'   => isset($values['tagged_friends']) ? $values['tagged_friends'] : null,
                        'user_id'          => $iUserId
                    ]);
                }
                return true;
            }
        } else if (!empty($values['url']) && $parsed = $this->processValidateUrl($values['url'])) {

            if (!empty($parsed['text']) && empty($values['text'])) {
                $values['text'] = $parsed['text'];
            }
            if (isset($parsed['raw_duration'])) {
                $values['duration'] = $parsed['raw_duration'];
            }
            $values['embed_code'] = $parsed['embed_code'];
            $values['default_image'] = $parsed['default_image'];
            if (isset($values['module_id'])) {
                $values['callback_module'] = $values['module_id'];
            }
            if (isset($values['item_id'])) {
                $values['callback_item_id'] = $values['item_id'];
            }
            if (isset($values['categories'])) {
                $values['category'] = $values['categories'];
            }
            $values['status_info'] = isset($values['status_info']) ? text()->clean($values['status_info']) : '';
            if (!isset($values['tagged_friends'])) {
                $values['tagged_friends'] = [];
            }
            return Phpfox::getService('v.process')->addVideo($values);
        }
        return false;
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var VideoForm $form */
        $form = $this->createForm(VideoForm::class);
        $form->setEditing(true);
        $video = $this->loadResourceById($id, true);
        if (empty($video)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(VideoAccessControl::EDIT, $video);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success === true && $this->isPassed()) {
                return $this->success([
                    'id'            => (int)$id,
                    'resource_name' => VideoResource::populate([])->getResourceName(),
                ], [], $this->getLocalization()->translate('video_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        if (!empty($values['categories'])) {
            $values['category'] = $values['categories'];
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
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $itemId = $params['id'];
        $item = $this->loadResourceById($itemId);
        if (!$itemId || !$item) {
            return $this->notFoundError();
        }

        if (Phpfox::getUserParam('pf_video_view') && $this->processService->delete($itemId)) {
            return $this->success([], [], $this->getLocalization()->translate('video_successfully_deleted'));
        }

        return $this->permissionError();
    }

    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->videoService->getVideo($id);
        if (empty($item['video_id'])) {
            return null;
        }
        if ($returnResource) {
            return VideoResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        /** @var VideoResource $resource */
        $resource = $this->populateResource(VideoResource::class, $item);
        $this->setHyperlinks($resource);

        $shortFields = [];
        $view = $this->request()->get('view');
        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'image', 'statistic', 'user', 'duration', 'embed_code', 'id', 'is_sponsor', 'is_featured'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
    }

    function validateUrl($params)
    {
        $params = $this->resolver->setRequired(['url'])->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $sUrl = trim($params['url']);
        $result = $this->processValidateUrl($sUrl, false);
        if ($result == false) {
            return $this->error($this->getLocalization()->translate('we_could_not_find_a_video_there_please_check_the_url_and_try_again'));
        } else {
            return $this->success($result);
        }
    }

    private function processValidateUrl($sUrl, $bThrow = true)
    {
        if (substr($sUrl, 0, 7) != 'http://' && substr($sUrl, 0, 8) != 'https://') {
            return $this->error($this->getLocalization()->translate('please_provide_a_valid_url'));
        }
        if (preg_match('/dailymotion/', $sUrl) && substr($sUrl, 0, 8) == 'https://') {
            $sUrl = str_replace('https', 'http', $sUrl);
        }
        try {
            $parsed = Phpfox::getService('link')->getLink($sUrl);
        } catch (Exception $e) {
            return $bThrow ? $this->error($e->getMessage()) : false;
        }
        if (empty($parsed['embed_code'])) {
            return $this->error($this->getLocalization()->translate('unable_to_load_a_video_to_embed'));
        }
        $embed_code = str_replace('http://player.vimeo.com/', 'https://player.vimeo.com/', $parsed['embed_code']);
        $description = str_replace("<br />", "\r\n", $parsed['description']);
        return [
            'url'           => $sUrl,
            'title'         => $parsed['title'],
            'text'          => $description,
            'embed_code'    => $embed_code,
            'default_image' => (strpos($parsed['default_image'], 'http') === false ? 'https:' : '') . $this->getParse()->cleanOutput($parsed['default_image']),
            'duration'      => Phpfox::getService('v.video')->getDuration($parsed['duration']),
            'raw_duration'  => $parsed['duration']
        ];
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new VideoAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get("module_id");
        $itemId = $this->request()->get("item_id");

        if ($moduleId && $moduleId != 'video') {
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
        $this->denyAccessUnlessGranted(VideoAccessControl::VIEW);
        /** @var VideoSearchForm $form */
        $form = $this->createForm(VideoSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('video')
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(VideoResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            VideoAccessControl::VIEW   => $this->createHyperMediaLink(VideoAccessControl::VIEW, $resource,
                HyperLink::GET, 'video/:id', ['id' => $resource->getId()]),
            VideoAccessControl::EDIT   => $this->createHyperMediaLink(VideoAccessControl::EDIT, $resource,
                HyperLink::GET, 'video/form/:id', ['id' => $resource->getId()]),
            VideoAccessControl::DELETE => $this->createHyperMediaLink(VideoAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'video/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(VideoAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'v']),
                'comments' => $this->createHyperMediaLink(VideoAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'v']),
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', VideoResource::RESOURCE_NAME);
        $module = 'video';
        return [
            [
                'path'      => 'video/play/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'video/share',
                'routeName' => ROUTE_MODULE_ADD,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'video(/*)',
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
        $app = new MobileApp('video', [
            'title'             => $l->translate('videos'),
            'home_view'         => 'menu',
            'main_resource'     => new VideoResource([]),
            'category_resource' => new VideoCategoryResource([]),
            'other_resource'    => [],
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $resourceName = (new VideoResource([]))->getResourceName();
        $headerButtons[$resourceName] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ],
        ];
        if ($this->getAccessControl()->isGranted(VideoAccessControl::ADD)) {
            $headerButtons[$resourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => (new VideoResource([]))->getResourceName()]
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    public function getFeedDisplay($feed, $item)
    {
        if (empty($item) && !$item = $this->loadResourceById($feed['item_id'])) {
            return null;
        }
        if (empty($item['duration']) || empty($item['file_ext'])) {
            $extra = $this->database()->select('v.duration, v.file_ext')
                ->from(':video', 'v')
                ->where('v.video_id = ' . (int)$item['video_id'])
                ->executeRow();
            $item = !empty($extra) ? array_merge($item, $extra) : $item;
        }
        $resource = $this->populateResource(VideoResource::class, $item);

        return $resource->getFeedDisplay();
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var VideoResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(VideoAccessControl::APPROVE, $item);

        $result = $this->processService->approve($id, false);
        if ($result && $this->isPassed()) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('video_has_been_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(VideoAccessControl::FEATURE, $item);

        $result = $this->processService->feature($id, $feature);
        if ($result && $this->isPassed()) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('video_successfully_featured') : $this->getLocalization()->translate('video_successfully_unfeatured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $isSponsorFeed = $this->resolver->resolveSingle($params, 'is_sponsor_feed', null, [], 0);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($isSponsorFeed) {
            //Support un-sponsor in feed
            $this->denyAccessUnlessGranted(VideoAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('v', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(VideoAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(VideoAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }

            if ($this->processService->sponsor($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('video');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module'  => 'v',
                        'item_id' => $id,
                        'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('v', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('video_successfully_sponsored') : $this->getLocalization()->translate('video_successfully_un_sponsored'));
            }
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    public function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->videoService->getSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    public function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $featuredItems = $this->videoService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('video', []);
        $resourceName = VideoResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_TOP    => ['component' => 'item_video'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    'item_title',
                    'item_author',
                    'item_stats',
                    'item_like_phrase',
                    ['component' => 'item_pending', 'message' => 'video_is_pending_approval'],
                    'item_html_content',
                    'item_category'
                ],
            ],
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header'],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            'screen_title'                 => $l->translate('video') . ' > ' . $l->translate('video') . ' - ' . $l->translate('mobile_detail_page')
        ]);

        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_videos'),
                'resource_name' => $resourceName,
                'module_name'   => 'video',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_videos'),
                'resource_name' => $resourceName,
                'module_name'   => 'video',
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
            ScreenSetting::MODULE_HOME    => 'v.index',
            ScreenSetting::MODULE_LISTING => 'v.index',
            ScreenSetting::MODULE_DETAIL  => 'v.play'
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
                $this->denyAccessUnlessGranted(VideoAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->approve($id, false)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_pending' => false];
                $sMessage = $this->getLocalization()->translate('video_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(VideoAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('video_s_successfully_featured') : $this->getLocalization()->translate('video_s_successfully_unfeatured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(VideoAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('video_s_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}