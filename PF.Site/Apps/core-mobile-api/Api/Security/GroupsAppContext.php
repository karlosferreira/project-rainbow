<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 1/6/18
 * Time: 10:10 AM
 */

namespace Apps\Core_MobileApi\Api\Security;

use Apps\PHPfox_Groups\Service\Groups;
use Phpfox;

class GroupsAppContext implements PermissionInterface, AppContextInterface
{
    protected $group;

    public function __construct($group)
    {
        $this->group = $group;
    }

    /**
     * @param string $permission Key to check permission
     *
     * @return bool
     */
    function hasPermission($permission)
    {
        defined('PHPFOX_IS_PAGES_VIEW') or define('PHPFOX_IS_PAGES_VIEW', true);

        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            return false;
        }
        return $this->getService()->hasPerm($this->group, $permission);
    }

    function isExisted()
    {
        if ($this->getService()->getPage($this->group)) {
            return true;
        }
        return false;
    }

    function getAppId()
    {
        return $this->group;
    }

    function getAppData()
    {
        return $this->getService()->getPage($this->group);
    }

    function getAppName()
    {
        return "groups";
    }

    function isAdmin($userId = null)
    {
        return $this->getService()->isAdmin($this->getAppData(), $userId);
    }

    function isMember($userId = null)
    {
        return $this->getService()->isMember($this->getAppId(), $userId);
    }

    /**
     * @return Groups
     */
    private function getService()
    {
        return Phpfox::getService("groups");
    }
}