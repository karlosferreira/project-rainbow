<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Group\GroupPermissionForm;
use Apps\Core_MobileApi\Api\Resource\GroupPermissionResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Phpfox;


class GroupPermissionApi extends AbstractResourceApi
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
     * GroupPermissionApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('groups.facade');
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
        $params = $this->resolver->setDefined([
            'group_id'
        ])
            ->setAllowedTypes('group_id', 'int')
            ->setRequired(['group_id'])->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('pf_group_browse')) {
            return $this->permissionError();
        }
        $group = NameResource::instance()->getApiServiceByResourceName(GroupResource::RESOURCE_NAME)->loadResourceById($params['group_id']);
        if (!$group) {
            return $this->notFoundError();
        }
        if ($group['user_id'] != Phpfox::getUserId() && !$this->groupService->isAdmin($group) && !Phpfox::getUserParam('groups.can_edit_all_groups')) {
            return $this->permissionError();
        }
        $items = $this->getPermissions($params['group_id']);
        $this->processRows($items);
        return $this->success($items);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        return $this->findAll(['group_id' => $id]);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $editId = $this->resolver->resolveId($params);
        /** @var GroupPermissionForm $form */
        $form = $this->createForm(GroupPermissionForm::class, [
            'title'  => 'edit_permissions',
            'action' => UrlUtility::makeApiUrl('group-permission/:id', $editId),
            'method' => 'PUT'
        ]);
        $group = NameResource::instance()->getApiServiceByResourceName(GroupResource::RESOURCE_NAME)->loadResourceById($editId, true);
        if (empty($group)) {
            return $this->notFoundError();
        }
        $perms = $this->getPermissions($editId, true);
        $form->setPermissions($perms);
        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $group);

        return $this->success($form->getFormStructure());
    }

    private function getPermissions($id, $isForm = false)
    {
        $perms = $this->getPerms($id);
        return $this->filterModules($perms, $isForm);
    }

    private function filterModules($perms, $isForm = false)
    {
        $supportModules = NameResource::instance()->getSupportModules();
        if ($integrate = storage()->get('groups_integrate')) {
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
     * Get page permissions
     *
     * @param $iPage
     *
     * @return array
     */
    public function getPerms($iPage)
    {
        $aCallbacks = Phpfox::massCallback('getGroupPerms');

        $aPerms = [];
        $aUserPerms = $this->groupService->getPermsForPage($iPage);
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
        /** @var GroupPermissionForm $form */
        $form = $this->createForm(GroupPermissionForm::class);
        $group = NameResource::instance()->getApiServiceByResourceName(GroupResource::RESOURCE_NAME)->loadResourceById($id, true);
        if (empty($group)) {
            return $this->notFoundError();
        }
        $perms = $this->getPermissions($id, true);
        $form->setPermissions($perms);
        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $group);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => GroupResource::populate([])->getResourceName()
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
        return GroupPermissionResource::populate($item)->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new GroupAccessControl($this->getSetting(), $this->getUser());
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