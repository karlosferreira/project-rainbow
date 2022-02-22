<?php

namespace Apps\PHPfox_CDN_Service\Model;

/**
 * @package Apps\PHPfox_CDN_Service\Model
 */
class CDN_Service
{

    public function getUrl($path, $server_id = 0)
    {
        if ($server_id != 0) {
            return $path;
        }
        $sCdnUrl = setting('pf_cdn_service_url');
        if (empty($sCdnUrl)) {
            return $path;
        }
        $sCdnUrl = trim($sCdnUrl, PHPFOX_DS);
        $sCdnUrl = $sCdnUrl . PHPFOX_DS;
        if ((strpos($sCdnUrl, 'http://') !== 0) && (strpos($sCdnUrl, 'https://') !== 0)) {
            $sCdnUrl = 'http://' . $sCdnUrl;
        }
        $path = str_replace(\Phpfox::getBaseUrl(), $sCdnUrl, $path);

        return $path;
    }

    public function __returnObject()
    {
        return $this;
    }
}
