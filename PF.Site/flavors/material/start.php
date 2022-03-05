<?php
// change location of sub_menu block

Phpfox::getLib('setting')->setParam('core.sub_menu_location', '6');

if (\Phpfox::getMessage()) {
    new \Core\Event('lib_module_page_class', function ($object) {
        $object->cssClass .= ' has-public-message';
    });
}

// add class material_html (for clone from material)
new \Core\Event('lib_module_page_class', function ($object) {
    $object->cssClass .= ' material_html';
});
