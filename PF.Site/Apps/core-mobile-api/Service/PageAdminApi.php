<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\PageAdminResource;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Pages\Service\Facade;
use Apps\Core_Pages\Service\Pages;
use Apps\Core_Pages\Service\Process;
use Phpfox;


class PageAdminApi extends AbstractResourceApi
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
     * PageAdminApi constructor.
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
            'page_id', 'limit', 'page', 'q', 'is_manage'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('page_id', 'int')
            ->setRequired(['page_id'])
            ->setDefault([
                'page'  => 1,
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE
            ])
            ->resolve($params)->getParameters();
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
        if ($page['view_id'] != '0' && !(Phpfox::getUserParam('pages.can_approve_pages') || Phpfox::getUserParam('pages.can_edit_all_pages') ||
                Phpfox::getUserParam('pages.can_delete_all_pages') || $page['is_admin'] || $page['user_id'] == $this->getUser()->getId())
        ) {
            return $this->permissionError();
        }
        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('pages', $page['page_id'], $page['user_id'],
                $page['privacy'], (isset($page['is_friend']) ? $page['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        if ($page['user_id'] != $this->getUser()->getId() && !$page['is_admin'] && !Phpfox::getUserParam('pages.can_edit_all_pages') && !$this->pageService->hasPerm($params['page_id'], 'pages.view_admins')) {
            return $this->permissionError();
        }
        if (!empty($params['is_manage'])) {
            $admins = $this->database()->select(Phpfox::getUserField() . ', pa.page_id')
                ->from(Phpfox::getT('pages_admin'), 'pa')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
                ->where('pa.page_id = ' . (int)$params['page_id'])
                ->execute('getSlaveRows');
        } else {
            $admins = $this->pageService->getPageAdmins($params['page_id'], empty($params['page']) ? 1 : $params['page'], empty($params['limit']) ? null : $params['limit'], $params['q']);
        }
        if (!empty($admins) && empty($params['is_manage'])) {
            foreach ($admins as $key => $admin) {
                $admins[$key]['page_id'] = $params['page_id'];
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
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $params = $this->resolver->setDefined(['user_ids', 'page_id'])
            ->setRequired(['page_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($params['page_id'], true);
        if (empty($page)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $page);
        $id = $this->processCreate($params, $page);
        if ($id) {
            return $this->success([
                'id' => $id
            ], [], $this->getLocalization()->translate('page_successfully_updated'));
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    private function processCreate($values, PageResource $page)
    {
        $iId = $values['page_id'];
        $aOldAdmins = $this->database()->select('user_id')->from(':pages_admin')->where(['page_id' => (int)$iId])->executeRows();
        $aOldAdminIds = array_column($aOldAdmins, 'user_id');
        $aAdmins = !is_array($values['user_ids']) ? explode(',', $values['user_ids']) : $values['user_ids'];
        $bPass = false;
        if (count($aAdmins)) {
            foreach ($aAdmins as $iAdmin) {
                if (!Phpfox::getService('user')->isUser($iAdmin, true)) {
                    continue;
                }
                if ($page->getAuthor()->getId() == $iAdmin) {
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

                $this->cache()->remove('admin_' . $iAdmin . '_pages');

                $bPass = true;
                $aOldAdminIds[] = $iAdmin;
            }
            $this->cache()->remove('pages_' . $iId . '_admins');
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
            'page_id'  => $params['id'],
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
            ->setRequired(['page_id', 'user_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($params['page_id']);
        $admin = $this->database()
            ->select('*')
            ->from(':pages_admin')
            ->where('page_id = ' . (int)$params['page_id'] . ' AND user_id = ' . (int)$params['user_id'])
            ->execute('getSlaveRow');
        if (!$page || !$admin) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('pages.can_view_browse_pages') && ($page['user_id'] == Phpfox::getUserId() || $this->pageService->isAdmin($page) || Phpfox::getUserParam('pages.can_edit_all_pages'))) {
            $this->database()->delete(':pages_admin', 'user_id = ' . (int)$params['user_id'] . ' AND page_id = ' . (int)$params['page_id']);
            $this->cache()->remove('pages_' . $params['page_id'] . '_admins');
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
        // TODO: Implement loadResourceById() method.
    }

    public function processRow($item)
    {
        return PageAdminResource::populate($item)->displayShortFields()->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new PageAccessControl($this->getSetting(), $this->getUser());
    }

    public function searchFriendFilter($id, $friends)
    {
        $aAdmins = $this->pageService->getPageAdmins($id);
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