<?php
if (defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && PHPFOX_PAGES_ITEM_TYPE == 'pages' && $iId == 2) {
    $aBlocks[2][] = ['type_id' => 0, 'component' => 'pages.pending', 'params' => []];
}

$sModuleName = Phpfox::getLib('module')->getModuleName();
if ($iId == 11 && Phpfox_Component::__getParam('show_page_cover')) {
    $aBlocks[11][] = ['type_id' => 0, 'component' => 'pages.photo', 'params' => [
        'aPage' => Phpfox_Component::__getParam('page_to_show_cover')
    ]];
}
