<?php

namespace Apps\Core_MobileApi\Version1_4\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\Type\BirthdayType;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\CustomGendersType;
use Apps\Core_MobileApi\Api\Form\Type\DateType;
use Apps\Core_MobileApi\Api\Form\Type\EmailType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\MultiChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\PasswordType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Version1_4\Api\Form\GeneralForm;
use Phpfox;

class UserRegisterForm extends GeneralForm
{
    protected $defaultGender = 1;

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        if ($this->getSetting()->getAppSetting('user.multi_step_registration_form')) {
            $this->setIsMultiStep(true);
            switch ($this->getStep()) {
                case 1:
                    $this->setSubmitButton([
                        'position' => 'append',
                        'label'    => $this->getLocal()->translate('continue')
                    ]);
                    $this->buildFormStep1();
                    break;
                case 2:
                    $this->setSubmitButton([
                        'position' => 'append',
                        'label'    => $this->getLocal()->translate('create_account')
                    ]);
                    $this->setCancelButton([
                        'position' => 'header',
                        'label'    => $this->getLocal()->translate('back'),
                        'icon'     => 'angle-left'
                    ]);
                    $this->setNextStep(0);
                    $this->buildFormStep2();
                    break;
            }
        } else {
            $this->buildProfileImageGroup();
            $this->buildAccountInfoGroup();
            $this->buildAdditionInfoGroup();
        }
        $this->addField('submit', SubmitType::class, [
            'label' => 'sign_up_button'
        ]);


    }

    /**
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function buildFormStep1()
    {
        $this->addField('current_step', HiddenType::class, [
            'value' => $this->step
        ]);
        $this->buildAccountInfoGroup();

    }

    /**
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function buildFormStep2()
    {
        $this->addField('current_step', HiddenType::class, [
            'value' => $this->step
        ]);
        $this->buildAccountInfoGroup(true);
        $this->buildProfileImageGroup();
        $this->buildAdditionInfoGroup();
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
                            'label' => 'first_name',
                            'placeholder' => 'enter_first_name',
                            'inline' => true,
                            'required' => true
                        ], null, $sectionName)
                        ->addField('last_name', TextType::class, [
                            'label' => 'last_name',
                            'placeholder' => 'enter_last_name',
                            'inline' => true,
                            'required' => true
                        ], null, $sectionName);
                }
            } else {
                if ($buildHidden) {
                    $this->addField('full_name', HiddenType::class);
                } else {
                    $this->addField('full_name', TextType::class, [
                        'label' => ($this->getSetting()->getAppSetting('user.display_or_full_name') == 'full_name' ?
                            'full_name' : 'display_name'),
                        'placeholder' => 'enter_full_name',
                        'inline' => true,
                        'required' => true
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
                    'required'    => true
                ], null, $sectionName);
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
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function buildAdditionInfoGroup()
    {
        $sectionName = 'additional_info';
        $this->addSection($sectionName, 'additional_info');
        if ($this->getSetting()->getAppSetting('core.registration_enable_dob')) {
            $this->addField('birthday', BirthdayType::class, [
                'label'       => 'birthday',
                'minDate'     => $this->getSetting()->getAppSetting('user.date_of_birth_start') . '-1-1',
                'maxDate'     => $this->getSetting()->getAppSetting('user.date_of_birth_end') . '-12-31',
                'placeholder' => 'MM/DD/YYYY',
                'displayFormat' => 'MM/DD/YYYY',
                'required'    => true,
                'inline'      => true
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
                    'label'         => $this->getLocal()->translate('i_have_read_and_agree_terms_and_privacy', [
                        'terms'   => $this->getTermsPolicyUrl(2),
                        'privacy' => $this->getTermsPolicyUrl(1, 'policy'),
                    ]),
                    'label_is_html' => true,
                    'value_default' => 0,
                    'required'      => true
                ], null, $sectionName);
        }
    }

    /**
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    protected function buildProfileImageGroup()
    {
        if ($this->getSetting()->getAppSetting('user.force_user_to_upload_on_sign_up')) {
            $sectionName = 'profile_image';
            $this
                ->addSection($sectionName, '')
                ->addField('image', FileType::class, [
                    'label'         => 'profile_image',
                    'required'      => true,
                    'style'         => 'user_avatar',
                    'file_type'     => FileType::TYPE_PHOTO,
                    'status'        => FileType::NEW_UPLOAD,
                    'direct_upload' => true
                ], null, $sectionName);
        }
    }

    public function isValid()
    {
        $passed = true;
        if (!parent::isValid()) {
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

        if ($setting->getAppSetting('user.signup_repeat_password')
            && ($this->getField('password')->getValue() != $this->getField('repassword')->getValue())) {
            $this->setInvalidField('repassword', $this->getLocal()->translate('password_do_not_match'));
            $passed = false;
        }

        if ($setting->getAppSetting('core.registration_enable_gender') && $this->isField('gender') && $genderField = $this->getField('gender')) {
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

    protected function getTimezones()
    {
        $timezones = Phpfox::getService('core')->getTimeZones();
        if ($timezones) {
            $results = [];
            foreach ($timezones as $key => $timezone) {
                $results[] = [
                    'value' => $key,
                    'label' => $timezone
                ];
            }
            return $results;
        }
        return [];
    }

    protected function genderOptions()
    {
        $genders = Phpfox::getService('core')->getGenders();
        $options = [];
        $i = 0;
        foreach ($genders as $key => $gender) {
            if ($i == 0) {
                $this->defaultGender = $key;
            }
            $options[] = [
                'value' => $key,
                'label' => $this->getLocal()->translate($gender)
            ];
            $i++;
        }
        if ($this->getSetting()->getUserSetting('user.can_add_custom_gender') || $this->getSetting()->getUserSetting('user.can_add_custom_gender') === null) {
            $options[] = [
                'value' => '127',
                'label' => $this->getLocal()->translate('others_upper')
            ];
        }
        return $options;
    }

    /**
     * @param null $sectionName
     *
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    protected function buildCustomFields($sectionName = null)
    {
        $customFields = Phpfox::getService('custom')->getForEdit([
            'user_main',
            'user_panel',
            'profile_panel'
        ], null, null, true);

        foreach ($customFields as $field) {
            if ($field['on_signup'] && $field['is_active']) {
                $require = (bool)$field['is_required'];
                $fieldName = "custom_" . $field['field_id'];
                switch ($field['var_type']) {
                    case "textarea":
                        $this->addField($fieldName, TextareaType::class, [
                            'label'       => $field['phrase_var_name'],
                            'placeholder' => 'type_something_dot',
                            'required'    => $require
                        ], null, $sectionName);
                        break;
                    case "text":
                        $this->addField($fieldName, TextType::class, [
                            'label'       => $field['phrase_var_name'],
                            'placeholder' => 'type_something_dot',
                            'required'    => $require,
                            'maxLength'   => 60
                        ], [new StringLengthValidator(0, 60)], $sectionName);
                        break;
                    case "date":
                        $this->addField($fieldName, DateType::class, [
                            'label'       => $field['phrase_var_name'],
                            'required'    => $require,
                            'placeholder' => 'YYYY-MM-DD',
                            'separate'    => true,
                            'prefix'      => $fieldName . '_',
                        ], null, $sectionName);
                        break;
                    case "select":
                        $options = [];
                        foreach ($field['options'] as $value => $option) {
                            $options[] = [
                                'label' => $this->getLocal()->translate($option['value']),
                                'value' => $value
                            ];
                        }
                        $this->addField($fieldName, ChoiceType::class, [
                            'options'  => $options,
                            'label'    => $field['phrase_var_name'],
                            'required' => $require
                        ], null, $sectionName);
                        break;
                    case "multiselect":
                    case "checkbox":
                        $options = [];
                        foreach ($field['options'] as $value => $option) {
                            $options[] = [
                                'label' => $this->getLocal()->translate($option['value']),
                                'value' => $value
                            ];
                        }
                        $this->addField($fieldName, MultiChoiceType::class, [
                            'options'  => $options,
                            'label'    => $field['phrase_var_name'],
                            'required' => $require
                        ], null, $sectionName);
                        break;
                    case "radio":
                        $options = [];
                        foreach ($field['options'] as $value => $option) {
                            $options[] = [
                                'label' => $this->getLocal()->translate($option['value']),
                                'value' => $value
                            ];
                        }
                        $this->addField($fieldName, RadioType::class, [
                            'options'  => $options,
                            'label'    => $field['phrase_var_name'],
                            'required' => $require
                        ], null, $sectionName);
                        break;
                }
            }
        }
    }

    protected function getTermsPolicyUrl($id, $default = 'terms')
    {
        $page = Phpfox::getService('page')->getPage($id);
        $path = isset($page['title_url']) ? $page['title_url'] : $default;

        return Phpfox::getLib('url')->makeUrl($path);
    }
}