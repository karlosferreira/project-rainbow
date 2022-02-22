<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Page\PagePermissionForm;
use Apps\Core_MobileApi\Api\Resource\PagePermissionResource;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\Core_Pages\Service\Facade;
use Apps\Core_Pages\Service\Pages;
use Apps\Core_Pages\Service\Process;
use Phpfox;


class PagePermissionApi extends AbstractResourceApi
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
     * PagePermissionApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('pages.facade');
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
        $params = $this->resolver->setDefined([
            'page_id'
        ])
            ->setAllowedTypes('page_id', 'int')
            ->setRequired(['page_id'])->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('pages.can_view_browse_pages')) {
            return $this->permissionError();
        }
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($params['page_id']);
        if (!$page) {
            return $this->notFoundError();
        }
        if ($page['user_id'] != Phpfox::getUserId() && !$this->pageService->isAdmin($page) && !Phpfox::getUserParam('pages.can_edit_all_pages')) {
            return $this->permissionError();
        }
        $items = $this->getPermissions($params['page_id']);
        $this->processRows($items);
        return $this->success($items);
    }


    private function filterModules($perms, $isForm = false)
    {
        $supportModules = NameResource::instance()->getSupportModules();
        if ($integrate = storage()->get('pages_integrate')) {
            $integrate = (array)$integrate->value;
        }
        $hiddenPerms = ['music.share_music'];
        foreach ($perms as $key => $perm) {
            if (empty($perm['module_id'])) {
                continue;
            }
            $module = $perm['module_id'];
            if ($integrate && array_key_exists($module, $integrate) && !$integrate[$module]) {
                unset($perms[$key]);
                continue;
            }
            if ((!empty($module) && !in_array($module, $supportModules)) || in_array($perm['id'], $hiddenPerms)) {
                if ($isForm) {
                    $perms[$key]['is_hidden'] = true;
                } else {
                    unset($perms[$key]);
                }
            }
        }
        return array_values($perms);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        return $this->findAll(['page_id' => $id]);
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
        $id = $this->resolver->resolveId($params);
        /** @var PagePermissionForm $form */
        $form = $this->createForm(PagePermissionForm::class);
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($id, true);
        if (empty($page)) {
            return $this->notFoundError();
        }
        $perms = $this->getPermissions($id, true);
        $form->setPermissions($perms);
        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $page);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => PageResource::populate([])->getResourceName()
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
        unset($values['submit']);
        $this->database()->delete(':pages_perm', 'page_id = ' . (int)$id);
        foreach ($values as $sPermId => $iPermValue) {
            $this->database()->insert(Phpfox::getT('pages_perm'),
                ['page_id' => (int)$id, 'var_name' => str_replace('__', '.', $sPermId), 'var_value' => (int)$iPermValue]);
        }
        return $id;
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
        return $this->permissionError();
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $editId = $this->resolver->resolveId($params);
        /** @var PagePermissionForm $form */
        $form = $this->createForm(PagePermissionForm::class, [
            'title'  => 'edit_permissions',
            'action' => UrlUtility::makeApiUrl('page-permission/:id', $editId),
            'method' => 'PUT'
        ]);
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($editId, true);
        if (empty($page)) {
            return $this->notFoundError();
        }
        $perms = $this->getPermissions($editId, true);
        $form->setPermissions($perms);
        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $page);

        return $this->success($form->getFormStructure());
    }

    private function getPermissions($id, $isForm = false)
    {
        $perms = $this->getPerms($id);
        return $this->filterModules($perms, $isForm);
    }

    /**
     * Get page permissions
     *
     * @param $iPage
     *
     * @return array
     */
    public function getPerms($iPage)
    {
        $aCallbacks = Phpfox::massCallback('getPagePerms');
        $aPerms = [];
        $aUserPerms = $this->pageService->getPermsForPage($iPage);
        foreach ($aCallbacks as $module => $aCallback) {
            foreach ($aCallback as $sId => $sPhrase) {
                $aPerms[] = [
                    'id'        => $sId,
                    'phrase'    => $sPhrase,
                    'module_id' => $module,
                    'is_active' => (isset($aUserPerms[$sId]) ? $aUserPerms[$sId] : '0')
                ];
            }
        }

        return $aPerms;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        // TODO: Implement loadResourceById() method.
    }

    public function processRow($item)
    {
        return PagePermissionResource::populate($item)->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new PageAccessControl($this->getSetting(), $this->getUser());
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