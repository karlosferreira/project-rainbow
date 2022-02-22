<?php

namespace Apps\Core_MobileApi\Version1_7_3\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\User\UserSearchForm;
use Apps\Core_MobileApi\Api\Resource\ActivityPointResource;
use Apps\Core_MobileApi\Api\Resource\BlockedUserResource;
use Apps\Core_MobileApi\Api\Resource\EmailNotificationSettingsResource;
use Apps\Core_MobileApi\Api\Resource\ItemPrivacySettingsResource;
use Apps\Core_MobileApi\Api\Resource\ProfilePrivacySettingsResource;
use Apps\Core_MobileApi\Api\Resource\SubscriptionResource;
use Apps\Core_MobileApi\Api\Resource\UserInfoResource;
use Apps\Core_MobileApi\Api\Resource\UserPhotoResource;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Resource\UserStatisticResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Version1_7_3\Api\Form\User\UserRegisterForm;
use Apps\Core_MobileApi\Version1_7_3\Api\Resource\PushNotificationSettingsResource;
use Apps\Core_MobileApi\Version1_7_3\Api\Resource\SmsNotificationSettingsResource;
use Exception;
use Phpfox;
use Phpfox_Error;
use Phpfox_Request;

class UserApi extends \Apps\Core_MobileApi\Version1_4\Service\UserApi
{
    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        return new MobileApp('user', [
            'title'           => $l->translate('members'),
            'home_view'       => 'tab',
            'main_resource'   => new UserResource([]),
            'other_resources' => [
                new ActivityPointResource([]),
                new UserInfoResource([]),
                new BlockedUserResource([]),
                new EmailNotificationSettingsResource([]),
                new PushNotificationSettingsResource([]),
                new SmsNotificationSettingsResource([]),
                new ItemPrivacySettingsResource([]),
                new ProfilePrivacySettingsResource([]),
                new UserPhotoResource([]),
                new UserStatisticResource([])
            ]
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
    }

    /**
     * @var \User_Service_Process
     */

    public function form($params = [])
    {
        $currentStep = $this->resolver->resolveSingle($params, 'current_step', 'int', [], 1);
        $nextStep = $this->resolver->resolveSingle($params, 'next_step', 'int', [], 2);
        $values = $this->resolver->resolveSingle($params, 'values', 'array', [], []);
        $this->denyAccessUnlessGranted(UserAccessControl::ADD);
        /** @var UserRegisterForm $form */
        $form = $this->createForm(UserRegisterForm::class, [
            'title'  => $this->getLocalization()->translate('sign_up', [
                'site' => $this->getSetting()->getAppSetting('core.site_title'),
            ]),
            'action' => UrlUtility::makeApiUrl('user'),
            'method' => 'post',
        ]);
        $form->setStep($currentStep);
        $form->setNextStep($nextStep);

        if ($currentStep > 1) {
            $form->assignValues($values);
            return $this->success([
                'module_name'   => 'user',
                'resource_name' => 'user',
                'formData'      => $form->getFormStructure()
            ]);
        }
        return $this->success($form->getFormStructure());
    }

    /**
     * Register user
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function create($params)
    {
        // by pass Anti-Spam Security Questions
        if (!defined('PHPFOX_IS_FB_USER')) {
            define('PHPFOX_IS_FB_USER', true);
        }
        $currentStep = $this->resolver->resolveSingle($params, 'current_step', 'int', [], 1);
        $this->denyAccessUnlessGranted(UserAccessControl::ADD);
        /** @var UserRegisterForm $form */
        $form = $this->createForm(UserRegisterForm::class);
        $form->setStep($currentStep);
        $form->buildForm();
        $form->buildValues();
        $form->setIsUsePhone(!!$form->getField('use_phone')->getValue());
        if ($form->isValid() && $values = $form->getValues()) {
            if ($form->isMultiStep() && $form->getStep() == 1) {
                $this->processCreate($values, true);
                //Process step 1
                return $this->form(['current_step' => 2, 'values' => $values]);
            } else {
                // force subscription
                $values['custom'] = $form->getGroupValues('custom');
                if (!empty($values['gender']) && $values['gender'] == '127') {
                    $values['gender'] = 'custom';
                }
                $id = $this->processCreate($values);

                //In case user must pay subscription
                $purchase = [];
                if (defined('PHPFOX_MUST_PAY_FIRST')) {
                    $purchase = Phpfox::getService('subscribe.purchase')->getPurchase(PHPFOX_MUST_PAY_FIRST, true);
                    $package = Phpfox::getService('subscribe')->getPackage($purchase['package_id']);
                    if ($package) {
                        $purchase['title'] = $package['title'];
                        $purchase['description'] = $package['description'];
                        $purchase['image_path'] = $package['image_path'];
                        $purchase['server_id'] = $package['server_id'];
                    }
                    $purchase = SubscriptionResource::populate($purchase)->toArray();
                    $id = $purchase['user_id'];
                }

                $user = Phpfox::getService('user')->get($id, true);
                if ($this->isPassed() && $user) {
                    return $this->success([
                        'id'                  => (int)$user['user_id'],
                        'email'               => $user['email'],
                        'full_phone_number'   => isset($user['full_phone_number']) ? $user['full_phone_number'] : '',
                        'phone_number'        => isset($user['phone_number']) ? $user['phone_number'] : '',
                        'default_country_iso' => Phpfox::getLib('request')->getIpInfo(null, 'country_code'),
                        'password'            => $values['password'],
                        'status_id'           => (int)$user['status_id'],
                        'is_use_phone'        => defined('PHPFOX_FORCE_VERIFY_PHONE_NUMBER') && PHPFOX_FORCE_VERIFY_PHONE_NUMBER,
                        'sent_token'          => defined('PHPFOX_FORCE_VERIFY_PHONE_NUMBER') && PHPFOX_FORCE_VERIFY_PHONE_NUMBER,
                        'pending_purchase'    => $purchase
                    ], []);
                }
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->error($this->getErrorMessage());
    }

    /**
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function findMe()
    {
        if ($this->getUser()->getId()) {
            $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED, null, null,
                $this->getLocalization()->translate('can_not_login'));

            return $this->findOne(['id' => $this->getUser()->getId(), 'is_me' => true]);
        }
        return $this->success([]);
    }

    protected function processCreate($values, $onlyValidate = false)
    {
        if (!empty($values['custom'])) {
            // Hard code to bypass custom fields checking
            Phpfox_Request::instance()->set('custom', $values['custom']);
        }
        if (isset($values['user_name']) && !$this->getSetting()->getAppSetting('user.profile_use_id') && ($this->getSetting()->getAppSetting('user.disable_username_on_sign_up') != 'full_name')) {
            Phpfox::getService('user.validate')->user($values['user_name'], true);
        }
        if (!empty($values['use_phone']) && $this->getSetting()->getAppSetting('core.enable_register_with_phone_number')) {
            if ($this->validateSignupPhone($values['phone'])) {
                $values['email'] = $values['phone'];
                if (isset($values['reenter_phone'])) {
                    $values['reenter_email'] = $values['reenter_phone'];
                }
            }
        } else {
            $this->validateSignupEmail($values['email']);
        }
        if (Phpfox_Error::isPassed()) {
            if ($onlyValidate) {
                return true;
            }
            return $this->getProcessService()->add($values);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    protected function validateSignUpPhone($phone, $iIgnoredUserId = false)
    {
        $oPhone = Phpfox::getLib('phone');
        if ($oPhone->setRawPhone($phone) && $oPhone->isValidPhone()) {
            $phone = $oPhone->getPhoneE164();
        } else {
            return Phpfox_Error::set(_p('phone_number_is_invalid'));
        }
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(':user')
            ->where("full_phone_number = '" . $this->database()->escape($phone) . "'" . ($iIgnoredUserId ? " AND user_id <> " . (int)$iIgnoredUserId : ""))
            ->execute('getSlaveField');

        if ($iCnt && $phone) {
            return Phpfox_Error::set(_p('mobile_phone_is_in_use_and_user_can_login', ['phone' => trim(strip_tags($phone))]));
        }
        return true;
    }

    public function sendRegistrationSms($params)
    {
        $params = $this->resolver
            ->setRequired(['phone'])
            ->setDefined(['email', 'user_id'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $isPhone = false;
        $value = isset($params['email']) ? $params['email'] : '';
        if (empty($value)) {
            $isPhone = true;
            $value = $params['phone'];
        }

        $phoneLib = Phpfox::getLib('phone');
        $phoneLib->setRawPhone($params['phone']);
        if ($phoneLib->isValidPhone()) {
            $phone = $phoneLib->getPhoneE164();
            if (!Phpfox::getService('user.validate')->phone($phone, true, true, isset($params['user_id']) ? (int)$params['user_id'] : $this->getUser()->getId())) {
                Phpfox_Error::reset();
                return $this->error($this->getLocalization()->translate('mobile_phone_is_in_use_and_user_can_login', ['phone' => $params['phone']]));
            }
            if ($isPhone) {
                $value = $phone;
            }
        } else {
            return $this->error($this->getLocalization()->translate('invalid_phone_number_or_contact_admin', ['phone' => $params['phone']]));
        }

        $sendToken = Phpfox::getService('user.verify')->getVerifyHashByEmail($value, $isPhone);
        $sendToken = substr($sendToken, 0, 3) . ' ' . substr($sendToken, 3);

        $message = _p('sms_registration_verification_message', ['token' => $sendToken]);
        if (Phpfox::getLib('phpfox.verify')->sendSMS($phone, $message)) {
            return $this->success([]);
        }

        return $this->error($this->getLocalization()->translate('invalid_phone_number_or_contact_admin', ['phone' => $params['phone']]));
    }

    public function verifyRegistration($params)
    {
        $code = $this->resolver->setRequired(['code'])->resolveSingle($params, 'code');
        $loginAgain = $this->resolver->resolveSingle($params, 'login_again');

        if (empty($code)) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (Phpfox::getService('user.verify.process')->verify($code)) {
            if ($this->getSetting()->getAppSetting('user.approve_users')) {
                return $this->success([], [], $this->getLocalization()->translate('your_account_is_pending_approval'));
            }
            return $this->success([], [], $this->getLocalization()->translate(!$loginAgain ? 'your_account_has_been_verified' : 'your_account_has_been_verified_please_log_in_with_the_information_you_provided_during_sign_up'));
        }

        return $this->error($this->getLocalization()->translate('invalid_verification_token'));
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws Exception
     */
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(UserAccessControl::VIEW);
        $params = $this->resolver->setDefined(['limit', 'page', 'q', 'gender', 'age_from', 'age_to', 'sort', 'view', 'city', 'zip_code', 'country', 'state', 'country_state', 'age'])
            ->setDefault([
                'limit' => 10,
                'page'  => 1,
            ])
            ->setAllowedValues('sort', ['name', 'active'])
            ->setAllowedValues('view', ['online', 'featured', 'recommend'])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        //Get customs search
        $form = $this->createForm(UserSearchForm::class);
        $customs = $form->getGroupValues('custom');
        $customs = array_filter($customs);

        if (!empty($params['country_state']) && is_array($params['country_state'])) {
            $params['country'] = isset($params['country_state'][0]) ? $params['country_state'][0] : '';
            $params['state'] = isset($params['country_state'][1]) ? $params['country_state'][1] : 0;
        }

        if (!empty($params['age']) && is_array($params['age'])) {
            $params['age_from'] = isset($params['age']['from']) ? $params['age']['from'] : null;
            $params['age_to'] = isset($params['age']['to']) ? $params['age']['to'] : null;
        }
        if ($params['view'] != 'recommend' && $params['view'] != 'featured' && $params['view'] != 'online') {
            $search = $params['q'];
            $gender = $params['gender'];
            $from = $params['age_from'];
            $to = $params['age_to'];
            $country = $params['country'];
            $state = $params['state'];
            $city = $params['city'];
            $zipcode = $params['zip_code'];
            $year = intval(date('Y'));
            $sort = ($params['sort'] == 'active' ? 'u.last_activity DESC' : 'u.full_name ASC');

            $bIsGender = false;

            $aConditions = [];

            $aConditions[] = "AND (u.profile_page_id = 0) ";

            $oDb = Phpfox::getLib('database');

            if ($search) {
                $condition = 'AND (u.user_name LIKE \'%' . $oDb->escape($search) . '%\' OR u.full_name LIKE \'%' . $oDb->escape($search) . '%\'';
                if (function_exists('filter_var')) {
                    $condition .= filter_var($search, FILTER_VALIDATE_EMAIL) ? (' OR u.email LIKE \'' . $oDb->escape($search) . '\'') : '';
                } elseif (preg_match('/^[0-9a-zA-Z]([\-+.\w]*[0-9a-zA-Z]?)*@([0-9a-zA-Z\-.\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,}$/', $search) && strlen($search) <= 100) {
                    $condition .= ' OR u.email LIKE \'' . $oDb->escape($search) . '\'';
                }
                if ($this->getSetting()->getAppSetting('core.enable_register_with_phone_number')) {
                    //Search phone number
                    $oPhone = Phpfox::getLib('phone');
                    if ($oPhone->setRawPhone(trim($search)) && $oPhone->isValidPhone()) {
                        $fullPhone = $oPhone->getPhoneE164();
                        $condition .= ' OR u.full_phone_number LIKE \'' . $fullPhone . '\'';
                    }
                }
                $condition .= ')';
                $aConditions[] = $condition;
            }

            if ($gender) {
                $aConditions[] = 'AND u.gender = \'' . $oDb->escape($gender) . '\'';
            }
            if ($country != 'null' && $country) {
                $aConditions[] = 'AND u.country_iso = \'' . $oDb->escape($country) . '\'';
            }

            if ((int)$state > 0) {
                $aConditions[] = 'AND ufield.country_child_id = ' . (int)$state;
            }

            if ($city) {
                $aConditions[] = 'AND ufield.city_location = \'' . $oDb->escape(Phpfox::getLib('parse.input')->convert($city)) . '\'';
            }

            if ($zipcode) {
                $aConditions[] = 'AND ufield.postal_code = ' . $zipcode;
            }
            $bAgeSearch = false;
            if ($from) {
                $aConditions[] = 'AND u.birthday_search <= \'' . Phpfox::getLib('date')->mktime(0, 0, 0, 1, 1, $year - $from) . '\'' . ((defined('PHPFOX_IS_ADMIN_SEARCH')
                        && $this->getSetting()->getUserSetting('user.remove_users_hidden_age')) ? '' : ' AND ufield.dob_setting IN(0,1,2)');
                $bIsGender = true;
                $bAgeSearch = true;
            }

            if ($to) {
                $aConditions[] = 'AND u.birthday_search >= \'' . Phpfox::getLib('date')->mktime(0, 0, 0, 1, 1, $year - $to) . '\'' . ((defined('PHPFOX_IS_ADMIN_SEARCH')
                        && $this->getSetting()->getUserSetting('user.remove_users_hidden_age')) ? '' : ' AND ufield.dob_setting IN(0,1,2)');
                $bIsGender = true;
                $bAgeSearch = true;
            }
            if ($bAgeSearch) {
                $aConditions[] = 'AND u.birthday IS NOT NULL';
            }
            if ($this->getUser()->getId()) {
                $aBlockedUserIds = Phpfox::getService('user.block')->get(null, true);
                if (!empty($aBlockedUserIds)) {
                    $aConditions[] = 'AND u.user_id NOT IN (' . implode(',', $aBlockedUserIds) . ')';
                }
            }
            $aConditions[] = 'AND u.status_id = 0 AND u.view_id = 0';

            // search by conditions.


            /** @var \User_Service_Browse $browseService */
            $browseService = Phpfox::getService('user.browse');

            list($iCnt, $aUsers) = $browseService
                ->extend(true)
                ->conditions($aConditions)
                ->page($params['page'])
                ->limit($params['limit'])
                ->gender($bIsGender)
                ->sort($sort)
                ->custom($customs)
                ->get();

            if ($iCnt < ($params['page'] - 1) * $params['limit']) {
                return $this->success();
            }
        } else {
            $isNoFriend = false;
            if ($params['view'] == "recommend") {
                $isNoFriend = true;
                if ($this->getUser()->getId() > 0 && Phpfox::isModule('friend')) {
                    $aUsers = Phpfox::getService('friend.suggestion')->get();
                } else {
                    $aUsers = [];
                }
            } elseif ($params['view'] == "featured") {
                list($aUsers) = Phpfox::getService('user.featured')->get();
                uasort($aUsers, function ($a, $b) {
                    return ($a['full_name'] > $b['full_name']);
                });
                $aUsers = array_values($aUsers);

            } else {
                $aUsers = Phpfox::getService('user.featured')->getRecentActiveUsers();
            }
            if (Phpfox::isModule('friend')) {
                if (count($aUsers)) {
                    foreach ($aUsers as $key => $aUser) {
                        $aUsers[$key]['is_friend'] = $isNoFriend ? 0 : Phpfox::getService('friend')->isFriend($this->getUser()->getId(), $aUser['user_id']);
                    }
                }
            }
        }
        if ($aUsers) {
            $this->processRows($aUsers);
        }

        return $this->success($aUsers);
    }
}