<?php

namespace Apps\Core_Activity_Points\Controller;

use Phpfox;
use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class InformationController
 * @package Apps\Core_Activity_Points\Controller
 */
class InformationController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        $userGroupId = Phpfox::getUserBy('user_group_id');
        $aActiveSettings = Phpfox::getService('activitypoint')->getPointSettings($userGroupId);
        $aActiveSettings = Phpfox::getService('activitypoint')->filterActivePointSetting($aActiveSettings, $userGroupId);

        // build app menu items
        Phpfox::getService('activitypoint')->buildMenu();

        $this->template()->setTitle(_p('activitypoint_how_to_earn'))
            ->setPhrase(['activitypoint_select_payment_method'])
            ->setBreadCrumb(_p('activitypoint_how_to_earn'))
            ->assign([
                'aModules' => $aActiveSettings,
            ]);
    }
}