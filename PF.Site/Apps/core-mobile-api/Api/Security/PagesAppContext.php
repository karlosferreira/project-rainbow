<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 1/6/18
 * Time: 10:10 AM
 */

namespace Apps\Core_MobileApi\Api\Security;


class PagesAppContext implements PermissionInterface, AppContextInterface
{
    protected $page;
    protected $name = "pages";

    public function __construct($page)
    {
        $this->page = $page;
    }

    /**
     * @param string $permission Key to check permission
     *
     * @return bool
     */
    function hasPermission($permission)
    {
        defined('PHPFOX_IS_PAGES_VIEW') or define('PHPFOX_IS_PAGES_VIEW', true);

        if (!\Phpfox::isAppActive('Core_Pages')) {
            return false;
        }
        return \Phpfox::getService("pages")->hasPerm($this->page, $permission);
    }

    function isExisted()
    {
        if ($this->getAppData()) {
            return true;
        }
        return false;
    }

    function getAppId()
    {
        return $this->page;
    }

    function getAppData()
    {
        return $this->getService()->getPage($this->page);
    }

    function getAppName()
    {
        return $this->name;
    }

    /**
     * @return \Phpfox_Pages_Pages
     */
    private function getService()
    {
        return \Phpfox::getService('pages');
    }

    function isAdmin($userId = null)
    {
        return $this->getService()->isAdmin($this->getAppData(), $userId);
    }

    function isMember($userId = null)
    {
        return $this->getService()->isMember($this->page, $userId);
    }
}