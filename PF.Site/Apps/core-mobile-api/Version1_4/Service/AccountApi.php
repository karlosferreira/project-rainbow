<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_4\Service;


use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Resource\AccountResource;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Service\SubscriptionApi;
use Apps\Core_MobileApi\Version1_4\Api\Form\User\AccountSettingForm;
use Phpfox;
use Phpfox_Plugin;

class AccountApi extends \Apps\Core_MobileApi\Service\AccountApi
{
    function formSetting()
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);

        $user = $this->userService->get($this->getUser()->getId(), true);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        if ($this->getSetting()->getAppSetting('user.split_full_name') && empty($user['first_name']) && empty($user['last_name'])) {
            preg_match('/(.*) (.*)/', $user['full_name'], $aNameMatches);
            if (isset($aNameMatches[1]) && isset($aNameMatches[2])) {
                $user['first_name'] = $aNameMatches[1];
                $user['last_name'] = $aNameMatches[2];
            } else {
                $user['first_name'] = $user['full_name'];
            }
        }
        /** @var AccountSettingForm $form */
        $form = $this->createForm(AccountSettingForm::class, [
            'title'  => 'account_settings',
            'method' => 'PUT',
            'action' => UrlUtility::makeApiUrl('account/setting')
        ]);
        $form->setGateways($this->getSettingGateways($user));
        $form->setCanChangeFullName($this->allowChangeFullName($user, $form));
        $form->setCanChangeUserName($this->allowChangeUserName($user, $form));

        $form->assignValues(AccountResource::populate($user));

        return $this->success($form->getFormStructure());
    }

    function updateAccountSetting($params)
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);
        $user = $this->userService->get($this->getUser()->getId());
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        /** @var AccountSettingForm $form */
        $form = $this->createForm(AccountSettingForm::class);
        $form->setGateways($this->getSettingGateways($user));
        if ($form->isValid() && ($aVals = $form->getValues())) {
            $aVals['gateway_detail'] = $form->getAssociativeArrayData('gateway_detail');
            //Support 3rd gateway
            (($sPlugin = Phpfox_Plugin::get('user.component_controller_setting_process_check')) ? eval($sPlugin) : false);

            $success = $this->processUpdateAccountSetting($user, $aVals);
            if ($success) {
                //Check if change package
                $purchase = [];
                $purchaseId = 0;
                if (!empty($aVals['package_id']) && (empty($aVals['current_package_id']) || $aVals['current_package_id'] !== $aVals['package_id'])) {
                    //Update membership package
                    $subscriptionApi = (new SubscriptionApi());
                    $purchaseId = $subscriptionApi->processCreate($aVals);
                    if ($purchaseId !== true && (int)$purchaseId > 0) {
                        $purchase = $subscriptionApi->loadPurchaseById($purchaseId, true);
                    }
                }
                return $this->success([
                    'pending_purchase' => $purchase,
                    'restart_app'      => empty($purchase) && $purchaseId,
                ], [], $this->getLocalization()->translate('account_settings_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }

    }

    public function getSettingGateways($aUser)
    {
        $aGateways = Phpfox::getService('api.gateway')->getActive();
        if (!empty($aGateways)) {
            $unsetFields = [
                'paypal' => ['client_id', 'client_secret'],
            ];
            $userGateway = Phpfox::getService('api.gateway')->getUserGateways($aUser['user_id']);
            foreach ($aGateways as $key => $gateway) {
                foreach ($gateway['custom'] as $customKey => $aCustom) {
                    if (isset($unsetFields[$gateway['gateway_id']]) && in_array($customKey, $unsetFields[$gateway['gateway_id']])) {
                        unset($aGateways[$key]['custom'][$customKey]);
                        continue;
                    }
                    if (isset($userGateway[$gateway['gateway_id']]['gateway'][$customKey])) {
                        $aGateways[$key]['custom'][$customKey]['user_value'] = $userGateway[$gateway['gateway_id']]['gateway'][$customKey];
                    }
                }
            }
        }
        (($sPlugin = Phpfox_Plugin::get('user.component_controller_setting_settitle')) ? eval($sPlugin) : false);

        return $aGateways;
    }
}