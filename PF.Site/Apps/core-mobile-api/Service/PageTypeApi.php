<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Page\PageTypeForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\PageCategoryResource;
use Apps\Core_MobileApi\Api\Resource\PageTypeResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_Pages\Service\Category;
use Apps\Core_Pages\Service\Facade;
use Apps\Core_Pages\Service\Pages;
use Apps\Core_Pages\Service\Process;
use Apps\Core_Pages\Service\Type;
use Phpfox;


class PageTypeApi extends AbstractResourceApi
{
    /**
     * @var Facade
     */
    private $facadeService;
    /**
     * @var Pages
     */
    private $pageService;
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
     * PageTypeApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('pages.facade');
        $this->typeService = Phpfox::getService('pages.type');
        $this->categoryService = Phpfox::getService('pages.category');
        $this->pageService = Phpfox::getService('pages');
        $this->processService = Phpfox::getService('pages.process');
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $result = $this->typeService->get();
        $this->processRows($result);
        return $this->success($result);
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
        $category['categories'] = $this->facadeService->getCategory()->getByTypeId($category['type_id']);
        return $this->success(PageTypeResource::populate($category)->toArray());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::SYSTEM_ADMIN);
        /** @var PageTypeForm $form */
        $form = $this->createForm(PageTypeForm::class);
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

    private function processCreate($aVals)
    {
        //Add phrase for category
        if (!empty($aVals['file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['file']['temp_file']);
            if (empty($aFile)) {
                return $this->notFoundError($this->getLocalization()->translate('file_not_found'));
            }
        }
        $aLanguages = Phpfox::getService('language')->getAll();
        $name = $aVals['name_' . $aLanguages[0]['language_id']];
        $phrase_var_name = $this->facadeService->getItemType() . '_category_' . md5('Pages/Groups Category' . $name . PHPFOX_TIME);
        //Add phrases
        $aText = [];
        foreach ($aLanguages as $aLanguage) {
            if (isset($aVals['name_' . $aLanguage['language_id']]) && !empty($aVals['name_' . $aLanguage['language_id']])) {
                $aText[$aLanguage['language_id']] = $aVals['name_' . $aLanguage['language_id']];
            } else {
                return false;
            }
        }
        $aValsPhrase = [
            'var_name' => $phrase_var_name,
            'text'     => $aText
        ];
        $finalPhrase = Phpfox::getService('language.phrase.process')->add($aValsPhrase);
        $sServerId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');
        if (!empty($aFile)) {
            $sFileName = $aFile['path'];
            $sServerId = $aFile['server_id'];
            Phpfox::getService('core.temp-file')->delete($aVals['file']['temp_file']);
        }
        return $this->database()->insert(':pages_type', array_merge([
            'is_active'  => isset($aVals['is_active']) ? $aVals['is_active'] : '1',
            'name'       => $finalPhrase,
            'time_stamp' => PHPFOX_TIME,
            'ordering'   => '0',
            'item_type'  => $this->facadeService->getItemTypeId(),
        ], !isset($sFileName) ? [] : [
            'image_path'      => 'PF.Base/file/pic/pages/' . $sFileName,
            'image_server_id' => $sServerId
        ]));
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
        /** @var PageTypeForm $form */
        $form = $this->createForm(PageTypeForm::class);
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

    private function processUpdate($id, $aVals)
    {
        if (!empty($aVals['file'])) {
            if ($aVals['file']['status'] == FileType::NEW_UPLOAD || $aVals['file']['status'] == FileType::CHANGE) {
                $aVals['temp_file'] = $aVals['file']['temp_file'];
            } else if ($aVals['file']['status'] == FileType::REMOVE) {
                $aVals['remove_photo'] = 1;
            }
        }
        if (!empty($aVals['temp_file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            if (empty($aFile)) {
                return $this->notFoundError($this->getLocalization()->translate('file_not_found'));
            }
        }
        $aLanguages = Phpfox::getService('language')->getAll();
        if (Phpfox::isPhrase($aVals['name'])) {
            foreach ($aLanguages as $aLanguage) {
                if (isset($aVals['name_' . $aLanguage['language_id']])) {
                    $name = $aVals['name_' . $aLanguage['language_id']];
                    Phpfox::getService('language.phrase.process')->updateVarName($aLanguage['language_id'],
                        $aVals['name'], $name);
                }
            }
        } else {
            //Add new phrase if before is not phrase
            $name = $aVals['name_' . $aLanguages[0]['language_id']];
            $phrase_var_name = $this->facadeService->getItemType() . '_category_' . md5('Pages/Groups Category' . $name . PHPFOX_TIME);
            $aText = [];
            foreach ($aLanguages as $aLanguage) {
                if (isset($aVals['name_' . $aLanguage['language_id']]) && !empty($aVals['name_' . $aLanguage['language_id']])) {
                    $aText[$aLanguage['language_id']] = $aVals['name_' . $aLanguage['language_id']];
                } else {
                    return false;
                }
            }
            $aValsPhrase = [
                'product_id' => 'phpfox',
                'module'     => $this->facadeService->getItemType() . '|' . $this->facadeService->getItemType(),
                'var_name'   => $phrase_var_name,
                'text'       => $aText
            ];
            $aVals['name'] = Phpfox::getService('language.phrase.process')->add($aValsPhrase);
        }
        $sServerId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');
        if (!empty($aFile)) {
            $sFileName = $aFile['path'];
            $sServerId = $aFile['server_id'];
            $this->typeService->deleteImage((int)$id);
            Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
        } else if (!empty($aVals['remove_photo'])) {
            $this->typeService->deleteImage((int)$id);
        }
        return $this->database()->update(':pages_type', array_merge([
            'name' => $aVals['name']
        ], !isset($sFileName) ? [] : [
            'image_path'      => 'PF.Base/file/pic/pages/' . $sFileName,
            'image_server_id' => $sServerId
        ]), 'type_id = ' . (int)$id);
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
            if (!$newIsSub) {
                if (!$this->loadResourceById($sub[0])) {
                    return $this->notFoundError();
                }
            } else {
                if (!NameResource::instance()
                    ->getApiServiceByResourceName(PageCategoryResource::RESOURCE_NAME)
                    ->loadResourceById($sub[0])) {
                    return $this->notFoundError();
                }
            }
            $this->pageService->moveItemsToAnotherCategory($params['id'], $sub[0], false,
                $newIsSub, $this->facadeService->getItemTypeId());
            $this->categoryService->moveSubCategoriesToAnotherType($params['id'], $sub[0]);
        }
        $this->processService->deleteCategory($params['id'], false, $params['child_action'] === 'del');
        return $this->success([], [], $this->getLocalization()->translate('successfully_deleted_the_category'));
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
        /** @var PageTypeForm $form */
        $form = $this->createForm(PageTypeForm::class, [
            'title'  => 'create_a_new_category',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('page-type')
        ]);
        if ($editId) {
            $type = $this->loadResourceById($editId);
            if (empty($type)) {
                return $this->notFoundError();
            }
            $form->setEditing(true);
            $form->setTitle('edit_a_category')
                ->setAction(UrlUtility::makeApiUrl('page-type/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($this->convertForm($type));
        }
        return $this->success($form->getFormStructure());
    }

    public function convertForm($item)
    {
        if (!empty($item['image_path'])) {
            $item['image'] = Image::createFrom([
                'file'      => $item['image_path'],
                'server_id' => $item['image_server_id'],
                'path'      => 'core.path_actual',
                'suffix'    => '_200'
            ])->toArray();
        }
        return $item;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $category = $this->typeService->getForEdit($id);
        if (empty($category['type_id'])) {
            return null;
        }
        return $category;
    }

    public function processRow($item)
    {
        return PageTypeResource::populate($item)->setViewMode(ResourceBase::VIEW_LIST)->toArray();
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