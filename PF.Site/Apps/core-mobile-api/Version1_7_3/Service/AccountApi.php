<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7_3\Service;


use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Exception\NotFoundErrorException;
use Apps\Core_MobileApi\Api\Exception\PermissionErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\Type\PrivacyType;
use Apps\Core_MobileApi\Api\Resource\AccountResource;
use Apps\Core_MobileApi\Api\Resource\EmailNotificationSettingsResource;
use Apps\Core_MobileApi\Api\Resource\ItemPrivacySettingsResource;
use Apps\Core_MobileApi\Api\Resource\ProfilePrivacySettingsResource;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\Core_MobileApi\Service\SubscriptionApi;
use Apps\Core_MobileApi\Version1_7_3\Api\Form\User\AccountSettingForm;
use Apps\Core_MobileApi\Version1_7_3\Api\Form\User\ContactUsForm;
use Apps\Core_MobileApi\Version1_7_3\Api\Resource\PushNotificationSettingsResource;
use Apps\Core_MobileApi\Version1_7_3\Api\Resource\SmsNotificationSettingsResource;
use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;

class AccountApi extends \Apps\Core_MobileApi\Version1_7\Service\AccountApi
{

    public function findAllProfilePrivacy()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $results = $this->getProfilePrivacy();
        return $this->success($results);
    }

    public function findAllProfilePrivacySettings()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $results = $this->getProfilePrivacy($userId, false);
        return $this->success([
            'id'            => (int)$userId,
            'resource_name' => ProfilePrivacySettingsResource::populate([])->getResourceName(),
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/profile-privacy-setting',
            'settings'      => $results,
        ]);
    }

    protected function getProfilePrivacy($userId = null, $noKey = true)
    {
        if (!$userId) {
            $userId = (int)$this->getUser()->getId();
        }
        $user = $this->userService->get($userId, true);
        if (!$user) {
            return $this->notFoundError();
        }
        $userPrivacy = [];
        $rows = $this->database()->select('user_privacy, user_value')
            ->from(Phpfox::getT('user_privacy'))
            ->where('user_id = ' . $userId)
            ->execute('getSlaveRows');
        foreach ($rows as $row) {
            $userPrivacy[$row['user_privacy']] = $row['user_value'];
        }
        $profiles = Phpfox::massCallback('getProfileSettings');
        $supportModules = NameResource::instance()->getSupportModules();
        $results = [];
        $i = 0;
        foreach ($profiles as $module => $profile) {
            if ($module == 'mail' || !in_array($module, $supportModules)) {
                continue;
            }
            foreach ($profile as $var => $info) {
                list($options, $allowOption) = $this->getProfileSettingOptions($info);
                $results[$noKey ? $i : $var] = [
                    'module_id' => $module,
                    'var_name'  => $var,
                    'value'     => (int)(isset($userPrivacy[$var]) ? (int)$userPrivacy[$var] : (isset($info['default']) ? (in_array($info['default'], $allowOption) ? (int)$info['default'] : $allowOption[0]) : $allowOption[0])),
                    'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                    'options'   => $options
                ];
                $i++;
            }
        }
        $results[$noKey ? $i : 'dob_setting'] = [
            'module_id' => 'user',
            'var_name'  => 'dob_setting',
            'value'     => (int)$user['dob_setting'],
            'phrase'    => $this->getLocalization()->translate('date_of_birth'),
            'options'   => [
                [
                    'label' => $this->getLocalization()->translate('no_select'),
                    'value' => 0
                ],
                [
                    'label' => $this->getLocalization()->translate('show_only_month_amp_day_in_my_profile'),
                    'value' => 1
                ],
                [
                    'label' => $this->getLocalization()->translate('display_only_my_age'),
                    'value' => 2
                ],
                [
                    'label' => $this->getLocalization()->translate('don_t_show_my_birthday_in_my_profile'),
                    'value' => 3
                ],
                [
                    'label' => $this->getLocalization()->translate('show_my_full_birthday_in_my_profile'),
                    'value' => 4
                ],

            ]
        ];
        return $results;
    }
    function findAllItemPrivacy()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $results = $this->getItemPrivacy();

        return $this->success($results);
    }

    public function findAllItemPrivacySettings()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $results = $this->getItemPrivacy($userId, false);
        return $this->success([
            'id'            => (int)$userId,
            'resource_name' => ItemPrivacySettingsResource::populate([])->getResourceName(),
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/item-privacy-setting',
            'settings'      => $results,
        ]);
    }

    protected function getItemPrivacy($userId = null, $noKey = true)
    {
        if (!$userId) {
            $userId = (int)$this->getUser()->getId();
        }
        $user = $this->userService->get($userId, true);
        if (!$user) {
            return $this->notFoundError();
        }
        $userPrivacy = [];
        $rows = $this->database()->select('user_privacy, user_value')
            ->from(Phpfox::getT('user_privacy'))
            ->where('user_id = ' . $userId)
            ->execute('getSlaveRows');
        foreach ($rows as $row) {
            $userPrivacy[$row['user_privacy']] = $row['user_value'];
        }
        $itemPrivacy = Phpfox::massCallback('getGlobalPrivacySettings');
        $supportModules = NameResource::instance()->getSupportModules();
        $results = [];
        $privacyOption = (new PrivacyType())->getDefaultPrivacy();
        $i = 0;
        foreach ($itemPrivacy as $module => $profile) {
            if (!in_array($module, $supportModules)) {
                continue;
            }
            foreach ($profile as $var => $info) {
                $results[$noKey ? $i : $var] = [
                    'module_id' => $module,
                    'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                    'var_name'  => $var,
                    'value'     => $this->getPrivacyDefault($privacyOption, isset($userPrivacy[$var]) ? (int)$userPrivacy[$var] : (isset($info['default']) ? (int)$info['default'] : 0)),
                    'options'   => $privacyOption,
                    'custom_id' => str_replace('.', '_', $var),
                ];
                $i++;
            }

        }
        return $results;
    }

    public function findAllEmailNotification()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_notification_privacy')) {
            return $this->permissionError();
        }
        $results = $this->getEmailNotification();

        return $this->success($results);
    }

    public function findEmailNotificationSettings()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_notification_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)$this->getUser()->getId();

        $settings = $this->getEmailNotification($userId, false);

        return $this->success([
            'id'            => $userId,
            'resource_name' => EmailNotificationSettingsResource::populate([])->getResourceName(),
            'settings'      => $settings,
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/email-notification-setting'
        ]);
    }

    protected function getEmailNotification($userId = null, $noKey = true)
    {
        if (!$userId) {
            $userId = (int)$this->getUser()->getId();
        }
        $user = $this->userService->get($userId, true);
        if (!$user) {
            return $this->notFoundError();
        }
        $notifications = [];
        $hasType = $this->database()->isField(Phpfox::getT('user_notification'), 'notification_type');
        $rows = $this->database()->select('user_notification')
            ->from(Phpfox::getT('user_notification'))
            ->where('user_id = ' . $userId . ($hasType ? ' AND notification_type = \'email\''  : ''))
            ->execute('getSlaveRows');

        foreach ($rows as $row) {
            $notifications[$row['user_notification']] = true;
        }
        $emailNotifications = array_merge([
            'core' => [
                'core.enable_notifications' => [
                    'phrase' => $this->getLocalization()->translate('enable_email_notifications'),
                    'default' => 1
                ]
            ]
        ], Phpfox::massCallback('getNotificationSettings'));
        $supportModules = NameResource::instance()->getSupportModules();
        $results = [];
        $i = 0;
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $profile) {
                if (!empty($profile)) {
                    foreach ($profile as $var => $info) {
                        if ($module == 'mail' || !in_array($module, $supportModules)) {
                            continue;
                        }
                        $results[$noKey ? $i : $var] = [
                            'module_id' => $module,
                            'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                            'var_name'  => $var,
                            'value'     => isset($notifications[$var]) ? 0 : $info['default'],
                        ];
                        $i++;
                    }
                }
            }
        }
        return $results;
    }

    protected function getProfileSettingOptions($profile = [])
    {
        $options = [];
        $allowOptions = [];
        if (!isset($profile['anyone']) && !Phpfox::getParam('core.friends_only_community')) {
            $options[] = [
                'label' => $this->getLocalization()->translate('anyone'),
                'value' => 0
            ];
            $allowOptions[] = '0';
        }
        if (!isset($profile['no_user'])) {
            if (!isset($profile['friend_only']) && !Phpfox::getParam('core.friends_only_community')) {
                $options[] = [
                    'label' => $this->getLocalization()->translate('community'),
                    'value' => 1
                ];
                $allowOptions[] = '1';
            }
            if (Phpfox::isModule('friend')) {
                if (!isset($profile['friend']) || $profile['friend']) {
                    $options[] = [
                        'label' => $this->getLocalization()->translate('friends_only'),
                        'value' => 2
                    ];
                    $allowOptions[] = '2';
                }
                if (!empty($profile['friend_of_friend'])) {
                    $options[] = [
                        'label' => $this->getLocalization()->translate('friends_of_friends'),
                        'value' => 3
                    ];
                    $allowOptions[] = '3';
                }
            }
        }
        $options[] = [
            'label' => $this->getLocalization()->translate('no_one'),
            'value' => 4
        ];
        $allowOptions[] = '4';
        return [$options, $allowOptions];
    }

    function updateProfilePrivacy($params)
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        $profiles = Phpfox::massCallback('getProfileSettings');
        $varName = [];
        foreach ($profiles as $module => $profile) {
            foreach ($profile as $var => $info) {
                $varName[] = $var;
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }
        //Add date of birth
        $varName[] = 'dob_setting';
        $allowVal = ['0', '1', '2', '3', '4'];
        foreach ($params as $var => $val) {
            if (!$this->checkValidVar($var, $val, $varName, $var != 'dob_setting' ? $allowVal : array_merge($allowVal, ['3']))) {
                continue;
            }
            if ($var == 'dob_setting') {
                Phpfox::getService('user.field.process')->update($userId, 'dob_setting', (int)$val);
                $this->cache()->remove(['udob', $userId]);
                continue;
            }
            //Remove old
            $this->database()->delete(':user_privacy', 'user_id = ' . $userId . ' AND user_privacy = \'' . $var . '\'');

            if (!$val) {
                continue;
            }

            $this->database()->insert(':user_privacy', [
                    'user_id'      => $userId,
                    'user_privacy' => $var,
                    'user_value'   => $val
                ]
            );
        }
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }

    function updateProfilePrivacySettings($params)
    {
        $params = $this->resolver->setRequired(['var_name', 'value'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        $profiles = Phpfox::massCallback('getProfileSettings');
        $varName = [];
        foreach ($profiles as $module => $profile) {
            foreach ($profile as $var => $info) {
                $varName[] = $var;
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }
        //Add date of birth
        $varName[] = 'dob_setting';
        $allowVal = ['0', '1', '2', '3', '4'];
        $var = $params['var_name'];
        $val = $params['value'];
        if (!$this->checkValidVar($var, $val, $varName, $var != 'dob_setting' ? $allowVal : array_merge($allowVal, ['3']))) {
            return $this->error();
        }
        if ($var == 'dob_setting') {
            Phpfox::getService('user.field.process')->update($userId, 'dob_setting', (int)$val);
            $this->cache()->remove(['udob', $userId]);
        }
        //Remove old
        $this->database()->delete(':user_privacy', 'user_id = ' . $userId . ' AND user_privacy = \'' . $var . '\'');

        if ($val) {

            $this->database()->insert(':user_privacy', [
                    'user_id'      => $userId,
                    'user_privacy' => $var,
                    'user_value'   => $val
                ]
            );
        }

        $this->cache()->remove('user_privacy_' . $userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }

    public function getContactForm($params)
    {
        /** @var ContactUsForm $form */
        $form = $this->createForm(ContactUsForm::class, [
            'title'  => 'contact_us',
            'method' => 'post',
            'action' => UrlUtility::makeApiUrl('account/contact-us')
        ]);
        $user = $this->getUser();
        $form->setIsUser($user->getId() > 0);
        $form->setFullName($user->getFullName());
        $form->setEmail($user->getEmail());
        $form->setCategory($this->getContactCategory());
        return $this->success($form->getFormStructure());
    }

    protected function getContactCategory()
    {
        $categories = Phpfox::getService('contact')->getCategories();
        $categories = array_map(function($category) {
            return [
                'value' => $category['title'],
                'label' => $this->getLocalization()->translate($category['title'])
            ];
        }, $categories);
        return $categories;
    }

    public function submitContactForm($params)
    {
        /** @var ContactUsForm $form */
        $form = $this->createForm(ContactUsForm::class);
        $user = $this->getUser();
        $form->setIsUser($user->getId() > 0);
        $form->setFullName($user->getFullName());
        $form->setEmail($user->getEmail());
        $form->setCategory($this->getContactCategory());
        if ($form->isValid()) {
            $id = Phpfox::getService('contact')->sendContactMessage($form->getValues());

            if ($id) {
                return $this->success([], [], $this->getLocalization()->translate('your_message_was_successfully_sent'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    public function findAllMobileNotifications()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_notification_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)$this->getUser()->getId();
        $user = $this->userService->get($userId, true);
        if (!$user) {
            return $this->notFoundError();
        }
        $notifications = [];
        $rows = $this->database()->select('module_id')
            ->from(':mobile_api_push_notification_setting')
            ->where('user_id = ' . $userId)
            ->execute('getSlaveRows');

        foreach ($rows as $row) {
            $notifications[$row['module_id']] = true;
        }
        $emailNotifications = Phpfox::massCallback('getNotificationSettings');
        $supportModules = NameResource::instance()->getSupportModules();
        $settings = [];

        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $profile) {
                if ($module == 'mail' || !in_array($module, $supportModules)) {
                    continue;
                }
                if (!isset($settings[$module])) {
                    $settings[$module] = [
                        'module_id' => $module,
                        'phrase'    => $this->getLocalization()->translate(Phpfox::getService('language.phrase')->isPhrase('module_' . $module) ? 'module_' . $module : $module),
                        'var_name'  => $module,
                        'value'     => isset($notifications[$module]) ? 0 : 1,
                    ];
                }
            }
        }

        return $this->success([
            'id'            => $userId,
            'resource_name' => PushNotificationSettingsResource::populate([])->getResourceName(),
            'settings'      => $settings,
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/mobile-push-notification'
        ]);
    }

    public function updateMobileNotification($params)
    {
        $params = $this->resolver->setRequired(['var_name', 'value'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        //Remove old email notification
        $emailNotifications = Phpfox::massCallback('getNotificationSettings');
        $varName = [];
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $item) {
                $varName[] = $module;
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }
        $allowVal = ['0', '1'];
        $var = $params['var_name'];
        $val = $params['value'];

        if (!in_array($var, $varName) || !in_array($val, $allowVal)) {
            return $this->notFoundError();
        }
        //Remove old
        if ($val) {
            $this->database()->delete(':mobile_api_push_notification_setting', 'user_id = ' . $userId . ' AND module_id = \'' . $var . '\'');
        } else {
            $this->database()->insert(':mobile_api_push_notification_setting', [
                    'user_id'           => $userId,
                    'module_id'         => $var,
                    'time_stamp'        => PHPFOX_TIME
                ]
            );
        }
        $this->cache()->remove('user_push_notification_' . $userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }

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
        if (!empty($user['full_phone_number'])) {
            $phoneLib = Phpfox::getLib('phone');
            if ($phoneLib->setRawPhone($user['full_phone_number']) &&  $phoneLib->isValidPhone()) {
                $user['phone_number'] = $phoneLib->getPhoneInternational();
            }
        }
        $form->assignValues(AccountResource::populate($user));

        return $this->success($form->getFormStructure());
    }

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
                        'succeedAction' => !empty($response['action']) ? $response['action'] : null,
                        'data' => isset($response['data']) ? $response['data'] : null
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
        $changedEmail = $changedPhone = false;
        $params['old_user_name'] = $user['user_name'];
        if ($this->getSetting()->getAppSetting('core.enable_register_with_phone_number') && empty($params['phone_number'])
            && !filter_var(isset($params['email']) ? $params['email'] : '',FILTER_VALIDATE_EMAIL)) {
            Phpfox_Error::set($this->getLocalization()->translate('provide_a_valid_email_address_or_phone_number'));
            $allowed = false;
        }
        if ($this->getSetting()->getUserSetting('user.can_change_email') && $params['email'] != $user['email']
            && filter_var(isset($params['email']) ? $params['email'] : '',FILTER_VALIDATE_EMAIL)) {
            Phpfox::getService('user.validate')->email($params['email']);
            if (!Phpfox_Error::isPassed()) {
                $allowed = false;
            } else {
                $changedEmail = true;
            }
        }
        if ($allowed && $this->getSetting()->getUserSetting('user.can_change_phone') && !empty($params['phone_number'])) {
            $phoneLib = Phpfox::getLib('phone');
            if ($phoneLib->setRawPhone($params['phone_number']) && $phoneLib->isValidPhone()) {
                $sPhone = $phoneLib->getPhoneE164();
                if ($sPhone != $user['full_phone_number']) {
                    Phpfox::getService('user.validate')->phone($sPhone, true);
                    if (!Phpfox_Error::isPassed()) {
                        $allowed = false;
                    } else {
                        $changedPhone = true;
                    }
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
            if ($changedEmail) {
                $allowed = Phpfox::getService('user.verify.process')->changeEmail($user, $params['email'], $changedEmail);
                if (is_string($allowed)) {
                    Phpfox_Error::set($allowed);
                }
                if ($this->getSetting()->getAppSetting('user.verify_email_at_signup')) {
                    $message = $this->getLocalization()->translate('account_settings_updated_your_new_mail_address_requires_verification_and_an_email_has_been_sent_until_then_your_email_remains_the_same');
                    if ($this->getSetting()->getAppSetting('user.logout_after_change_email_if_verify')) {
                        return [
                            'success' => true,
                            'action' => '@auth/logout',
                            'message' => $message
                        ];
                    }
                    return [
                        'success' => true,
                        'message' => $message
                    ];
                }
            }
            if ($changedPhone) {
                $allowed = Phpfox::getService('user.verify.process')->changePhone($user, $params['phone_number'], true);
                if ($allowed === true) {
                    //Changed phone, redirect to verify
                    $message = $this->getLocalization()->translate('account_settings_updated_your_new_phone_number_requires_verification_and_an_sms_has_been_sent_until_then_your_phone_remains_the_same');
                    if ($this->getSetting()->getAppSetting('user.logout_after_change_phone_number')) {
                        $action = '@auth/verify_sms_logout';
                        $noLogin = false;
                    } else {
                        $action = '@auth/verify_sms';
                        $noLogin = true;
                    }
                    return [
                        'success' => true,
                        'action' => $action,
                        'data' => [
                            'userId' => $user['user_id'],
                            'isPhoneSignUp' => true,
                            'sentToken' => true,
                            'phoneNumber' => $params['phone_number'],
                            'noLogin' => $noLogin
                        ],
                        'message' => $message
                    ];
                } elseif (!$allowed) {
                    return [
                        'success' => false
                    ];
                }
            }
            return [
                'success' => true,
                'message' => $message
            ];
        }
        return [
            'success' => false
        ];
    }

    /**
     * @param $params
     *
     * @return mixed
     * @throws NotFoundErrorException
     * @throws PermissionErrorException
     * @throws ValidationErrorException
     */
    public function updateEmailNotificationSettings($params)
    {
        $params = $this->resolver->setRequired(['var_name', 'value'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        $hasType = $this->database()->isField(Phpfox::getT('user_notification'), 'notification_type');
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        //Remove old email notification
        $emailNotifications = array_merge([
            'core' => [
                'core.enable_notifications' => [
                    'phrase' => $this->getLocalization()->translate('enable_email_notifications'),
                    'default' => 1
                ]
            ]
        ], Phpfox::massCallback('getNotificationSettings'));
        $varName = [];
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $item) {
                if (!empty($item)) {
                    foreach ($item as $var => $info) {
                        $varName[] = $var;
                    }
                }
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }
        $allowVal = ['0', '1'];
        $var = $params['var_name'];
        $val = $params['value'];

        if (!$this->checkValidVar($var, $val, $varName, $allowVal)) {
            return $this->notFoundError();
        }
        //Remove old
        if ($val) {
            $this->database()->delete(':user_notification', 'user_id = ' . $userId . ' AND user_notification = \'' . $var . '\'' . ($hasType ? ' AND notification_type = \'email\'' : ''));
        } else {
            $this->database()->insert(':user_notification', $hasType ? [
                'user_id'           => $userId,
                'user_notification' => $var,
                'notification_type' => 'email'
            ] : [
                'user_id'           => $userId,
                'user_notification' => $var
            ]
            );
        }
        $this->cache()->remove('user_notification_' . $userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }

    public function findAllSmsNotification()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_notification_privacy') || !$this->database()->isField(Phpfox::getT('user_notification'), 'notification_type')) {
            return $this->permissionError();
        }
        $results = $this->getSmsNotification();

        return $this->success($results);
    }

    public function findSmsNotificationSettings()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_notification_privacy') || !$this->database()->isField(Phpfox::getT('user_notification'), 'notification_type')) {
            return $this->permissionError();
        }
        $userId = (int)$this->getUser()->getId();

        $settings = $this->getSmsNotification($userId, false);

        return $this->success([
            'id'            => $userId,
            'resource_name' => SmsNotificationSettingsResource::populate([])->getResourceName(),
            'settings'      => $settings,
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/sms-notification-setting'
        ]);
    }

    protected function getSmsNotification($userId = null, $noKey = true)
    {
        if (!$userId) {
            $userId = (int)$this->getUser()->getId();
        }
        $user = $this->userService->get($userId, true);
        if (!$user) {
            return $this->notFoundError();
        }
        $notifications = [];
        $rows = $this->database()->select('user_notification')
            ->from(Phpfox::getT('user_notification'))
            ->where('user_id = ' . $userId . ' AND notification_type = \'sms\'')
            ->execute('getSlaveRows');

        foreach ($rows as $row) {
            $notifications[$row['user_notification']] = true;
        }
        $emailNotifications = array_merge([
            'core' => [
                'core.enable_notifications' => [
                    'phrase' => $this->getLocalization()->translate('enable_sms_notifications'),
                    'default' => 1
                ]
            ]
        ], Phpfox::massCallback('getNotificationSettings'));
        $supportModules = NameResource::instance()->getSupportModules();
        $results = [];
        $i = 0;
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $profile) {
                if (!empty($profile)) {
                    foreach ($profile as $var => $info) {
                        if ($module == 'mail' || !in_array($module, $supportModules)) {
                            continue;
                        }
                        $results[$noKey ? $i : $var] = [
                            'module_id' => $module,
                            'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                            'var_name'  => $var,
                            'value'     => isset($notifications[$var]) ? 0 : $info['default'],
                        ];
                        $i++;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * @param $params
     *
     * @return mixed
     * @throws NotFoundErrorException
     * @throws PermissionErrorException
     * @throws ValidationErrorException
     */
    public function updateSmsNotificationSettings($params)
    {
        $params = $this->resolver->setRequired(['var_name', 'value'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy') || !$this->database()->isField(Phpfox::getT('user_notification'), 'notification_type')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        //Remove old email notification
        $emailNotifications = array_merge([
            'core' => [
                'core.enable_notifications' => [
                    'phrase' => $this->getLocalization()->translate('enable_sms_notifications'),
                    'default' => 1
                ]
            ]
        ], Phpfox::massCallback('getNotificationSettings'));
        $varName = [];
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $item) {
                if (!empty($item)) {
                    foreach ($item as $var => $info) {
                        $varName[] = $var;
                    }
                }
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }
        $allowVal = ['0', '1'];
        $var = $params['var_name'];
        $val = $params['value'];

        if (!$this->checkValidVar($var, $val, $varName, $allowVal)) {
            return $this->notFoundError();
        }
        //Remove old
        if ($val) {
            $this->database()->delete(':user_notification', 'notification_type = \'sms\' AND user_id = ' . $userId . ' AND user_notification = \'' . $var . '\'');
        } else {
            $this->database()->insert(':user_notification', [
                    'user_id'           => $userId,
                    'user_notification' => $var,
                    'notification_type' => 'sms'
                ]
            );
        }
        $this->cache()->remove('user_sms_notification_' . $userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }

    function updateSmsNotification($params)
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy') || !$this->database()->isField(Phpfox::getT('user_notification'), 'notification_type')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        //Remove old email notification
        $emailNotifications = Phpfox::massCallback('getNotificationSettings');
        $varName = [];
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $item) {
                if (!empty($item)) {
                    foreach ($item as $var => $info) {
                        $varName[] = $var;
                    }
                }
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }
        $allowVal = ['0', '1'];
        foreach ($params as $var => $val) {
            if (!$this->checkValidVar($var, $val, $varName, $allowVal)) {
                continue;
            }
            //Remove old
            $this->database()->delete(':user_notification', 'notification_type = \'email\' AND user_id = ' . $userId . ' AND user_notification = \'' . $var . '\'');

            if ($val) {
                continue;
            }
            $this->database()->insert(':user_notification', [
                    'user_id'           => $userId,
                    'user_notification' => $var,
                    'notification_type' => 'sms'
                ]
            );
        }
        $this->cache()->remove('user_sms_notification_' . $userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }
}