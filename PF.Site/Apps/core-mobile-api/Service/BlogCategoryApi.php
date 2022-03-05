<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_Blogs\Service\Category\Category;
use Apps\Core_Blogs\Service\Category\Process;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ReducerInterface;
use Apps\Core_MobileApi\Api\Resource\BlogCategoryResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\Blog\BlogAccessControl;
use Phpfox;

class BlogCategoryApi extends AbstractResourceApi implements ReducerInterface
{
    /**
     * @var Category
     */
    private $categoryService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var BlogCategoryResource[]
     */
    private $fetchedCategories;

    public function __construct()
    {
        parent::__construct();
        $this->categoryService = Phpfox::getService("blog.category");
        $this->processService = Phpfox::getService('blog.category.process');
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(BlogAccessControl::VIEW);

        $result = $this->categoryService->getForBrowse();
        $result = array_map(function ($item) {
            return BlogCategoryResource::populate($item)->toArray();
        }, $result);
        return $this->success($result);
    }

    /**
     * Get Categories associated with a blog
     *
     * @param $id int
     *
     * @return array|int|string
     */
    public function getByBlogId($id)
    {
        $where = ["AND cd.blog_id = " . (int)$id];
        $result = $this->database()->select("c.*")
            ->from(\Phpfox::getT("blog_category"), 'c')
            ->join(\Phpfox::getT("blog_category_data"), 'cd', 'c.category_id = cd.category_id')
            ->where($where)
            ->group("c.category_id")
            ->execute("getRows");

        $this->processRows($result);
        return $result;
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function findOne($params)
    {
        $category = $this->loadResourceById($params['id']);
        if (empty($category)) {
            return $this->notFoundError();
        }

        return $this->success(BlogCategoryResource::populate($category)->toArray());
    }

    /**
     * Create new document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        // TODO: Implement create() method.
    }

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function update($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        // TODO: Implement update() method.
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
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        // TODO: Implement updateAll() method.
    }

    /**
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function delete($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);

        $category = $this->loadResourceById($params['id']);
        if (empty($category)) {
            return $this->notFoundError('blog_category_not_found');
        }
        $this->processService->delete($params['id']);
        return $this->success(BlogCategoryResource::populate($category)->toArray());
    }

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    function form($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $returnResource = false)
    {
        $category = $this->database()->select("*")
            ->from(Phpfox::getT("blog_category"))
            ->where("category_id = " . (int)$id)
            ->execute("getSlaveRow");
        return $category;
    }

    /**
     * Fetch All category associate with blogs
     *
     * @param $conditions
     *
     * @return array|mixed
     */
    function reduceFetchAll($conditions)
    {
        $conditions = $this->resolver->clearConfigure()
            ->setRequired(['blog_id'])
            ->setAllowedTypes('blog_id', 'array')
            ->resolve($conditions)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return null;
        }

        $where = [
            'd.blog_id in (' . implode(",", $conditions['blog_id']) . ')',
            'c.is_active' => 1
        ];

        $this->fetchedCategories = $this->database()
            ->select("c.*, d.blog_id")
            ->from(":blog_category", 'c')
            ->join(':blog_category_data', 'd', 'd.category_id = c.category_id')
            ->where($where)
            ->execute("getSlaveRows");
    }

    /**
     * Query fetched data
     *
     * @param $condition
     *
     * @return array|mixed
     */
    function reduceQuery($condition)
    {
        $condition = $this->resolver->clearConfigure()
            ->setRequired(['blog_id'])
            ->setAllowedTypes('blog_id', 'int')
            ->resolve($condition)->getParameters();
        if (!$this->resolver->isValid()) {
            return null;
        }
        $result = [];
        foreach ($this->fetchedCategories as $blogCat) {
            if ($blogCat['blog_id'] == $condition['blog_id']) {
                $result[] = $this->processRow($blogCat);
            }
        }
        return $result;
    }

    public function processRow($item)
    {
        return BlogCategoryResource::populate($item);
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }
}