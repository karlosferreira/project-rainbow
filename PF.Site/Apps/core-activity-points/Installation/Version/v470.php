<?php
namespace Apps\Core_Activity_Points\Installation\Version;
use Phpfox;

class v470
{
    public function process()
    {
        $this->initSettingData();
        $this->initStatistic();
    }
    private function initSettingData()
    {
        if(db()->tableExists(Phpfox::getT('activitypoint_setting'))) {
            $iCnt = db()->select('COUNT(*)')
                        ->from(Phpfox::getT('activitypoint_setting'))
                        ->execute('getSlaveField');
            if((int)$iCnt == 0) {
                //init setting for core-app
                db()->query('INSERT INTO '.Phpfox::getT('activitypoint_setting').' (SELECT NULL AS setting_id, name, concat("user_setting","_",name) AS phrase_var_name, module_id, NULL, NULL, NULL FROM '.Phpfox::getT('user_group_setting') .' WHERE name LIKE "%points_%" AND module_id !="activitypoint")');
                //create new user_grou_setting for core
                $aNewSettings = [
                    'points_user_sign-up' => [
                        'module' => 'user',
                        'name' => 'points_user_signup',
                        'user_group' => [
                            '1' => 10,
                            '2' => 10,
                            '3' => 10,
                            '4' => 10,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user sign up.'
                    ],
                    'points_user_accesssite' => [
                        'module' => 'user',
                        'name' => 'points_user_accesssite',
                        'user_group' => [
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user access to site.'
                    ],
                    'points_share_item' => [
                        'module' => 'share',
                        'name' => 'points_share_item',
                        'user_group' => [
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user shared an item.'
                    ],
                    'points_feed_postonwall' => [
                        'module' => 'feed',
                        'name' => 'points_feed_postonwall',
                        'user_group' => [
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user posted a status on wall.'
                    ],
                    'points_feed_postonotherprofile' => [
                        'module' => 'feed',
                        'name' => 'points_feed_postonotherprofile',
                        'user_group' => [
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user posted a status on other profiles.'
                    ],
                    'points_user_uploadprofilephoto' => [
                        'module' => 'user',
                        'name' => 'points_user_uploadprofilephoto',
                        'user_group' => [
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user uploaded profile photos.'
                    ],
                    'points_user_uploadcoverphoto' => [
                        'module' => 'user',
                        'name' => 'points_user_uploadcoverphoto',
                        'user_group' => [
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user uploaded cover photos.'
                    ],
                    'points_friend_addnewfriend' => [
                        'module' => 'friend',
                        'name' => 'points_friend_addnewfriend',
                        'user_group' => [
                            '1' => 1,
                            '2' => 1,
                            '3' => 1,
                            '4' => 1,
                        ],
                        'product_id' => 'phpfox',
                        'type' => 'string',
                        'text' => 'Get activity points when user added a new friend.'
                    ],
                ];
                $aLanguages = Phpfox::getService('language')->getAll();
                foreach($aNewSettings as $aSetting)
                {
                    $aText = [];
                    foreach ($aLanguages as $aLanguage) {
                        $aText[$aLanguage['language_code']] = $aSetting['text'];
                    }
                    $aSetting['text'] = $aText;
                    Phpfox::getService('user.group.setting.process')->addSetting($aSetting);
                    db()->insert(Phpfox::getT('activitypoint_setting'),[
                       'var_name' => $aSetting['name'],
                        'phrase_var_name' => 'user_setting_'.$aSetting['name'],
                        'module_id' => $aSetting['module'],
                    ]);
                }
            }
        }
    }
    private function initStatistic()
    {
        $aUsers = db()->select('user_id, activity_points')
            ->from(Phpfox::getT('user_activity'))
            ->execute('getSlaveRows');
        foreach ($aUsers as $aUser) {
            $userId = $aUser['user_id'];
            $tableStatistics = Phpfox::getT('activitypoint_statistics');
            $iCnt = db()->select('COUNT(*)')
                ->from($tableStatistics)
                ->where(['user_id' => $userId])
                ->execute('getSlaveField');
            if(!$iCnt) {
                $tableTransactions = Phpfox::getT('activitypoint_transaction');
                $iCnt = db()->select('COUNT(*)')
                    ->from($tableTransactions)
                    ->where(['user_id' => $userId])
                    ->execute('getSlaveField');
                if ($iCnt) {
                    $query = "INSERT INTO `" . $tableStatistics . "` (`user_id`, `total_earned`, `total_bought`, `total_sent`, `total_spent`, `total_received`, `total_retrieved`) VALUES (
                        " . $userId . ", 
                        COALESCE((SELECT SUM(t.points) FROM `" . $tableTransactions . "` t WHERE t.user_id = " . $userId . " AND t.type = 'Earned' GROUP BY t.user_id), 0), 
                        COALESCE((SELECT SUM(t.points) FROM `" . $tableTransactions . "` t WHERE t.user_id = " . $userId . " AND t.type = 'Bought' GROUP BY t.user_id), 0), 
                        COALESCE((SELECT SUM(t.points) FROM `" . $tableTransactions . "` t WHERE t.user_id = " . $userId . " AND t.type = 'Sent' GROUP BY t.user_id), 0), 
                        COALESCE((SELECT SUM(t.points) FROM `" . $tableTransactions . "` t WHERE t.user_id = " . $userId . " AND t.type = 'Spent' GROUP BY t.user_id), 0), 
                        COALESCE((SELECT SUM(t.points) FROM `" . $tableTransactions . "` t WHERE t.user_id = " . $userId . " AND t.type = 'Received' GROUP BY t.user_id), 0),
                        COALESCE((SELECT SUM(t.points) FROM `" . $tableTransactions . "` t WHERE t.user_id = " . $userId . " AND t.type = 'Retrieved' GROUP BY t.user_id), 0)
                    )";
                    db()->query($query);
                } else {
                    db()->insert($tableStatistics, [
                        'user_id' => $userId,
                        'total_earned' => $aUser['activity_points']
                    ]);
                }
            }
        }
    }

}
