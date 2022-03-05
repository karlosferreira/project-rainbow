<?php
namespace Apps\Core_Messages\Installation\Version;
use Phpfox;

class v470
{
    public function process()
    {
        $this->updateModuleToApp();
        $this->migrate();
    }
    private function updateModuleToApp()
    {
        //remove hook from old version
        db()->delete(':plugin_hook', 'module_id = "mail"');

        //remove user group setting
        $aOldSettings = ['can_add_folders', 'send_message_to_max_users_each_time', 'total_folders', 'show_core_mail_folders_item_count', 'can_message_self', 'mail_box_warning'];
        foreach($aOldSettings as $sSetting)
        {
            Phpfox::getService('user.group.setting.process')->deleteOldSetting($sSetting, 'mail');
        }

        // update module is app
        db()->update(':module', ['phrase_var_name' => 'module_apps', 'is_active' => 1], ['module_id' => 'mail']);

        //delete menu mail.compose
        db()->delete(':menu','module_id = "mail"');

        //delete cron for mail
        db()->delete(Phpfox::getT('cron'),'module_id = "mail"');
    }
    private function migrate()
    {
        $iMailThreadUserCount = 0;
        if(db()->tableExists(Phpfox::getT('mail_thread_user_compare')))
        {
            if(db()->tableExists(Phpfox::getT('mail_thread_user')))
            {
                $iMailThreadUserCount = db()->select('COUNT(*)')
                    ->from(Phpfox::getT('mail_thread_user'))
                    ->execute('getSlaveField');
            }
            $iMailThreadUserCompareCount =  db()->select('COUNT(*)')
                                                ->from(Phpfox::getT('mail_thread_user_compare'))
                                                ->execute('getSlaveField');
            if((int)$iMailThreadUserCompareCount == 0 && (int)$iMailThreadUserCount > 0)
            {
                db()->query('INSERT INTO '.Phpfox::getT('mail_thread_user_compare').' SELECT thread_id, user_id FROM '.Phpfox::getT('mail_thread_user'));
            }
        }
    }

}