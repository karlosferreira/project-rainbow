<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 22/5/18
 * Time: 11:30 AM
 */

namespace Apps\Core_MobileApi\Service\Auth;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Exception\NotFoundErrorException;
use Apps\Core_MobileApi\Api\Exception\PaymentRequiredErrorException;
use Apps\Core_MobileApi\Api\Exception\PermissionErrorException;
use Apps\Core_MobileApi\Api\Exception\UnauthorizedErrorException;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Service\AbstractApi;
use Apps\Core_MobileApi\Service\Auth\GrantType\AppleAuth;
use Apps\Core_MobileApi\Service\Auth\GrantType\FacebookAuth;
use Apps\Core_MobileApi\Service\Auth\GrantType\GoogleAuth;
use Apps\Core_MobileApi\Service\Auth\GrantType\UserPasswordAuth;
use OAuth2\ResponseInterface;
use Phpfox;


class AuthenticationApi extends AbstractApi
{

    public function __naming()
    {
        return [
            'verify-token' => [
                'get' => 'verifyToken'
            ]
        ];
    }

    public function verifyToken($params = [])
    {
        $user = Phpfox::getService("user")->get(Phpfox::getUserId());
        if (!empty($user)) {
            return $this->success(UserResource::populate($user)->toArray());
        } else {
            return $this->notFoundError();
        }
    }

    public function setUserFromToken($token)
    {
        $token = \Phpfox::getService('mobile.auth.storage')->getAccessToken($token);
        if (!empty($token) && !empty($token['user_id']) && ($user = Phpfox::getService("user")->get($token['user_id']))) {
            /** @var \User_Service_Auth $auth */
            $auth = Phpfox::getService("user.auth");
            $auth->setUser($user);
            //Set cookie
            $sPasswordHash = Phpfox::getLib('hash')->setRandomHash(Phpfox::getLib('hash')->setHash($user['password'], $user['password_salt']));
            $iTime = 0;
            $cookieUserId = 'user_id';
            $cookieUserHash = 'user_hash';
            if (Phpfox::getParam('core.use_custom_cookie_names')) {
                $cookieUserId = md5(Phpfox::getParam('core.custom_cookie_names_hash') . $cookieUserId);
                $cookieUserHash = md5(Phpfox::getParam('core.custom_cookie_names_hash') . $cookieUserHash);
            }
            Phpfox::setCookie($cookieUserId, $user['user_id'], $iTime, (Phpfox::getParam('core.force_https_secure_pages') ? true : false));
            Phpfox::setCookie($cookieUserHash, $sPasswordHash, $iTime, (Phpfox::getParam('core.force_https_secure_pages') ? true : false));

        }
    }

