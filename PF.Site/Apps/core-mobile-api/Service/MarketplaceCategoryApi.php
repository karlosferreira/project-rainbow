<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_Marketplace\Service\Category\Category;
use Apps\Core_Marketplace\Service\Category\Process;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\MarketplaceCategoryResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Marketplace\MarketplaceAccessControl;
use Phpfox;

/**
 * Class MarketplaceCategoryApi
 * @package Apps\Core_MobileApi\Service
 */
class MarketplaceCategoryApi extends AbstractResourceApi
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
        $this->categoryService = Phpfox::getService('marketplace.category');
        $this->processService = Phpfox::getService('marketplace.category.process');
    }

    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::VIEW);
        $result = $this->categoryService->getForBrowse();
        $this->processRows($result);
        return $this->success($result);
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $category = $this->loadResourceById($id);
        if (empty($category)) {
            return $this->notFoundError();
        }
        return $this->success(MarketplaceCategoryResource::populate($category)->toArray());
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
            ->from(':marketplace_category')
            ->where('category_id = ' . (int)$id)
            ->execute('getSlaveRow');
        return $category;
    }

    public function getByListingId($id)
    {
        $where = ['AND cd.listing_id = ' . (int)$id];
        $result = $this->database()->select('c.*')
            ->from(':marketplace_category', 'c')
            ->join(':marketplace_category_data', 'cd', 'c.category_id = cd.category_id')
            ->where($where)
            ->order('c.parent_id ASC')
            ->group('c.category_id')
            ->execute('getRows');
        $result = array_map(function ($item) {
            return MarketplaceCategoryResource::populate($item)->displayShortFields()->toArray();
        }, $result);
        return $result;
    }

    public function processRow($item)
    {
        return MarketplaceCategoryResource::populate($item)->displayShortFields()->toArray();
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
    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MarketplaceAccessControl($this->getSetting(), $this->getUser());

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
}