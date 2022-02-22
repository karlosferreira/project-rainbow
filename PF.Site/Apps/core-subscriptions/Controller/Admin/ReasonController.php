<?php
namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Pager;
use Phpfox_Search;

defined('PHPFOX') or exit('NO DICE!');

class ReasonController extends Phpfox_Component
{
    public function process()
    {
        $aPages = array(3, 30, 40, 50);
        $aDisplays = array();
        foreach ($aPages as $iPageCnt) {
            $aDisplays[$iPageCnt] = _p('per_page', array('total' => $iPageCnt));
        }
        $oFilter = Phpfox_Search::instance()->live()
            ->setRequests();
        $bSearching = $oFilter->get('searching');
        $aSearch = empty($bSearching) ? $oFilter->get('search') : $oFilter->get();

        $aSorts = array(
            'ordering' => _p('ordering'),
        );

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

        $aFilters = array(

            'display' => array(
                'type' => 'select',
                'options' => $aDisplays,
                'default' => '20'
            ),
            'sort' => array(
                'type' => 'select',
                'options' => $aSorts,
                'default' => 'ordering',
                'alias' => 'sr'
            ),
            'sort_by' => array(
                'type' => 'select',
                'options' => array(
                    'DESC' => _p('descending'),
                    'ASC' => _p('ascending')
                ),
                'default' => 'ASC'
            ),
            'period' => array(
                'type' => 'select',
                'id' => 'period',
                'options' => [
                    '' => _p('all_time'),
                    'custom' => _p('subscribe_custom_time'),
                ],
                'search' => ($aSearch['period'] == 'custom') ? ' AND (scr.time_stamp BETWEEN '.strtotime($aSearch['from'].' 00:00:00').' AND '.strtotime($aSearch['to'].' 23:59:59').')' : ''
            )
        );

        $oFilter->set(array(
                'type' => 'subscribe',
                'filters' => $aFilters,
                'redirect' => true,
                'redirect_url' => 'admincp.subscribe.reason'
            )
        );

        $aReasons = [];

        if(Phpfox_Error::isPassed())
        {
            $iPage = $this->request()->getInt('page');
            $iPageSize = $oFilter->getDisplay();

            list($iCnt, $aReasons) = Phpfox::getService('subscribe.reason')->getReasonSearchForAdmin($oFilter->getConditions(), $oFilter->getSort(), $oFilter->getPage(), $iPageSize);

            $iCnt = $oFilter->getSearchTotal($iCnt);

            Phpfox_Pager::instance()->set(array('page' => $iPage, 'size' => $iPageSize, 'count' => $iCnt));
        }

        $this->template()->setBreadCrumb(_p('apps'),$this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('subscriptions'),$this->url()->makeUrl('admincp.subscribe'))
            ->setBreadCrumb(_p('subscribe_menu_cancel_reason_title'), $this->url()->makeUrl('admincp.subscribe.reason'))
            ->assign([
            'bIsSearching' => $oFilter->isSearching(),
            'aSearch' => $aSearch,
            'aReasons' => $aReasons
        ])
        ->setHeader('cache', [
            'head' => ['colorpicker/css/colpick.css' => 'static_script'],
        ]);
    }
}