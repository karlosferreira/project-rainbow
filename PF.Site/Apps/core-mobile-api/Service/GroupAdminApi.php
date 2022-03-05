<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\GroupAdminResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Phpfox;


class GroupAdminApi extends AbstractResourceApi
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
     * GroupAdminApi constructor.
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
            'group_id', 'limit', 'page', 'q', 'is_manage'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('group_id', 'int')
            ->setRequired(['group_id'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('pf_group_browse')) {
            return $this->permissionError();
        }
        $group = $this->groupService->getForView($params['group_id']);
        if (!$group) {
            return $this->notFoundError();
        }
        if ($group['view_id'] == '2' || ($group['view_id'] != '0' && !$this->groupService->canModerate() && (Phpfox::getUserId() != $group['user_id']))) {
            return $this->permissionError();
        }
        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('groups', $group['page_id'], $group['user_id'],
                $group['privacy'], (isset($group['is_friend']) ? $group['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        if ($group['reg_method'] == 2 && !$this->groupService->isMember($group['page_id'])
            && !Phpfox::isAdmin() && !$this->groupService->isInvited($group['page_id']) && $this->getUser()->getId() != $group['user_id']) {
            return $this->permissionError();
        }
        if ($group['user_id'] != Phpfox::getUserId() && !$this->groupService->isAdmin($group) && !Phpfox::getUserParam('groups.can_edit_all_groups') && !$this->groupService->hasPerm($params['group_id'], 'groups.view_admins')) {
            return $this->permissionError();
        }
        if (!empty($params['is_manage'])) {
            $admins = $this->database()->select(Phpfox::getUserField() . ', pa.page_id as group_id')
                ->from(Phpfox::getT('pages_admin'), 'pa')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
                ->where('pa.page_id = ' . (int)$params['group_id'])
                ->execute('getSlaveRows');
        } else {
            $admins = $this->groupService->getPageAdmins($params['group_id'], empty($params['page']) ? 1 : $params['page'], empty($params['limit']) ? null : $params['limit'], $params['q']);
        }
        if (!empty($admins) && empty($params['is_manage'])) {
            foreach ($admins as $key => $admin) {
                $admins[$key]['group_id'] = $params['group_id'];
            }
        }
        $this->processRows($admins);
        return $this->success($admins);
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
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $params = $this->resolver->setDefined(['user_ids', 'group_id'])
            ->setRequired(['group_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        $group = NameResource::instance()->getApiServiceByResourceName(GroupResource::RESOURCE_NAME)->loadResourceById($params['group_id'], true);
        if (empty($group)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $group);
        $id = $this->processCreate($params, $group);
        if ($id) {
            return $this->success([
                'id' => $id
            ], [], $this->getLocalization()->translate('group_successfully_updated'));
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    private function processCreate($values, GroupResource $group)
    {
        $iId = $values['group_id'];
        $aOldAdmins = $this->database()->select('user_id')->from(':pages_admin')->where(['page_id' => (int)$iId])->executeRows();
        $aOldAdminIds = array_column($aOldAdmins, 'user_id');
        $aAdmins = !is_array($values['user_ids']) ? explode(',', $values['user_ids']) : $values['user_ids'];
        $bPass = false;
        if (count($aAdmins)) {
            foreach ($aAdmins as $iAdmin) {
                if (!Phpfox::getService('user')->isUser($iAdmin, true)) {
                    continue;
                }
                if ($group->getAuthor()->getId() == $iAdmin) {
                    continue;
                }

                // If already admin, skip it.
                if (in_array($iAdmin, $aOldAdminIds)) {
                    continue;
                }

                //Add to member first
                $sType = $this->facadeService->getItemType();
                //Check is liked
                $iCnt = $this->database()->select('COUNT(*)')
                    ->from(':like')
                    ->where('type_id="' . $sType . '" AND item_id=' . (int)$iId . " AND user_id=" . (int)$iAdmin)
                    ->executeField();
                if (!$iCnt) {
                    Phpfox::getService('like.process')->add($sType, $iId, $iAdmin);
                }

                Phpfox::getService('notification.process')->add($this->facadeService->getItemType() . '_invite_admin',
                    $iId, $iAdmin);

                //Then add to admin
                $this->database()->insert(Phpfox::getT('pages_admin'), ['page_id' => $iId, 'user_id' => $iAdmin]);

                $this->cache()->remove('admin_' . $iAdmin . '_groups');

                $bPass = true;
                $aOldAdminIds[] = $iAdmin;
            }
            $this->cache()->remove('groups_' . $iId . '_admins');
        } else {
            $bPass = true;
        }
        return $bPass ? $iId : false;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $params = $this->resolver
            ->setRequired(['id', 'user_ids'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        return $this->create([
            'group_id' => $params['id'],
            'user_ids' => $params['user_ids']
        ]);
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
            ->setRequired(['group_id', 'user_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $group = NameResource::instance()->getApiServiceByResourceName(GroupResource::RESOURCE_NAME)->loadResourceById($params['group_id']);

        $admin = $this->database()
            ->select('*')
            ->from(':pages_admin')
            ->where('page_id = ' . (int)$params['group_id'] . ' AND user_id = ' . (int)$params['user_id'])
            ->execute('getSlaveRow');
        if (!$group || !$admin) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('pf_group_browse') && ($group['user_id'] == Phpfox::getUserId() || $this->groupService->isAdmin($group) || Phpfox::getUserParam('groups.can_edit_all_groups'))) {
            $this->database()->delete(':pages_admin', 'user_id = ' . (int)$params['user_id'] . ' AND page_id = ' . (int)$params['group_id']);
            $this->cache()->remove('groups_' . $params['group_id'] . '_admins');
            return $this->success([], [], $this->getLocalization()->translate('admin_successfully_deleted'));
        }
        return $this->permissionError();
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
        return null;
    }

    public function processRow($item)
    {
        return GroupAdminResource::populate($item)->displayShortFields()->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new GroupAccessControl($this->getSetting(), $this->getUser());
    }

    public function searchFriendFilter($id, $friends)
    {
        $aAdmins = $this->groupService->getPageAdmins($id);
        $aAdminId = [];
        if (!empty($aAdmins)) {
            $aAdminId = array_map(function ($value) {
                return $value['user_id'];
            }, $aAdmins);
        }
        if (!empty($aAdminId)) {
            foreach ($friends as $iKey => $friend) {
                if (in_array($friend['user_id'], $aAdminId)) {
                    unset($friends[$iKey]);
                }
            }
        }
        return $friends;
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