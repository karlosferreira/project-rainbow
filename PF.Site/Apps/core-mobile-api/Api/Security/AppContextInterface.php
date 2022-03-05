<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 7/6/18
 * Time: 3:18 PM
 */

namespace Apps\Core_MobileApi\Api\Security;


interface AppContextInterface
{
    function getAppId();

    function getAppData();

    function getAppName();

    function isAdmin($userId = null);

    function isMember($userId = null);

}