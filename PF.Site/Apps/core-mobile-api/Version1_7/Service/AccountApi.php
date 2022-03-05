<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7\Service;


use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Service\SubscriptionApi;
use Apps\Core_MobileApi\Version1_4\Api\Form\User\AccountSettingForm;
use Phpfox;
use Phpfox_Plugin;

class AccountApi extends \Apps\Core_MobileApi\Version1_4\Service\AccountApi
{

    function updateAccountSetting($params)
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);

        $user = $this->getUser()->getRawData();
        /** @var AccountSettingForm $form */
        $form = $this->createForm(AccountSettingForm::class);
        $form->setGateways($this->getSettingGateways($user));
        if ($form->isValid() && ($aVals = $form->getValues())) {
            $aVals['gateway_detail'] = $form->getAssociativeArrayData('gateway_detail');
            //Support 3rd gateway
            (($sPlugin = Phpfox_Plugin::get('user.component_controller_setting_process_check')) ? eval($sPlugin) : false);

            $response = $this->processUpdateAccountSetting($user, $aVals);

            if (isset($response['success']) && $response['success'] == true) {
                //Check if change package
                $purchase = [];
                if (!empty($aVals['package_id']) && (empty($aVals['current_package_id']) || $aVals['current_package_id'] !== $aVals['package_id'])) {
                    //Update membership package
                    $subscriptionApi = (new SubscriptionApi());
                    $purchaseId = $subscriptionApi->processCreate($aVals);
                    if ($purchaseId !== true && (int)$purchaseId > 0) {
                        $purchase = $subscriptionApi->loadPurchaseById($purchaseId, true);
                    }
                    $result = [
                        'pending_purchase' => $purchase,
                        'restart_app' => empty($purchase) && $purchaseId,
                    ];
                } else {
                    $result = [
                        'succeedAction' => !empty($response['action']) ? $response['action'] : null
                    ];
                }
                return $this->success($result, [], !empty($response['message']) ? $response['message'] : $this->getLocalization()->translate('account_settings_updated'));
            } else {
                return $this->error(preg_replace('/<a[^>]*>[^<]*<\/a>/', '', $this->getErrorMessage()));
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }

    }

    protected function processUpdateAccountSetting($user, $params)
    {
        $allowed = true;
        $message = null;
        $params['old_user_name'] = $user['user_name'];
        if ($this->getSetting()->getUserSetting('user.can_change_email') && $user['email'] != $params['email']) {
            $allowed = Phpfox::getService('user.verify.process')->changeEmail($user, $params['email']);
            if (is_string($allowed) || !$allowed) {
                $allowed = false;
            } elseif ($this->getSetting()->getAppSetting('user.verify_email_at_signup')) {
                $message = $this->getLocalization()->translate('account_settings_updated_your_new_mail_address_requires_verification_and_an_email_has_been_sent_until_then_your_email_remains_the_same');
                if ($this->getSetting()->getAppSetting('user.logout_after_change_email_if_verify')) {
                    return [
                        'success' => true,
                        'action' => '@auth/logout',
                        'message' => $message
                    ];
                }
            }
        }

        $special = [
            'changes_allowed' => Phpfox::getUserParam('user.total_times_can_change_user_name'),
            'total_user_change' => $user['total_user_change'],
            'full_name_changes_allowed' => Phpfox::getUserParam('user.total_times_can_change_own_full_name'),
            'total_full_name_change' => $user['total_full_name_change'],
            'current_full_name' => $user['full_name']
        ];
        if ($allowed && ($iId = Phpfox::getService('user.process')->update($user['user_id'], $params, $special, true))) {
            return [
                'success' => true,
                'message' => $message
            ];
        }
        return [
            'success' => false
        ];
    }
}