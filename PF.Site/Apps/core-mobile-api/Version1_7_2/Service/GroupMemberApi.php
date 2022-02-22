<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7_2\Service;

use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\Core_MobileApi\Service\GroupApi;
use Apps\Core_MobileApi\Service\GroupMemberApi as GroupMemberApiOrigin;
use Phpfox;


class GroupMemberApi extends GroupMemberApiOrigin
{
    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $params = $this->resolver->setDefined(['group_id', 'user_id'])
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
        $isRemoveMember = false;
        if (!empty($params['user_id']) && $params['user_id'] != $this->getUser()->getId()) {
            $this->denyAccessUnlessGranted(GroupAccessControl::DELETE_MEMBER, GroupResource::populate($group));
            $isRemoveMember = true;
        }
        elseif (!$this->groupService->isMember($group['page_id'])) {
            return $this->error();
        }
        if (Phpfox::getService('like.process')->delete('groups', $params['group_id'], (int)$params['user_id'])) {
            $pageApi = (new GroupApi());
            $group = $pageApi->loadResourceById($group['page_id']);
            $pageProfileMenu = $pageApi->getProfileMenus($group['page_id']);
            if ($isRemoveMember) {
                return $this->success([
                    'id' => (int)$params['user_id']
                ], [], $this->getLocalization()->translate('member_deleted_successfully'));
            } else {
                return $this->success([
                    'id' => (int)$params['group_id'],
                    'membership' => GroupResource::NO_JOIN,
                    'total_like' => $group['total_like'],
                    'profile_menus' => $pageProfileMenu,
                    'post_types' => $pageApi->getPostTypes($group['page_id'])
                ], [], $this->getLocalization()->translate('un_joined_successfully'));
            }
        }
        return $this->permissionError();
    }
}