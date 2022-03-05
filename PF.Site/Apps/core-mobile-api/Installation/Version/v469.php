<?php

namespace Apps\Core_MobileApi\Installation\Version;

use Phpfox;

class v469
{
    public function process()
    {
        //update phrases
        $updatePhrases = [
            "enter_email_or_phone_number" => "Enter Email or Phone Number"
        ];
        Phpfox::getService('language.phrase.process')->updatePhrases($updatePhrases);
    }
}