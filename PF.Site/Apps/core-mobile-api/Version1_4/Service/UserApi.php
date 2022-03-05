<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_4\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Resource\SubscriptionResource;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Version1_4\Api\Form\User\UserRegisterForm;
use Exception;
use Phpfox;
use Phpfox_Error;
use Phpfox_Request;
use User_Service_Process;

class UserApi extends \Apps\Core_MobileApi\Service\UserApi
{
    /**
     * @var User_Service_Process
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
     *
     * @param $params
     *
     * @return mixed
     * @throws Exception
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
                        'password'            => $values['password'],
                        'status_id'           => (int)$user['status_id'],
                        'pending_purchase'    => $purchase,
                        'default_country_iso' => Phpfox::getLib('request')->getIpInfo(null, 'country_code'),
                    ], []);
                }
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }

        return $this->error($this->getErrorMessage());

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

        $this->validateSignupEmail($values['email']);

        if (Phpfox_Error::isPassed()) {
            if ($onlyValidate) {
                return true;
            }
            return $this->getProcessService()->add($values);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(UserAccessControl::FEATURE, $item);
        $pass = false;
        $message = '';
        $featureService = Phpfox::getService('user.featured.process');
        if ($feature && $featureService->feature($id)) {
            $message = $this->getLocalization()->translate('user_successfully_featured');
            $pass = true;
        } elseif (!$feature && $featureService->unfeature($id)) {
            $message = $this->getLocalization()->translate('user_successfully_unfeatured');
            $pass = true;
        }
        if ($pass) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $message);
        }
        return $this->error();
    }
}