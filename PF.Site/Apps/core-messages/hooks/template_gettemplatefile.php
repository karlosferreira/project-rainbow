<?php
//todo core should support to do this
$sDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
$sCoreMessagesPath = $sDir . '/PF.Site/Apps/core-messages/views/controller/';
if ($sTemplate == 'mail.controller.index') {
    $sTemplateFile = $sCoreMessagesPath . 'index' . PHPFOX_TPL_SUFFIX;
    if (file_exists($sTemplateFile)) {
        $sFile = $sTemplateFile;
    }
}
if ($sTemplate == 'mail.controller.panel') {
    $sTemplateFile = $sCoreMessagesPath . 'panel' . PHPFOX_TPL_SUFFIX;
    if (file_exists($sTemplateFile)) {
        $sFile = $sTemplateFile;
    }
}
