<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Exception\NotFoundErrorException;
use Apps\Core_MobileApi\Api\Exception\PermissionErrorException;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\Type\PrivacyType;
use Apps\Core_MobileApi\Api\Form\User\AccountSettingForm;
use Apps\Core_MobileApi\Api\Form\User\UpdateLanguageForm;
use Apps\Core_MobileApi\Api\Resource\AccountResource;
use Apps\Core_MobileApi\Api\Resource\BlockedUserResource;
use Apps\Core_MobileApi\Api\Resource\EmailNotificationSettingsResource;
use Apps\Core_MobileApi\Api\Resource\ItemPrivacySettingsResource;
use Apps\Core_MobileApi\Api\Resource\ProfilePrivacySettingsResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Phpfox;

class AccountApi extends AbstractResourceApi
{

    /**
     * @var \Custom_Service_Custom
     */
    protected $customService;

    /**
     * @var \User_Service_User
     */
    protected $userService;

    /**
     * @var \User_Service_Block_Block
     */
    protected $userBlockService;

    /**
     * @var \User_Service_Block_Process
     */
    protected $userBlockProcessService;
    protected $oParsed;

    public function __construct()
    {
        parent::__construct();
        $this->oParsed = Phpfox::getService(ParseInterface::class);
        $this->customService = Phpfox::getService('custom');
        $this->userService = Phpfox::getService('user');
        $this->userBlockService = Phpfox::getService('user.block');
        $this->userBlockProcessService = Phpfox::getService('user.block.process');
    }

    public function __naming()
    {
        return [
            'account/setting/form'               => [
                'get' => 'formSetting',
            ],
            'account/setting'                    => [
                'get' => 'findAllAccountSettings',
                'put' => 'updateAccountSetting'
            ],
            'account/profile-privacy'            => [
                'get' => 'findAllProfilePrivacy',
                'put' => 'updateProfilePrivacy'
            ],
            'account/profile-privacy/:id'        => [
                'get' => 'findAllProfilePrivacySettings',
            ],
            'account/profile-privacy-setting'    => [
                'put' => 'updateProfilePrivacySettings'
            ],
            'account/item-privacy'               => [
                'get' => 'findAllItemPrivacy',
                'put' => 'updateItemPrivacy'
            ],
            'account/item-privacy/:id'           => [
                'get' => 'findAllItemPrivacySettings',
            ],
            'account/item-privacy-setting'       => [
                'put' => 'updateItemPrivacySettings'
            ],
            'account/email-notification'         => [
                'get' => 'findAllEmailNotification',
                'put' => 'updateEmailNotification'
            ],
            'account/email-notification/:id'     => [
                'get' => 'findEmailNotificationSettings',
            ],
            'account/email-notification-setting' => [
                'put' => 'updateEmailNotificationSettings'
            ],
            'account/blocked-user'               => [
                'get'    => 'findAllBlockedUser',
                'post'   => 'addBlockedUser',
                'delete' => 'deleteBlockedUser'
            ],
            'account/invisible'                  => [
                'get' => 'findInvisibleMode',
                'put' => 'updateInvisibleMode'
            ],
            'account/blocked-user/:id'           => [
                'maps'  => [
                    'get'    => 'findAllBlockedUser',
                    'delete' => 'deleteBlockedUser'
                ],
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'account/timezone'                   => [
                'get' => 'findAllTimezone'
            ],
            'account/language'                   => [
                'get' => 'updateUserLanguage',
                'put' => 'updateUserLanguage'
            ],
            'account/contact-us'                 => [
                'get' => 'getContactForm',
                'post' => 'submitContactForm'
            ],
            'account/mobile-push-notification/:id'    => [
                'get' => 'findAllMobileNotifications'
            ],
            'account/mobile-push-notification/'       => [
                'get' => 'findAllMobileNotifications',
                'put' => 'updateMobileNotification'
            ],
            'account/sms-notification/:id'    => [
                'get' => 'findSmsNotificationSettings'
            ],
            'account/sms-notification/'       => [
                'get' => 'findAllSmsNotifications',
                'put' => 'updateSmsNotification'
            ],
            'account/sms-notification-setting' => [
                'put' => 'updateSmsNotificationSettings'
            ],
        ];
    }

    function findAll($params = [])
    {
        return null;
    }

    function findAllAccountSettings()
    {
        if (!Phpfox::isUser()) {
            return $this->permissionError();
        }
        $user = $this->userService->get(Phpfox::getUserId(), true);
        if (!$user) {
            return $this->notFoundError();
        }

        $user = $this->processRow($user);
        return $this->success($user);
    }

    function findAllProfilePrivacy()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)Phpfox::getUserId();
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
        foreach ($profiles as $module => $profile) {
            if ($module == 'mail' || !in_array($module, $supportModules)) {
                continue;
            }
            foreach ($profile as $var => $info) {
                list($options, $allowOption) = $this->getProfileSettingOptions($info);
                $results[] = [
                    'module_id' => $module,
                    'var_name'  => $var,
                    'value'     => (int)(isset($userPrivacy[$var]) ? (int)$userPrivacy[$var] : (isset($info['default']) ? (in_array($info['default'], $allowOption) ? (int)$info['default'] : $allowOption[0]) : $allowOption[0])),
                    'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                    'options'   => $options
                ];
            }
        }
        $results[] = [
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
        return $this->success($results);
    }

