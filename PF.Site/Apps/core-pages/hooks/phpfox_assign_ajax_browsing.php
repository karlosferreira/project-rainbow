<?php
if (defined('PHPFOX_IS_PAGES_VIEW')
    && defined('PHPFOX_PAGES_ITEM_TYPE')
    && PHPFOX_PAGES_ITEM_TYPE == 'pages'
    && !empty($oTpl->getVar('aSubMenus'))
    && !empty($sModule)
    && !Phpfox::getService('pages')->isActiveIntegration($sModule)) {
    $oTpl->assign('aSubMenus', null);
}