<?php

$sDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
$sCommentPath = $sDir . '/PF.Site/Apps/core-comments/views/block/';

if ($sTemplate == 'feed.block.comment') {
    $sTemplateFile = $sCommentPath . 'comment' . PHPFOX_TPL_SUFFIX;
    if (file_exists($sTemplateFile)) {
        $sFile = $sTemplateFile;
    }
}

if ($sTemplate == 'comment.block.mini') { // overwrite material-html template, it will @deprecated in Core 4.8.0
    $sTemplateFile = $sCommentPath . 'mini' . PHPFOX_TPL_SUFFIX;
    if (file_exists($sTemplateFile)) {
        $sFile = $sTemplateFile;
    }
}
