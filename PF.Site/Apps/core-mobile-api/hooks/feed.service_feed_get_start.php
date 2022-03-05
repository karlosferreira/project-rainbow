<?php

if (defined('PHPFOX_IS_MOBILE_API_CALL') && PHPFOX_IS_MOBILE_API_CALL) {
    $extra .= Phpfox::getService('mobile.device')->getExtraConditions('feed.type_id');
}

