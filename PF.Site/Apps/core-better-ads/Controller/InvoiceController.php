<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class InvoiceController
 * @package Apps\Core_BetterAds\Controller
 */
class InvoiceController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        if (($sId = $this->request()->get('item_number')) != '') {
            define('PHPFOX_SKIP_POST_PROTECTION', true);
            $this->url()->send('ad.invoice', null, _p('better_ads_payment_completed'));
        }

        $this->template()->setTitle(_p('better_ads_ad_invoices'))
            ->setBreadCrumb(_p('better_ads_advertise'), $this->url()->makeUrl('ad'))
            ->setBreadCrumb(_p('better_ads_invoices'), $this->url()->makeUrl('ad.invoice'), true)
            ->setPhrase([
                'cancelled',
                'n_a',
            ])
            ->assign([
                'aInvoices' => $this->_getInvoices()
            ]);
        Phpfox::getService('ad.get')->getSectionMenu();
    }

    private function _getInvoices()
    {
        $aCond = ['ai.user_id = ' . Phpfox::getUserId()];
        $aSearch = $this->request()->getArray('search');

        if (!empty($aSearch)) {
            if (!empty($aSearch['from_month']) && !empty($aSearch['from_day']) && !empty($aSearch['from_year'])) {
                $iFromTimestamp = strtotime(strtr("{day}-{month}-{year} 00:00:00", [
                    '{day}'   => $aSearch['from_day'],
                    '{month}' => $aSearch['from_month'],
                    '{year}'  => $aSearch['from_year'],
                ]));
                $aCond[] = " AND ai.time_stamp >= $iFromTimestamp";
            }
            if (!empty($aSearch['to_month']) && !empty($aSearch['to_day']) && !empty($aSearch['to_year'])) {
                $iToTimestamp = strtotime(strtr("{day}-{month}-{year} 23:59:59", [
                    '{day}'   => $aSearch['to_day'],
                    '{month}' => $aSearch['to_month'],
                    '{year}'  => $aSearch['to_year'],
                ]));
                $aCond[] = " AND ai.time_stamp <= $iToTimestamp";
            }
            if (!empty($aSearch['status'])) {
                if ($aSearch['status'] == 'pending') {
                    $aCond[] = " AND (ai.status='$aSearch[status]' OR ai.status IS NULL)";
                } else {
                    $aCond[] = " AND ai.status='$aSearch[status]'";
                }
            }
            $this->template()->assign('aForms', $aSearch);
        } else {
            $this->template()->assign('aForms', [
                'from_day'   => date('j'),
                'from_month' => date('n'),
                'from_year'  => date('Y'),
                'to_day'     => date('j'),
                'to_month'   => date('n'),
                'to_year'    => date('Y'),
            ]);
        }

        // sort
        $sSort = $this->request()->get('sort', 'ai.time_stamp DESC');
        if ($sSort) {
            $this->template()->assign(['sCurrentSort' => empty($sSort) ? '' : $sSort]);
            $this->search()->setSort($sSort);
        }

        list(, $aInvoices) = Phpfox::getService('ad.get')->getInvoices($aCond, $sSort);

        return $aInvoices;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_invoice_index_clean')) ? eval($sPlugin) : false);
    }
}
