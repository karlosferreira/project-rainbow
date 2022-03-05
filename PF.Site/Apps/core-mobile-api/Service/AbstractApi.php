<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Adapter\Privacy\UserPrivacyInterface;
use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\ApiRequestInterface;
use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Exception\NotFoundErrorException;
use Apps\Core_MobileApi\Api\Exception\PermissionErrorException;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Apps\Core_MobileApi\Service\Helper\ParametersBag;
use Apps\Core_MobileApi\Service\Helper\ParametersResolver;
use Apps\Core_MobileApi\Service\Helper\PsrRequestHelper;
use Core\Api\ApiServiceBase;
use Core\Route\RouteUrl;
use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX_IS_MOBILE_API_CALL') or define('PHPFOX_IS_MOBILE_API_CALL', true);

class AbstractApi extends ApiServiceBase
{
    const STATUS_FAILED = "failed";
    const STATUS_SUCCESS = "success";

    /**
     * @var AccessControl Control security
     */
    protected $accessControl;

    /**
     * @var UserResource|UserInterface Current logged in User
     */
    protected $userContext;

    /**
     * @var Helper\RequestHelper
     */
    protected $_oReq;

    /**
     * @var Helper\SearchHelper
     */
    protected $_oSearch;
    /**
     * @var Helper\BrowseHelper
     */
    protected $_oBrowse;

    /**
     * @var Helper\FeedAttachmentHelper
     */
    protected $feedAttachmentHelper;

    /**
     * @var PsrRequestHelper|ApiRequestInterface
     */
    protected $psrRequest;

    /**
     * @var ParametersResolver
     */
    public $resolver;

    /**
     * @var SettingInterface
     */
    protected $setting;

    /**
     * @var LocalizationInterface
     */
    protected $localization;

    /**
     * @var ParseInterface
     */
    protected $parse;

    /**
     * @var UserPrivacyInterface
     */
    protected $userPrivacy;
    /**
     * @var ParametersBag
     */
    protected $parametersBag;

    /**
     * @return array
     */
    public function __naming()
    {
        return [];
    }

    /**
     * ApiAbstract constructor.
     */
    public function __construct()
    {
        $this->_oReq = \Phpfox::getService('mobile.helper.request');
        $this->_oSearch = \Phpfox::getService('mobile.helper.search');
        $this->_oBrowse = \Phpfox::getService('mobile.helper.browse');
        $this->feedAttachmentHelper = Phpfox::getService('mobile.helper.feedPresentation');
        $this->psrRequest = PsrRequestHelper::instance($this->isUnitTest());
        $this->resolver = ParametersResolver::createResolver($this->psrRequest);
        $this->parametersBag = ParametersBag::instance();
    }

