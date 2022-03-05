<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7_3\Service;

use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\GroupApi;
use Apps\Core_MobileApi\Version1_7_2\Service\GroupMemberApi as GroupMemberApiOrigin;
use Phpfox;


class GroupMemberApi extends GroupMemberApiOrigin
{
    public function deleteMemberRequest($params)
    {
        $params = $this->resolver->setRequired(['group_id'])
            ->setDefined(['user_id'])
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
        $isOwnerRequest = false;
        if (empty($params['user_id'])) {
            $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
            $params['user_id'] = $this->getUser()->getId();
            $isOwnerRequest = true;
        } elseif (!$this->facadeService->getItems()->isAdmin($item)) {
            return $this->permissionError();
        }
        $signUp = $this->database()->select('ps.*')
            ->from(':pages_signup', 'ps')
            ->join(':pages', 'p', 'p.page_id = ps.page_id')
            ->where([
                'ps.user_id' => $params['user_id'],
                'ps.page_id' => $params['group_id']
            ])
            ->execute('getSlaveRow');
        if (empty($signUp)) {
            return $this->notFoundError();
        }
        if (!$isOwnerRequest) {
            Phpfox::getService('notification.process')->delete('groups_register', $signUp['signup_id'], $this->getUser()->getId());
        }
        $this->database()->delete(Phpfox::getT('pages_signup'), 'signup_id =' . (int)$signUp['signup_id']);
        $this->cache()->remove('groups_' . $params['group_id'] . '_pending_users');
        return $this->success($isOwnerRequest ? ['membership' => 0] : ['is_pending' => false], [],
            $isOwnerRequest ? $this->getLocalization()->translate('successfully_deleted_request_register_for_this_group') : '');
    }
}