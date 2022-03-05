<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 9:58 AM
 */

namespace Apps\Core_MobileApi\Adapter\Privacy;

use Phpfox;

class UserPrivacy implements UserPrivacyInterface
{

    protected $privacyService;

    public function __construct()
    {
        $this->privacyService = Phpfox::getService('user.privacy');
    }

    function getValue($perm)
    {
        return $this->privacyService->getValue($perm);
    }

    function hasAccess($userId, $perm)
    {
        return $this->privacyService->hasAccess($userId, $perm);
    }


}