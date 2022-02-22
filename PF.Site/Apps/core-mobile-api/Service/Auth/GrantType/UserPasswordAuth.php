<?php

namespace Apps\Core_MobileApi\Service\Auth\GrantType;


use Apps\Core_MobileApi\Service\Auth\Storage;
use OAuth2\GrantType\UserCredentials;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\Storage\UserCredentialsInterface;
use Phpfox;

class UserPasswordAuth extends UserCredentials
{

    /**
     * @var array
     */
    protected $userInfo;

    /**
     * @param UserCredentialsInterface $storage - REQUIRED Storage class for retrieving user credentials information
     */
    public function __construct(UserCredentialsInterface $storage)
    {
        parent::__construct($storage);
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return bool|mixed|null
     *
     * @throws \LogicException
     */
    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        if (!empty($request->request('login'))) {
            $loginInfo = $request->request('login');
        } else if (!empty($request->request('email'))) {
            $loginInfo = $request->request('email');
        } else if (!empty($request->request('username'))) {
            $loginInfo = $request->request('username');
        } else {
            $loginInfo = "";
        }

        if (!$request->request("password") || !$loginInfo) {
            $response->setError(400, 'invalid_request', 'Missing parameters: `login` or `password` required');
            return null;
        }

        $checker = $this->storage->checkUserCredentials($loginInfo, $request->request("password"));
        $userInfo = $this->storage->getUserDetails($loginInfo);

        if ($checker === false) {
            $errors = \Phpfox_Error::get();
            $response->setError(401, 'invalid_grant', count($errors) > 0 ? $errors[0] : _p('invalid_login_or_password_combination'));
            return null;
        } elseif (Storage::UN_VERIFY_STATUS === $checker) {
            if (isset($userInfo['full_phone_number'])) {
                //User must verify phone number
                $response->addParameters([
                    'verify_phone' => $userInfo['full_phone_number'],
                    'user_id' => $userInfo['user_id']
                ]);
            }
            $response->addParameters([
                'default_country_iso' => Phpfox::getLib('request')->getIpInfo(null, 'country_code')
            ]);
            $response->setError(402, 'invalid_grant', _p('please_verify_your_account_before_login'));
            return null;
        } elseif (Storage::PENDING_STATUS === $checker) {
            $response->setError(406, 'invalid_grant', _p('your_account_is_pending_approval'));
            return null;
        }

        if (empty($userInfo)) {
            $response->setError(405, 'invalid_grant', _p('unable_to_retrieve_user_information'));
            return null;
        }

        if (!isset($userInfo['user_id'])) {
            throw new \LogicException("you must set the user_id on the array returned by getUserDetails");
        }

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
        return $this->userInfo['user_id'];
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

}