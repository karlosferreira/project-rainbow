<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Database;
use Phpfox_Pager;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class InvoiceController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class InvoiceController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        if (($iId = $this->request()->getInt('delete'))) {
            if (Phpfox::getService('ad.process')->deleteInvoice($iId)) {
                $this->url()->send('admincp.ad.invoice', null, _p('better_ads_invoice_successfully_deleted'));
            }
        }

        $this->template()
            ->setTitle(_p('manage_invoices'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb(_p('manage_invoices'))
            ->assign([
                'aInvoices' => $this->_getInvoices()
            ]);

        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();
    }

    private function _getInvoices()
    {
        $iLimit = 10;
        $iPage = $this->request()->getInt('page', 1);
        $aSearch = $this->request()->getArray('search');
        $sSort = $this->request()->get('sort');
        $aConds = ['1'];

        if (!empty($aSearch)) {
            if ($aSearch['from_day'] && $aSearch['from_month'] && $aSearch['from_year']) {
                $aConds[] = ' AND ai.time_stamp>=' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['from_day'],
                        $aSearch['from_month'], $aSearch['from_year']);
            }

            if ($aSearch['to_day'] && $aSearch['to_month'] && $aSearch['to_year']) {
                $aConds[] = ' AND ai.time_stamp<=' . Phpfox::getService('ad.process')->convertToTimestamp($aSearch['to_day'],
                        $aSearch['to_month'], $aSearch['to_year'], 'end');
            }

            if (!empty($aSearch['user'])) {
                $aConds[] = " AND u.full_name LIKE '%$aSearch[user]%'";
            }

            if (!empty($aSearch['status'])) {
                switch ($aSearch['status']) {
                    case '1':
                        $aConds[] = ' AND ai.status = \'completed\'';
                        break;
                    case '2':
                        $aConds[] = ' AND (ai.status = \'pending\' OR ' . Phpfox_Database::instance()->isNull('ai.status') . ')';
                        break;
                    case '3':
                        $aConds[] = ' AND ai.status = \'cancel\'';
                        break;
                    default:
                        break;
                }
            }
            $this->template()->assign('aForms', $aSearch);
        } else {
            $this->template()->assign('aForms', [
                'from_day' => date('j'),
                'from_month' => date('n'),
                'from_year' => date('Y'),
                'to_day' => date('j'),
                'to_month' => date('n'),
                'to_year' => date('Y'),
            ]);
        }

        if (!empty($sSort)) {
            $this->search()->setSort(reset(explode(' ', $sSort)));
        }
        $this->template()->assign('sCurrentSort', empty($sSort) ? 'ai.time_stamp asc' : $sSort);

        list($iCnt, $aInvoices) = Phpfox::getService('ad.get')->getInvoices($aConds, $sSort, $iPage, $iLimit);

        Phpfox_Pager::instance()->set([
            'page' => $iPage,
            'size' => $iLimit,
            'count' => $iCnt
        ]);

        return $aInvoices;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_invoice_clean')) ? eval($sPlugin) : false);
    }
}
