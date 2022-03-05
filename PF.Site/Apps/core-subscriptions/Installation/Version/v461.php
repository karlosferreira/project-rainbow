<?php
namespace Apps\Core_Subscriptions\Installation\Version;

use Phpfox;

/**
 * Class v461
 * @package Apps\Core_Subscriptions\Installation\Version
 */
class v461
{
    public function process()
    {
        $this->updatePermissionForOldPackages();
    }

    private function updatePermissionForOldPackages()
    {
        $aPackages = db()->select('package_id, user_group_id, recurring_cost, cost, recurring_period, number_day_notify_before_expiration')
                        ->from(Phpfox::getT('subscribe_package'))
                        ->where('visible_group IS NULL OR visible_group = ""')
                        ->execute('getSlaveRows');
        if(!empty($aPackages))
        {
            $aUserGroups = Phpfox::getService('user.group')->get();
            $aIds = array_column($aUserGroups,'user_group_id');
            foreach($aPackages as $aPackage)
            {
                $aTemp = array_combine($aIds, $aIds);
                if((int)$aPackage['user_group_id'] > 0)
                {
                    unset($aTemp[$aPackage['user_group_id']]);
                    $aTemp = array_values($aTemp);
                    $bIsFree = true;
                    if((int)$aPackage['recurring_period'] == 0)
                    {
                        $aCost = unserialize($aPackage['cost']);
                        foreach($aCost as $currency => $price)
                        {
                            if((float)$price > 0)
                            {
                                $bIsFree = false;
                                break;
                            }

                        }
                    }
                    else
                    {
                        $bIsFree = false;
                    }

                    $aUpdate = [
                        'visible_group' => serialize($aTemp),
                        'is_free' => $bIsFree ? 1 : 0,
                        'number_day_notify_before_expiration' => ((int)$aPackage['recurring_period'] > 0 ? 3 : 0)
                    ];
                    db()->update(Phpfox::getT('subscribe_package'), $aUpdate, 'package_id = '.(int)$aPackage['package_id']);
                }
            }
        }
    }
}