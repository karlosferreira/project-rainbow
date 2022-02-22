<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 20/6/18
 * Time: 4:29 PM
 */

namespace Apps\Core_MobileApi\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\EmailType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\EmailValidator;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;

class AccountSettingForm extends GeneralForm
{

    protected $canChangeFullName;
    protected $fullNameDescription;
    protected $canChangeUserName;
    protected $userNameDescription;

    /**
     * Override build form to generate form
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    public function buildForm()
    {
        $sectionName = 'account_setting';
        $this->addSection($sectionName, 'account_settings');
        if ($this->getSetting()->getAppSetting('user.split_full_name')) {
            $this->addField('first_name', TextType::class, [
                'label'       => 'first_name',
                'editable'    => !!$this->canChangeFullName,
                'description' => $this->fullNameDescription,
                'inline'      => true,
                'placeholder' => 'enter_first_name',
                'required'    => true
            ], [new RequiredValidator()], $sectionName)
                ->addField('last_name', TextType::class, [
                    'label'       => 'last_name',
                    'editable'    => !!$this->canChangeFullName,
                    'inline'      => true,
                    'placeholder' => 'enter_last_name',
                    'required'    => true
                ], [new RequiredValidator()], $sectionName);
        } else {
            $this->addField('full_name', TextType::class, [
                'label'       => 'full_name',
                'editable'    => !!$this->canChangeFullName,
                'description' => $this->fullNameDescription,
                'inline'      => true,
                'placeholder' => 'enter_full_name',
                'required'    => true
            ], [new RequiredValidator(), new StringLengthValidator(1, $this->getSetting()->getAppSetting('user.maximum_length_for_full_name'))], $sectionName);
        }
        $this
            ->addField('user_name', TextType::class, [
                'label'          => 'username',
                'editable'       => !!$this->canChangeUserName,
                'description'    => $this->userNameDescription,
                'inline'         => true,
                'placeholder'    => 'enter_username',
                'required'       => true,
                'autoCapitalize' => 'none'
            ], [new RequiredValidator()], $sectionName)
            ->addField('email', EmailType::class, [
                'label'       => 'email',
                'editable'    => $this->getSetting()->getUserSetting('user.can_change_email'),
                'description' => $this->getSetting()->getUserSetting('user.can_change_email') && $this->getSetting()->getAppSetting('user.verify_email_at_signup') ? $this->getLocal()->translate('changing_your_email_address_requires_you_to_verify_your_new_email') : '',
                'inline'      => true,
                'placeholder' => 'enter_email_address',
                'required'    => true
            ], [new RequiredValidator(), new EmailValidator()], $sectionName)
            ->addField('language_id', HiddenType::class, [
                'label' => 'primary_language'
            ], null, $sectionName);
        if ($this->getSetting()->getUserSetting('user.can_edit_currency')) {
            $this->addField('default_currency', ChoiceType::class, [
                'options'     => $this->getCurrencies(),
                'label'       => 'preferred_currency',
                'description' => $this->getLocal()->translate('show_prices_and_make_purchases_in_this_currency'),
            ], null, $sectionName);
        }
        $this->addField('time_zone', ChoiceType::class, [
            'options'  => $this->getTimezones(),
            'label'    => 'time_zone',
        ], null, $sectionName);

        $this->addField('submit', SubmitType::class, [
            'label' => 'update'
        ]);
    }

    protected function getTimezones()
    {
        $timezones = \Phpfox::getService('core')->getTimeZones();
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

    protected function getCurrencies()
    {
        $currencies = \Phpfox::getService('core.currency')->get();
        if ($currencies) {
            $results = [];
            foreach ($currencies as $key => $currency) {
                $results[] = [
                    'value' => $key,
                    'label' => $this->getLocal()->translate($currency['name']),
                ];
            }
            return $results;
        }
        return [];
    }

    /**
     * @return mixed
     */
    public function getCanChangeFullName()
    {
        return $this->canChangeFullName;
    }

    /**
     * @param mixed $canChangeFullName
     */
    public function setCanChangeFullName($canChangeFullName)
    {
        $this->canChangeFullName = $canChangeFullName;
    }

    /**
     * @return mixed
     */
    public function getCanChangeUserName()
    {
        return $this->canChangeUserName;
    }

    /**
     * @param mixed $canChangeUserName
     */
    public function setCanChangeUserName($canChangeUserName)
    {
        $this->canChangeUserName = $canChangeUserName;
    }

    /**
     * @return mixed
     */
    public function getFullNameDescription()
    {
        return $this->fullNameDescription;
    }

    /**
     * @param mixed $fullNameDescription
     */
    public function setFullNameDescription($fullNameDescription)
    {
        $this->fullNameDescription = $fullNameDescription;
    }

    /**
     * @return mixed
     */
    public function getUserNameDescription()
    {
        return $this->userNameDescription;
    }

    /**
     * @param mixed $userNameDescription
     */
    public function setUserNameDescription($userNameDescription)
    {
        $this->userNameDescription = $userNameDescription;
    }

}