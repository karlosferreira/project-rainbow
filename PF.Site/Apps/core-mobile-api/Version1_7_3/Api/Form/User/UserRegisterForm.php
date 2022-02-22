<?php

namespace Apps\Core_MobileApi\Version1_7_3\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\BirthdayType;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\CustomGendersType;
use Apps\Core_MobileApi\Api\Form\Type\EmailType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\PasswordType;
use Apps\Core_MobileApi\Api\Form\Type\PhoneNumberType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Version1_4\Api\Form\User\UserRegisterForm as BaseUserForm;
use Phpfox;

class UserRegisterForm extends BaseUserForm
{

    protected $isUsePhone = null;

    /**
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function buildAdditionInfoGroup()
    {
        $sectionName = 'additional_info';
        $this->addSection($sectionName, 'additional_info');
        if ($this->getSetting()->getAppSetting('core.registration_enable_dob')) {
            $this->addField('birthday', BirthdayType::class, [
                'label'         => 'birthday',
                'minDate'       => $this->getSetting()->getAppSetting('user.date_of_birth_start') . '-1-1',
                'maxDate'       => $this->getSetting()->getAppSetting('user.date_of_birth_end') . '-12-31',
                'placeholder'   => 'MM/DD/YYYY',
                'displayFormat' => 'MM/DD/YYYY',
                'required'      => true,
                'inline'        => true
            ], null, $sectionName);
        }

        if ($this->getSetting()->getAppSetting('core.registration_enable_gender')) {
            $this
                ->addField('gender', RadioType::class, [
                    'options'       => $this->genderOptions(),
                    'label'         => 'i_am',
                    'value_default' => $this->defaultGender,
                    'required'      => true
                ], null, $sectionName)
                ->addField('custom_gender', CustomGendersType::class, [
                    'label'        => '',
                    'hidden_by'    => '!gender',
                    'hidden_value' => ['127'],
                    'description'  => 'separate_multiple_genders_with_commas'
                ], null, $sectionName);
        }

        if ($this->getSetting()->getAppSetting('core.registration_enable_location')) {
            $this->addCountryField(true, 'country', $sectionName);
        }

        if ($this->getSetting()->getAppSetting('core.city_in_registration')) {
            $this->addField('city_location', TextType::class, [
                'label'       => 'city',
                'inline'      => true,
                'placeholder' => 'city_name'
            ], null, $sectionName);
        }

        if ($this->getSetting()->getAppSetting('core.registration_enable_timezone')) {
            $this->addField('time_zone', ChoiceType::class, [
                'options'       => $this->getTimezones(),
                'label'         => 'time_zone',
                'value_default' => Phpfox::getParam('core.default_time_zone_offset'),
                'required'      => true
            ], null, $sectionName);
        }

        // Adding custom fields
        $this->buildCustomFields($sectionName);

        if ($this->getSetting()->getAppSetting('user.new_user_terms_confirmation')) {
            $sectionName = 'terms_and_privacy_policy';
            $this
                ->addSection($sectionName, 'terms_and_privacy_policy')
                ->addField('agree', CheckboxType::class, [
                    'label'         => $this->getLocal()->translate('mobile_i_have_read_and_agree_terms_and_privacy_new', [
                        'terms'   => $this->getTermsPolicyUrl(2),
                        'privacy' => $this->getTermsPolicyUrl(1),
                    ]),
                    'label_is_html' => true,
                    'value_default' => 0,
                    'required'      => true
                ], null, $sectionName);
        }
    }

    protected function getTermsPolicyUrl($id, $default = 'terms')
    {
        $path = '';
        switch ((int)$id) {
            case 2:
                $path = '@core/terms-policies';
                break;
            case 1:
                $path = '@core/privacy';
                break;
        }
        return $path;
    }

    /**
     * @param bool $buildHidden
     *
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function buildAccountInfoGroup($buildHidden = false)
    {
        $sectionName = 'account_info';
        if (!$buildHidden) $this->addSection($sectionName, 'account_info');
        if ($this->getSetting()->getAppSetting('user.disable_username_on_sign_up') != 'username') {
            if ($this->getSetting()->getAppSetting('user.split_full_name')) {
                $this->addField('full_name', HiddenType::class, [], null, $sectionName);
                if ($buildHidden) {
                    $this->addField('first_name', HiddenType::class);
                    $this->addField('last_name', HiddenType::class);
                } else {
                    $this
                        ->addField('first_name', TextType::class, [
                            'label'       => 'first_name',
                            'placeholder' => 'enter_first_name',
                            'inline'      => true,
                            'required'    => true
                        ], null, $sectionName)
                        ->addField('last_name', TextType::class, [
                            'label'       => 'last_name',
                            'placeholder' => 'enter_last_name',
                            'inline'      => true,
                            'required'    => true
                        ], null, $sectionName);
                }
            } else {
                if ($buildHidden) {
                    $this->addField('full_name', HiddenType::class);
                } else {
                    $this->addField('full_name', TextType::class, [
                        'label'       => ($this->getSetting()->getAppSetting('user.display_or_full_name') == 'full_name' ?
                            'full_name' : 'display_name'),
                        'placeholder' => 'enter_full_name',
                        'inline'      => true,
                        'required'    => true
                    ], null, $sectionName);
                }
            }
        }
        if ($this->getSetting()->getAppSetting('user.disable_username_on_sign_up') != 'full_name') {
            if ($buildHidden) {
                $this->addField('user_name', HiddenType::class);
            } else {
                $this->addField('user_name', TextType::class, [
                    'label'          => 'username',
                    'placeholder'    => 'enter_username',
                    'required'       => true,
                    'inline'         => true,
                    'autoCapitalize' => 'none'
                ], null, $sectionName);
            }
        }
        if ($this->getSetting()->getAppSetting('core.enable_register_with_phone_number')) {
            if ($buildHidden) {
                $this->addField('email', HiddenType::class);
                $this->addField('phone', HiddenType::class);
            } else {
                // Email Field
                $this->addField('email', EmailType::class, [
                    'label'           => 'email',
                    'placeholder'     => 'enter_email_address',
                    'required'        => !$this->isUsePhone(),
                    'ignore_validate' => $this->isUsePhone(),
                    'inline'          => true,
                    'hidden_by'       => 'use_phone'
                ], null, $sectionName);
                $this->addField('phone', PhoneNumberType::class, [
                    'label'           => 'phone_upper',
                    'placeholder'     => 'enter_phone_number',
                    'required'        => $this->isUsePhone() === null || $this->isUsePhone(),
                    'ignore_validate' => !$this->isUsePhone(),
                    'inline'          => true,
                    'hidden_by'       => '!use_phone'
                ], null, $sectionName);
            }
            if ($this->getSetting()->getAppSetting('user.reenter_email_on_signup')) {
                if ($buildHidden) {
                    $this->addField('confirm_email', HiddenType::class);
                    $this->addField('confirm_phone', HiddenType::class);
                } else {
                    $this->addField('confirm_email', EmailType::class, [
                        'label'           => 'reenter_email_address',
                        'placeholder'     => 'reenter_email_address',
                        'required'        => !$this->isUsePhone(),
                        'ignore_validate' => $this->isUsePhone(),
                        'inline'          => true,
                        'hidden_by'       => 'use_phone'
                    ], null, $sectionName);
                    $this->addField('confirm_phone', PhoneNumberType::class, [
                        'label'           => 'reenter_phone',
                        'placeholder'     => 'reenter_phone_number',
                        'required'        => $this->isUsePhone() === null || $this->isUsePhone(),
                        'ignore_validate' => !$this->isUsePhone(),
                        'inline'          => true,
                        'hidden_by'       => '!use_phone'
                    ], null, $sectionName);
                }
            }
            if (!$buildHidden) {
                $this->addField('use_phone', RadioType::class, [
                    'options'       => [
                        [
                            'value' => 0,
                            'label' => $this->getLocal()->translate('use_your_email_address')
                        ],
                        [
                            'value' => 1,
                            'label' => $this->getLocal()->translate('use_your_phone_number')
                        ]
                    ],
                    'label'         => '',
                    'required'      => true,
                    'value_default' => 0
                ], null, $sectionName);
            } else {
                $this->addField('use_phone', HiddenType::class);
            }
        } else {
            if ($buildHidden) {
                $this->addField('email', HiddenType::class);
            } else {
                // Email Field
                $this->addField('email', EmailType::class, [
                    'label'       => 'email',
                    'placeholder' => 'enter_email_address',
                    'required'    => true,
                    'inline'      => true
                ], null, $sectionName);
            }
            if ($this->getSetting()->getAppSetting('user.reenter_email_on_signup')) {
                if ($buildHidden) {
                    $this->addField('confirm_email', HiddenType::class);
                } else {
                    $this->addField('confirm_email', EmailType::class, [
                        'label'       => 'reenter_email_address',
                        'placeholder' => 'please_reenter_your_email_again',
                        'required'    => true,
                        'inline'      => true
                    ], null, $sectionName);
                }
            }
        }
        if ($buildHidden) {
            $this->addField('password', HiddenType::class);
        } else {
            $this->addField('password', PasswordType::class, [
                'label'       => 'password',
                'placeholder' => 'enter_password',
                'required'    => true,
                'inline'      => true
            ], null, $sectionName);
        }
        if ($this->getSetting()->getAppSetting('user.signup_repeat_password')) {
            if ($buildHidden) {
                $this->addField('repassword', HiddenType::class);
            } else {
                $this->addField('repassword', PasswordType::class, [
                    'label'       => 'repassword',
                    'placeholder' => 'reenter_password',
                    'required'    => true
                ], null, $sectionName);
            }
        }

        // Membership Field
        if ($this->getSetting()->isApp('Core_Subscriptions') && $this->getSetting()->getAppSetting('subscribe.enable_subscription_packages')) {
            if ($buildHidden) {
                $this->addField('package_id', HiddenType::class);
            } else {
                $this->addMembershipPackageField($this->getSetting()->getAppSetting('subscribe.subscribe_is_required_on_sign_up'), 'package', 'membership', true);
            }
        }
    }

    /**
     * @return bool
     */
    public function isUsePhone()
    {
        return $this->isUsePhone;
    }

