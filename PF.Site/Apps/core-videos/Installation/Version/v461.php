<?php

namespace Apps\PHPfox_Videos\Installation\Version;

class v461
{
    public function process()
    {
        db()->delete(':language_phrase','var_name = \'Active\' AND text_default = \'active\'');
    }
}
