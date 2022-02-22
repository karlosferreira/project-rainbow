<?php
namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class IndexController extends Phpfox_Component
{
    public function process()
    {
        if (($iDeleteId = $this->request()->getInt('delete')))
        {
            if (Phpfox::getService('subscribe.process')->delete($iDeleteId))
            {
                $this->url()->send('admincp.subscribe', null, _p('package_successfully_deleted'));
            }
        }
        $aVals = $this->request()->getArray('val');
        $this->template()->assign([
            'aTypes' => [
                'onetime' => _p('One Time'),
                'recurring' => _p('Recurring')
            ],
            'aPackageStatus' => [
                'on' => _p('On'),
                'off' => _p('Off')
            ],
            'aPeriod' => [
                'all' => _p('all_time'),
                'custom' => _p('subscribe_custom_time')
            ],
            'sDefaultPeriod' => !empty($aVals['period']) ? $aVals['period'] : 'all'
        ]);
        if(!empty($aVals['period']) && $aVals['period'] == "custom")
        {
            if(empty($aVals['from']))
            {
                Phpfox_Error::set(_p("Time from can't be empty when selecting custom for period statistics"));
            }
            if(empty($aVals['to']))
            {
                Phpfox_Error::set(_p("Time to can't be empty when selecting custom for period statistics"));
            }
            if(!empty($aVals['from']) && !empty($aVals['to']) && ((int)strtotime($aVals['from']) > (int)strtotime($aVals['to']) ))
            {
                Phpfox_Error::set(_p("Time to must be longer than Time from"));
            }
        }

        $error = true;
        $aPackages = [];
        $bHasPackage = false;

        if(Phpfox_Error::isPassed())
        {
            $error = false;
            $aPackages = Phpfox::getService('subscribe')->getForAdmin($aVals);
            $bHasPackage = Phpfox::getService('subscribe')->getPackageCount();
        }

        $this->template()->setTitle(_p('subscription_packages'))
            ->setBreadCrumb(_p('apps'),$this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('subscriptions'),$this->url()->makeUrl('admincp.subscribe'))
            ->setBreadCrumb(_p('subscription_packages'))
            ->setActiveMenu('admincp.member.subscribe')
            ->assign(array(
                    'aPackages' => $aPackages,
                    'aSearch' => $aVals,
                    'bHasPackage' => (bool)$bHasPackage,
                    'bError' => $error
                )
            );
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