    /**
     * @param bool $isUsePhone
     */
    public function setIsUsePhone($isUsePhone)
    {
        $this->isUsePhone = $isUsePhone;
    }

    public function isValid()
    {
        $passed = true;
        if (!GeneralForm::isValid()) {
            $passed = false;
        }

        $setting = $this->getSetting();
        if ($setting->getAppSetting('user.disable_username_on_sign_up') != 'full_name') {
            // Check username
            $minLength = $setting->getAppSetting('user.min_length_for_username', 5);
            $maxLength = $setting->getAppSetting('user.max_length_for_username', 25);
            if (version_compare(Phpfox::getVersion(), '4.8.6', '>=')) {
                $regex = str_replace(['min', 'max'], [$minLength, $maxLength], $setting->getAppSetting('core.username_regex_rule'));
            } else {
                $regex = '/^[a-zA-Z0-9_\-]{' . $minLength . ',' . $maxLength . '}$/';
            }
            $user_name = $this->getField('user_name')->getValue();
            if (!preg_match($regex, $user_name) || !Phpfox::getService('ban')->check('username', $user_name)) {
                $this->setInvalidField('user_name', $this->getLocal()->translate('provide_a_valid_user_name', [
                    'min' => $minLength,
                    'max' => $maxLength
                ]));
                $passed = false;
            }
        }

        // Check email
        $isUsePhone = $setting->getAppSetting('core.enable_register_with_phone_number') && $this->getField('use_phone')->getValue();
        if (!$isUsePhone) {
            $email = $this->getField('email')->getValue();
            if (!Phpfox::getLib('mail')->checkEmail($email)) {
                $this->getField('email')->setError('email_is_not_valid');
                $this->setInvalidField('email', $this->getField('email')->getTitle());
                $passed = false;
            }

            if (!Phpfox::getService('ban')->check('email', $email)) {
                $this->setInvalidField('email', $this->getLocal()->translate('this_email_is_not_allowed_to_be_used'));
                $passed = false;
            }

            if ($setting->getAppSetting('user.reenter_email_on_signup')
                && ($email != $this->getField('confirm_email')->getValue())) {
                $this->setInvalidField('confirm_email', $this->getLocal()->translate('email_s_do_not_match'));
                $this->getField('confirm_email')->setError('email_s_do_not_match');
                $passed = false;
            }
        } else {
            $phone = $this->getField('phone')->getValue();
            if ($setting->getAppSetting('user.reenter_email_on_signup')
                && ($phone != $this->getField('confirm_phone')->getValue())) {
                $this->setInvalidField('confirm_phone', $this->getLocal()->translate('phone_number_s_do_not_match'));
                $this->getField('confirm_phone')->setError('phone_number_s_do_not_match');
                $passed = false;
            }
        }

        if ($setting->getAppSetting('user.signup_repeat_password')
            && ($this->getField('password')->getValue() != $this->getField('repassword')->getValue())) {
            $this->setInvalidField('repassword', $this->getLocal()->translate('password_do_not_match'));
            $passed = false;
        }

        if ($setting->getAppSetting('core.registration_enable_gender') && $genderField = $this->getField('gender')) {
            $gender = $genderField->getValue();
            $customGender = $this->getField('custom_gender')->getValue();
            $custom = explode(',', $customGender);
            if ($gender == '127' && (empty($customGender) || (count($custom) == 1 && !$custom[0]))) {
                $this->setInvalidField('custom_gender', $this->getLocal()->translate('please_type_at_least_one_custom_gender'));
                $passed = false;
            }
        }

        return $passed;
    }
}