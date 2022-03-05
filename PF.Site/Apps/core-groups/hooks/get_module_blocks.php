<?php
if (defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && PHPFOX_PAGES_ITEM_TYPE == 'groups' && $iId == 2) {
    $aBlocks[2][] = ['type_id' => 0, 'component' => 'groups.pending', 'params' => []];
}

$sModuleName = Phpfox::getLib('module')->getModuleName();
if ($iId == 11 && Phpfox_Component::__getParam('show_group_cover')) {
    $aBlocks[11][] = ['type_id' => 0, 'component' => 'groups.photo', 'params' => [
        'aPage' => Phpfox_Component::__getParam('group_to_show_cover')
    ]];
}
