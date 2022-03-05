<?php
/**
 * Created by PhpStorm.
 * User: phpFox
 * Date: 5/31/17
 * Time: 16:04
 */
defined('PHPFOX') or exit('NO DICE!');

if (Phpfox::isAppActive('Core_Polls') && !Phpfox::getUserParam('poll.can_access_polls')) {
    foreach ($aMenus as $key => $value) {
        if ($value['module'] == 'poll' && ($value['url'] = 'poll' || $value['url'] = 'profile.poll')) {
            unset($aMenus[$key]);
            break;
        }
    }
}