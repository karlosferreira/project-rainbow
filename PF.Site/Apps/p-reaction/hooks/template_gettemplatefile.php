<?php

if(Phpfox::isAppActive('P_Reaction')) {
    $sDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
    $sViewPath = $sDir . '/PF.Site/Apps/p-reaction/views/block/';

    if (in_array($sTemplate, ['ynccomment.block.like-link', 'like.block.link'])) {
        $sTemplate = 'preaction.block.link';
        $sTemplateFile = $sViewPath . 'link' . PHPFOX_TPL_SUFFIX;
        if (cached_file_exists($sTemplateFile)) {
            $sFile = $sTemplateFile;
        }
    }
    if (in_array($sTemplate, ['ynccomment.block.like-display', 'like.block.display'])) {
        $sTemplate = 'preaction.block.display';
        $sTemplateFile = $sViewPath . 'display' . PHPFOX_TPL_SUFFIX;
        if (cached_file_exists($sTemplateFile)) {
            $sFile = $sTemplateFile;
        }
    }
}