    function findAllProfilePrivacySettings()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)Phpfox::getUserId();
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
        foreach ($profiles as $module => $profile) {
            if ($module == 'mail' || !in_array($module, $supportModules)) {
                continue;
            }
            foreach ($profile as $var => $info) {
                list($options, $allowOption) = $this->getProfileSettingOptions($info);
                $results[$var] = [
                    'module_id' => $module,
                    'var_name'  => $var,
                    'value'     => (int)(isset($userPrivacy[$var]) ? (int)$userPrivacy[$var] : (isset($info['default']) ? (in_array($info['default'], $allowOption) ? (int)$info['default'] : $allowOption[0]) : $allowOption[0])),
                    'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                    'options'   => $options
                ];
            }
        }
        $results['dob_setting'] = [
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
        return $this->success([
            'id'            => $userId,
            'resource_name' => ProfilePrivacySettingsResource::populate([])->getResourceName(),
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/profile-privacy-setting',
            'settings'      => $results,
        ]);
    }

    function findAllItemPrivacy()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)Phpfox::getUserId();
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
        foreach ($itemPrivacy as $module => $profile) {
            if (!in_array($module, $supportModules)) {
                continue;
            }
            foreach ($profile as $var => $info) {
                $results[] = [
                    'module_id' => $module,
                    'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                    'var_name'  => $var,
                    'value'     => isset($userPrivacy[$var]) ? (int)$userPrivacy[$var] : (isset($info['default']) ? (int)$info['default'] : 0),
                    'options'   => $privacyOption,
                    'custom_id' => str_replace('.', '_', $var),
                ];
            }

        }
        return $this->success($results);
    }

    public function findAllItemPrivacySettings()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)Phpfox::getUserId();
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
        foreach ($itemPrivacy as $module => $profile) {
            if (!in_array($module, $supportModules)) {
                continue;
            }
            foreach ($profile as $var => $info) {
                $results[$var] = [
                    'module_id' => $module,
                    'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                    'var_name'  => $var,
                    'value'     => $this->getPrivacyDefault($privacyOption, isset($userPrivacy[$var]) ? (int)$userPrivacy[$var] : (isset($info['default']) ? (int)$info['default'] : 0)),
                    'options'   => $privacyOption,
                    'custom_id' => str_replace('.', '_', $var),
                ];
            }

        }
        return $this->success([
            'id'            => $userId,
            'resource_name' => ItemPrivacySettingsResource::populate([])->getResourceName(),
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/item-privacy-setting',
            'settings'      => $results,
        ]);
    }

    protected function getPrivacyDefault($privacyOption, $iDefaultValue)
    {
        $bCheckIsset = false;

        foreach ($privacyOption as $aDefaultPrivacy) {
            if ($aDefaultPrivacy['value'] == $iDefaultValue) {
                $bCheckIsset = true;
            }
        }

        if (!$bCheckIsset && isset($privacyOption[0])) {
            $iDefaultValue = $privacyOption[0]['value'];
        }

        return $iDefaultValue;
    }

    public function findAllTimezone()
    {
        if (!Phpfox::isUser()) {
            return $this->permissionError();
        }
        $timezones = Phpfox::getService('core')->getTimeZones();
        $results = [];
        if ($timezones) {
            $results = [];
            foreach ($timezones as $key => $timezone) {
                $results[] = [
                    'id'   => $key,
                    'name' => $timezone
                ];
            }
        }
        return $this->success($results);
    }

    public function findAllEmailNotification()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_notification_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)Phpfox::getUserId();
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
        $emailNotifications = Phpfox::massCallback('getNotificationSettings');
        $supportModules = NameResource::instance()->getSupportModules();
        $results = [];
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $profile) {
                if (!empty($profile)) {
                    foreach ($profile as $var => $info) {
                        if ($module == 'mail' || !in_array($module, $supportModules)) {
                            continue;
                        }
                        $results[] = [
                            'module_id' => $module,
                            'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                            'var_name'  => $var,
                            'value'     => isset($notifications[$var]) ? 0 : $info['default'],
                        ];
                    }
                }
            }
        }

        return $this->success($results);
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

    public function findEmailNotificationSettings()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_notification_privacy')) {
            return $this->permissionError();
        }
        $userId = (int)Phpfox::getUserId();
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
        $emailNotifications = Phpfox::massCallback('getNotificationSettings');
        $supportModules = NameResource::instance()->getSupportModules();
        $settings = [];
        if (!empty($emailNotifications)) {
            foreach ($emailNotifications as $module => $profile) {
                if (!empty($profile)) {
                    foreach ($profile as $var => $info) {
                        if ($module == 'mail' || !in_array($module, $supportModules)) {
                            continue;
                        }
                        $settings[$var] = [
                            'module_id' => $module,
                            'phrase'    => $this->oParsed->cleanOutput($info['phrase']),
                            'var_name'  => $var,
                            'value'     => isset($notifications[$var]) ? 0 : $info['default'],
                        ];
                    }
                }
            }
        }
        return $this->success([
            'id'            => $userId,
            'resource_name' => EmailNotificationSettingsResource::populate([])->getResourceName(),
            'settings'      => $settings,
            'submitMethod'  => 'put',
            'submitApiUrl'  => 'mobile/account/email-notification-setting'
        ]);
    }

    public function findAllBlockedUser()
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $blocked = Phpfox::getService('user.block')->get();
        $results = [];
        if ($blocked) {
            $results = array_map(function ($item) {
                $item['is_blocked'] = true;
                return BlockedUserResource::populate($item)->toArray(['id', 'resource_name', 'module_name', 'full_name', 'avatar', 'is_blocked', 'is_featured', 'user_name']);
            }, $blocked);
        }
        return $this->success($results);
    }

    /**
     * @return mixed
     * @throws NotFoundErrorException
     * @throws PermissionErrorException
     */
    public function findInvisibleMode()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.hide_from_browse')) {
            return $this->permissionError();
        }
        $userId = (int)Phpfox::getUserId();
        $user = $this->userService->get($userId, true);
        if (!$user) {
            return $this->notFoundError();
        }
        $results = [
            'module_id'   => 'user',
            'phrase'      => $this->getLocalization()->translate('enable_invisible_mode'),
            'description' => $this->getLocalization()->translate('invisible_mode_allows_you_to_browse_the_site_without_appearing_on_any_online_lists'),
            'var_name'    => 'invisible',
            'value'       => $user['is_invisible'],
        ];
        return $this->success($results);
    }

    public function updateInvisibleMode($params)
    {
        $params = $this->resolver
            ->setDefined(['invisible'])
            ->setRequired(['invisible'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.hide_from_browse')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        if ((int)$params['invisible']) {
            $value = 1;
        } else {
            $value = 0;
        }
        $this->database()->update(Phpfox::getT('user'), ['is_invisible' => $value], 'user_id = ' . (int)$userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
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
                $options[] = [
                    'label' => $this->getLocalization()->translate('friends_only'),
                    'value' => 2
                ];
                $allowOptions[] = '2';
            }
        }
        $options[] = [
            'label' => $this->getLocalization()->translate('no_one'),
            'value' => 4
        ];
        $allowOptions[] = '4';
        return [$options, $allowOptions];
    }

    /**
     * @param $params
     *
     * @return array|bool|void
     * @throws NotFoundErrorException
     * @throws PermissionErrorException
     * @throws UnknownErrorException
     * @throws ValidationErrorException
     * @throws ErrorException
     */
    public function deleteBlockedUser($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $userId = (int)$params['id'];
        $user = $this->userService->get($userId);

        if (!$userId || !$user) {
            return $this->notFoundError();
        }
        $currentUserId = (int)$this->getUser()->getId();
        if (!$currentUserId) {
            return $this->permissionError();
        }
        $blocked = $this->database()->select('*')
            ->from(':user_blocked')
            ->where('user_id = ' . $currentUserId . ' AND block_user_id = ' . $userId)
            ->execute('getSlaveRow');
        if (!$blocked) {
            return $this->notFoundError();
        }
        if (Phpfox::getService('user.block.process')->delete($userId)) {
            $this->cache()->remove('featured_users_' . $currentUserId);
            return $this->success([], [], $this->getLocalization()->translate('user_successfully_unblocked'));
        }
        return $this->error();
    }

    function findOne($params)
    {
        // TODO: Implement findOne() method.
    }

    function create($params)
    {
        // TODO: Implement create() method.
    }

    function update($params)
    {
        // TODO: Implement update() method.
    }

    function updateAccountSetting($params)
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);
        /** @var AccountSettingForm $form */
        $form = $this->createForm(AccountSettingForm::class);
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdateAccountSetting($user, $values);
            if ($success !== false) {
                return $this->success([], [], $this->getLocalization()->translate('account_settings_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    protected function processUpdateAccountSetting($user, $params)
    {
        $allowed = true;
        $params['old_user_name'] = $user['user_name'];
        if (Phpfox::getUserParam('user.can_change_email') && $user['email'] != $params['email']) {
            $allowed = Phpfox::getService('user.verify.process')->changeEmail($user, $params['email']);
            if (is_string($allowed)) {
                return $this->error($allowed);
            }
            if (Phpfox::getParam('user.verify_email_at_signup') && Phpfox::getParam('user.logout_after_change_email_if_verify')) {
                $message = $this->getLocalization()->translate('account_settings_updated_your_new_mail_address_requires_verification_and_an_email_has_been_sent_until_then_your_email_remains_the_same');
                return $this->success([], [], $message);
            }
        }

        $special = [
            'changes_allowed'           => Phpfox::getUserParam('user.total_times_can_change_user_name'),
            'total_user_change'         => $user['total_user_change'],
            'full_name_changes_allowed' => Phpfox::getUserParam('user.total_times_can_change_own_full_name'),
            'total_full_name_change'    => $user['total_full_name_change'],
            'current_full_name'         => $user['full_name']
        ];
        if ($allowed && ($iId = Phpfox::getService('user.process')->update($user['user_id'], $params, $special, true))) {
            return true;
        }
        return false;
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
        $allowVal = ['0', '1', '2', '4'];
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
        $allowVal = ['0', '1', '2', '4'];
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

    function updateItemPrivacy($params)
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('user.can_control_profile_privacy')) {
            return $this->permissionError();
        }
        $userId = $this->getUser()->getId();
        $user = $this->userService->get($userId);
        if (!isset($user['user_id'])) {
            return $this->notFoundError();
        }
        //Remove old item setting
        $itemPrivacy = Phpfox::massCallback('getGlobalPrivacySettings');
        $varName = [];
        foreach ($itemPrivacy as $module => $item) {
            foreach ($item as $var => $info) {
                $varName[] = $var;
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }

        $allowVal = ['0', '1', '2', '3', '6'];
        foreach ($params as $var => $val) {
            if (!$this->checkValidVar($var, $val, $varName, $allowVal)) {
                continue;
            }
            //Remove old
            $this->database()->delete(':user_privacy', 'user_id = ' . $userId . ' AND user_privacy = \'' . $var . '\'');

            $this->database()->insert(':user_privacy', [
                    'user_id'      => $userId,
                    'user_privacy' => $var,
                    'user_value'   => (int)$val
                ]
            );
        }
        $this->cache()->remove('user_privacy_' . $userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }

    function updateItemPrivacySettings($params)
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
        //Remove old item setting
        $itemPrivacy = Phpfox::massCallback('getGlobalPrivacySettings');
        $varName = [];
        foreach ($itemPrivacy as $module => $item) {
            foreach ($item as $var => $info) {
                $varName[] = $var;
            }
        }
        if (empty($varName)) {
            return $this->notFoundError();
        }

        $allowVal = ['0', '1', '2', '3', '6'];
        $var = $params['var_name'];
        $val = $params['value'];

        if (!$this->checkValidVar($var, $val, $varName, $allowVal)) {
            return $this->error();
        }
        //Remove old
        $this->database()->delete(':user_privacy', 'user_id = ' . $userId . ' AND user_privacy = \'' . $var . '\'');

        $this->database()->insert(':user_privacy', [
                'user_id'      => $userId,
                'user_privacy' => $var,
                'user_value'   => (int)$val
            ]
        );
        $this->cache()->remove('user_notification_' . $userId);
        return $this->success([], [], $this->getLocalization()->translate('privacy_settings_successfully_updated'));
    }

    function updateEmailNotification($params)
    {
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
        $hasType = $this->database()->isField(Phpfox::getT('user_notification'), 'notification_type');
        foreach ($params as $var => $val) {
            if (!$this->checkValidVar($var, $val, $varName, $allowVal)) {
                continue;
            }
            //Remove old
            $this->database()->delete(':user_notification', 'user_id = ' . $userId . ' AND user_notification = \'' . $var . '\'' . ($hasType ? ' AND notification_type = \'email\'' : ''));

            if ($val) {
                continue;
            }
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


    protected function checkValidVar($var, $val, $accept, $extra = [])
    {
        if (!in_array($var, $accept)) {
            return false;
        }
        if (!empty($extra) && !in_array($val, $extra)) {
            return false;
        }
        if ($var == 'dob_setting') {
            return true;
        }
        if (!preg_match('/^([^._-]*)[._-](.*)/', $var, $matches)) {
            return false;
        }
        if (!isset($matches[1])) {
            return false;
        }
        if (!Phpfox::isModule($matches[1])) {
            return false;
        }
        return true;
    }

    function addBlockedUser($params)
    {
        $params = $this->resolver->setDefined(['user_id'])
            ->setRequired(['user_id'])
            ->setAllowedTypes('user_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        $user = $this->userService->getUser($params['user_id']);
        if (!$currUser = $this->getUser()->getId()) {
            return $this->permissionError();
        }
        if (!$user || $user['profile_page_id']) {
            return $this->notFoundError($this->getLocalization()->translate('unable_to_find_this_member'));
        }
        $isBlocked = $this->userBlockService->isBlocked($currUser, $user['user_id']);
        if (!$isBlocked) {
            if (!Phpfox::getUserParam('user.can_block_other_members') || !Phpfox::getUserGroupParam($user['user_group_id'], 'user.can_be_blocked_by_others')) {
                return $this->permissionError($this->getLocalization()->translate('unable_to_block_this_user'));
            }
        }
        if ($this->userBlockProcessService->add($params['user_id'])) {
            $this->cache()->remove('featured_users_' . $currUser);
            return $this->success([], [], $this->getLocalization()->translate('user_successfully_blocked'));
        }
        return $this->permissionError($this->getErrorMessage());
    }

    function formSetting()
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);

        /** @var AccountSettingForm $form */
        $form = $this->createForm(AccountSettingForm::class, [
            'title'  => 'account_settings',
            'method' => 'PUT',
            'action' => UrlUtility::makeApiUrl('account/setting')
        ]);
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
        $form->setCanChangeFullName($this->allowChangeFullName($user, $form));
        $form->setCanChangeUserName($this->allowChangeUserName($user, $form));

        $form->assignValues(AccountResource::populate($user));

        return $this->success($form->getFormStructure());
    }

    /**
     * @param $user
     * @param $form AccountSettingForm
     *
     * @return bool
     */
    protected function allowChangeFullName($user, $form)
    {
        if (!$this->setting->getUserSetting('user.can_change_own_full_name')) {
            return false;
        }
        if (!isset($user['total_full_name_change'])) {
            return true;
        }
        $setting = $this->setting->getUserSetting('user.total_times_can_change_own_full_name');
        if ($setting) {
            $form->setFullNameDescription($this->getLocalization()->translate('total_full_name_change_out_of_allowed', [
                'total_full_name_change' => $user['total_full_name_change'],
                'allowed'                => $setting
            ]));
        }
        return ($user['total_full_name_change'] < $setting) || !$setting;
    }

    /**
     * @param $user
     * @param $form AccountSettingForm
     *
     * @return bool
     */
    protected function allowChangeUserName($user, $form)
    {
        if (!($this->setting->getUserSetting('user.can_change_own_user_name') && !$this->setting->getAppSetting('user.profile_use_id'))) {
            return false;
        }
        if (!isset($user['total_user_change'])) {
            return true;
        }
        $setting = $this->setting->getUserSetting('user.total_times_can_change_user_name');
        if ($setting) {
            $form->setUserNameDescription($this->getLocalization()->translate('total_user_change_out_of_total_user_name_changes', [
                'total_user_change' => $user['total_user_change'],
                'total'             => $setting
            ]));
        }
        return ($user['total_user_change'] < $setting) || !$setting;
    }

    /**
     * Update user language
     * @return array|bool|mixed
     * @throws UnknownErrorException
     * @throws ValidationErrorException
     */
    public function updateUserLanguage()
    {
        $form = $this->createForm(UpdateLanguageForm::class);
        if ($this->request()->isPut()) {
            if ($form->isValid()) {
                $params = $form->getValues();
                $languageId = $this->resolver->resolveSingle($params, 'language_id');
                if (empty($languageId)) {
                    return $this->validationParamsError(['language_id' => $this->getLocalization()->translate('invalid_language_package')]);
                }
                $options = $this->getLanguageIds();
                if (!in_array($languageId, $options)) {
                    return $this->validationParamsError(['language_id' => $this->getLocalization()->translate('invalid_language_package')]);
                }
                if (db()->update(':user', ['language_id' => $languageId], 'user_id = ' . (int)$this->getUser()->getId())) {
                    $cacheLib = Phpfox::getLib('cache');
                    $sLangId = $cacheLib->set(['locale', 'language_' . $languageId]);
                    if (!($aLanguage = $cacheLib->get($sLangId))) {
                        $aLanguage = db()->select('*')
                            ->from(Phpfox::getT('language'))
                            ->where("language_id = '" . db()->escape($languageId) . "'")
                            ->execute('getRow');
                    }
                    $direction = 'ltr';
                    if (isset($aLanguage['direction'])) {
                        $direction = $aLanguage['direction'];
                    }
                    return $this->success([
                        'language_id' => $languageId,
                        'direction'   => $direction
                    ]);
                }
                return $this->error($this->getErrorMessage());
            }
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->success($form->getFormStructure());
    }

    public function getLanguageIds()
    {
        $languages = Phpfox::getService('language')->get(['l.user_select = 1']);
        $options = [];
        if ($languages) {
            $options = array_map(function ($lang) {
                return $lang['language_id'];
            }, $languages);
        }
        return $options;
    }

    function patchUpdate($params)
    {
        return null;
    }

    function delete($params)
    {
        return null;
    }

    function form($params = [])
    {
        return null;
    }

    function loadResourceById($id, $returnResource = false)
    {
        return null;
    }

    public function processRow($item)
    {
        return AccountResource::populate($item)->toArray();
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