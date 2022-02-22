<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_8\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\Core_MobileApi\Service\PageApi as BasePageApi;
use Apps\Core_MobileApi\Version1_8\Api\Form\Page\ReassignOwnerForm;


class PageApi extends BasePageApi
{

    public function getReassignOwnerForm($params)
    {
        $id = $this->resolver->resolveId($params);
        $page = $this->loadResourceById($id, true);
        if (!$page) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::REASSIGN_OWNER, $page);
        /** @var ReassignOwnerForm $form */
        $form = $this->createForm(ReassignOwnerForm::class, [
            'title'  => 'reassign_owner',
            'action' => UrlUtility::makeApiUrl('pages/reassign-owner/:id', $id),
            'method' => 'POST'
        ]);
        $form->setUserId($this->getUser()->getId());
        $form->assignValues($page);
        return $this->success($form->getFormStructure());
    }

    public function reassignOwner($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var ReassignOwnerForm $form */
        $form = $this->createForm(ReassignOwnerForm::class);

        $page = $this->loadResourceById($id, true);
        if (empty($page)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::REASSIGN_OWNER, $page);
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