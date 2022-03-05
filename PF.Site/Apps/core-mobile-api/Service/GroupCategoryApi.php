<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Group\GroupCategoryForm;
use Apps\Core_MobileApi\Api\Resource\GroupCategoryResource;
use Apps\Core_MobileApi\Api\Resource\GroupTypeResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_Photos\Service\Category\Category;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Apps\PHPfox_Groups\Service\Type;
use Phpfox;


class GroupCategoryApi extends AbstractResourceApi
{
    /**
     * @var Facade
     */
    private $facadeService;
    /**
     * @var Groups
     */
    private $groupService;
    /**
     * @var Process
     */
    private $processService;
    /**
     * @var Type
     */
    private $typeService;
    /**
     * @var Category
     */
    private $categoryService;

    /**
     * GroupCategoryApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('groups.facade');
        $this->typeService = Phpfox::getService('groups.type');
        $this->categoryService = Phpfox::getService('groups.category');
        $this->groupService = Phpfox::getService('groups');
        $this->processService = Phpfox::getService('groups.process');
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $result = $this->facadeService->getCategory()->getAllCategories();
        $finalResult = [];
        foreach ($result as $res) {
            $finalResult[] = $res;
        }
        $this->processRows($finalResult);
        return $this->success($finalResult);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $category = $this->loadResourceById($id);
        if (empty($category)) {
            return $this->notFoundError();
        }
        return $this->success(GroupCategoryResource::populate($category)->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var GroupCategoryForm $form */
        $form = $this->createForm(GroupCategoryForm::class, [
            'title'  => 'create_a_new_category',
            'action' => UrlUtility::makeApiUrl('group-category'),
            'method' => 'POST'
        ]);

        $form->setTypes($this->getTypes());

        $category = $this->loadResourceById($editId);
        if ($editId && empty($category)) {
            return $this->notFoundError();
        }
        if ($editId) {
            $form->setEditing(true);
            $form->setTitle('edit_a_category')
                ->setAction(UrlUtility::makeApiUrl('group-category/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($this->convertForm($category));
        }

        return $this->success($form->getFormStructure());
    }

    private function convertForm($item)
    {
        if (!empty($item['type_id'])) {
            $item['type_id'] = [
                [
                    'id' => $item['type_id']
                ]
            ];
        }
        return $item;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        /** @var GroupCategoryForm $form */
        $form = $this->createForm(GroupCategoryForm::class);
        if ($form->isValid()) {
            $id = $this->processCreate($form->getValues());
            if ($id) {
                return $this->success([
                    'id' => $id
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        if (!empty($values['type_id'])) {
            $values['type_id'] = $values['type_id'][0];
            //Check is type
            $type = NameResource::instance()->getApiServiceByResourceName(GroupTypeResource::RESOURCE_NAME)->loadResourceById($values['type_id']);
            if (empty($type)) {
                return $this->notFoundError($this->getLocalization()->translate('group_type_is_not_found'));
            }
        }
        return $this->processService->addCategory($values);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        $id = $this->resolver->resolveId($params);
        $category = $this->loadResourceById($id);
        if (empty($category)) {
            return $this->notFoundError();
        }
        /** @var GroupCategoryForm $form */
        $form = $this->createForm(GroupCategoryForm::class);
        $form->setEditing(true);
        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id' => $id
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        if (!empty($values['type_id'])) {
            //Check is type
            $type = NameResource::instance()->getApiServiceByResourceName(GroupTypeResource::RESOURCE_NAME)->loadResourceById($values['type_id']);
            if (empty($type)) {
                return $this->notFoundError($this->getLocalization()->translate('group_type_is_not_found'));
            }
        }
        return $this->processService->updateCategory($id, $values);
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
                'child_action', 'category'
            ])
            ->setAllowedValues('child_action', ['move', 'del'])
            ->setRequired(['id'])
            ->resolve(array_merge(['child_action' => 'del'], $params))
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
        if ($params['child_action'] == 'move') {
            if (empty($params['category']) || $params['category'] == $params['id']) {
                return $this->notFoundError();
            }
            $sub = explode('_', $params['category']);
            $newIsSub = count($sub) > 1;
            if ($newIsSub) {
                if (!$this->loadResourceById($sub[0])) {
                    return $this->notFoundError();
                }
            } else {
                if (!NameResource::instance()
                    ->getApiServiceByResourceName(GroupTypeResource::RESOURCE_NAME)
                    ->loadResourceById($sub[0])) {
                    return $this->notFoundError();
                }
            }
            $this->groupService->moveItemsToAnotherCategory($params['id'], $sub[0], true,
                $newIsSub, $this->facadeService->getItemTypeId());
        }
        $this->processService->deleteCategory($params['id'], true, $params['child_action'] === 'del');
        return $this->success([], [], $this->getLocalization()->translate('successfully_deleted_the_category'));
    }

    private function getTypes()
    {
        return $this->typeService->getForAdmin(false);
    }

    /**
     * @param $id
     * @param $returnResource
     * @param $type
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false, $type = null)
    {
        if ($type != null) {
            $category = $this->database()->select('pc.*')
                ->from(':pages_category', 'pc')
                ->join(':pages_type', 'pt', 'pt.type_id = pc.type_id')
                ->where('pc.category_id = ' . (int)$id . ' AND pt.item_type =' . $this->facadeService->getItemTypeId() . ' AND pc.type_id = ' . (int)$type)
                ->execute('getSlaveRow');
        } else {
            $category = $this->categoryService->getForEdit($id);
        }
        if (empty($category['category_id'])) {
            return null;
        }
        return $category;
    }

    public function processRow($item)
    {
        return GroupCategoryResource::populate($item)->setViewMode(ResourceBase::VIEW_LIST)->toArray();
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