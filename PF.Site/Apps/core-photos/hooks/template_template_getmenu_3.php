<?php

if ((!(Phpfox::getUserParam('photo.can_view_photos'))) && isset($sConnection, $aMenus) && $sConnection == 'main') {
    foreach ($aMenus as $key => $menu) {
        if ($menu['module'] == 'photo') {
            unset($aMenus[$key]);
        }
    }
}
