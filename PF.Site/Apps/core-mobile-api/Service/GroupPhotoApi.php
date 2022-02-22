<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Group\GroupPhotoForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\GroupPhotoResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\PHPfox_Groups\Service\Facade;
use Apps\PHPfox_Groups\Service\Groups;
use Apps\PHPfox_Groups\Service\Process;
use Phpfox;


class GroupPhotoApi extends AbstractResourceApi
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
     * GroupPhotoApi constructor.
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
        $item = $this->database()->select('p.page_id, p.view_id, p.reg_method, p.total_like, p.image_path, p.image_server_id, p.cover_photo_id, p.cover_photo_position, p.user_id, p.privacy')
            ->from(':pages', 'p')
            ->join(':user', 'u', 'u.user_id = p.user_id')
            ->join(':user', 'u2', 'u2.profile_page_id = p.page_id')
            ->where('p.page_id =' . (int)$params['group_id'] . ' AND p.item_type = ' . $this->facadeService->getItemTypeId())
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
        /** @var GroupPhotoForm $form */
        $form = $this->createForm(GroupPhotoForm::class, [
            'title'  => 'edit_group',
            'action' => UrlUtility::makeApiUrl('group-photo/:id', $editId),
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
        /** @var GroupPhotoForm $form */
        $form = $this->createForm(GroupPhotoForm::class);
        $group = $this->loadResourceById($id);
        if (empty($group)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(GroupAccessControl::EDIT, GroupResource::populate($group));

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values, $group);
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

    private function processUpdate($id, $values, $group)
    {
        if ($values['file']['status'] == FileType::NEW_UPLOAD || $values['file']['status'] == FileType::CHANGE) {
            $values['temp_file'] = $values['file']['temp_file'];
        } else if ($values['file']['status'] == FileType::REMOVE) {
            $values['remove_photo'] = 1;
        }
        if (empty($values['temp_file']) && empty($values['remove_photo'])) {
            return $this->validationParamsError(['file']);
        }
        $bPass = false;
        if (!empty($values['temp_file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($values['temp_file']);
            if (empty($aFile)) {
                return $this->notFoundError($this->getLocalization()->translate('file_not_found'));
            }
        }
        $aUpdate = [];
        // remove old image
        if (!empty($group['image_path']) && (!empty($values['temp_file']) || !empty($values['remove_photo'])) && $this->processService->deleteImage($group)) {
            $bPass = true;
            $aUpdate['image_path'] = null;
            $aUpdate['image_server_id'] = 0;
        }
        $user = $this->database()->select('user_id')
            ->from(Phpfox::getT('user'))
            ->where('profile_page_id = ' . (int)$id)
            ->execute('getSlaveRow');
        if (!empty($aFile)) {
            $bPass = true;
            // get image from temp file
            if (!Phpfox::getService('user.space')->isAllowedToUpload($group['user_id'], $aFile['size'])) {
                Phpfox::getService('core.temp-file')->delete($values['temp_file'], true);

                return false;
            }
            $aUpdate['image_path'] = $aFile['path'];
            $aUpdate['image_server_id'] = $aFile['server_id'];
            $aUpdate['item_type'] = $this->facadeService->getItemTypeId();
            Phpfox::getService('user.space')->update($group['user_id'], 'groups', $aFile['size']);
            Phpfox::getService('core.temp-file')->delete($values['temp_file']);
            // change profile image of page
            define('PHPFOX_PAGES_IS_IN_UPDATE', true);
            $iServerId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');
            $sPath = Phpfox::getParam('pages.dir_image') . sprintf($aFile['path'], '');

            if (!empty($iServerId)) {
                $sFileUrl = Phpfox::getLib('cdn')->getUrl(str_replace(PHPFOX_DIR, '', $sPath), $iServerId);
                //Download to local server to process
                file_put_contents($sPath, fox_get_contents($sFileUrl));
                //Remove this temp file after process end
                register_shutdown_function(function () use ($sPath) {
                    @unlink($sPath);
                });
            }

            Phpfox::getService('user.process')->uploadImage($user['user_id'], true, $sPath);

            // add feed after updating page's profile image
            $iGroupUserId = Phpfox::getService('groups')->getUserId($id);
            if (Phpfox::isModule('feed') && $oProfileImage = storage()->get('user/avatar/' . $iGroupUserId, null)) {
                Phpfox::getService('feed.process')->callback([
                    'table_prefix'     => 'pages_',
                    'module'           => 'groups',
                    'add_to_main_feed' => true,
                    'has_content'      => true
                ])->add('groups_photo', $oProfileImage->value, 0, 0, $id, $iGroupUserId);
            }
        }

        return $bPass ? $this->database()->update(':pages', $aUpdate, 'page_id = ' . (int)$id) : false;
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
        $itemId = $this->resolver->resolveId($params);
        $item = $this->loadResourceById($itemId);
        if (!$itemId || !$item) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('pf_group_browse') && ($item['user_id'] == Phpfox::getUserId() || $this->groupService->isAdmin($item) || Phpfox::getUserParam('groups.can_edit_all_groups'))) {
            if ($this->processService->deleteImage($item)) {
                $this->database()->update(':pages', [
                    'image_path'      => null,
                    'image_server_id' => 0
                ], 'page_id = ' . (int)$itemId);
                return $this->success([], [], $this->getLocalization()->translate('group_successfully_updated'));
            }
        }
        return $this->permissionError();
    }

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
        return GroupPhotoResource::populate($item)->toArray();
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
        return null;
    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
    }
}