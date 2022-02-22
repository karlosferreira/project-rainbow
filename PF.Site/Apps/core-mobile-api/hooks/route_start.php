<?php

$vendor = PHPFOX_DIR_SITE_APPS . "core-mobile-api" . PHPFOX_DS . "vendor" . PHPFOX_DS . "autoload.php";
if (!file_exists($vendor)) {
    die (" Please update composer first. ");
}
require_once PHPFOX_DIR_SITE_APPS . "core-mobile-api" . PHPFOX_DS . "vendor" . PHPFOX_DS . "autoload.php";

$resourceNaming = new \Apps\Core_MobileApi\Service\NameResource();

\Core\Api\ApiManager::register($resourceNaming->generateRestfulRoute('mobile'));