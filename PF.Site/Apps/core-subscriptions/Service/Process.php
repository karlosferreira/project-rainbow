<?php
namespace Apps\Core_Subscriptions\Service;

use Core\Lib;
use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Image;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Service;


defined('PHPFOX') or exit('NO DICE!');

class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_package');
    }

    public function cancel($gateway, $subscriptionId)
    {
        if (empty($gateway) || empty($subscriptionId)) {
            return false;
        }

        $gatewaySettings = db()->select('setting')
            ->from(':api_gateway')
            ->where([
                'gateway_id' => $gateway,
            ])->executeField(false);
        $gatewaySettings = unserialize($gatewaySettings);

        switch ($gateway) {
            case 'paypal':
                if (empty($gatewaySettings['client_id']) || empty($gatewaySettings['client_secret'])) {
                    return false;
                }

                try {
                    $userClientId = $gatewaySettings['client_id'];
                    $userClientSecret = $gatewaySettings['client_secret'];
                    $token = Phpfox::getService('subscribe.helper')->getPaypalAccessToken($userClientId, $userClientSecret);

                    if ($token === false) {
                        return false;
                    }

                    $apiUrl = Phpfox::getService('subscribe.helper')->getApiUrl('subscription/cancel', 'paypal', [
                        '{id}' => $subscriptionId,
                    ]);

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $apiUrl);
                    curl_setopt($curl, CURLOPT_HEADER, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $token,
                    ]);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
                        'reason' => 'I want to cancel',
                    ]));
                    curl_exec($curl);
                } catch (\Exception $exception) {
                    \Phpfox_Error::set($exception->getMessage());
                }

                break;
        }
    }

    public function add($aVals, $iUpdateId = null)
    {
        $aVals['background_color'] = !empty($aVals['background_color']) ? '#'.$aVals['background_color'] : '#ebf1f5';
        //Validate title and description
        $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();
        if (!isset($aVals['title'][$sDefaultLanguageCode]) || empty($aVals['title'][$sDefaultLanguageCode])) {
            return Phpfox_Error::set(_p('provide_a_message_for_the_package'));
        }
        if (!isset($aVals['description'][$sDefaultLanguageCode]) || empty($aVals['description'][$sDefaultLanguageCode])) {
            return Phpfox_Error::set(_p('provide_a_description_for_the_package'));
        }
        //Add phrase for title & description
        $aLanguages = Phpfox::getService('language')->getAll();
        foreach ($aLanguages as $aLanguage) {
            if (empty($aVals['title'][$aLanguage['language_code']])) {
                $aVals['title'][$aLanguage['language_code']] = $aVals['title'][$sDefaultLanguageCode];
            }
            if (empty($aVals['description'][$aLanguage['language_code']])) {
                $aVals['description'][$aLanguage['language_code']] = $aVals['description'][$sDefaultLanguageCode];
            }
        }
        $bCheckSubscriptions = false;
        if ($iUpdateId) {
            //Edit case, update phrases only
            $aPackage = Phpfox::getService('subscribe')->getForEdit($iUpdateId);
            $bCheckSubscriptions = (int)Phpfox::getService('subscribe')->checkNumbersOfSubscription($aPackage['package_id']);
            $bAlreadyPhrase = true;
            foreach ($aLanguages as $aLanguage) {
                $iPhraseId = Phpfox::getLib('database')->select('phrase_id')
                    ->from(':language_phrase')
                    ->where('language_id="' . $aLanguage['language_id'] . '" AND var_name="' . $aPackage['title'] . '"' )
                    ->executeField();
                if ($iPhraseId) {
                    Phpfox::getService('language.phrase.process')->update($iPhraseId, $aVals['title'][$aLanguage['language_id']]);
                } else {
                    $bAlreadyPhrase = false;
                    break;
                }
                $iPhraseId = Phpfox::getLib('database')->select('phrase_id')
                    ->from(':language_phrase')
                    ->where('language_id="' . $aLanguage['language_id'] . '" AND var_name="' . $aPackage['description'] . '"' )
                    ->executeField();
                if ($iPhraseId) {
                    Phpfox::getService('language.phrase.process')->update($iPhraseId, $aVals['description'][$aLanguage['language_id']]);
                }
            }
            if (!$bAlreadyPhrase) {
                $sTitleVarName = 'subscription_package_title_' . md5($sDefaultLanguageCode . time());
                $sDescriptionVarName = 'subscription_package_description_' . md5($sDefaultLanguageCode . time());
                Lib::phrase()->addPhrase($sTitleVarName, $aVals['title']);
                Lib::phrase()->addPhrase($sDescriptionVarName , $aVals['description'], true);
            }
        } else {
            //Add case, add new phrase
            $sTitleVarName = 'subscription_package_title_' . md5($sDefaultLanguageCode . time());
            $sDescriptionVarName = 'subscription_package_description_' . md5($sDefaultLanguageCode . time());
            Lib::phrase()->addPhrase($sTitleVarName, $aVals['title']);
            Lib::phrase()->addPhrase($sDescriptionVarName , $aVals['description'], true);
        }

        //Validation other fields
        $aForms = array(
            'is_registration' => array(
                'message' => _p('provide_if_the_package_should_be_added_to_the_registration_form'),
                'type' => 'int:required'
            ),
            'is_active' => array(
                'message' => _p('select_if_the_package_is_active_or_not'),
                'type' => 'int:required'
            ),
            'show_price' => array(
                'type' => 'int:required'
            ),
            'background_color' => array('type' => 'string'),
            'visible_group' => array('type' => 'string'),
            'is_free' => array('type' => 'int'),
        );

        if (!isset($aVals['is_free']) || $aVals['is_free'] != 1) {
            $aForms['cost'] = [
                'message' => _p('provide_a_price_for_the_package'),
                'type' => 'currency',
            ];
        }

        if(!$bCheckSubscriptions)
        {
            $aForms['user_group_id'] = array(
                'message' => _p('provide_a_user_group_on_success'),
                'type' => 'int:required'
            );

            $aForms['fail_user_group'] =  array(
                'message' => _p('provide_a_user_group_on_cancellation'),
                'type' => 'int:required'
            );

            $aForms['number_day_notify_before_expiration'] = array('type' => 'int');
        }


        $bIsRecurring = false;
        if (isset($aVals['is_recurring']) && $aVals['is_recurring'] && !$bCheckSubscriptions) {
            $aForms['recurring_cost'] = array(
                'message' => _p('provide_a_recurring_cost'),
                'type' => 'currency:required'
            );
            $aForms['recurring_period'] = array(
                'message' => _p('provide_a_recurring_period'),
                'type' => 'int:required'
            );
            $aForms['allow_payment_methods'] = array(
                'message' => _p('must_provide_at_least_1_payment_method'),
                'type' => 'array:required'
            );
            $bIsRecurring = true;
        }
        if ($iUpdateId !== null ){
            if (isset($aVals['is_recurring']) && !$aVals['is_recurring']) {
                $aCacheForm = $aVals;
            }
        }

        $sVisibleGroup = !empty($aVals['visible_group']) ? serialize($aVals['visible_group']) : '';
        $aVals['visible_group'] = $sVisibleGroup;

        if(!$bCheckSubscriptions)
        {
            $aVals['number_day_notify_before_expiration'] = !empty($aVals['number_day_notify_before_expiration']) ? (int)$aVals['number_day_notify_before_expiration'] : 0;
        }

        $aVals['is_free'] = !empty($aVals['is_free']) ? 1 : 0;
        $aCost = !empty($aVals['cost']) ? $aVals['cost'] : [];

        $aVals = $this->validator()->process($aForms, $aVals);

        if (!Phpfox_Error::isPassed()) {
            return false;
        }

        if ($iUpdateId !== null) {
            if (isset($aCacheForm['is_recurring']) && !$aCacheForm['is_recurring']) {
                $aVals['recurring_period'] = 0;
                $aVals['recurring_cost'] = null;
                $aVals['number_day_notify_before_expiration'] = 0;
            }
        }

        if (!empty($aVals['is_free'])) {
            foreach($aCost as $keycost => $aInputCost) {
                $aCost[$keycost] = 0;
            }
            $aVals['cost'] = serialize($aCost);
        } else {
            $aVals['cost'] = serialize($aCost);
        }

        if ($bIsRecurring && !$bCheckSubscriptions) {
            $aVals['recurring_cost'] = serialize($aVals['recurring_cost']);
        }

        $iTotalPaymentMethods = Phpfox::getService('subscribe')->getTotalPaymentMethods();
        if (!empty($aVals['allow_payment_methods']) && count($aVals['allow_payment_methods']) < $iTotalPaymentMethods) {
            $aVals['allow_payment_methods'] = serialize($aVals['allow_payment_methods']);
        } else {
            $aVals['allow_payment_methods'] = null;
        }

        if (!empty($_FILES['image']['name'])) {
            $aImage = Phpfox_File::instance()->load('image', array('jpg', 'gif', 'png'));

            if ($aImage === false) {
                return false;
            }
        }
        if (!$iUpdateId || !$bAlreadyPhrase) {
            $aVals['title'] = $sTitleVarName;
            $aVals['description'] = $sDescriptionVarName;
        }
        $aVals['background_color'] = Phpfox::getLib('parse.input')->clean($aVals['background_color']);
        $aVals['time_updated'] = PHPFOX_TIME;

        if ($iUpdateId !== null) {
            $iId = $iUpdateId;

            $this->database()->update($this->_sTable, $aVals, 'package_id = ' . (int)$iUpdateId);
        } else {
            $iLastOrderId = (int)$this->database()->select('ordering')->from($this->_sTable)->order('ordering DESC')->execute('getSlaveField');
            $aVals['ordering'] = ($iLastOrderId + 1);

            $iId = $this->database()->insert($this->_sTable, $aVals);
        }

        if (!empty($_FILES['image']['name']) && ($sFileName = Phpfox_File::instance()->upload('image',
                Phpfox::getParam('subscribe.dir_image'), $iId))) {
            $this->database()->update($this->_sTable, array(
                'image_path' => $sFileName,
                'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
            ), 'package_id = ' . (int)$iId);

            Phpfox_Image::instance()->createThumbnail(Phpfox::getParam('subscribe.dir_image') . sprintf($sFileName, ''),
                Phpfox::getParam('subscribe.dir_image') . sprintf($sFileName, '_120'), 120, 120);

            unlink(Phpfox::getParam('subscribe.dir_image') . sprintf($sFileName, ''));
        }

        return $iId;
    }

    public function update($iId, $aVals)
    {
        $iId = $this->add($aVals, $iId);
        Lib::phrase()->clearCache();
        return $iId;
    }

    public function updateOrder($aVals)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        if (!isset($aVals['ordering'])) {
            return Phpfox_Error::set(_p('not_a_valid_request'));
        }

        foreach ($aVals['ordering'] as $iId => $iOrder) {
            $this->database()->update($this->_sTable, array('ordering' => (int)$iOrder), 'package_id = ' . (int)$iId);
        }
        return null;
    }

    public function updateActivity($iId, $iType)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $this->database()->update($this->_sTable, array('is_active' => (int)($iType == '1' ? 1 : 0)),
            'package_id = ' . (int)$iId);
    }

    public function deleteImage($iId, &$aPackage = null)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        if ($aPackage === null) {
            $aPackage = $this->database()->select('package_id, image_path, server_id')
                ->from($this->_sTable)
                ->where('package_id = ' . (int)$iId)
                ->execute('getSlaveRow');
        }

        if (!isset($aPackage['package_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_package'));
        }

        if (!empty($aPackage['image_path'])) {
            $sImage = Phpfox::getParam('subscribe.dir_image') . sprintf($aPackage['image_path'], '_120');
            if (file_exists($sImage)) {
                unlink($sImage);
            }

            $this->database()->update($this->_sTable, array('image_path' => null, 'server_id' => '0'),
                'package_id = ' . $aPackage['package_id']);
        }

        return true;
    }

    public function delete($iId)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $aPackage = $this->database()->select('package_id, image_path, server_id, title, description, recurring_period')
            ->from($this->_sTable)
            ->where('package_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aPackage['package_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_package'));
        }

        $bHasSubscription = Phpfox::getService('subscribe')->checkNumbersOfSubscription($iId);
        if($bHasSubscription) {
            db()->update(Phpfox::getT('subscribe_package'),['is_removed' => 1], 'package_id = '.$iId);
            if((int)$aPackage['recurring_period'] > 0) {
                $aActiveSubscriptions = Phpfox::getService('subscribe')->getActiveUserListByPackage($iId);
                $sDefaultLanguage = Phpfox::getService('language')->getDefaultLanguage();
                foreach($aActiveSubscriptions as $aActiveSubscription) {
                    if (!empty($aActiveSubscription['extra_params'])) {
                        $aActiveSubscription['extra_params'] = unserialize($aActiveSubscription['extra_params']);
                    }
                    Phpfox::getLib('queue')->instance()->addJob('subscribe_process_active_subscription_after_delete_package', [
                        'user_id' => $aActiveSubscription['user_id'],
                        'sender_id' => Phpfox::getUserId(),
                        'title' => $aActiveSubscription['title'],
                        'language_id' => !empty($aActiveSubscription['language_id']) ? $aActiveSubscription['language_id'] : $sDefaultLanguage,
                        'full_name' => $aActiveSubscription['full_name'],
                        'purchase_id' => $aActiveSubscription['last_purchase_id'],
                        'expiry_date' => $aActiveSubscription['expiry_date'],
                        'subscription_id' => !empty($aActiveSubscription['extra_params']['subscription_id']) ? $aActiveSubscription['extra_params']['subscription_id'] : 0,
                        'gateway' => !empty($aActiveSubscription['payment_method']) ? $aActiveSubscription['payment_method'] : '',
                    ], null, 3600);
                }
            }

            return true;
        }

        Phpfox::getService('language.phrase.process')->delete($aPackage['title'], true);
        Phpfox::getService('language.phrase.process')->delete($aPackage['description'], true);
        $this->deleteImage($aPackage['package_id'], $aPackage);
        $this->database()->delete(Phpfox::getT('subscribe_purchase'), 'package_id = ' . $aPackage['package_id']);
        $this->database()->delete(Phpfox::getT('subscribe_package'), 'package_id = ' . $aPackage['package_id']);

        return true;
    }

    /**
     * Update total active of package
     * @param $iPackageId
     */
    public function updateTotalActive($iPackageId)
    {
        $iTotalActive = db()->select('count(*)')->from(':subscribe_purchase')->where([
            'package_id' => $iPackageId,
            'status' => 'completed'
        ])->executeField();

        db()->update($this->_sTable, ['total_active' => $iTotalActive], ['package_id' => $iPackageId]);
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('subscribe.service_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}