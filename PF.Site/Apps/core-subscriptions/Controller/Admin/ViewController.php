<?php

namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Pager;

defined('PHPFOX') or exit('NO DICE!');

class ViewController extends Phpfox_Component
{
    public function process()
    {
        $iPurchaseId = $this->request()->getInt('id');
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getPurchase($iPurchaseId, true))) {
            return Phpfox_Error::display(_p('Unable to find this purchase'));
        }
        $aPurchase = Phpfox::getService('subscribe.purchase.process')->getMoreInfoForAdmin($aPurchase);

        $iSize = 20;
        $iPage = $this->request()->getInt('page');
        if (empty($iPage)) {
            $iPage = 1;
        }
        list($aRecentPayments, $iCnt) = Phpfox::getService('subscribe.purchase.process')->getRecentPayments($aPurchase['purchase_id'], $iPage, $iSize, true);

        $this->search()->browse()->setPagingMode('pagination');
        Phpfox_Pager::instance()->set([
            'page' => $iPage,
            'size' => $iSize,
            'count' => $iCnt,
            'paging_mode' => $this->search()->browse()->getPagingMode(),
            'params' => [
                'paging_show_icon' => true // use icon only
            ]
        ]);

        $this->template()
            ->setHeader('cache', [
                'head' => ['colorpicker/css/colpick.css' => 'static_script'],
            ])
            ->assign([
                'aPurchase' => $aPurchase,
                'aRecentPayments' => $aRecentPayments
            ]);

    }
}