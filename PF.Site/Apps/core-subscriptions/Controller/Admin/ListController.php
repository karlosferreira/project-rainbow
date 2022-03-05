<?php
namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Pager;
use Phpfox_Plugin;
use Phpfox_Search;

defined('PHPFOX') or exit('NO DICE!');

class ListController extends Phpfox_Component
{
    public function process()
    {
        if (($iDeleteId = $this->request()->getInt('delete'))) {
            if (Phpfox::getService('subscribe.purchase.process')->delete($iDeleteId)) {
                $this->url()->send('admincp.subscribe.list', null, _p('purchase_order_successfully_deleted'));
            }
        }
        $aPackages = Phpfox::getService('subscribe')->getPackages(false, true);
        $aTitles = [];
        foreach($aPackages as $aPackage)
        {
            $aTitles[$aPackage['title']] = _p($aPackage['title']);
        }


        $aPages = array(20, 30, 40, 50);
        $aDisplays = array();
        foreach ($aPages as $iPageCnt) {
            $aDisplays[$iPageCnt] = _p('per_page', array('total' => $iPageCnt));
        }
        $aSearch = $this->request()->get('search');
        $aSorts = array(
            'sp.time_stamp' => _p('time'),
            'u.full_name' => _p('name'),
            'sp.purchase_id' => _p('Order ID')
        );
        $bCustomSort = false;
        $sDefaultOrderName = 'sp.time_stamp';
        $sDefaultSort = 'DESC';
        $aSort = $this->request()->get('search');
        if (!empty($aSort) && isset($aSort['sort'])) {
            $aSearchSort = explode(' ', $aSearch['sort']);
            if (isset($aSearchSort[1])) {
                $sDefaultSort = $aSearchSort[1];
                $bCustomSort = true;
            }
        }
        if(!empty($aSearch['period']) && $aSearch['period'] == "custom")
        {
            if(empty($aSearch['from']))
            {
                Phpfox_Error::set(_p("Time from can't be empty when selecting custom for period statistics"));
            }
            if(empty($aSearch['to']))
            {
                Phpfox_Error::set(_p("Time to can't be empty when selecting custom for period statistics"));
            }
            if(!empty($aSearch['from']) && !empty($aSearch['to']) && ((int)strtotime($aSearch['from']) > (int)strtotime($aSearch['to']) ))
            {
                Phpfox_Error::set(_p("Time to must be longer than Time from"));
            }

        }
        if(!empty($aSearch['subscription_id']) && !is_numeric($aSearch['subscription_id']))
        {
            Phpfox_Error::set(_p("Subscription ID must be a number"));
        }
        $aStatusOptions = [
            'completed' => [
                'cancel' => _p('Cancel')
            ],
            'cancel' => [
                'completed' => _p('sub_active')
            ],
            'pending' => [
                'completed' => _p('sub_active')
            ]
        ];
        $aReasons = Phpfox::getService('subscribe.reason')->getReasonForCancelSubscription();
        $aReasonOptions = [
          '' => _p('Any')
        ];
        foreach($aReasons as $aReason)
        {
            $aReasonOptions[$aReason['reason_id']] = $aReason['title_parsed'];
        }

        $bSearchReason = false;
        if(!empty($aSearch['reason']) && !empty($aSearch['status']) && $aSearch['status'] == 'cancel')
        {
            $bSearchReason = true;
        }

        $aFilters = array(
            'status' => array(
                'type' => 'select',
                'options' => array(
                    'completed' => _p('sub_active'),
                    'cancel' => _p('canceled'),
                    'expire' => _p('expired'),
                    'pending' => _p('pending_payment'),
                ),
                'add_any' => true,
                'search' => (empty($aSearch['status']) ? 'AND (sp.status IS NOT NULL OR sp.status != \'\')' : 'AND sp.status = \'[VALUE]\''),
                'id' => 'status'
            ),
            'display' => array(
                'type' => 'select',
                'options' => $aDisplays,
                'default' => '20'
            ),
            'sort' => array(
                'type' => 'select',
                'options' => $aSorts,
                'default' => $sDefaultOrderName,
            ),
            'sort_by' => array(
                'type' => 'select',
                'options' => array(
                    'DESC' => _p('descending'),
                    'ASC' => _p('ascending')
                ),
                'default' => $sDefaultSort
            ),
            'title' => array(
                'type' => 'select',
                'options' => $aTitles,
                'add_any' => true,
                'search' => !empty($aSearch['title']) ? 'AND (spack.title LIKE \'%[VALUE]%\') '.(empty($aSearch['status']) ? ' AND (sp.status IS NOT NULL OR sp.status != \'\')' : '') : ''
            ),
            'username' => array(
                'type' => 'input:text',
                'search' => !empty($aSearch['username']) ? 'AND (u.full_name LIKE \'%[VALUE]%\')'.(empty($aSearch['status']) ? ' AND (sp.status IS NOT NULL OR sp.status != \'\')' : '') : ''
            ),
            'subscription_id' => array(
                'type' => 'input:text',
                'search' => !empty($aSearch['subscription_id']) ? 'AND (sp.purchase_id = [VALUE])'.(empty($aSearch['status']) ? ' AND (sp.status IS NOT NULL OR sp.status != \'\')' : '') : ''
            ),
            'period' => array(
                'type' => 'select',
                'id' => 'period',
                'options' => [
                    '' => _p('all_time'),
                    'custom' => _p('subscribe_custom_time'),
                ],
                'search' => (!empty($aSearch['period']) && ($aSearch['period'] == 'custom')) ? ' AND (sp.time_stamp BETWEEN '.strtotime($aSearch['from'].' 00:00:00').' AND '.strtotime($aSearch['to'].' 23:59:59').')'.(empty($aSearch['status']) ? ' AND (sp.status IS NOT NULL OR sp.status != \'\')' : '') : ''
            ),
            'reason' => array(
                'type' => 'select',
                'id' => 'reason',
                'options' => $aReasonOptions,
                'search' => ($bSearchReason ? ' AND (sr.reason_id = '.$aSearch['reason'].')' : '')

            )
        );

        $oFilter = Phpfox_Search::instance()->set(array(
                    'type' => 'subscribe',
                    'filters' => $aFilters,
                )
            );


        if($bCustomSort && isset($aSearchSort[0]))
        {
            $oFilter->setSort($aSearchSort[0]);
        }

        $aPurchases = [];

        if(Phpfox_Error::isPassed())
        {
            $iPage = $this->request()->getInt('page');
            $iPageSize = $oFilter->getDisplay();

            list($iCnt, $aPurchases) = Phpfox::getService('subscribe.purchase')->getSearch($oFilter->getConditions(),
                $oFilter->getSort(), $oFilter->getPage(), $iPageSize, $bSearchReason);

            foreach($aPurchases as $iKey => $aPurchase)
            {
                $aPurchases[$iKey]['status_options'] = $aStatusOptions[$aPurchase['status']];
            }

            $iCnt = $oFilter->getSearchTotal($iCnt);

            Phpfox_Pager::instance()->set(array('page' => $iPage, 'size' => $iPageSize, 'count' => $iCnt));
        }

        $this->template()->setTitle(_p('subscription_purchase_orders'))
            ->setBreadCrumb(_p('apps'),$this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('subscriptions'),$this->url()->makeUrl('admincp.subscribe'))
            ->setBreadCrumb(_p('purchase_orders'), $this->url()->makeUrl('admincp.subscribe.list'))
            ->setActiveMenu('admincp.member.subscribe')
            ->setHeader('cache', [
                'head' => ['colorpicker/css/colpick.css' => 'static_script'],
            ])
            ->setPhrase(['subscribe_activation_date', 'subscribe_expiration_date', 'subscribe_cancelation_date'])
            ->assign(array(
                    'aPurchases' => $aPurchases,
                    'bIsSearching' => $oFilter->isSearching(),
                    'aSearch' => $aSearch,
                    'aStatusOptions' => $aStatusOptions
                )
            );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_admincp_list_clean')) ? eval($sPlugin) : false);
    }
}