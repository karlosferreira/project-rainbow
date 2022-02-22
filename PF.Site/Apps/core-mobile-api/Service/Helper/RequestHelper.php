<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service\Helper;

use Phpfox;
use Phpfox_Url;

class RequestHelper extends \Phpfox_Service
{
    /**
     * Request data
     *
     * @var array
     */
    private $_aArgs;

    /**
     * List of requests being checked.
     *
     * @var array
     */
    private $_aName = [];

    /**
     * Last name being checked.
     *
     * @var string
     */
    private $_sName;

    /**
     * RequestHelper constructor.
     */
    public function __construct()
    {
        if (isset($_GET['nginx'])) {
            $parts = explode('?', $_SERVER['REQUEST_URI']);
            $_GET['do'] = $parts[0];
            if (isset($parts[1])) {
                $gets = explode('&', $parts[1]);
                foreach ($gets as $get) {
                    $sub = explode('=', $get);
                    $_GET[$sub[0]] = (isset($sub[1]) ? $sub[1] : '');
                }
            }
            unset($_GET['nginx']);
        }

        $mParam = array_merge($_GET, $_POST, $_FILES, Phpfox_Url::instance()->getParams());

        foreach (['sort', 'view', 'when', 'page', 'limit'] as $key) {
            if (isset($mParam[$key]) and is_array($mParam[$key])) {
                unset($mParam[$key]);
            }
        }
        $this->_aArgs = $this->_trimData($mParam);
    }

    /**
     * @return mixed
     */

    public static function instance()
    {
        return Phpfox::getService('mobile.helper.request');
    }

    /**
     * Retrieve parameter value from request.
     *
     * @param string $sName name of argument
     * @param string $mDef
     *
     * @return string parameter value
     * @internal param string $sCommand is any extra commands we need to execute
     *
     */
    public function get($sName = null, $mDef = '')
    {
        if ($this->_sName) {
            $sName = $this->_sName;
        }

        if ($sName === null) {
            return (object)$this->_aArgs;
        }

        return (isset($this->_aArgs[$sName]) ? ((empty($this->_aArgs[$sName]) && isset($this->_aName[$sName])) ? true : $this->_aArgs[$sName]) : $mDef);
    }

    /**
     * Set a request manually.
     *
     * @param mixed  $mName  ARRAY include a name and value, STRING just the request name.
     * @param string $sValue If the 1st argument is a string this must be the request value.
     */
    public function set($mName, $sValue = null)
    {
        if (!is_array($mName)) {
            $mName = [$mName => $sValue];
        }

        foreach ($mName as $sKey => $sValue) {
            $this->_aArgs[$sKey] = $sValue;
        }
    }

    /**
     * Get a request and convert it into an INT.
     *
     * @param string $sName Name of the request.
     * @param string $mDef  Default value in case the request does not exist.
     *
     * @return int INT value of the request.
     */
    public function getInt($sName, $mDef = '')
    {
        return (int)$this->get($sName, $mDef);
    }

    /**
     * Get a request and make sure it is an ARRAY.
     *
     * @param string $sName Name of the request.
     * @param array  $mDef  ARRAY of default values in case the request does not exist.
     *
     * @return array Returns an ARRAY value.
     */
    public function getArray($sName, $mDef = [])
    {
        return (array)(isset($this->_aArgs[$sName]) ? $this->_aArgs[$sName] : $mDef);
    }

    /**
     * Get all the requests.
     *
     * @return array
     */
    public function getRequests()
    {
        return (array)$this->_aArgs;
    }

    /**
     * Trims params and strip slashes if magic_quotes_gpc is set.
     *
     * @param mixed $mParam request params
     *
     * @return mixed trimmed params.
     */
    private function _trimData($mParam)
    {
        if (is_array($mParam)) {
            return array_map([&$this, '_trimData'], $mParam);
        }

        $mParam = is_string($mParam) ? trim($mParam) : $mParam;

        return $mParam;
    }
}