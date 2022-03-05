<?php

namespace Apps\Core_MobileApi\Adapter\Utility;

class UrlUtility
{

    const API_PREFIX = 'mobile';

    public static function makeRoute($route, $prefix = self::API_PREFIX)
    {
        return ($prefix . '/' . $route);
    }

    /**
     * Create full restful API url
     *
     * @param string         $route
     * @param int|array|null $param parameters to build url
     * @param string         $prefix
     * @param bool           $full
     *
     * @return string
     */
    public static function makeApiUrl($route, $param = null, $prefix = self::API_PREFIX, $full = false)
    {
        if (is_array($param)) {
            $path = $route . "?";
            foreach ($param as $key => $value) {
                if (strpos($path, ":$key")) {
                    $path = str_replace(":$key", $value, $path);
                } else {
                    $path .= "$key=$value" . "&";
                }
            }
            $path = trim($path, "&?");

            $returnUrl = $prefix . "/" . $path;
        } else if (is_scalar($param)) {
            $returnUrl = $prefix . "/" . str_replace(":id", $param, $route);
        } else {
            return $returnUrl = $prefix . '/' . $route;
        }
        if ($full) {
            return (\Phpfox::getLib("url")
                    ->makeUrl("restful_api") . $returnUrl);
        }
        return $returnUrl;

    }

    /**
     * @return string Home site url
     */
    public static function makeHomeUrl()
    {
        return \Phpfox::getLib("url")
            ->makeUrl("");
    }

    /**
     * @return string Api Endpoint
     */
    public static function apiEndpoint()
    {
        return \Phpfox::getLib("url")
                ->makeUrl("restful_api") . self::API_PREFIX;
    }

    /**
     * Covert rule {resource}/{id}
     *
     * @param $link
     *
     * @return null|string
     */
    public static function convertWebLinkToApi($link)
    {
        $link = str_replace(self::makeHomeUrl(), "", $link);
        $paths = explode("/", $link);

        if (count($paths) >= 3) {
            if (is_string($paths[0]) && is_numeric($paths[1]) && \Phpfox::isModule($paths[0])) {
                return self::makeApiUrl($paths[0] . "/:id", $paths[1]);
            }
            if (is_string($paths[0]) && is_string($paths[1]) && is_numeric($paths[2]) && (\Phpfox::isApps($paths[0]) || \Phpfox::isModule($paths[0]))) {
                return self::makeApiUrl("{$paths[0]}/{$paths[1]}/:id", $paths[2]);
            }
        }
        return null;
    }

    public static function getPhotoUrl($dir, $serverId, $destination, $suffix)
    {
        return \Phpfox::getLib('image.helper')->display([
                'server_id'  => $serverId,
                'path'       => $dir,
                'file'       => $destination,
                'suffix'     => '_' . $suffix,
                'return_url' => true,
            ]
        );
    }
}