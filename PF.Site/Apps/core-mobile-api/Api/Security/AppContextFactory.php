<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 1/6/18
 * Time: 10:26 AM
 */

namespace Apps\Core_MobileApi\Api\Security;


class AppContextFactory
{
    public static function create($module, $itemId)
    {
        $context = null;
        if (!\Phpfox::isModule($module)) {
            return null;
        }
        $otherContext = [];
        //Support Pages/Groups
        (($sPlugin = \Phpfox_Plugin::get('mobile.api_security_app_context_factory_1')) ? eval($sPlugin) : false);
        if (!in_array($module, array_merge($otherContext, ['pages', 'groups']))) {
            return false;
        }
        if ($module == "pages") {
            $context = new PagesAppContext($itemId);
        }
        if ($module == "groups") {
            $context = new GroupsAppContext($itemId);
        }
        (($sPlugin = \Phpfox_Plugin::get('mobile.api_security_app_context_factory_2')) ? eval($sPlugin) : false);
        if ($context && !$context->isExisted()) {
            return null;
        }
        return $context;
    }
}