<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service\Helper;

use Apps\Core_MobileApi\Api\ApiRequestInterface;
use Symfony\Component\HttpFoundation\Request;

class PsrRequestHelper extends Request implements ApiRequestInterface
{
    /**
     * Request data
     *
     * @var array
     */
    private $_aArgs = [];

    /**
     * @var array
     */
    private $requestData;

    private static $symfonyInst;
    private static $forceTest;

    /**
     * @param bool $forceTest
     * @return mixed
     */
    public static function instance($forceTest = false)
    {
        if (self::$symfonyInst == null) {
            self::$symfonyInst = static::createFromGlobals();
        }
        self::$forceTest = $forceTest;
        return self::$symfonyInst;
    }

    /**
     * {@inheritdoc}
     */
    public function getInt($sName, $mDef = '')
    {
        return (int)$this->get($sName, $mDef);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray($sName, $mDef = [])
    {
        return (array)(isset($this->_aArgs[$sName]) ? $this->_aArgs[$sName] : $mDef);
    }


    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        $this->headers->all();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        $this->headers->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        $this->headers->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        $this->getContent(true);
    }


    /**
     * {@inheritdoc}
     */
    public function isPost()
    {
        return $this->isMethod(self::METHOD_POST);
    }

    /**
     * {@inheritdoc}
     */
    public function isGet()
    {
        return $this->isMethod(self::METHOD_GET);
    }

    /**
     * {@inheritdoc}
     */
    public function isPatch()
    {
        return $this->isMethod(self::METHOD_PATCH);
    }

    /**
     * {@inheritdoc}
     */
    public function isPut()
    {
        return $this->isMethod(self::METHOD_PUT);
    }

    /**
     * {@inheritdoc}
     */
    public function isOptions()
    {
        return $this->isMethod(self::METHOD_OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function isDelete()
    {
        return $this->isMethod(self::METHOD_DELETE);
    }

    /**
     * {@inheritdoc}
     */
    public function isHead()
    {
        return $this->isMethod(self::METHOD_HEAD);
    }

    public function get($name, $default = null)
    {
        $request = $this->getRequests();
        return (isset($request[$name]) ? $request[$name] : $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequests()
    {
        if (!$this->requestData || self::$forceTest) {
            $contents = json_decode($this->getContent(), true);
            if (($this->isPut() || $this->isPost() || $this->isPatch()) && $contents !== null) {
                $this->requestData = array_merge($this->query->all(), $this->request->all(), $contents);
            } else {
                $this->requestData = array_merge($this->query->all(), $this->request->all());
            }

            // If file upload exist. this help to skip require validator
            if (!empty($this->files->keys())) {
                foreach ($this->files->keys() as $key) {
                    $this->requestData[$key] = true;
                }
            }
        }

        return $this->requestData;
    }

    public function getFiles()
    {
        return $this->files->all();
    }

    public function getFile($key)
    {
        return $this->files->get($key);
    }
}