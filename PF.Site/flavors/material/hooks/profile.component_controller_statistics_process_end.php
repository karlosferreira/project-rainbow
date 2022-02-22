<?php
$aIcons = [];
foreach ($aModules as $sModuleId => $aModule) {
    foreach ($aModule as $sPhrase => $sLink) {
        if (function_exists('materialParseIcon')) {
            $aIcons[$sPhrase] = materialParseIcon($sModuleId);
        }
    }
}
$this->template()->assign([
    'aIcons'      => $aIcons,
    'iTotalItems' => $aActivites[_p('total_items')]
]);
unset($aActivites[_p('total_items')]);
$this->template()->assign('aActivites', $aActivites);
