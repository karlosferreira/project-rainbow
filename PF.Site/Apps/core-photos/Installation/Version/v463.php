<?php

namespace Apps\Core_Photos\Installation\Version;

class v463
{
    public function __construct()
    {

    }

    public function process()
    {
        //Removed setting "photo_pic_sizes"
        db()->delete(':setting', ['var_name' => 'photo_pic_sizes', 'module_id' => 'photo']);

        //Deprecated setting "enabled_watermark_on_photos", remove this settings in v4.6.4
        db()->update(':setting', ['is_hidden' => 1], ['var_name' => 'enabled_watermark_on_photos', 'module_id' => 'photo']);
    }
}