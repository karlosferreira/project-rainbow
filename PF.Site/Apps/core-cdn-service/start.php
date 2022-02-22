<?php
// Check if CDN Service is enabled
if (setting('pf_cdn_service_enabled')) {

    new Core\Event([
        'lib_phpfox_cdn_service' => 'Apps\PHPfox_CDN_Service\Model\CDN_Service'
    ]);
}