    /**
     * Create form and Handle Request
     *
     * @param string $class
     * @param array  $options
     *
     * @return GeneralForm
     */
    public function createForm($class, $options = [])
    {
        /** @var GeneralForm $class */
        $form = $class::createForm($options, $this->request()->getRequests(),
            $this->getLocalization(),
            $this->getSetting(),
            $this->getUserPrivacy(),
            $this->getParse());

        $form->setRequest($this->request());
        \Phpfox_Request::instance()->set('api_form', 1);
        return $form;
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getApiUrl($url)
    {
        return $url;
    }

    /**
     * @param $aRows array of item to process
     */
    public function processRows(&$aRows)
    {
        $aRows = array_map(function ($aItem) {
            $row = $this->processRow($aItem);
            if ($row instanceof ResourceBase) {
                return $row->toArray();
            } else {
                return $row;
            }
        }, $aRows);
    }

    /**
     * @param array  $params
     * @param mixed  $transport
     * @param string $method
     *
     * @return array|bool
     */
    public function process($params, $transport, $method)
    {
        try {
            $params['args'] = array_merge(!empty($params['args']) ? $params['args'] : []
                , $this->request()->getRequests());
            $response = $this->handleStatus($transport, $params, $method);
            if (isset($response['status']) && $response['status'] === false) {
                $this->setTransport($transport);
                $message = isset($response['message']) ? $response['message'] : $this->getLocalization()->translate('unknown_error');
                return $this->processContent([
                    'status' => isset($response['response_status']) ? $response['response_status'] : self::STATUS_FAILED,
                    'error'  => [
                        'message' => $message,
                        'type' => 301,
                        'action' => isset($response['action']) ? $response['action'] : []
                    ],
                    'data' => []
                ]);
            }
            return parent::process($params, $transport, $method);
        } catch (\Exception $e) {
            return $this->processContent([
                'status' => self::STATUS_FAILED,
                'error'  => (new UnknownErrorException($e->getMessage()))->getResponse()
            ]);
        }
    }

    /**
     * @param array $item
     *
     * @return array
     * @codeCoverageIgnore - always be overrided by child.
     */
    public function processRow($item)
    {
        return $item;
    }

    protected function request()
    {
        return $this->psrRequest;
    }


    /**
     * Throw item not found error
     *
     * @param string $phrase
     *
     * @throws NotFoundErrorException
     */
    protected function notFoundError($phrase = '')
    {
        throw new NotFoundErrorException($phrase);
    }

    /**
     * Throw permission error
     *
     * @param string $phrase override default message
     *
     * @throws PermissionErrorException
     */
    protected function permissionError($phrase = '')
    {
        throw new PermissionErrorException($phrase);
    }

    /**
     * @param string $phrase
     *
     * @throws PermissionErrorException
     */
    protected function privacyError($phrase = '')
    {
        throw new PermissionErrorException($phrase);
    }

    /**
     * @param array $params list missing parameters
     *
     * @throws ValidationErrorException
     */
    public function missingParamsError($params)
    {
        throw new ValidationErrorException(_p('following_parameters_is_invalid_params', [
            'params' => implode(", ", $params)
        ]),
            ErrorException::INVALID_REQUEST_PARAMETERS);
    }

    /**
     * Throw validation error
     *
     * @param $params
     *
     * @throws ValidationErrorException
     */
    public function validationParamsError($params)
    {
        throw new ValidationErrorException(_p('following_parameters_is_invalid_params', [
            'params' => implode(", ", $params)
        ]), ErrorException::VALIDATION_ERROR, null, $params);
    }

    /**
     * @param string $error set error message
     * @param bool   $ignoredLast
     *
     * @return array|bool|void
     * @throws UnknownErrorException
     */
    public function error($error = "", $ignoredLast = false)
    {
        if (is_array($error)) {
            $error = implode(", ", $error);
        }
        $error = html_entity_decode($error, ENT_QUOTES);
        throw new UnknownErrorException($error);
    }

    /**
     * Return success response
     *
     * @param array        $data
     * @param array        $extra Add extra information like pagination
     * @param array|string $messages
     *
     * @return array|bool
     */
    public function success($data = [], $extra = [], $messages = [])
    {
        if (empty($data)) {
            $data = new \stdClass();
        }

        if (!empty($messages) && is_string($messages)) {
            $messages = [$messages];
        }
        if ($messages) {
            foreach ($messages as $key => $phrase) {
                $messages[$key] = $this->getLocalization()->translate($phrase);
            }
        }

        $return = parent::success($data, $messages);
        if (empty($return['messages'])) {
            unset($return['messages']);
        }

        if (defined('PHPFOX_MOBILE_GENERATE_DOCS') && PHPFOX_MOBILE_GENERATE_DOCS == true) {
            $this->generateDocument($this->request()->getUri(), $this->request()->getMethod(), $this->request()->getRequests(), array_merge($return, $extra));
        }
        return array_merge($return, $extra);
    }


    /**
     * Override this function to create specific access control for each Api section
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new AccessControl($this->getSetting(), $this->getUser());
    }

    /**
     * Magic function to check permission
     *
     * @param        $permission
     * @param null   $resource
     * @param null   $parameters
     * @param string $message
     *
     * @throws PermissionErrorException
     */
    public function denyAccessUnlessGranted($permission, $resource = null, $parameters = null, $message = '')
    {
        if ($parameters) {
            $this->getAccessControl()->setParameters($parameters);
        }
        if (!$this->getAccessControl()->isGranted($permission, $resource)) {
            if (!isset($message) || $message == '') {
                $message = $this->getAccessControl()->getErrorMessage();
            }
            return $this->permissionError($message);
        }
        return true;
    }

    /**
     * @return AccessControl
     */
    public function getAccessControl()
    {
        if ($this->accessControl == null || $this->isUnitTest()) {
            $this->createAccessControl();
        }
        return $this->accessControl;
    }

    /**
     * @return UserResource|UserInterface
     */
    public function getUser()
    {
        if ($this->userContext == null || ($this->isUnitTest() && $this->userContext->getId() != Phpfox::getUserId())) {
            $this->userContext = UserResource::populate(Phpfox::getService("user")->get(Phpfox::getUserId()));
        }
        return $this->userContext;
    }

    /**
     * @param UserResource|UserInterface $user
     */
    public function setUser($user)
    {
        if ($user instanceof UserResource || $user === null) {
            $this->userContext = $user;
        }
    }

    /**
     * @return SettingInterface|object
     */
    protected function getSetting()
    {
        if (!$this->setting) {
            $this->setting = Phpfox::getService(SettingInterface::class);
        }
        return $this->setting;
    }

    /**
     * @return LocalizationInterface|object
     */
    protected function getLocalization()
    {
        if (!$this->localization) {
            $this->localization = Phpfox::getService(LocalizationInterface::class);
        }
        return $this->localization;
    }

    /**
     * @return ParseInterface|object
     */
    protected function getParse()
    {
        if (!$this->parse) {
            $this->parse = Phpfox::getService(ParseInterface::class);
        }
        return $this->parse;
    }

    protected function getUserPrivacy()
    {
        if (!$this->userPrivacy) {
            $this->userPrivacy = Phpfox::getService(UserPrivacyInterface::class);
        }
        return $this->userPrivacy;
    }

    protected function getErrorMessage()
    {
        $e = implode(", ", \Phpfox_Error::get());
        \Phpfox_Error::reset();
        return $e;
    }

    /**
     * @alias \Phpfox_Url::makeUrl()
     * @param       $url
     * @param array $params
     *
     * @return string
     */
    protected function makeUrl($url, $params = [])
    {
        return Phpfox::getLib('url')->makeUrl($url, $params);
    }

    /**
     * @alias \Phpfox_Error::isPassed()
     * @return bool
     */
    protected function isPassed()
    {
        return \Phpfox_Error::isPassed();
    }

    /**
     * Creating resource instance helper method
     *
     * @param string $class class name
     * @param mixed  $data  data of the object
     *
     * @return ResourceBase|mixed
     */
    protected function populateResource($class, $data)
    {
        /** @var ResourceBase $resource */
        $resource = new $class($data);
        $resource->setAccessControl($this->getAccessControl());
        return $resource;
    }

    /**
     *
     * Create multiple links with same route base on given permissions
     *
     * @param $permissionMap
     * @param $resource
     * @param $method
     * @param $route
     * @param $params
     *
     * @return array|null
     * @codeCoverageIgnore - not used anywhere.
     */
    protected function createHyperMediaLinks($permissionMap, $resource, $method, $route, $params)
    {
        $hyperLinks = [];
        foreach ($permissionMap as $linker => $permission) {
            $hyperLinks[$linker] = $this->createHyperMediaLink($permission, $resource, $method, $route, $params);
            if ($hyperLinks[$linker] == null) {
                unset($hyperLinks[$linker]);
            }
        }
        if (empty($hyperLinks)) {
            return null;
        }
        return $hyperLinks;
    }

    /**
     * Create single hyper link
     *
     * @param string       $permission check permission before generate link (null to skip permission check)
     * @param ResourceBase $resource
     * @param string       $method     default is get if null provided
     * @param string       $route      to generate api
     * @param array|int    $params     route's parameters
     *
     * @return array|null
     */
    protected function createHyperMediaLink($permission, $resource, $method, $route, $params)
    {
        if ($permission && !$this->getAccessControl()->isGranted($permission, $resource)) {
            return null;
        }
        $hyperLink = (new HyperLink($method, $route, $params));
        return $hyperLink->toArray();
    }

    public function generateDocument($uri, $method, $requestData, $responseData)
    {
        preg_match('/\/restful_api\/mobile\/(.+[\-|\w].+)\?/', $uri, $match);
        $dirWrite = str_replace('/PF.Base', '', PHPFOX_DIR) . 'PF.Site/Apps/core-mobile-api/docs/example/';
        if (empty($match[1])) {
            return false;
        }
        $parts = explode('/', rtrim($match[1], '/'));
        $realPath = '';
        $fileName = '';
        $hasId = false;
        $isForm = false;
        foreach ($parts as $part) {
            if (NameResource::instance()->hasApiResourceService($part) || in_array($part, ['page-home', 'group-home'])) {
                if (strpos($part, '-') > -1) {
                    $realPath .= explode('-', $part)[0] . '/';
                } else {
                    if (in_array($part, ['pages', 'groups'])) {
                        $part = str_replace('s', '', $part);
                    }
                    $realPath .= $part . '/';
                }
                $fileName .= !empty($fileName) ? '-' . $part : $part;
            } else if (is_numeric($part)) {
                $hasId = true;
                if ($isForm) {
                    $fileName .= '-edit';
                }
            } else if ($part == 'form') {
                $isForm = true;
                $fileName .= '-form';
            } else {
                $isForm = true;
                $fileName .= !empty($fileName) ? '-' . $part : $part;
            }
        }
        rtrim($fileName, '-');
        switch ($method) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $fileName .= '-' . strtolower($method) . '.json';
                break;
            case 'GET':
                if (!$isForm) {
                    if ($hasId) {
                        $fileName .= '-one.json';
                    } else {
                        $fileName .= '-all.json';
                    }
                } else {
                    $fileName .= '.json';
                }
                break;
        }
        @chmod($dirWrite, 0777);

        $responseDir = $dirWrite . $realPath . 'response/';

        if (!is_dir($responseDir)) {
            if (!@mkdir($responseDir, 0777, true)) {
                return false;
            }
            @chmod($responseDir, 0777);
        }

        $fp = fopen($responseDir . $fileName, 'wa+');
        fwrite($fp, json_encode($responseData));
        fclose($fp);

        if (isset($requestData['do'])) {
            unset($requestData['do']);
        }
        if (isset($requestData['access_token'])) {
            unset($requestData['access_token']);
        }
        if (!empty($requestData)) {
            $requestDir = $dirWrite . $realPath . 'request/';
            if (!is_dir($requestDir)) {
                if (!@mkdir($requestDir, 0777, true)) {
                    return false;
                }
                @chmod($requestDir, 0777);
            }

            $fp = fopen($requestDir . $fileName, 'wa+');
            fwrite($fp, json_encode($requestData));
            fclose($fp);
        }
        return true;
    }

