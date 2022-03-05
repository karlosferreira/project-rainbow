<?php


namespace Apps\Core_MobileApi\Api\Form\Subscribe;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Phpfox;


class ChangePackageForm extends GeneralForm
{
    protected $action = "mobile/subscribe/change-package";

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    function buildForm($options = null, $data = [])
    {
        if ($this->getSetting()->isApp('Core_Subscriptions') && $this->getSetting()->getAppSetting('subscribe.enable_subscription_packages') && (int)Phpfox::getUserBy('user_group_id') != 1) {
            $this->addMembershipPackageField(true, 'package', null, true, [
                'display_type' => 'inline',
            ]);
        }
        $this->addField('user_id', HiddenType::class)
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }
}