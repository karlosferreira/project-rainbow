<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\GroupMemberResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Phpfox;


class GroupMemberApi extends AbstractResourceApi
{

    /**
     * @var Facade
     */
    protected $facadeService;

    /**
     * @var Groups
     */
    protected $groupService;
    /**
     * @var Process
     */
    protected $processService;

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

    public function __naming()
    {
        return [
            'group-member/request' => [
                'put'    => 'approveMemberRequest',
                'delete' => 'deleteMemberRequest'
            ],
        ];
    }

    /**
     * @param array $params
     *
     * @return mixed
     */

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'group_id', 'limit', 'page', 'q', 'view'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('group_id', 'int')
            ->setRequired(['group_id'])
            ->setAllowedValues('view', ['all', 'pending', 'admin'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1,
                'view'  => 'all'
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
        if (!$group || $group['view_id'] == '2' || ($group['view_id'] != '0'
                && !$this->groupService->canModerate() && ($this->getUser()->getId() != $group['user_id']))) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('groups', $group['page_id'], $group['user_id'],
                $group['privacy'], (isset($group['is_friend']) ? $group['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        if ($group['reg_method'] == 2 && !$this->groupService->isMember($group['page_id'])
            && !Phpfox::isAdmin() && !$this->groupService->isInvited($group['page_id']) && $this->getUser()->getId() != $group['user_id']) {
            return $this->permissionError();
        }
        switch ($params['view']) {
            case 'pending':
                if (!$this->groupService->isAdmin($group)) {
                    return $this->permissionError();
                }
                $members = $this->groupService->getPendingUsers($params['group_id'], false, empty($params['page']) ? 1 : $params['page'], empty($params['limit']) ? null : $params['limit'], $params['q']);
                break;
            case 'admin':
                $this->denyAccessUnlessGranted(GroupAccessControl::VIEW_ADMIN, GroupResource::populate($group));
                $members = $this->groupService->getPageAdmins($params['group_id'], empty($params['page']) ? 1 : $params['page'], empty($params['limit']) ? null : $params['limit'], $params['q']);
                break;
            default:
                list(, $members) = $this->groupService->getMembers($params['group_id'], empty($params['limit']) ? null : $params['limit'], empty($params['page']) ? 1 : $params['page'], $params['q']);
                break;
        }
        if (!empty($members)) {
            $members = array_map(function($member) use ($params) {
                $member['page_id'] = $params['group_id'];
                if ($params['view'] == 'admin') {
                    $member['is_admin'] = 1;
                }
                return $member;
            }, $members);
        }
        $this->processRows($members);

        return $this->success($members);
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
        $params = $this->resolver->setDefined(['group_id'])
            ->setRequired(['group_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        $group = $this->groupService->getForView($params['group_id']);
        if (!$group) {
            return $this->notFoundError();
        }
        if ($this->groupService->isMember($group['page_id'])) {
            return $this->error();
        }
        $isInvited = $this->groupService->isInvited($params['group_id']);
        if ($group['reg_method'] == 2 && !$isInvited && $this->getUser()->getId() != $group['user_id']) {
            return $this->permissionError();
        }
        if (!$isInvited && Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('groups', $group['page_id'], $group['user_id'],
                $group['privacy'], (isset($group['is_friend']) ? $group['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        $result = $this->processCreate($group, $isInvited);
        if (is_array($result)) {
            return $this->success($result['data'], [], $result['message']);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    private function processCreate($group, $isInvited = false)
    {
        if ($group['reg_method'] == 0 || $isInvited || $this->getUser()->getId() == $group['user_id']) {
            if (Phpfox::getService('like.process')->add('groups', $group['page_id'])) {
                $pageApi = (new GroupApi());
                $group = $pageApi->loadResourceById($group['page_id']);
                $pageProfileMenu = $pageApi->getProfileMenus($group['page_id']);
                return [
                    'data'    => [
                        'id'            => (int)$group['page_id'],
                        'total_like'    => $group['total_like'],
                        'membership'    => GroupResource::JOINED,
                        'profile_menus' => $pageProfileMenu,
                        'post_types'    => $pageApi->getPostTypes($group['page_id'])
                    ],
                    'message' => $this->getLocalization()->translate('joined_successfully')
                ];
            } else {
                return false;
            }
        } else {
            if ($this->processService->register($group['page_id'])) {
                return [
                    'data'    => [
                        'id'         => (int)$group['page_id'],
                        'membership' => GroupResource::REQUESTED,
                        'total_like' => (int)$group['total_like'],
                    ],
                    'message' => $this->getLocalization()->translate('Successfully registered for this group. Your membership is pending an admins approval. As soon as your membership has been approved you will be notified.')
                ];
            } else {
                return false;
            }
        }
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
        $params = $this->resolver->setDefined(['group_id'])
            ->setRequired(['group_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        $group = $this->groupService->getForView($params['group_id']);
        if (!$group) {
            return $this->notFoundError();
        }
        if (!$this->groupService->isMember($group['page_id'])) {
            return $this->error();
        }
        if (Phpfox::getService('like.process')->delete('groups', $params['group_id'])) {
            $pageApi = (new GroupApi());
            $group = $pageApi->loadResourceById($group['page_id']);
            $pageProfileMenu = $pageApi->getProfileMenus($group['page_id']);
            return $this->success([
                'id'            => (int)$params['group_id'],
                'membership'    => GroupResource::NO_JOIN,
                'total_like'    => $group['total_like'],
                'profile_menus' => $pageProfileMenu,
                'post_types'    => $pageApi->getPostTypes($group['page_id'])
            ], [], $this->getLocalization()->translate('un_joined_successfully'));
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
        // TODO: Implement loadResourceById() method.
    }

    public function processRow($item)
    {
        return GroupMemberResource::populate($item)
            ->setExtra([
                'can_view_remove_friend_link' => $this->getSetting()->getUserSetting('friend.link_to_remove_friend_on_profile')
            ])
            ->displayShortFields()->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new GroupAccessControl($this->getSetting(), $this->getUser());
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', GroupMemberResource::RESOURCE_NAME);
        $module = 'groups';
        return [
            [
                'path'      => 'groups/:id/members',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'groups, groups/',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => 'group_home',
                ]
            ]
        ];
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

    public function approveMemberRequest($params)
    {
        $params = $this->resolver->setRequired(['user_id', 'group_id'])
            ->setAllowedTypes('user_id', 'int')
            ->setAllowedTypes('group_id', 'int')
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $item = (new GroupApi())->loadResourceById($params['group_id']);
        if (!isset($item['page_id'])) {
            return $this->notFoundError($this->getLocalization()->translate('unable_to_find_the_page'));
        }
        if (!$this->facadeService->getItems()->isAdmin($item)) {
            return $this->permissionError();
        }
        $signup = $this->database()->select('p.*, ps.user_id AS post_user_id, ps.signup_id')
            ->from(Phpfox::getT('pages_signup'), 'ps')
            ->join(Phpfox::getT('pages'), 'p', 'p.page_id = ps.page_id')
            ->where([
                'ps.user_id' => $params['user_id'],
                'ps.page_id' => $params['group_id']
            ])
            ->execute('getSlaveRow');
        if (empty($signup)) {
            return $this->notFoundError();
        }
        Phpfox::getService('like.process')->add('groups', $params['group_id'],
            $params['user_id'], null, ['ignoreCheckPermission' => true]);
        Phpfox::getService('notification.process')->delete('groups_register', $signup['signup_id'], $this->getUser()->getId());
        $this->database()->delete(Phpfox::getT('pages_signup'), [
            'user_id' => $params['user_id'],
            'page_id' => $params['group_id']
        ]);
        $this->cache()->remove('groups_' . $params['group_id'] . '_pending_users');
        return $this->success([
            'is_pending' => false,
        ]);
    }

    public function deleteMemberRequest($params)
    {
        $params = $this->resolver->setRequired(['user_id', 'group_id'])
            ->setAllowedTypes('user_id', 'int')
            ->setAllowedTypes('group_id', 'int')
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $item = (new GroupApi())->loadResourceById($params['group_id']);
        if (!isset($item['page_id'])) {
            return $this->notFoundError($this->getLocalization()->translate('unable_to_find_the_page'));
        }
        if (!$this->facadeService->getItems()->isAdmin($item)) {
            return $this->permissionError();
        }
        $signup = $this->database()->select('ps.*')
            ->from(Phpfox::getT('pages_signup'), 'ps')
            ->join(Phpfox::getT('pages'), 'p', 'p.page_id = ps.page_id')
            ->where([
                'ps.user_id' => $params['user_id'],
                'ps.page_id' => $params['group_id']
            ])
            ->execute('getSlaveRow');
        if (empty($signup)) {
            return $this->notFoundError();
        }

        Phpfox::getService('notification.process')->delete('groups_register', $signup['signup_id'], $this->getUser()->getId());
        $this->database()->delete(Phpfox::getT('pages_signup'), 'signup_id =' . (int)$signup['signup_id']);
        $this->cache()->remove('groups_' . $params['group_id'] . '_pending_users');
        return $this->success([
            'is_pending' => false,
        ]);
    }
}