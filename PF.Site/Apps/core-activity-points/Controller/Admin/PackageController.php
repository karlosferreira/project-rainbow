<?php
namespace Apps\Core_Activity_Points\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class PackageController
 * @package Apps\Core_Activity_Points\Controller\Admin
 */
class PackageController extends Phpfox_Component
{
    public function process()
    {
        if($iDelete = $this->request()->getInt('delete'))
        {
            if(Phpfox::getService('activitypoint.package.process')->delete($iDelete))
            {
                $this->url()->send('admincp.activitypoint.package',null, _p('Delete package successfully'));
            }
        }
        $sDefaultCurrency = Phpfox::getService('core.currency')->getDefault();
        $iPage = $this->request()->getInt('page');
        if(empty($iPage))
        {
            $iPage = 1;
        }
        $iSize = 10;
        $sOrder = !empty($this->request()->get('sort')) ? $this->request()->get('sort') : 'package_id DESC';
        list($iCnt,$aPackages) = Phpfox::getService('activitypoint.package')->getForAdmin($iPage, $iSize, $sOrder);
        $this->search()->browse()->setPagingMode('pagination');
        Phpfox_Pager::instance()->set(array(
            'page' => $iPage,
            'size' => $iSize,
            'count' => $iCnt,
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ));
        $bEmpty = Phpfox::getService('activitypoint.package')->checkEmpty();
        $this->template()->setTitle(_p('activitypoint_points_package'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb('module_activitypoint', $this->url()->makeUrl('admincp.app', ['id' => 'Core_Activity_Points']))
            ->setBreadCrumb(_p('activitypoint_points_package'), $this->url()->makeUrl('admincp.activitypoint.package'))
            ->assign([
            'sDefaultCurrencySymbol' => Phpfox::getService('core.currency')->getSymbol($sDefaultCurrency),
            'aPackages' => $aPackages,
            'sCurrent' =>$sOrder,
            'bEmpty' => $bEmpty
        ]);
    }
}