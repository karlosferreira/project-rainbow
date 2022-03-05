<?php


namespace Apps\Core_MobileApi\Api\Resource\Object;


use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;

class HyperLink
{
    const GET = "get";
    const POST = "post";
    const PUT = "put";
    const PATCH = "patch";
    const DELETE = "delete";

    public function __construct($method, $route, $params = [])
    {
        $this->method = $method;
        $this->ref = UrlUtility::makeApiUrl($route, $params);
    }

    /**
     * @var string HTTP Method
     */
    public $method = self::GET;

    /**
     * @var string API url
     */
    public $ref;

    public function toArray()
    {
        if (empty($this->method) || $this->method == self::GET) {
            return [
                'ref' => $this->ref
            ];
        }
        return [
            'method' => $this->method,
            'ref'    => $this->ref
        ];
    }

}