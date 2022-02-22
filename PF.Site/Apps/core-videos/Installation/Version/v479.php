<?php

namespace Apps\PHPfox_Videos\Installation\Version;

use Phpfox;

class v479
{
    public function process()
    {
        Phpfox::getService('language.phrase.process')->updatePhrases([
            'user_name_tagged_you_in_video_tittle_link' => '{user_name} tagged you in a video "{title}". <a href="{link}">check it out</a>'
        ]);
    }
}
