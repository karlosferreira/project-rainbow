<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Events\Service\Category\Category;
use Apps\Core_Events\Service\Category\Process;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Event\EventCategoryForm;
use Apps\Core_MobileApi\Api\Resource\EventCategoryResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\Event\EventAccessControl;
use Phpfox;

/**
 * Class EventCategoryApi
 * @package Apps\Core_MobileApi\Service
 */
class EventCategoryApi extends AbstractResourceApi
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
        $this->categoryService = Phpfox::getService('event.category');
        $this->processService = Phpfox::getService('event.category.process');
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(EventAccessControl::VIEW);

        $result = $this->categoryService->getForBrowse();
        $result = array_map(function ($item) {
            /** @var EventCategoryResource $resource */
            $resource = $this->populateResource(EventCategoryResource::class, $item);
            $this->setHyperlinks($resource);
            return $resource->displayShortFields()->toArray();
        }, $result);
        return $this->success($result);
    }

    public function getByEventId($id)
    {
        $where = ['AND cd.event_id = ' . (int)$id];
        $result = $this->database()->select('c.*')
            ->from(':event_category', 'c')
            ->join(':event_category_data', 'cd', 'c.category_id = cd.category_id')
            ->where($where)
            ->order('c.parent_id ASC')
            ->group('c.category_id')
            ->execute('getRows');
        $result = array_map(function ($item) {
            return EventCategoryResource::populate($item)->displayShortFields()->toArray();
        }, $result);
        return $result;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        if (!Phpfox::getUserParam('event.can_access_event')) {
            return $this->permissionError();
        }
        $category = $this->loadResourceById($id);
        if (empty($category)) {
            return $this->notFoundError();
        }
        /** @var EventCategoryResource $resource */
        $resource = $this->populateResource(EventCategoryResource::class, $category);
        $this->setHyperlinks($resource);
        return $this->success($resource->toArray());
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
        /** @var EventCategoryForm $form */
        $form = $this->createForm(EventCategoryForm::class, [
            'title'  => $editId ? 'edit_a_category' : 'create_a_new_category',
            'method' => $editId ? 'PUT' : 'POST'
        ]);
        $form->setCategories($this->getCategories($editId));

        if ($editId && ($event = $this->loadResourceById($editId))) {
            $form->setEditing(true);
            $form->assignValues($event);
        }

        return $this->success($form->getFormStructure());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        /** @var EventCategoryForm $form */
        $form = $this->createForm(EventCategoryForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processService->add($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => EventCategoryResource::populate([])->getResourceName()
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
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
        /** @var EventCategoryForm $form */
        $form = $this->createForm(EventCategoryForm::class);
        $form->setEditing(true);
        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => EventCategoryResource::populate([])->getResourceName()
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
        $values['edit_id'] = $id;
        if ($values['parent_id'] == $id) {
            return false;
        }
        return $this->processService->update($values);
    }

    private function getCategories($editId = 0)
    {
        return $this->categoryService->getForAdmin(0, 0, 0, (int)$editId);
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
        //delete type
        if ($params['delete_type'] == 2 && !$params['category']) {
            return $this->missingParamsError(['category']);
        }
        $category = $this->loadResourceById($params['id']);
        if (empty($category)) {
            return $this->notFoundError();
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
     * @param $id
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $category = $this->categoryService->getForEdit($id);
        if (empty($category['category_id'])) {
            return null;
        }
        return $category;
    }

    private function setHyperlinks(EventCategoryResource $resource)
    {
        $resource->setSelf([
            EventAccessControl::VIEW   => $this->createHyperMediaLink(EventAccessControl::VIEW, null,
                HyperLink::GET, 'event-category/:id', ['id' => $resource->getId()]),
            EventAccessControl::DELETE => $this->createHyperMediaLink(EventAccessControl::SYSTEM_ADMIN, null,
                HyperLink::DELETE, 'event-category/:id', ['id' => $resource->getId()]),
            EventAccessControl::EDIT   => $this->createHyperMediaLink(EventAccessControl::SYSTEM_ADMIN, null,
                HyperLink::GET, 'event-category/form/:id', ['id' => $resource->getId()]),
        ]);
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