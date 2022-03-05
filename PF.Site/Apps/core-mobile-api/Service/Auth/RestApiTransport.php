<?php

namespace Apps\Core_MobileApi\Service\Auth;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Core\Api\ApiTransportInterface;
use OAuth2\Autoloader;
use Phpfox;
use Phpfox_Error;
use Phpfox_Service;

/**
 * Class RestApiAdapter
 * @package Apps\phpFox_RESTful_API\Service
 */
class RestApiTransport extends Phpfox_Service implements ApiTransportInterface
{

    /**
     * @return LocalizationInterface|object
     */
    protected function getLocalization()
    {
        return Phpfox::getService(LocalizationInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function initSearchParams($params = [])
    {
        foreach ($params as $key => $value) {
            if ($newValue = $this->request()->get($key, null)) {
                $params[$key] = $newValue;
            }
        }

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function authorization()
    {

        //register and authorize request
        Autoloader::register();

        $storage = Phpfox::getService('restful_api.storage');

        $server = new \OAuth2\Server($storage, ['allow_implicit' => true]);

        $server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));
        $server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));
        $server->addGrantType(new \OAuth2\GrantType\UserCredentials($storage));
        if (!$server->verifyResourceRequest(\OAuth2\Request::createFromGlobals())) {
            $server->getResponse()->send();
            die;
        }

        //set user if the access token associate with an user
        $token = $server->getAccessTokenData(\OAuth2\Request::createFromGlobals());
        $user_id = $token['user_id'];
        if ($user_id) {
            $user = Phpfox::getService('user')->get($user_id, true);
            if (empty($user)) {
                return $this->error($this->getLocalization()->translate('The user cannot be found'));
            }
            Phpfox::getService('user.auth')->setUserId($user_id, $user);
            Phpfox::getService('user.auth')->setUser($user);
        } else {
            Phpfox::getService('user.auth')->reset();
            Phpfox::getService('user.auth')->logout();
        }

        //parsing API params
        $request = new \Restful\Parser();
        $request->parse();
        $this->request()->add($_REQUEST);
        return true;
    }

    /**
     * @inheritdoc
     */
    function isUser()
    {
        if (Phpfox::isUser()) {
            return true;
        }

        throw new \Exception($this->getLocalization()->translate('This request requires an user token.'));
    }

    /**
     * @inheritdoc
     */
    function processParams($params = [])
    {
        if (empty($params['maps'])) {
            $params['maps'] = ['get' => 'get', 'put' => 'put', 'post' => 'post', 'delete' => 'delete'];
        }

        $httpMethod = strtolower($this->request()->method());


        $params['callMethod'] = isset($params['maps'][$httpMethod]) ? $params['maps'][$httpMethod] : '';

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function requireParams($data, $requires = null)
    {
        foreach ($data as $param) {
            if ($requires === null) {
                $value = \Phpfox_Request::instance()->get($param, null);
                if (empty($value) && $value != '0') {
                    Phpfox_Error::set($this->getLocalization()->translate('Param "{{ field }}" is required.', ['field' => $param]));
                }
            } else if (is_array($requires) && (!isset($requires[$param]) || $requires[$param] == '')) {
                Phpfox_Error::set($this->getLocalization()->translate('Field "{{ field }}" is required.', ['field' => $param]));
            }
        }
        if (Phpfox_Error::isPassed()) {
            return true;
        }
        return $this->error();
    }

    /**
     * @inheritdoc
     */
    function processContent($content)
    {
        if (!Phpfox_Error::isPassed()) {
            return $this->error();
        }
        if (is_string($content)) {
            return $this->processReturn('success', [], [$content]);
        }

        if (!is_array($content)) {
            $content = (array)$content;
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    function processReturn($status, $data, $messages = null, $error = null)
    {
        $messages = is_array($messages) ? implode('\n', array_values($messages)) : $messages;

        return ['status' => $status, 'data' => $data, 'message' => $messages, 'error' => $error];
    }

    /**
     * @inheritdoc
     */
    function error($error = null, $ignoredLast = false)
    {
        if ($error !== null) {
            if (!$ignoredLast || Phpfox_Error::isPassed()) {
                Phpfox_Error::set($error);
            }
        }
        if (Phpfox_Error::isPassed()) {
            return $this->success();
        }
        return $this->processReturn('failed', [], [], ['message' => implode(', ', Phpfox_Error::get())]);
    }

    /**
     * @inheritdoc
     */
    function success($data = [], $messages = [])
    {
        if (!Phpfox_Error::isPassed()) {
            return $this->error();
        }
        return $this->processReturn('success', $data, $messages);
    }
}