<?php
namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class CompareController extends Phpfox_Component
{
    public function process()
    {
        if($iDeleteId = $this->request()->getInt('delete'))
        {
            Phpfox::getService('subscribe.compare.process')->deleteCompare($iDeleteId);
            $this->url()->send('admincp.subscribe.compare', null, _p('Delete successfully'));
        }
        $aForCompare = Phpfox::getService('subscribe')->getPackagesForCompare(true);

        $this->template()
            ->setPhrase(array(
                    'no_subscription_package_has_been_created_you_need_at_least_one_subscription_package',
                    'add_a_feature'
                )
            )
            ->setHeader('cache', [
                'jscript/compare.js' => 'app_core-subscriptions',
                'head' => ['colorpicker/css/colpick.css' => 'static_script'],
            ])
            ->assign(array(
                'aPackages' => $aForCompare ,
                'bIsDisplay' => false,
                'iTotalColumns' => (count($aForCompare['packages'])+1),
                'sCheckImage' => Phpfox::getParam('core.path_actual').'PF.Base/theme/adminpanel/default/style/default/image/misc/accept.png',
                'sUncheckImage' => Phpfox::getParam('core.path_actual').'PF.Base/theme/adminpanel/default/style/default/image/misc/cross.png',
            ))->setBreadCrumb(_p('apps'),$this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('subscriptions'),$this->url()->makeUrl('admincp.subscribe'))
            ->setBreadCrumb(_p('compare_subscription_packages'), $this->url()->makeUrl('admincp.subscribe.compare'))
            ->setActiveMenu('admincp.member.subscribe')
            ->setTitle(_p('compare_subscription_packages'));
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_admincp_index_clean')) ? eval($sPlugin) : false);
    }
}