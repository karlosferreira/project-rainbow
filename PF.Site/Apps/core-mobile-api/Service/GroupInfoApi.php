<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Group\GroupInfoForm;
use Apps\Core_MobileApi\Api\Resource\GroupInfoResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Phpfox;


class GroupInfoApi extends AbstractResourceApi
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
     * GroupInfoApi constructor.
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
        if (Phpfox::isModule('friend')) {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = p.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        $item = $this->database()->select('pt.*, p.title, p.view_id, p.reg_method, p.total_like, p.privacy, ' . Phpfox::getUserField())
            ->from(':pages', 'p')
            ->join(':pages_text', 'pt', 'p.page_id = pt.page_id')
            ->join(':user', 'u', 'u.user_id = p.user_id')
            ->join(':user', 'u2', 'u2.profile_page_id = p.page_id')
            ->where('pt.page_id =' . (int)$params['group_id'] . ' AND p.item_type = ' . $this->facadeService->getItemTypeId())
            ->execute('getSlaveRow');
        if (!$item || $item['view_id'] == '2' || ($item['view_id'] != '0' && !$this->groupService->canModerate() && (Phpfox::getUserId() != $item['user_id']))) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('groups', $item['page_id'], $item['user_id'],
                $item['privacy'], (isset($item['is_friend']) ? $item['is_friend'] : 0), true)) {
            return $this->permissionError();
        }
        if ($item['reg_method'] == 2 && !$this->groupService->isMember($item['page_id'])
            && !Phpfox::isAdmin() && !$this->groupService->isInvited($item['page_id']) && $this->getUser()->getId() != $item['user_id']) {
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
        /** @var GroupInfoForm $form */
        $form = $this->createForm(GroupInfoForm::class, [
            'title'  => 'edit_group_info',
            'action' => UrlUtility::makeApiUrl('group-info/:id', $editId),
            'method' => 'PUT'
        ]);
        $group = $this->loadResourceById($editId, true);
        if (empty($group)) {
            return $this->notFoundError();
        }

        if ($group) {
            $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, $group);
            $form->assignValues($group);
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
        /** @var GroupInfoForm $form */
        $form = $this->createForm(GroupInfoForm::class);
        $group = $this->loadResourceById($id);
        if (empty($group)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, GroupResource::populate($group));

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => GroupResource::populate([])->getResourceName()
                ], [], $this->localization->translate('group_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        return $this->database()->update(':pages_text', [
            'text'        => $this->preParse()->clean($values['text']),
            'text_parsed' => $this->preParse()->prepare($values['text'])
        ], 'page_id = ' . (int)$id);
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
            return GroupResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        return GroupInfoResource::populate($item)->toArray();
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