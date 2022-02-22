<?php

namespace Apps\Core_MobileApi\Service\Auth\GrantType;

use Apps\Core_MobileApi\Service\Auth\Storage;
use Core\Hash as Hash;
use Core\Model as Model;
use LogicException;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\Storage\UserCredentialsInterface;
use Phpfox;
use Phpfox_Image;
use Phpfox_Locale;

/**
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class FacebookAuth extends Model implements GrantTypeInterface
{
    /**
     * @var array
     */
    private $userInfo;

    /**
     * @var UserCredentialsInterface
     */
    protected $storage;

    /**
     * @param UserCredentialsInterface $storage - REQUIRED Storage class for retrieving user credentials information
     */
    public function __construct(Storage $storage)
    {
        parent::__construct();
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getQueryStringIdentifier()
    {
        return 'facebook';
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return bool|mixed|null
     *
     * @throws LogicException
     */
    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        $facebookId = $request->request('facebook_id');
        $facebookEmail = $request->request('facebook_email');
        $facebookName = $request->request('facebook_name');
        $facebookLink = $request->request('facebook_link', null);
        $accessToken = $request->request('facebook_token');

        if (empty($facebookName) && empty($facebookLink)) {
            $facebookProfile = $request->request('facebook_profile');
            $facebookName = isset($facebookProfile['name']) ? $facebookProfile['name'] : '';
            $facebookLink = isset($facebookProfile['link']) ? $facebookProfile['link'] : '';
        }
        if (empty($facebookId) || empty($facebookEmail) || empty($accessToken)) {
            $response->setError(500, 'invalid_request', 'Missing parameters: `facebook_id`, `facebook_email` and `facebook_token` required');
            return null;
        }

        $validate = true;
        if (!$validate) {
            $response->setError(501, 'invalid_grant', 'Invalid `facebook_id`, `facebook_email` and `facebook_token` combination');
            return null;
        }

        $cached = \storage()->get('fb_users_' . $facebookId);
        if (!empty($cached) && isset($cached->value->user_id)) {
            $userInfo = db()->select('user_id, user_name, email, full_name')->from(':user')
                ->where(['user_id' => $cached->value->user_id])
                ->execute('getRow');
        } else {
            $userInfo = db()->select('user_id, user_name, email, full_name')->from(':user')
                ->where(['email' => $facebookEmail])
                ->execute('getRow');
        }

        if (empty($userInfo)) {
            if (Phpfox::getParam('user.allow_user_registration')) {
                if (!Phpfox::getService('mobile.auth_api')->validateSocialLogin($facebookEmail, $response, $facebookName)) {
                    return null;
                }
                $userInfo = $this->createUser($facebookId, $facebookEmail, html_entity_decode($facebookName, ENT_QUOTES), $facebookLink);
            } else {
                $response->setError(505, 'invalid_grant', _p('Sorry, you cannot register an account now.'));
                return null;
            }
        } elseif (!Phpfox::getService('mobile.auth_api')->validateSocialLogin($userInfo['email'], $response, $userInfo['full_name'], false, $userInfo['user_id'])) {
            return null;
        }

        if (!isset($userInfo['user_id'])) {
            throw new \LogicException(_p('user_has_not_found_and_cant_sign_up'));
        }

        db()->update(':user', [
            'last_login' => PHPFOX_TIME,
            'last_activity' => PHPFOX_TIME,
            'last_ip_address' => Phpfox::getIp(),
        ], 'user_id = ' . $userInfo['user_id']);

        db()->insert(Phpfox::getT('user_ip'), [
                'user_id'    => $userInfo['user_id'],
                'type_id'    => 'login',
                'ip_address' => Phpfox::getIp(),
                'time_stamp' => PHPFOX_TIME
            ]
        );

        $this->userInfo = $userInfo;

        return true;
    }

    /**
     * Get client id
     *
     * @return mixed|null
     */
    public function getClientId()
    {
        return null;
    }

    /**
     * Get user id
     *
     * @return mixed
     */
    public function getUserId()
    {
        return isset($this->userInfo['user_id']) ? $this->userInfo['user_id'] : null;
    }

    /**
     * Get scope
     *
     * @return null|string
     */
    public function getScope()
    {
        return isset($this->userInfo['scope']) ? $this->userInfo['scope'] : null;
    }

    /**
     * Create access token
     *
     * @param AccessTokenInterface $accessToken
     * @param mixed $client_id - client identifier related to the access token.
     * @param mixed $user_id - user id associated with the access token
     * @param string $scope - scopes to be stored in space-separated string.
     *
     * @return array
     */
    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        return $accessToken->createAccessToken($client_id, $user_id, $scope);
    }

    public function createUser($facebookID, $facebookEmail, $facebookName, $facebookLink = null)
    {
        $url = null;

        $_password = $facebookID . uniqid();
        $password = (new Hash())->make($_password);
        $iGender = 0;
        $url = null;
        $blank_email = false;
        if (!$facebookEmail && $facebookLink != null) {
            stream_context_set_default(
                [
                    'http' => [
                        'header' => "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n"
                    ]
                ]
            );
            $headers = [];
            $filename = rtrim(str_replace('app_scoped_user_id/', '', $facebookLink), '/');

            if ($filename) {
                $headers = get_headers($filename);
            }


            if (isset($headers[1])) {
                $url = trim(str_replace('Location: https://www.facebook.com/', '', $headers[1]));
                $facebookEmail = strtolower($url) . '@facebook.com';
                $blank_email = true;
            }
        }

        if (!$facebookEmail) {
            $facebookEmail = $facebookID . '@fb';
            $blank_email = true;
        }
        $insert = [
            'user_group_id' => Phpfox::getParam('user.on_register_user_group', NORMAL_USER_ID),
            'email' => $facebookEmail,
            'password' => $password,
            'gender' => $iGender,
            'full_name' => $facebookName,
            'user_name' => 'fb-' . $facebookID,
            'user_image' => '',
            'view_id' => (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.approve_users')) ? '1' : '0',
            'joined' => PHPFOX_TIME,
            'language_id'     => Phpfox_Locale::instance()->getLangId(),
            'last_ip_address' => Phpfox::getIp(),
            'last_activity' => PHPFOX_TIME,
            'feed_sort' => (Phpfox::getParam('feed.default_sort_criterion_feed') == 'top_stories') ? 0 : 1
        ];

        $id = db()->insert(':user', $insert);

        // Get user's avatar
        $sImage = fox_get_contents("https://graph.facebook.com/" . $facebookID . "/picture?type=large");
        $sFileName = md5('user_avatar' . time()) . '.jpg';
        file_put_contents(Phpfox::getParam('core.dir_user') . $sFileName, $sImage);

        // check in case using cdn
        $aImage = (Phpfox::getService('user.process')->uploadImage($id, false,
            Phpfox::getParam('core.dir_user') . $sFileName));
        $oImage = Phpfox_Image::instance();

        //crop thumbnail avatar
        foreach (Phpfox::getService('user')->getUserThumbnailSizes() as $iSize) {
            if (Phpfox::getParam('core.keep_non_square_images')) {
                $oImage->createThumbnail(Phpfox::getParam('core.dir_user') . $sFileName, Phpfox::getParam('core.dir_user') . sprintf($aImage['user_image'], '_' . $iSize), $iSize, $iSize);
            }
            $oImage->createThumbnail(Phpfox::getParam('core.dir_user') . $sFileName, Phpfox::getParam('core.dir_user') . sprintf($aImage['user_image'], '_' . $iSize . '_square'), $iSize, $iSize, false);
        }
        register_shutdown_function(function () use ($sFileName) {
            @unlink(Phpfox::getParam('core.dir_user') . $sFileName);
        });
        // update user image
        !empty($aImage) && db()->update(':user', ['user_image' => $aImage['user_image'], 'server_id' => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')], ['user_id' => $id]);
        $storage = \storage();

        //Remove existed cache
        $storage->del('fb_users_' . $facebookID);

        if ($blank_email) {
            $storage->set('fb_force_email_' . $id, $facebookID);
        } else {
            //Set cache to show popup notify
            $storage->set('fb_user_notice_' . $id, ['email' => $facebookEmail]);
        }

        $storage->set('fb_users_' . $facebookID, [
            'user_id' => $id,
            'email' => $facebookEmail
        ]);

        //Storage account login by Facebook, in the first time this user change password, he/she doesn't need confirm old password.
        $storage->set('fb_new_users_' . $id, [
            'fb_id' => $facebookID,
            'email' => $facebookEmail
        ]);

        $aExtras = [
            'user_id' => $id
        ];

        (($sPlugin = \Phpfox_Plugin::get('user.service_process_add_extra')) ? eval($sPlugin) : false);

        $tables = [
            'user_activity',
            'user_field',
            'user_space',
            'user_count'
        ];
        foreach ($tables as $table) {
            db()->insert(':' . $table, $aExtras);
        }

        $iFriendId = (int)Phpfox::getParam('user.on_signup_new_friend');
        if ($iFriendId > 0 && Phpfox::isModule('friend')) {
            $iCheckFriend = db()->select('COUNT(*)')
                ->from(Phpfox::getT('friend'))
                ->where('user_id = ' . (int)$id . ' AND friend_user_id = ' . (int)$iFriendId)
                ->execute('getSlaveField');

            if (!$iCheckFriend) {
                db()->insert(Phpfox::getT('friend'), [
                        'list_id' => 0,
                        'user_id' => $id,
                        'friend_user_id' => $iFriendId,
                        'time_stamp' => PHPFOX_TIME
                    ]
                );

                db()->insert(Phpfox::getT('friend'), [
                        'list_id' => 0,
                        'user_id' => $iFriendId,
                        'friend_user_id' => $id,
                        'time_stamp' => PHPFOX_TIME
                    ]
                );

                if (!Phpfox::getParam('user.approve_users')) {
                    Phpfox::getService('friend.process')->updateFriendCount($id, $iFriendId);
                    Phpfox::getService('friend.process')->updateFriendCount($iFriendId, $id);
                }
            }
        }

        $iId = $id; // add for plugin use

        Phpfox::getService('mobile.auth_api')->initDefaultProfileSetting($iId);
        Phpfox::getService('mobile.auth_api')->initDefaultNotificationSettings($iId);

        //Support invite only make friend
        if (Phpfox::isModule('invite') && (Phpfox::getCookie('invited_by_email') || Phpfox::getCookie('invited_by_user'))) {
            Phpfox::getService('invite.process')->registerInvited($iId);
        } else if (Phpfox::isModule('invite')) {
            Phpfox::getService('invite.process')->registerByEmail($insert);
        }

        if (Phpfox::isAppActive('Core_Activity_Points')) {
            Phpfox::getService('activitypoint.process')->updatePoints($id, 'user_signup');
        }

        (($sPlugin = \Phpfox_Plugin::get('user.service_process_add_end')) ? eval($sPlugin) : false);

        //Auto pick a package if required on sign up
        if (!defined('PHPFOX_INSTALLER') && Phpfox::isAppActive('Core_Subscriptions') && Phpfox::getParam('subscribe.enable_subscription_packages') && Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up'))  {
            $aPackages = Phpfox::getService('subscribe')->getPackages(true);
            if (count($aPackages)) {
                //Get first package
                $aPackage = $aPackages[0];

                $iPurchaseId = Phpfox::getService('subscribe.purchase.process')->add([
                    'package_id' => $aPackage['package_id'],
                    'currency_id' => $aPackage['default_currency_id'],
                    'price' => $aPackage['default_cost']
                ], $iId);

                $iDefaultCost = (int)str_replace('.', '', $aPackage['default_cost']);

                if ($iPurchaseId) {
                    if ($iDefaultCost > 0) {
                        define('PHPFOX_MUST_PAY_FIRST', $iPurchaseId);

                        Phpfox::getService('user.field.process')->update($iId, 'subscribe_id', $iPurchaseId);
                    } else {
                        Phpfox::getService('subscribe.purchase.process')->update($iPurchaseId, $aPackage['package_id'], 'completed', $iId, $aPackage['user_group_id']);
                    }
                }
            }
        }
        if (!\Phpfox_Error::isPassed()) {
            throw new \Exception(implode('', \Phpfox_Error::get()));
        }

        return [
            'user_id' => $id,
            'email' => $facebookEmail,
            'user_name' => 'fb-' . $facebookID
        ];
    }
}