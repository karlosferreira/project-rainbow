<?php

namespace Apps\phpFox_CKEditor\Installation\Version;

use Phpfox;

class v424
{
    public function process()
    {
        $aCKEditorSetting = db()->select('*')
            ->from(Phpfox::getT('setting'))
            ->where('module_id = "phpFox_CKEditor" AND var_name = "ckeditor_package"')
            ->executeRow();

        if (!empty($aCKEditorSetting)) {
            db()->update(Phpfox::getT('user_group_setting'), [
                'default_admin' => $aCKEditorSetting['value_actual'],
                'default_user' => $aCKEditorSetting['value_actual'],
                'default_guest' => $aCKEditorSetting['value_actual'],
                'default_staff' => $aCKEditorSetting['value_actual']
            ], ['module_id' => "pckeditor", 'name' => "ckeditor_package"]);

            db()->delete(Phpfox::getT('setting'), ['module_id' => "phpFox_CKEditor", 'var_name' => "ckeditor_package"]);
        }
    }
}
