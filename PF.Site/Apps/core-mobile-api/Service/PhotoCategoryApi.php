<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\PhotoCategoryResource;
use Apps\Core_MobileApi\Api\Security\Photo\PhotoAccessControl;
use Apps\Core_Photos\Service\Category\Category;
use Apps\Core_Photos\Service\Category\Process;
use Phpfox;

/**
 * Class PhotoCategoryApi
 * @package Apps\Core_MobileApi\Service
 */
class PhotoCategoryApi extends AbstractResourceApi
{
    /**
     * @var Category
     */
    private $categoryService;

    /**
     * @var Process
     */
    private $processService;

    public function __construct()
    {
        parent::__construct();
        $this->categoryService = Phpfox::getService('photo.category');
        $this->processService = Phpfox::getService('photo.category.process');
    }

    /**
     * Get list of categories, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(PhotoAccessControl::VIEW);
        $result = $this->categoryService->getForBrowse();
        $result = array_map(function ($item) {
            return PhotoCategoryResource::populate($item)->displayShortFields()->toArray();
        }, $result);
        return $this->success($result);
    }

    public function getByPhotoId($id)
    {
        $where = ['AND cd.photo_id = ' . (int)$id];
        $result = $this->database()->select('c.*')
            ->from(':photo_category', 'c')
            ->join(':photo_category_data', 'cd', 'c.category_id = cd.category_id')
            ->where($where)
            ->group('c.category_id')
            ->execute('getRows');
        $result = array_map(function ($item) {
            return PhotoCategoryResource::populate($item)->displayShortFields()->toArray();
        }, $result);
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
        $id = $this->resolver->resolveId($params);
        $category = $this->loadResourceById($id);
        if (empty($category)) {
            return $this->notFoundError();
        }
        return $this->success(PhotoCategoryResource::populate($category)->toArray());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        // TODO: Implement create() method.
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        // TODO: Implement update() method.
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
            ->setDefined([
                'delete_type', 'category'
            ])
            ->setAllowedValues('delete_type', ['0', '1', '2'])
            ->setAllowedTypes('category', 'int')
            ->setRequired(['id'])
            ->resolve(array_merge(['delete_type' => 0], $params))
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::isAdmin()) {
            return $this->permissionError();
        }
        $category = $this->loadResourceById($params['id']);
        if (empty($category)) {
            return $this->notFoundError();
        }
        //delete type
        if ($params['delete_type'] == 2 && !$params['category']) {
            return $this->missingParamsError(['category']);
        }
        //delete type
        $aVals = [
            'delete_type'     => $params['delete_type'],
            'new_category_id' => $params['category']
        ];
        $this->processService->deleteCategory($params['id'], $aVals);
        return $this->success([], [], $this->getLocalization()->translate('successfully_deleted_the_category'));
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $category = $this->database()->select('*')
            ->from(':photo_category')
            ->where('category_id = ' . (int)$id)
            ->execute('getSlaveRow');
        return $category;
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