    public function handleTokenRequest()
    {
        try {
            \OAuth2\Autoloader::register();

            $storage = \Phpfox::getService('mobile.auth.storage');
            $server = new \OAuth2\Server($storage, [
                'allow_implicit'  => true,
                'access_lifetime' => 86400 * 30,
                'enforce_state'   => false
            ]);

            $server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));
            $server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));
            $server->addGrantType(new UserPasswordAuth($storage));
            $server->addGrantType(new FacebookAuth($storage));
            $server->addGrantType(new AppleAuth($storage));
            $server->addGrantType(new GoogleAuth($storage));
            $server->addGrantType(new \OAuth2\GrantType\RefreshToken($storage, [
                'always_issue_new_refresh_token' => true
            ]));

            $request = \OAuth2\Request::createFromGlobals();
            $response = $server->handleTokenRequest($request);


            if (!($error = $response->getParameter('error')) && ($token = $response->getParameter('access_token'))) {
                $this->response([
                    'access_token'  => $token,
                    'expires_in'    => 86400 * 30,
                    'token_type'    => $response->getParameter('token_type'),
                    'scope'         => $response->getParameter('scope'),
                    'refresh_token' => $response->getParameter('refresh_token')
                ]);
            } else {
                $this->response([
                    'error' => $this->error(
                        $response->getParameter('error_description'), false,
                        $response->getStatusCode(),false, [
                            'verify_phone' => $response->getParameter('verify_phone'),
                            'user_id' => $response->getParameter('user_id'),
                            'default_country_iso' => $response->getParameter('default_country_iso')
                        ]
                    )
                ]);
            }
        } catch (\Exception $exception) {
            $this->response(['error' => $this->error($exception->getMessage())]);
        }

    }


    public function response($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        $this->isUnitTest() ?: exit();
    }

    public function error($error = "", $ignoredLast = false, $code = 102, $validationDetail = false, $errorData = null)
    {
        $error = $this->getErrorException($error, $code, $validationDetail);
        return $error->getResponse($errorData);
    }

    public function getErrorException($error, $code, $validationDetail = false)
    {
        switch ($code) {
            case ErrorException::RESOURCE_NOT_FOUND:
                $error = new NotFoundErrorException($error, $code);
                break;
            case ErrorException::PERMISSION_DENIED:
                $error = new PermissionErrorException($error, $code);
                break;
            case ErrorException::UNKNOWN_ERROR:
                $error = new UnknownErrorException($error, $code);
                break;
            case ErrorException::INVALID_REQUEST_PARAMETERS:
                $error = new ValidationErrorException($error, $code, null, $validationDetail);
                break;
            case ErrorException::PAYMENT_REQUIRED:
                $error = new PaymentRequiredErrorException($error, $code);
                break;
            case ErrorException::UNAUTHORIZED:
                $error = new UnauthorizedErrorException($error, $code);
                break;
            default:
                $error = new ErrorException($error, $code);
                break;
        }
        return $error;
    }

    public function initDefaultProfileSetting($iUserId)
    {
        if (method_exists('User_Service_Process', 'initDefaultProfileSetting')) {
            return Phpfox::getService('user.process')->initDefaultProfileSetting($iUserId);
        }

        if (empty($iUserId)) {
            return false;
        }

        $bIsFriendOnly = Phpfox::getParam('core.friends_only_community');
        switch (Phpfox::getParam('user.on_register_privacy_setting')) {
            case 'network':
                $iPrivacySetting = $bIsFriendOnly ? '2' : '1';
                break;
            case 'friends_only':
                $iPrivacySetting = '2';
                break;
            case 'no_one':
                $iPrivacySetting = '4';
                break;
            default:
                break;
        }

        if (isset($iPrivacySetting)) {
            $aProfiles = Phpfox::massCallback('getProfileSettings');
            $aDefaultConvertedSettingValues = [];
            $aAllowPrivacyList = [];
            $aPrivacy = [];
            foreach ($aProfiles as $aSettings) {
                $aPrivacy = array_merge($aPrivacy, array_keys($aSettings));
                foreach ($aSettings as $settingKey => $aSetting) {
                    $aAllowPrivacyList[$settingKey] = [];
                    if (!isset($aSetting['anyone']) && !$bIsFriendOnly) {
                        $aAllowPrivacyList[$settingKey][] = '0';
                    }
                    if (!isset($aSetting['no_user'])) {
                        if (!isset($aSetting['friend_only']) && !$bIsFriendOnly) {
                            $aAllowPrivacyList[$settingKey][] = '1';
                        }
                        if (Phpfox::isModule('friend')) {
                            if (!isset($aSetting['friend']) || $aSetting['friend']) {
                                $aAllowPrivacyList[$settingKey][] = '2';
                            }
                            if (!empty($aSetting['friend_of_friend'])) {
                                $aAllowPrivacyList[$settingKey][] = '3';
                            }
                        }
                    }
                    //No one is always available
                    $aAllowPrivacyList[$settingKey][] = '4';
                    if (isset($aSetting['converted_default_value'])) {
                        $aDefaultConvertedSettingValues[$settingKey] = $aSetting['converted_default_value'];
                    }
                }
            }

            foreach ($aPrivacy as $sPrivacy) {
                $a = explode('.', $sPrivacy);
                if (!isset($a[0]) || !Phpfox::isModule($a[0])) {
                    continue;
                }
                $iDefaultValue = isset($aDefaultConvertedSettingValues[$sPrivacy][$iPrivacySetting]) ? $aDefaultConvertedSettingValues[$sPrivacy][$iPrivacySetting] : $iPrivacySetting;
                if (!in_array($iDefaultValue, $aAllowPrivacyList[$sPrivacy]) && count($aAllowPrivacyList[$sPrivacy])) {
                    $iDefaultValue = $aAllowPrivacyList[$sPrivacy][0];
                }
                db()->insert(':user_privacy', [
                        'user_id'      => $iUserId,
                        'user_privacy' => $sPrivacy,
                        'user_value'   => $iDefaultValue,
                    ]
                );
            }
        }
        return true;
    }

    public function initDefaultNotificationSettings($iId)
    {
        if (!method_exists('Admincp_Service_Setting_Setting', 'getDefaultNotificationSettings')) {
            return false;
        }
        //Add default notification settings
        $aDefaultEmailNotification = Phpfox::getService('admincp.setting')->getDefaultNotificationSettings('email', true, true);
        if (count($aDefaultEmailNotification)) {
            $aDefaultEmailInsert = [];
            foreach ($aDefaultEmailNotification as $sVar => $iValue) {
                $aDefaultEmailInsert[] = [$iId, $sVar, 'email', 0];
            }
            $this->database()->multiInsert(Phpfox::getT('user_notification'), [
                'user_id', 'user_notification', 'notification_type', 'is_admin_default'
            ], $aDefaultEmailInsert);
        }
        $aDefaultSmsNotification = Phpfox::getService('admincp.setting')->getDefaultNotificationSettings('sms', true, true);
        if (count($aDefaultSmsNotification)) {
            $aDefaultSmsInsert = [];
            foreach ($aDefaultSmsNotification as $sVar => $iValue) {
                $aDefaultSmsInsert[] = [$iId, $sVar, 'sms', 0];
            }
            $this->database()->multiInsert(Phpfox::getT('user_notification'), [
                'user_id', 'user_notification', 'notification_type', 'is_admin_default'
            ], $aDefaultSmsInsert);
        }
        return true;
    }

    /**
     * @param $sEmail
     * @param ResponseInterface $oResponse
     * @param string $sFullName
     * @param bool $bIsSignup
     * @param null $iUserId
     * @return bool
     */
    public function validateSocialLogin($sEmail, $oResponse, $sFullName = '', $bIsSignup = true, $iUserId = null)
    {
        $iPass = true;
        //Invite only check
        if ($bIsSignup && Phpfox::getParam('user.invite_only_community') && !Phpfox::getService('invite')->isValidInvite($sEmail)) {
            $oResponse->setError(505, 'invalid_grant', _p('site_is_an_invite_only_community_unable_to_find_your_invitation', ['siteTitle' => Phpfox::getParam('core.site_title')]));
            $iPass = false;
        }
        if (!$bIsSignup && $iUserId && Phpfox::getService('user')->isAdminUser($iUserId)) {
            return $iPass;
        }
        // Ban check
        $oBan = Phpfox::getService('ban');
        if ($sEmail && !$oBan->check('email', $sEmail)) {
            $oResponse->setError(505, 'invalid_grant', _p('your_email_address_has_been_banned_from_the_site'));
            $iPass = false;
        }
        if (!$oBan->check('ip', Phpfox::getLib('request')->getIp())) {
            $oResponse->setError(505, 'invalid_grant', _p('not_allowed_ip_address'));
            $iPass = false;
        }
        if ($sFullName && !$oBan->check('display_name', $sFullName)) {
            $oResponse->setError(505, 'invalid_grant', _p('your_full_name_is_not_allowed_to_be_used'));
            $iPass = false;
        }
        return $iPass;
    }
}