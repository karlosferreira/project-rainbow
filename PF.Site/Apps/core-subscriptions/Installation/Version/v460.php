<?php
namespace Apps\Core_Subscriptions\Installation\Version;
use Phpfox;

class v460
{
    public function process()
    {
        $this->updateModuleToApp();
        $this->addCron();
        $this->addDefaultReason();
    }

    private function addCron()
    {
        $iCron = db()->select('COUNT(*)')
            ->from(':cron')
            ->where('module_id = \'subscribe\'')
            ->execute('getSlaveField');

        if (!$iCron) {
            db()->insert(Phpfox::getT('cron'), [
                'module_id' => 'subscribe',
                'product_id' => 'phpfox',
                'type_id' => 2,
                'every' => 1,
                'is_active' => 1,
                'php_code' => 'Phpfox::getService("subscribe.purchase.process")->downgradeExpiredSubscribers();'
            ]);
        }
    }
    private function updateModuleToApp()
    {
        // update module is app
        db()->update(':module', ['phrase_var_name' => 'module_apps', 'is_active' => 1], ['module_id' => 'subscribe']);
    }
    private function addDefaultReason()
    {
        if(db()->tableExists(Phpfox::getT('subscribe_reason')))
        {
            $aRow = db()->select('*')
                    ->from(Phpfox::getT('subscribe_reason'))
                    ->where('is_default = 1')
                    ->execute('getSlaveRow');
            if(empty($aRow))
            {
                $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();
                $aLanguages = Phpfox::getService('language')->getAll();
                $aText = [];
                foreach ($aLanguages as $aLanguage) {
                    $aText[$aLanguage['language_code']] = 'Other reasons';
                }
                $sTitleVarName = 'subscription_reason_title_' . md5($sDefaultLanguageCode . time());
                \Core\Lib::phrase()->addPhrase($sTitleVarName, $aText);

                $iLastOrderId = db()->select('ordering')->from(Phpfox::getT('subscribe_reason'))->order('ordering DESC')->execute('getSlaveField');
                $iLastOrderId = empty($iLastOrderId) ?  1 : ((int)$iLastOrderId + 1);
                $aInsert = [
                    'title' => $sTitleVarName,
                    'ordering' => $iLastOrderId,
                    'is_default' => 1
                ];
                db()->insert(Phpfox::getT('subscribe_reason'), $aInsert);
            }

        }
    }
}
