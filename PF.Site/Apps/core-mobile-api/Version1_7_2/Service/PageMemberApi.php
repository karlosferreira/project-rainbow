<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7_2\Service;

use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\Core_MobileApi\Service\PageApi;
use Apps\Core_MobileApi\Service\PageMemberApi as PageMemberApiOrigin;
use Phpfox;


class PageMemberApi extends PageMemberApiOrigin
{
    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $params = $this->resolver->setDefined(['page_id', 'user_id'])
            ->setRequired(['page_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        $page = $this->pageService->getForView($params['page_id']);
        if (!$page) {
            return $this->notFoundError();
        }
        $isRemoveMember = false;
        if (!empty($params['user_id']) && $params['user_id'] != $this->getUser()->getId()) {
            $this->denyAccessUnlessGranted(PageAccessControl::DELETE_MEMBER, PageResource::populate($page));
            $isRemoveMember = true;
        }
        elseif (!$this->pageService->isMember($page['page_id'])) {
            return $this->error();
        }
        if (Phpfox::getService('like.process')->delete('pages', $params['page_id'], (int)$params['user_id'])) {
            $pageApi = (new PageApi());
            $page = $pageApi->loadResourceById($page['page_id']);
            $pageProfileMenu = $pageApi->getProfileMenus($page['page_id']);
            if ($isRemoveMember) {
                return $this->success([
                    'id' => (int)$params['user_id']
                ], [], $this->getLocalization()->translate('member_deleted_successfully'));
            } else {
                return $this->success([
                    'id' => (int)$page['page_id'],
                    'total_like' => $page['total_like'],
                    'membership' => PageResource::NO_LIKE,
                    'profile_menus' => $pageProfileMenu,
                    'post_types' => $pageApi->getPostTypes($page['page_id'])
                ], [], $this->getLocalization()->translate('un_liked_successfully'));
            }
        }
        return $this->permissionError();
    }
}