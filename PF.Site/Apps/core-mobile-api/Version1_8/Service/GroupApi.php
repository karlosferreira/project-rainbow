<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_8\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Security\Group\GroupAccessControl;
use Apps\Core_MobileApi\Service\GroupApi as BaseGroupApi;
use Apps\Core_MobileApi\Version1_8\Api\Form\Group\ReassignOwnerForm;


class GroupApi extends BaseGroupApi
{

    public function getReassignOwnerForm($params)
    {
        $id = $this->resolver->resolveId($params);
        $group = $this->loadResourceById($id, true);
        if (!$group) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(GroupAccessControl::REASSIGN_OWNER, $group);
        /** @var ReassignOwnerForm $form */
        $form = $this->createForm(ReassignOwnerForm::class, [
            'title'  => 'reassign_owner',
            'action' => UrlUtility::makeApiUrl('groups/reassign-owner/:id', $id),
            'method' => 'POST'
        ]);
        $form->setUserId($this->getUser()->getId());
        $form->assignValues($group);
        return $this->success($form->getFormStructure());
    }

    public function reassignOwner($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var ReassignOwnerForm $form */
        $form = $this->createForm(ReassignOwnerForm::class);

        $group = $this->loadResourceById($id, true);
        if (empty($group)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(GroupAccessControl::REASSIGN_OWNER, $group);
        if ($form->isValid() && ($values = $form->getValues())) {
            $userId = !empty($values['user_id']) && is_array($values['user_id']) ? end($values['user_id']) : $values['user_id'];
            if ($this->processService->reassignOwner($id, (int)trim($userId, ','))) {
                return $this->success([
                    'id' => $id
                ], [], $this->getLocalization()->translate('reassign_owner_successfully'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }
}