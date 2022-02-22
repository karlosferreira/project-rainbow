<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_Marketplace\Service\Category\Category;
use Apps\Core_Marketplace\Service\Category\Process;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\VideoCategoryResource;
use Phpfox;

/**
 * Class MarketplaceCategoryApi
 * @package Apps\Core_MobileApi\Service
 */
class VideoCategoryApi extends AbstractResourceApi
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
        $this->categoryService = Phpfox::getService('v.category');
        $this->processService = Phpfox::getService('v.process');
    }

    function findAll($params = [])
    {
        if (!Phpfox::getUserParam('pf_video_view')) {
            return $this->permissionError();
        }
        $result = $this->categoryService->getForUsers(0, 1, 1, Phpfox::getParam('core.cache_time_default', 0));
        $this->processRows($result);
        return $this->success($result);
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
        $category = $this->loadResourceById($params['id']);
        if (empty($category)) {
            return $this->notFoundError();
        }
        return $this->success(VideoCategoryResource::populate($category)->toArray());
    }

    function create($params)
    {
        // TODO: Implement create() method.
    }

    function update($params)
    {
        // TODO: Implement update() method.
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

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
        $aVals = [
            'delete_type'     => $params['delete_type'],
            'new_category_id' => $params['category']
        ];
        $this->processService->deleteCategory($params['id'], $aVals);
        return $this->success([], [], $this->getLocalization()->translate('successfully_deleted_the_category'));
    }

    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $returnResource = false)
    {
        $category = $this->database()->select('*')
            ->from(':video_category')
            ->where('category_id = ' . (int)$id)
            ->execute('getSlaveRow');
        return $category;
    }

    public function getByVideoId($id)
    {
        $where = ['AND cd.video_id = ' . (int)$id];
        $result = $this->database()->select('c.*')
            ->from(':video_category', 'c')
            ->join(':video_category_data', 'cd', 'c.category_id = cd.category_id')
            ->where($where)
            ->group('c.category_id')
            ->execute('getRows');
        $result = array_map(function ($item) {
            return VideoCategoryResource::populate($item)->displayShortFields()->toArray();
        }, $result);
        return $result;
    }

    public function processRow($item)
    {
        return VideoCategoryResource::populate($item)->displayShortFields()->toArray();
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