<?php

namespace Apps\Core_MobileApi\Version1_8\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Exception\PermissionErrorException;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Version1_7_3\Service\UserApi as BaseUserApi;
use Apps\Core_MobileApi\Version1_8\Api\Form\User\ChangePasswordForm;
use Apps\Core_MobileApi\Version1_8\Api\Form\User\ForgetPasswordRequest;
use Phpfox;

class UserApi extends BaseUserApi
{
    protected $userPassword;

    /**
     * @return \User_Service_Password
     */
    protected function getUserPassword()
    {
        if (!$this->userPassword) {
            $this->userPassword = Phpfox::getService('user.password');
        }
        return $this->userPassword;
    }

    public function passwordRequest($params)
    {
        /** @var ForgetPasswordRequest $form */
        $form = $this->createForm(ForgetPasswordRequest::class, [
            'title'  => 'password_request',
            'method' => 'put',
            'action' => UrlUtility::makeApiUrl('user/password/request')
        ]);
        if ($this->request()->isPut()) {
            $form->buildForm();
            $form->buildValues();
            $form->setIsUsePhone(!!$form->getField('use_phone')->getValue());
            if ($form->isValid()) {
                $values = $form->getValues();
                if (empty($values['use_phone'])) {
                    //Email
                    $this->getUserPassword()->requestPassword($values);
                    if ($this->isPassed()) {
                        return $this->success([], [], 'password_request_successfully_sent_check_your_email_to_verify_your_request');
                    }
                } else {
                    //Phone
                    if ($result = $this->getUserPassword()->requestPassword(['email' => $values['phone']])) {
                        return $this->success([
                            'succeedAction' => '@auth/verification_code',
                            'data'          => [
                                'headerTitle'        => $this->getLocalization()->translate('password_request'),
                                'requestTokenAction' => [
                                    'url' => 'user/password/request',
                                ],
                                'verifyTokenAction'  => [
                                    'data' => [
                                        'request_id' => $result
                                    ],
                                    'url'  => 'user/password/verify-request'
                                ],
                                'isPhoneSignUp'      => true,
                                'sentToken'          => true,
                                'noLogin'            => false,
                                'phoneNumber'        => $values['phone']
                            ]
                        ], [], 'text_message_was_sent_to_to_phone');
                    }
                }
                return $this->error($this->getErrorMessage());
            }
            return $this->validationParamsError($form->getInvalidFields());
        } elseif ($this->request()->isPost()) {
            $phone = $this->resolver->resolveSingle($params, 'phone');
            if (!empty($phone) && $this->getUserPassword()->requestPassword(['email' => $phone])) {
                return $this->success([], [], 'passcode_successfully_sent_to_your_phone_number');
            } else {
                return $this->error($this->getLocalization()->translate('phone_number_is_invalid'));
            }
        }
        return $this->success($form->getFormStructure());
    }

    public function verifyPasswordRequest($params)
    {
        $params = $this->resolver
            ->setRequired(['request_id', 'code'])
            ->resolve($params)->getParameters();
        if (Phpfox::getService('user.verify.process')->verify($params['code'], true, true)) {
            if ($this->getSetting()->getAppSetting('user.shorter_password_reset_routine')) {
                if ($this->getUserPassword()->isValidRequest($params['request_id']) == true) {
                    //Shorten
                    return $this->success([
                        'succeedAction' => '@user/changePassword',
                        'data'          => [
                            'request_id' => $params['request_id']
                        ]
                    ]);
                }
            } else {
                if ($this->getUserPassword()->verifyRequest($params['request_id'])) {
                    return $this->success([], [], 'new_password_successfully_sent_check_your_phone_number_to_use_your_new_password');
                }
            }
        }
        return $this->error($this->getLocalization()->translate('provide_valid_verification_code'));
    }

    /**
     * Change User Password
     *
     * @param $params
     *
     * @return array|bool|mixed
     * @throws UnknownErrorException
     * @throws ValidationErrorException
     * @throws PermissionErrorException
     */
    public function changePassword($params)
    {
        $requestPasswordId = $this->resolver->resolveSingle($params, 'request_id');
        if (!empty($requestPasswordId)) {
            /** @var ChangePasswordForm $form */
            $form = $this->createForm(ChangePasswordForm::class, [
                'title'  => 'password_request',
                'method' => 'put',
                'action' => UrlUtility::makeApiUrl('account/password')
            ]);
            $form->setPassOldPassword(true);
            $form->setRequestId($requestPasswordId);
        } else {
            $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);
            /** @var ChangePasswordForm $form */
            $form = $this->createForm(ChangePasswordForm::class, [
                'title'  => 'change_password',
                'method' => 'put',
                'action' => UrlUtility::makeApiUrl('account/password')
            ]);
            //Pass Fb user
            $user = storage()->get('fb_new_users_' . $this->getUser()->getId());
            $form->setPassOldPassword(!empty($user));
        }
        if ($this->request()->isPut()) {
            if ($form->isValid()) {
                $values = $form->getValues();
                if (!empty($values['request_id'])) {
                    if ($this->getUserPassword()->updatePassword($values['request_id'], [
                        'newpassword'  => $values['confirm_password'],
                        'newpassword2' => $values['new_password']
                    ])) {
                        return $this->success([], [], 'password_successfully_updated');
                    }
                } else {
                    $this->getProcessService()->updatePassword($values);
                    if ($this->isPassed()) {
                        return $this->success([], [], 'password_successfully_updated');
                    }
                }
                return $this->error($this->getErrorMessage());

            }
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->success($form->getFormStructure());
    }
}