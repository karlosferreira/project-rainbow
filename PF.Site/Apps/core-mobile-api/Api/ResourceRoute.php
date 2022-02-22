<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 14/3/18
 * Time: 10:47 AM
 */

namespace Apps\Core_MobileApi\Api;

class ResourceRoute
{
    const COLLECTION_METHOD = "findAll";
    const FIND_METHOD = "findOne";
    const CREATE_METHOD = "create";
    const UPDATE_METHOD = "update";
    const PATCH_UPDATE_METHOD = "patchUpdate";
    const DELETE_METHOD = "delete";
    const GET_FORM_METHOD = "form";
    const APPROVE_METHOD = "approve";
    const FEATURE_METHOD = "feature";
    const SPONSOR_METHOD = "sponsor";
    const MODERATION_METHOD = "moderation";

    /**
     * @var string resource name ex: user, blog...
     */
    protected $name;

    /**
     * @var string The Document API service for requesting resource
     */
    protected $apiService;

    protected $resources;

    protected $customRoutes = [];

    public function __construct($name, $serviceName = '')
    {
        $this->name = $name;
        $this->apiService = $serviceName;
    }

    /**
     * Generate api route map for specific resource.
     * Registry point point: "route_start.php" hook
     * @return array maps of api url and function would be execute to process api request
     * @throws \Exception
     */
    public function getRouteMap()
    {
        if (empty($this->name) || empty($this->apiService)) {
            throw new \Exception("Incorrect resource definition");
        }
        $maps = [
            "{$this->name}/form"        => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "get" => self::GET_FORM_METHOD
                ]
            ],
            "{$this->name}/form/:id"    => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "get" => self::GET_FORM_METHOD
                ],
                "where"       => [
                    'id' => "(\d+)"
                ]
            ],
            "{$this->name}/approve/:id" => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "put" => self::APPROVE_METHOD
                ],
                "where"       => [
                    'id' => "(\d+)"
                ]
            ],
            "{$this->name}/feature/:id" => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "put" => self::FEATURE_METHOD
                ],
                "where"       => [
                    'id' => "(\d+)"
                ]
            ],
            "{$this->name}/sponsor/:id" => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "put" => self::SPONSOR_METHOD
                ],
                "where"       => [
                    'id' => "(\d+)"
                ]
            ],
            "{$this->name}"             => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "get"  => self::COLLECTION_METHOD,
                    'post' => self::CREATE_METHOD
                ]
            ],
            "{$this->name}/:id"         => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "get"    => self::FIND_METHOD,
                    "delete" => self::DELETE_METHOD,
                    "put"    => self::UPDATE_METHOD,
                    "patch"  => self::PATCH_UPDATE_METHOD
                ],
                "where"       => [
                    'id' => "(\d+)"
                ]
            ],
            "{$this->name}/moderation"  => [
                "api_service" => $this->apiService,
                "maps"        => [
                    "put"  => self::MODERATION_METHOD,
                    "post" => self::MODERATION_METHOD
                ]
            ]
        ];
        if (!empty($this->customRoutes)) {
            $maps = array_merge($maps, $this->customRoutes);
        }

        return $maps;
    }

    /**
     * Adding custom route mapping
     *
     * @param $route
     *
     * @codeCoverageIgnore
     */
    public function addCustomRoute($route)
    {
        $this->customRoutes[] = $route;
    }
}