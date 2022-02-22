<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Blogs\Service\Blog;
use Apps\Core_Blogs\Service\Category\Category;
use Apps\Core_Blogs\Service\Process;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\Blog\BlogForm;
use Apps\Core_MobileApi\Api\Form\Blog\BlogSearchForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\AttachmentResource;
use Apps\Core_MobileApi\Api\Resource\BlogCategoryResource;
use Apps\Core_MobileApi\Api\Resource\BlogResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\TagResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Blog\BlogAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class BlogApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    /**
     * @var Blog
     */
    private $blogService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var Category
     */
    private $categoryService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    private $adProcessService = null;

    /**
     * BlogApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->blogService = Phpfox::getService("blog");
        $this->categoryService = Phpfox::getService('blog.category');
        $this->processService = Phpfox::getService('blog.process');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    /**
     * @return array
     */
    public function __naming()
    {
        return [
            'blog/search-form' => [
                'get' => 'searchForm'
            ],
            'blog/publish/:id' => [
                'put' => 'publish'
            ]
        ];
    }

    /**
     * Get list blog
     *
     * @param array $params
     *
     * @return array|bool|mixed
     * @throws ValidationErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    function findAll($params = [])
    {
        // Resolve and validate parameter from the requests
        $params = $this->resolver
            ->setDefined([
                'view', 'module_id', 'item_id', 'category', 'q', 'sort', 'when', 'profile_id', 'limit', 'page', 'tag'
            ])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->setAllowedValues("sort", ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'spam', 'pending', 'draft', 'friend', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int', ['min' => 1])
            ->setAllowedTypes('profile_id', 'int', ['min' => 1])
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->setAllowedTypes('category', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        // Security checking
        $this->denyAccessUnlessGranted(BlogAccessControl::VIEW, null, [
            'view' => $params['view']
        ]);


        $sort = $params['sort'];
        $view = $params['view'];

        if (in_array($view, ['sponsor', 'feature'])) {
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

        $user = null;
        $isProfile = $params['profile_id'];
        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError("User profile not found");
            }
            $this->search()->setCondition('AND blog.user_id = ' . $user['user_id']);
        }

        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'blog',
            'alias'     => 'blog',
            'field'     => 'blog_id',
            'table'     => Phpfox::getT('blog'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'blog.browse',
        ];

        $this->search()->setSearchTool([
            'table_alias' => 'blog'
        ]);

        switch ($view) {
            case 'spam':
                $this->search()->setCondition('AND blog.is_approved = 9');
                break;
            case 'pending':
                $this->search()->setCondition('AND blog.is_approved = 0');
                break;
            case 'my':
                $this->search()->setCondition('AND blog.user_id = ' . $this->getUser()->getId());
                break;
            case 'draft':
                $this->search()->setCondition("AND blog.user_id = " . (int)$this->getUser()->getId() . " AND blog.is_approved IN(" . (isset($user['user_id']) && $user['user_id'] == $this->getUser()->getId() ? '0,1' : '1')
                    . ") AND blog.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') || !isset($user['user_id']) ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user))
                    . ") AND blog.post_status = 2");
                break;
            default:
                $this->search()->setCondition("AND blog.is_approved = 1 AND blog.post_status = 1" . (Phpfox::getUserParam('privacy.can_comment_on_all_items') ? ""
                        : " AND blog.privacy IN(%PRIVACY%)"));
                break;
        }


        if (!empty($params['category'])) {
            if ($aBlogCategory = $this->categoryService->getCategory($params['category'])) {
                $this->search()->setCondition('AND blog_category.category_id = ' . (int)$params['category'] . ' AND blog_category.user_id = 0');
            } else {
                return $this->notFoundError($this->getLocalization()->translate('category_not_found'));
            }
        }

        if (isset($parentModule) && isset($parentModule['module_id'])) {
            $this->search()->setCondition('AND blog.module_id = \'' . $parentModule['module_id'] . '\' AND blog.item_id = ' . (int)$parentModule['item_id']);
        } else {
            if ($parentModule === null) {
                if (($view == 'pending' && Phpfox::getUserParam('blog.can_approve_blogs')) || in_array($view,
                        ['draft', 'my'])) {
                    $aModules = [];
                    if (!Phpfox::isAppActive('PHPfox_Groups')) {
                        $aModules[] = 'groups';
                    }
                    if (!Phpfox::isAppActive('Core_Pages')) {
                        $aModules[] = 'pages';
                    }

                    if (count($aModules)) {
                        $this->search()->setCondition('AND blog.module_id NOT IN ("' . implode('","',
                                $aModules) . '")');
                    }
                } else {
                    $aModules = ['blog'];
                    // Apply setting show blog of pages / groups into All Blog
                    if (!defined('PHPFOX_IS_USER_PROFILE')) {
                        if (Phpfox::getParam('blog.display_blog_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                            $aModules[] = 'groups';
                        }

                        if (Phpfox::getParam('blog.display_blog_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                            $aModules[] = 'pages';
                        }
                    }

                    $this->search()->setCondition('AND blog.module_id IN ("' . implode('","', $aModules) . '")');
                }
            }
        }

        // search query
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND blog.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }

        // Search By tag
        if ($params['tag']) {
            if (Phpfox::isModule('tag') && $aTag = Phpfox::getService('tag')->getTagInfo('blog', $params['tag'])) {
                $this->search()->setCondition('AND tag.tag_text = \'' . Phpfox::getLib('database')->escape($aTag['tag_text']) . '\'');
            } else {
                $this->search()->setCondition('AND 0');
            }
        }


        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'blog.total_view DESC, blog.time_stamp DESC';
                break;
            case 'most_liked':
                $sort = 'blog.total_like DESC, blog.time_stamp DESC';
                break;
            case 'most_discussed':
                $sort = 'blog.total_comment DESC, blog.time_stamp DESC';
                break;
            default:
                $sort = 'blog.time_stamp DESC, blog.time_stamp DESC';
                break;
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);
        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $aItems = $this->browse()->getRows();
        if ($aItems) {
            $this->processRows($aItems);
        }

        return $this->success($aItems);
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);

        $item = $this->blogService->getBlog($id);

        if ((!isset($item['blog_id'])) || (isset($item['module_id']) && Phpfox::isModule($item['module_id']) != true)
            || ($item['post_status'] == 2 && Phpfox::getUserId() != $item['user_id'])) {
            return $this->notFoundError();
        }
        if (!$this->getAccessControl()->isGrantedSetting('blog.can_approve_blogs')) {
            if ($item['is_approved'] != '1' && $item['user_id'] != Phpfox::getUserId()) {
                return $this->notFoundError();
            }
        }

        $resource = $this->processOne($item)->lazyLoad(["user"]);

        $this->denyAccessUnlessGranted(BlogAccessControl::VIEW, $resource);

        // Increment the view counter
        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!Phpfox::getUserBy('is_invisible')) {
                if (!$item['is_viewed']) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('blog', $item['blog_id']);
                } else {
                    if (!Phpfox::getParam('track.unique_viewers_counter')) {
                        $updateCounter = true;
                        Phpfox::getService('track.process')->add('blog', $item['blog_id']);
                    } else {
                        Phpfox::getService('track.process')->update('blog', $item['blog_id']);
                    }
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->processService->updateView($item['blog_id']);
        }

        return $this->success($resource->loadFeedParam()->toArray());
    }

    /**
     * Process Detail response
     *
     * @param $item
     *
     * @return BlogResource|array
     * @throws \Exception
     */
    public function processOne($item)
    {
        $resource = $this->processRow($item);
        $resource->categories = $this->getCategoryApi()
            ->getByBlogId($resource->id);
        $resource->tags = $this->getTagApi()
            ->getTagsBy(BlogResource::TAG_CATEGORY, $resource->id);
        if (isset($item['total_attachment']) && $item['total_attachment'] > 0) {
            $resource->attachments = $this->getAttachmentApi()
                ->getAttachmentsBy($resource->getId(), 'blog');
        }

        $this->setSelfHyperMediaLinks($resource);
        $this->setLinksHyperMediaLinks($resource);

        return $resource;
    }

    /**
     * Process list of blog
     *
     * @param array $aRows
     *
     * @throws \Exception
     */
    public function processRows(&$aRows)
    {
        /** @var TagApi $tagReducer */
        $tagReducer = $this->getTagApi();
        $tagCond = [
            'category_id' => 'blog',
        ];

        /** @var BlogCategoryApi $categoryReducer */
        $categoryReducer = $this->getCategoryApi();
        $categoryCond = [];

        foreach ($aRows as $aRow) {
            $tagCond['item_id'][] = $aRow['blog_id'];
            $categoryCond['blog_id'][] = $aRow['blog_id'];
        }

        $tagReducer->reduceFetchAll($tagCond);
        $categoryReducer->reduceFetchAll($categoryCond);

        $view = $this->request()->get('view');
        $shortFields = [];
        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'description', 'image', 'statistic', 'user', 'categories', 'tags', 'id',
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
                $shortFields[] = 'is_sponsor';
            } else {
                $shortFields[] = 'is_featured';
            }
        }

        foreach ($aRows as $key => $aRow) {
            $aRow['tags'] = $tagReducer->reduceQuery([
                'category_id' => BlogResource::TAG_CATEGORY,
                'item_id'     => $aRow['blog_id']
            ]);
            $aRow['categories'] = $categoryReducer->reduceQuery([
                'blog_id' => $aRow['blog_id']
            ]);
            if ($view == 'sponsor') {
                $aRow['is_sponsor'] = true;
            }
            if ($view == 'feature') {
                $aRow['is_featured'] = true;
            }
            $aRows[$key] = $this->processRow($aRow)
                ->displayShortFields()
                ->toArray($shortFields);
        }
    }

    /**
     * Process single row
     *
     * @param array $item
     *
     * @return BlogResource|array
     */
    public function processRow($item)
    {
        $resource = $this->populateResource(BlogResource::class, $item);
        $resource->setExtra($this->getAccessControl()->getPermissions($resource));

        // Add self Hyper Media Links
        $this->setSelfHyperMediaLinks($resource);


        return $resource;
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    public function delete($params)
    {
        $id = $this->resolver->resolveId($params);
        $blog = $this->loadResourceById($id, true);

        if (!$blog) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(BlogAccessControl::DELETE, $blog);

        $mResult = Phpfox::getService('blog.process')->delete($id);
        if ($mResult !== false) {
            return $this->success([
                'id' => $id
            ]);
        }

        return $this->error('Cannot delete blog');

    }

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var BlogForm $form */
        $form = $this->createForm(BlogForm::class, [
            'title'  => 'adding_a_new_blog',
            'method' => 'post',
            'action' => UrlUtility::makeApiUrl('blog')
        ]);
        $form->setCategories($this->getCategories());

        if ($editId && ($blog = $this->loadResourceById($editId, true))) {
            $this->denyAccessUnlessGranted(BlogAccessControl::EDIT, $blog);

            $form->setAction(UrlUtility::makeApiUrl('blog/:id', $editId))
                ->setTitle('editing_blog')
                ->setMethod('put');
            $form->assignValues($blog);
        } else {
            $this->denyAccessUnlessGranted(BlogAccessControl::ADD);
            if (($iFlood = $this->getSetting()->getUserSetting('blog.flood_control_blog')) !== 0) {
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field'      => 'time_stamp', // The time stamp field
                        'table'      => Phpfox::getT('blog'), // Database table we plan to check
                        'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];

                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error($this->getLocalization()->translate('your_are_posting_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }
        }

        return $this->success($form->getFormStructure());
    }

    /**
     * Create a new Blog API
     *
     * @param array $params
     *
     * @return array|bool|mixed|void
     * @throws ValidationErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    public function create($params = [])
    {
        // Checking create blog permission
        $this->denyAccessUnlessGranted(BlogAccessControl::ADD);

        /** @var BlogForm $form */
        $form = $this->createForm(BlogForm::class);
        $form->setCategories($this->getCategories());
        if ($form->isValid()) {
            $id = $this->processCreate($form->getValues());

            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => BlogResource::populate([])->getResourceName(),
                ], [], $this->localization->translate('blog_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }

    }

    /**
     * Update a blog
     *
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws ValidationErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    public function update($params)
    {
        $id = $this->resolver->resolveId($params);
        // Get blog resource and checking for permission
        $blog = $this->loadResourceById($id);
        if (empty($blog)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(BlogAccessControl::EDIT, BlogResource::populate($blog));

        /** @var BlogForm $form */
        $form = $this->createForm(BlogForm::class);
        $form->setCategories($this->getCategories());
        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values, $blog);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => BlogResource::populate([])->getResourceName(),
                ], [], $this->localization->translate('blog_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }

    }

    /**
     * @param      $id
     * @param bool $returnResource
     *
     * @return BlogResource|array|null
     * @throws \Exception
     */
    function loadResourceById($id, $returnResource = false)
    {
        $item = Phpfox::getService("blog")->getBlog($id);

        if (empty($item['blog_id'])) {
            return null;
        }
        if ($returnResource) {
            return $this->processOne($item);
        }
        return $item;
    }

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function patchUpdate($params)
    {
        return null;
    }

    /**
     * Get for display on activity feed
     *
     * @param       $feed
     * @param array $item
     *
     * @return array
     * @throws \Exception
     */
    public function getFeedDisplay($feed, $item)
    {
        if (empty($item)) {
            return null;
        }
        $categoryCond = [
            'blog_id' => []
        ];
        /** @var BlogCategoryApi $categoryReducer */
        $categoryReducer = $this->getCategoryApi();
        $categoryCond['blog_id'][] = $item['blog_id'];

        $categoryReducer->reduceFetchAll($categoryCond);

        $item['categories'] = $categoryReducer->reduceQuery([
            'blog_id' => $item['blog_id']
        ]);
        return $this->processRow($item)->getFeedDisplay();
    }

    /**
     * @return AbstractResourceApi|\Apps\Core_MobileApi\Api\ResourceInterface|mixed
     * @throws \Exception
     */
    private function getCategoryApi()
    {
        return NameResource::instance()
            ->getApiServiceByResourceName(BlogCategoryResource::RESOURCE_NAME);
    }

    /**
     * @return AbstractResourceApi|\Apps\Core_MobileApi\Api\ResourceInterface|mixed
     * @throws \Exception
     */
    public function getTagApi()
    {
        return NameResource::instance()
            ->getApiServiceByResourceName(TagResource::RESOURCE_NAME);
    }

    /**
     * @return array
     */
    private function getCategories()
    {
        return Phpfox::getService('blog.category')->getForBrowse();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new BlogAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get('module_id');
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
     * Internal process adding blog
     *
     * @param $values
     *
     * @return array|bool|int|void
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    private function processCreate($values)
    {
        if (($iFlood = $this->getSetting()->getUserSetting('blog.flood_control_blog')) !== 0) {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp', // The time stamp field
                    'table'      => Phpfox::getT('blog'), // Database table we plan to check
                    'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->error($this->getLocalization()->translate('your_are_posting_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }
        if (!empty($values['file']) && !empty($values['file']['temp_file'])) {
            $values['temp_file'] = $values['file']['temp_file'];
        }
        if (!empty($values['categories'])) {
            $values['selected_categories'] = $values['categories'];
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        if (!empty($values['tags'])) {
            $values['tag_list'] = $values['tags'];
        }
        if (!empty($values['draft'])) {
            $values['post_status'] = 2;
        } else {
            $values['post_status'] = 1;
        }
        return $this->processService->add($values);
    }

    /**
     * Internal process update a blog
     *
     * @param       $id
     * @param       $values
     * @param array $item
     *
     * @return bool
     */
    private function processUpdate($id, $values, $item)
    {
        if (!empty($values['file'])) {
            if ($values['file']['status'] == FileType::NEW_UPLOAD || $values['file']['status'] == FileType::CHANGE) {
                $values['temp_file'] = $values['file']['temp_file'];
            } else if ($values['file']['status'] == FileType::REMOVE) {
                $values['remove_photo'] = 1;
            }
        }
        if (!empty($values['categories'])) {
            $values['selected_categories'] = $values['categories'];
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        if (!empty($values['tags'])) {
            $values['tag_list'] = $values['tags'];
        }
        if (!empty($values['draft'])) {
            $values['post_status'] = $item['post_status'];
        } else {
            $values['post_status'] = 1;
            $values['draft_publish'] = $item['post_status'] == 2 ? 1 : 0;
        }
        $userId = $item['user_id'];
        return $this->processService->update($id, $userId, $values, $item);
    }

    /**
     * @return AbstractResourceApi|\Apps\Core_MobileApi\Api\ResourceInterface|mixed
     * @throws \Exception
     */
    private function getAttachmentApi()
    {
        return NameResource::instance()
            ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME);
    }

    /**
     * @param array $params
     *
     * @return array|bool
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(BlogAccessControl::VIEW);
        /** @var BlogSearchForm $form */
        $form = $this->createForm(BlogSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('blog')
        ]);

        return $this->success($form->getFormStructure());
    }

    /**
     * @param BlogResource $resource
     */
    private function setSelfHyperMediaLinks($resource)
    {
        $resource->setSelf([
            BlogAccessControl::VIEW   => $this->createHyperMediaLink(BlogAccessControl::VIEW,
                $resource,
                HyperLink::GET, 'blog/:id',
                ['id' => $resource->getId()]),
            BlogAccessControl::EDIT   => $this->createHyperMediaLink(BlogAccessControl::EDIT,
                $resource,
                HyperLink::GET, 'blog/form/:id',
                ['id' => $resource->getId()]),
            BlogAccessControl::DELETE => $this->createHyperMediaLink(BlogAccessControl::DELETE,
                $resource, HyperLink::DELETE,
                'blog/:id',
                ['id' => $resource->getId()])
        ]);
    }

    /**
     * @param BlogResource $resource
     */
    private function setLinksHyperMediaLinks($resource)
    {
        $resource->setLinks([
            "likes"    => $this->createHyperMediaLink(null,
                $resource,
                HyperLink::GET, 'like',
                ['item_type' => "blog", 'item_id' => $resource->getId()]),
            "comments" => $this->createHyperMediaLink(null,
                $resource,
                HyperLink::GET, 'comment',
                ['item_type' => "blog", 'item_id' => $resource->getId()])
        ]);
    }

    /**
     * @return array
     */
    public function getRouteMap()
    {
        $resource = str_replace('-', '_', BlogResource::RESOURCE_NAME);
        $module = 'blog';
        return [
            [
                'path'      => 'blog/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'blog/category/:category(/*), blog/tag/:tag',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'blog/add',
                'routeName' => ROUTE_MODULE_ADD,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'blog(/*)',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    /**
     * @param $param
     *
     * @return MobileApp
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     */
    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('blog', [
            'title'             => $l->translate('blogs'),
            'home_view'         => 'menu',
            'main_resource'     => new BlogResource([]),
            'category_resource' => new BlogCategoryResource([]),
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $blogResourceName = (new BlogResource([]))->getResourceName();
        $headerButtons[$blogResourceName] = [
            [
                'icon'   => 'list-bullet-o',
                'action' => Screen::ACTION_FILTER_BY_CATEGORY,
            ],
        ];
        if ($this->getAccessControl()->isGranted(BlogAccessControl::ADD)) {
            $headerButtons[$blogResourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => $blogResourceName]
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    public function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var BlogResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(BlogAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            return $this->success([
                'is_pending' => false
            ], [], $this->getLocalization()->translate('blog_has_been_approved'));
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws ValidationErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver
            ->setAllowedValues('feature', ['1', '0'])
            ->resolveSingle($params, 'feature', null, [], 1);
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(BlogAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('blog_successfully_featured') : $this->getLocalization()->translate('blog_successfully_un_featured'));
        }
        return $this->error();
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws ValidationErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $isSponsorFeed = $this->resolver
            ->setAllowedValues('is_sponsor_feed', ['1', '0'])
            ->resolveSingle($params, 'is_sponsor_feed', null, [], 0);
        $sponsor = (int)$this->resolver
            ->setAllowedValues('sponsor', ['1', '0'])
            ->resolveSingle($params, 'sponsor', null, [], 1);

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($isSponsorFeed) {
            //Support un-sponsor in feed
            $this->denyAccessUnlessGranted(BlogAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('blog', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(BlogAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(BlogAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }
            if ($this->processService->sponsor($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('blog');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module' => 'blog',
                        'item_id' => $id,
                        'name' => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('blog', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('blog_successfully_sponsored') : $this->getLocalization()->translate('blog_successfully_un_sponsored'));
            }
        }
        return $this->error();
    }

    /**
     * Update sponsored view count
     *
     * @param $sponsorItems
     */
    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'blog');
            }
        }
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return [
            'blog/publish' => [
                'method'          => 'put',
                'url'             => 'mobile/blog/publish/:id',
                'data'            => 'id=:id, ignore_error=1',
                'new_state'       => 'is_draft=false',
                'confirm_title'   => $this->getLocalization()->translate('confirm'),
                'confirm_message' => $this->getLocalization()->translate('are_you_sure'),
            ],
        ];
    }

    /**
     * @param $params
     *
     * @return array|bool|void
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\UnknownErrorException
     */
    public function publish($params)
    {
        $id = $this->resolver->resolveId($params);

        $item = $this->blogService->getBlogForEdit($id);
        if (Phpfox::isModule('tag')) {
            $tags = Phpfox::getService('tag')->getTagsById('blog', $id);
            if (isset($tags[$item['blog_id']])) {
                $item['tag_list'] = '';
                foreach ($tags[$item['blog_id']] as $aTag) {
                    $item['tag_list'] .= ' ' . $aTag['tag_text'] . ',';
                }
                $item['tag_list'] = trim(trim($item['tag_list'], ','));
            }
        }

        $categories = Phpfox::getService('blog.category')->getCategoriesByBlogId($item['blog_id']);
        $selectedCategories = [];

        if (!empty($categories)) {
            foreach ($categories as $aCategory) {
                $selectedCategories[] = $aCategory['category_id'];
            }
        }
        $item['selected_categories'] = $selectedCategories;
        $oldItem = $item;

        $this->denyAccessUnlessGranted(BlogAccessControl::PUBLISH, BlogResource::populate($oldItem));
        $item['draft_publish'] = true;
        $item['post_status'] = BLOG_STATUS_PUBLIC;
        if ($item['privacy'] == PRIVACY_CUSTOM) {
            $privacyList = Phpfox::getService('privacy')->get('blog', $id);
            if (!empty($privacyList)) {
                $item['privacy_list'] = array_map(function ($val) {
                    return $val['friend_list_id'];
                }, $privacyList);
            }
        }
        if ($this->processService->update($id, $item['user_id'], $item, $oldItem)) {
            return $this->success([
                'is_draft' => false
            ], [], $this->getLocalization()->translate('blog_successfully_publish'));
        }
        return $this->error();
    }

    /**
     * @param $param
     *
     * @return ScreenSetting|array
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     */
    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('blog', [
            'name' => 'blogs'
        ]);
        $resourceName = BlogResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    [
                        'component'    => 'item_image',
                        'imageDefault' => false
                    ],
                    'item_title',
                    'item_author',
                    'item_stats',
                    'item_like_phrase',
                    ['component' => 'item_pending', 'message' => 'this_blog_is_pending_an_admins_approval'],
                    'item_html_content',
                    'item_category',
                    'item_tags',
                    'item_user_tags'
                ]
            ],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            'screen_title'                 => $l->translate('blogs') . ' > ' . $l->translate('blog') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_blog'),
                'resource_name' => $resourceName,
                'module_name'   => 'blog',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored_blog'),
                'resource_name' => $resourceName,
                'module_name'   => 'blog',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);
        return $screenSetting;
    }

    /**
     * @return array
     */
    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'blog.index',
            ScreenSetting::MODULE_LISTING => 'blog.index',
            ScreenSetting::MODULE_DETAIL  => 'blog.view'
        ];
    }

    /**
     * @param $params
     *
     * @return array
     * @throws ValidationErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    protected function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $featuredItems = $this->blogService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    /**
     * @param $params
     *
     * @return array|bool
     * @throws ValidationErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->blogService->getRandomSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * Moderation items
     *
     * @param $params
     *
     * @return array|bool|mixed
     * @throws ValidationErrorException
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
                $this->denyAccessUnlessGranted(BlogAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->approve($id)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_pending' => false];
                $sMessage = $this->getLocalization()->translate('blog_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(BlogAccessControl::FEATURE);
                if (!$this->processService->feature($ids, $value)) {
                    $ids = [];
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('blog_s_successfully_featured') : $this->getLocalization()->translate('blog_s_successfully_unfeatured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(BlogAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->delete($id)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('blog_s_successfully_deleted');
                break;
        }

        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}