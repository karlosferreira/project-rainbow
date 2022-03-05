<?php

namespace Apps\Core_MobileApi\Adapter\Privacy;


interface UserPrivacyInterface
{
    /**
     * @param $perm
     *
     * @return mixed
     */
    function getValue($perm);

    /**
     * @param $userId
     * @param $perm
     *
     * @return mixed
     */
    function hasAccess($userId, $perm);
}