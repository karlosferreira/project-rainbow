<?php

namespace Apps\Core_Subscriptions\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Error;
use Phpfox_Locale;
use Phpfox_Pager;

defined('PHPFOX') or exit('NO DICE!');

class ViewController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($this->request()->getInt('id')))) {
            return Phpfox_Error::display(_p('unable_to_find_this_invoice'));
        }
        $iSize = 20;
        $iPage = $this->request()->getInt('page', 1);
        $aPaymentMethodPhrase = [
            'activitypoints' => _p('Activity Point'),
            'paypal' => _p('Paypal')
        ];
        list($aRecentPayments, $iCnt) = Phpfox::getService('subscribe.purchase.process')->getRecentPayments($aPurchase['purchase_id'], $iPage, $iSize, true);
        foreach ($aRecentPayments as $iKey => $aRecentPayment) {
            if (!empty($aRecentPayment['payment_method'])) {
                $aRecentPayments[$iKey]['payment_method'] = $aPaymentMethodPhrase[$aRecentPayment['payment_method']];
            }
        }

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

        $this->template()->setTitle(_p('membership_packages'))
            ->setBreadCrumb(_p('membership_packages'), $this->url()->makeUrl('subscribe'))
            ->setBreadCrumb(_p('subscriptions'), $this->url()->makeUrl('subscribe.list'))
            ->setBreadCrumb(_p('order_purchase_id_title', [
                    'purchase_id' => $aPurchase['purchase_id'],
                    'title' => Phpfox_Locale::instance()->convert($aPurchase['title'])
                ]
            ), null, true)
            ->assign([
                    'aPurchase' => $aPurchase,
                    'aRecentPayments' => $aRecentPayments,
                    'sDefaultPhoto' => Phpfox::getParam('subscribe.default_photo_package')
                ]
            );
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_view_clean')) ? eval($sPlugin) : false);
    }
}