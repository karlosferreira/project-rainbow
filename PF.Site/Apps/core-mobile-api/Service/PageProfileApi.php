<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Page\PageProfileForm;
use Apps\Core_MobileApi\Api\Resource\PageCategoryResource;
use Apps\Core_MobileApi\Api\Resource\PageProfileResource;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Resource\PageTypeResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Apps\PHPfox_Groups\Service\Type;
use Phpfox;


class PageProfileApi extends AbstractResourceApi
{

    /**
     * @var Facade
     */
    private $facadeService;

    /**
     * @var Groups
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
     * GroupApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('pages.facade');
        $this->pageService = Phpfox::getService('pages');
        $this->processService = Phpfox::getService('pages.process');
        $this->typeService = Phpfox::getService('pages.type');
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
        if (Phpfox::isModule('friend')) {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = p.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'' . $this->facadeService->getItemType() . '\' AND l.item_id = p.page_id AND l.user_id = ' . Phpfox::getUserId());
        }
        $item = $this->database()->select('p.*, ' . Phpfox::getUserField())
            ->from(':pages', 'p')
            ->join(':user', 'u', 'u.user_id = p.user_id')
            ->join(':user', 'u2', 'u2.profile_page_id = p.page_id')
            ->where('p.page_id =' . (int)$params['page_id'] . ' AND p.item_type = ' . $this->facadeService->getItemTypeId())
            ->execute('getSlaveRow');
        if (!$item || $item['view_id'] == '2' || ($item['view_id'] != '0' && !(Phpfox::getUserParam('pages.can_approve_pages') || Phpfox::getUserParam('pages.can_edit_all_pages') ||
                    Phpfox::getUserParam('pages.can_delete_all_pages') || $item['is_admin']))) {
            return $this->notFoundError();
        }
        if ($item['page_id'] == Phpfox::getUserBy('profile_page_id')) {
            $item['is_liked'] = true;
        }
        if (!isset($item['is_liked'])) {
            $item['is_liked'] = false;
        }
        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('pages', $item['page_id'], $item['user_id'],
                $item['privacy'], (isset($item['is_friend']) ? $item['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        if ($item['reg_method'] == 2 && !$this->pageService->isMember($item['page_id'])
            && !Phpfox::isAdmin() && !$this->pageService->isInvited($item['page_id']) && $this->getUser()->getId() != $item['user_id']) {
            return $this->permissionError();
        }
        $item = $this->processRow($item);
        return $this->success($item);
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
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $editId = $this->resolver->resolveId($params);
        /** @var PageProfileForm $form */
        $form = $this->createForm(PageProfileForm::class, [
            'title'  => 'edit_page_detail',
            'action' => UrlUtility::makeApiUrl('page-profile/:id', $editId),
            'method' => 'PUT'
        ]);
        $form->setCategories(NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->getCategories());
        $page = $this->loadResourceById($editId, true);
        if (empty($page)) {
            return $this->notFoundError();
        }

        if ($page) {
            $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $page);
            $form->assignValues($page);
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
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var PageProfileForm $form */
        $form = $this->createForm(PageProfileForm::class);
        $page = $this->loadResourceById($id);
        if (empty($page)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, PageResource::populate($page));

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => PageResource::populate([])->getResourceName()
                ], [], $this->localization->translate('page_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        $type = null;
        if (!empty($values['type_category'])) {
            foreach ($values['type_category'] as $val) {
                if (strpos($val, 'type_') > -1) {
                    $values['type'] = str_replace('type_', '', $val);
                } else if (strpos($val, 'category_') > -1) {
                    $values['category'] = str_replace('category_', '', $val);
                }
            }
        }
        if (!empty($values['type'])) {
            $type = NameResource::instance()->getApiServiceByResourceName(PageTypeResource::RESOURCE_NAME)->loadResourceById($values['type']);
            if (empty($type) || empty($type['is_active'])) {
                //Not valid group type
                return $this->notFoundError($this->getLocalization()->translate('page_type_is_not_found'));
            }
        } else {
            return $this->error($this->getLocalization()->translate('page_type_is_required'));
        }
        if (!empty($values['category'])) {
            $category = NameResource::instance()->getApiServiceByResourceName(PageCategoryResource::RESOURCE_NAME)->loadResourceById($values['category'], false, $values['type']);
            if (empty($category) || empty($category['is_active'])) {
                //Not valid group category
                return $this->notFoundError($this->getLocalization()->translate('page_category_does_not_exist_or_does_not_belonging_to_type_name', ['name' => isset($type['name']) ? $type['name'] : '']));
            }
        }
        $aUpdate = [
            'type_id'     => (isset($values['type']) ? (int)$values['type'] : '0'),
            'category_id' => (isset($values['category']) ? (int)$values['category'] : 0),
            'title'       => Phpfox::getLib('parse.input')->clean($values['title'])
        ];
        $this->database()->update(':user',
            ['full_name' => Phpfox::getLib('parse.input')->clean($values['title'], 255)],
            'profile_page_id = ' . (int)$id);
        return $this->database()->update(':pages', $aUpdate, 'page_id = ' . (int)$id);
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
     * @param $returnResource
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->database()->select('*')
            ->from(':pages')
            ->where('item_type = ' . $this->facadeService->getItemTypeId() . ' AND page_id = ' . (int)$id)
            ->execute('getSlaveRow');
        if (empty($item['page_id'])) {
            return null;
        }
        if ($returnResource) {
            $item['is_form'] = true;
            return PageResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        return PageProfileResource::populate($item)->toArray();
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