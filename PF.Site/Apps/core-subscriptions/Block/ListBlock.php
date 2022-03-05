<?php
namespace Apps\Core_Subscriptions\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class ListBlock extends Phpfox_Component
{
    public function process()
    {
        if (Phpfox::isUser()) {
            $aGroup = Phpfox::getService('user.group')->getGroup(Phpfox::getUserBy('user_group_id'));
        }

        $this->template()->assign(array(
                'aPurchases' => (Phpfox::isUser() ? Phpfox::getService('subscribe.purchase')->get(Phpfox::getUserId(),
                    5) : array()),
                'aPackages' => Phpfox::getService('subscribe')->getPackages((Phpfox::isUser() ? false : true),
                    Phpfox::isUser()),
                'sDefaultImagePath' => setting('core.path_actual') . 'PF.Site/Apps/core-subscriptions/assets/images/membership_thumbnail.jpg',
                'aGroup' => ((Phpfox::isUser() && isset($aGroup)) ? $aGroup : array()),
                'bIsOnSignup' => ($this->getParam('on_signup') ? true : false)
            )
        );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_block_list_clean')) ? eval($sPlugin) : false);
    }
}