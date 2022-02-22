<?php

namespace Apps\Core_MobileApi\Installation\Version;

class v460
{
    public function process()
    {
        $aUsers = db()->select('user_id, user_name')->from(':user')->where('user_name LIKE "apple-%"')->executeRows();
        if (count($aUsers)) {
            foreach ($aUsers as $key => $aUser) {
                $iCnt = db()->select('count(*)')
                    ->from(':user')
                    ->where(['user_name' => 'profile-' . $aUser['user_id']])
                    ->executeField();

                if ($iCnt) {
                    db()->update(':user', ['user_name' => 'profile-' . $key . uniqid()], 'user_id = ' . $aUser['user_id']);
                } else {
                    db()->update(':user', ['user_name' => 'profile-' . $aUser['user_id']], 'user_id = ' . $aUser['user_id']);
                }
            }
        }
    }
}