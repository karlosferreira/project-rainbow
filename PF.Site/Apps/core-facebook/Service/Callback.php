<?php

namespace Apps\PHPfox_Facebook\Service;

use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Callback extends Phpfox_Service
{
    public function onDeleteUser($iUser)
    {
        $sFilename = $this->database()->select('file_name')
            ->from(':cache')
            ->where('cache_data LIKE "%'.(int)$iUser.'%" AND file_name LIKE "fb_users_%"')
            ->executeField();
        storage()->del($sFilename);
        storage()->del('fb_new_users_' . (int)$iUser);
        storage()->del('fb_force_email_' . (int)$iUser);
        storage()->del('fb_user_notice_' . (int)$iUser);
    }
}

