<?php

namespace Apps\Core_MobileApi\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\MultiChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\PasswordType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;

class DeleteAccountForm extends GeneralForm
{
    protected $userId;
    protected $userGroupId;
    protected $reasons = null;

    public function __construct()
    {
        $this->userId = \Phpfox::getUserId();
        $this->userGroupId = \Phpfox::getUserBy('user_group_id');
    }

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        $sectionName = 'basic';
        if ($this->reasons == null) {
            $this->setReasons();
        }
        if (is_array($this->reasons) && count($this->reasons)) {
            $this->addField('reason', MultiChoiceType::class, [
                'label'   => 'why_are_you_deleting_your_account',
                'options' => $this->reasons
            ], [], $sectionName);
        }
        $this->addField('feedback_text', TextareaType::class, [
            'label'       => 'please_tell_us_why',
            'placeholder' => 'type_something_dot'
        ], [], $sectionName);
        if (!\Phpfox::getUserBy('fb_user_id') && !\Phpfox::getUserBy('janrain_user_id')) {
            $this->addField('password', PasswordType::class, [
                'label'       => 'password',
                'placeholder' => 'enter_your_password',
                'inline'      => true,
                'required'    => true
            ], [], $sectionName);
        }
        $this->addField('submit', SubmitType::class, [
            'label' => 'delete_my_account'
        ]);
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param string $userGroupId
     */
    public function setUserGroupId($userGroupId)
    {
        $this->userGroupId = $userGroupId;
    }

    public function setReasons()
    {
        $reasons = \Phpfox::getService('user')->getReasons();
        $result = [];
        foreach ($reasons as $reason) {
            $result[] = [
                'value' => (int)$reason['delete_id'],
                'label' => $this->getLocal()->translate($reason['phrase_var'])
            ];
        }
        $this->reasons = $result;
    }
}