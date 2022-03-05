<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 1/6/18
 * Time: 10:08 AM
 */

namespace Apps\Core_MobileApi\Api\Security;


interface PermissionInterface
{
    /**
     * @param string $permission Key to check permission
     *
     * @return bool
     */
    function hasPermission($permission);

    function isExisted();
}