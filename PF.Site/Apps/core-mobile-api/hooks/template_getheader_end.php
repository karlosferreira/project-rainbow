<?php
if (isset($sData) && Phpfox::isAppActive('Core_MobileApi')) {
    $sData .= Phpfox::getService('mobile.admincp.setting')->getSmartBannerScript();
}