    /**
     * @param $transport mixed might useful for plugin
     * @param $param RouteUrl | array might useful for plugin
     * @param $method
     * @return array|bool[]
     */
    protected function handleStatus($transport, $param, $method)
    {
        $transport->authorization();
        $exceptionMethod = ['deleteUserDeviceToken'];

        (($sPlugin = Phpfox_Plugin::get('mobile.service_abstract_api_handle_status_start')) ? eval($sPlugin) : false);
        $response = [
            'status' => true
        ];
        $message = null;

        if (!in_array($method, $exceptionMethod)) {
            if ($this->getSetting()->getAppSetting('core.site_is_offline') && !$this->getSetting()->getUserSetting('core.can_view_site_offline')) {
                $message = $this->getSetting()->getAppSetting('core.site_offline_message');
                $message = $message ? $message : $this->getLocalization()->translate('the_site_is_currently_in_offline_mode');
            } elseif ($bannedData = Phpfox::getService('ban')->isUserBanned()) {
                if (isset($bannedData['ban_data_id']) && isset($bannedData['is_expired']) && $bannedData['is_expired'] == 0
                    && isset($bannedData['end_time_stamp']) && ($bannedData['end_time_stamp'] == 0 || $bannedData['end_time_stamp'] >= PHPFOX_TIME)
                ) {
                    if (isset($bannedData['reason']) && !empty($bannedData['reason'])) {
                        $bannedData['reason'] = html_entity_decode(Phpfox::getLib('parse.output')->parse($bannedData['reason']), ENT_QUOTES);
                        $sReason = preg_replace_callback('/{_p var=\'(.*)\'}/is', function ($m) {
                            return $this->getLocalization()->translate($m[1]);
                        }, $bannedData['reason']);
                        $message = $this->getLocalization()->translate('you_have_been_banned_for_the_following_reason', ['reason' => $sReason]) . '.';
                    } else {
                        $message = $this->getLocalization()->translate('global_ban_message');
                    }
                    if ($bannedData['end_time_stamp']) {
                        $message .= ' ' . $this->getLocalization()->translate('the_ban_will_be_expired_on_datetime', ['datetime' => Phpfox::getTime($this->getSetting()->getAppSetting('core.global_update_time'), $bannedData['end_time_stamp'])]);
                    }
                }
            }
        }
        if ($message !== null) {
            $response = [
                'status' => false,
                'message' => $message,
                'action' => '@auth/logout'
            ];
        }
        (($sPlugin = Phpfox_Plugin::get('mobile.service_abstract_api_handle_status_end')) ? eval($sPlugin) : false);

        return $response;
    }

    /**
     * Check if current context is unit test.
     *
     * @return bool
     */
    public function isUnitTest() {
        return defined('PHPFOX_UNIT_TEST') && PHPFOX_UNIT_TEST === true;
    }
}