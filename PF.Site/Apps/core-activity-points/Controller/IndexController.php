<?php

namespace Apps\Core_Activity_Points\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Pager;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class IndexController
 * @package Apps\Core_Activity_Points\Controller
 */
class IndexController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        $iDay = 7;
        $sDefaultDateTo = PHPFOX_TIME;
        $sDefaultDateFrom = $sDefaultDateTo - ($iDay * 86400);
        $aStatistics = Phpfox::getService('activitypoint')->getStatisticsForUser(Phpfox::getUserId());
        $aSettingApps = Phpfox::getService('activitypoint')->getAllAppAndModuleNameForTransaction();
        $aSearch = $this->request()->getArray('val');
        $sSort = !empty($this->request()->get('sort')) ? $this->request()->get('sort') : 't.time_stamp DESC';
        $oSearch = $this->search();
        if ((!empty($aSearch['from_month']) && empty($aSearch['to_month'])) || (!empty($aSearch['from_month']) && empty($aSearch['to_month'])) || (!empty($aSearch['to_month']) && !empty($aSearch['to_month']) && (Phpfox::getLib('date')->mktime(0, 0, 0,
                        $aSearch['from_month'], $aSearch['from_day'], $aSearch['from_year']) > Phpfox::getLib('date')->mktime(0, 0, 0,
                        $aSearch['to_month'], $aSearch['to_day'], $aSearch['to_year'])))) {
            Phpfox_Error::set(_p('activitypoint_invalid_filter_by_time'));
        }
        if (empty($aSearch['from_month']) && empty($aSearch['to_month'])) {
            $aSearch['from_day'] = Phpfox::getTime('j', $sDefaultDateFrom);
            $aSearch['from_month'] = Phpfox::getTime('n', $sDefaultDateFrom);
            $aSearch['from_year'] = Phpfox::getTime('Y', $sDefaultDateFrom);

            $aSearch['to_day'] = Phpfox::getTime('j', $sDefaultDateTo);
            $aSearch['to_month'] = Phpfox::getTime('n', $sDefaultDateTo);
            $aSearch['to_year'] = Phpfox::getTime('Y', $sDefaultDateTo);
        }

        $aTransactions = [];
        if (Phpfox_Error::isPassed()) {
            if (!empty($aSearch['from_month']) && !empty($aSearch['to_month'])) {
                $oSearch->setCondition(' AND (t.time_stamp BETWEEN ' . Phpfox::getLib('date')->mktime(0, 0, 0,
                        $aSearch['from_month'], $aSearch['from_day'], $aSearch['from_year']) . ' AND ' . Phpfox::getLib('date')->mktime(23, 59, 59,
                        $aSearch['to_month'], $aSearch['to_day'], $aSearch['to_year']) . ')');
            }
            if (!empty($aSearch['type'])) {
                $oSearch->setCondition(' AND (t.type="' . $aSearch['type'] . '")');
            }
            $oSearch->setCondition(' AND (t.user_id = ' . Phpfox::getUserId() . ' AND t.is_hidden = 0)');
            $iPage = $this->request()->getInt('page');
            if (empty($iPage)) {
                $iPage = 1;
            }
            $iSize = 10;
            list($iCnt, $aTransactions) = Phpfox::getService('activitypoint')->getTransactions($oSearch->getConditions(), $iPage, $iSize, $sSort);
            foreach ($aTransactions as $iKey => $aTransaction) {
                $aTransactions[$iKey]['module_text'] = $aSettingApps[$aTransaction['module_id']];
                $aParams = [];
                if (!empty($aTransaction['action_params']) && Phpfox::getLib('parse.format')->isSerialized($aTransaction['action_params'])) {
                    $aParams = unserialize($aTransaction['action_params']);
                }
                if (isset($aParams['item_type']) && $aParams['item_type'] == 'user') {
                    $iUserId = isset($aParams['item_id']) ? $aParams['item_id'] : 0;
                    $tempParam = [];
                    if (!empty($iUserId)) {
                        $aUser = Phpfox::getService('user')->getUser($iUserId, 'full_name, user_name');
                        $tempParam = [
                            'link' => '<a href="' . $this->url()->makeUrl($aUser['user_name']) . '" target="_blank">' . $aUser['full_name'] . '</a>'
                        ];
                    }
                    $aParams = $tempParam;
                }
                $aTransactions[$iKey]['phrase'] = _p($aTransaction['phrase'], $aParams);
                $aTransactions[$iKey]['custom_class'] = in_array($aTransaction['type'], ['activitypoint_sent', 'activitypoint_spent', 'activitypoint_retrieved']) ? 'minus' : 'plus';
            }
            $oSearch->browse()->setPagingMode('pagination');
            Phpfox_Pager::instance()->set(array(
                'page' => $iPage,
                'size' => $iSize,
                'count' => $iCnt,
                'paging_mode' => $this->search()->browse()->getPagingMode()
            ));
        }

        Phpfox::getService('activitypoint')->buildMenu();
        $this->template()->setTitle(_p('activitypoint_point_transaction_title'))
            ->setBreadCrumb(_p('activitypoint_point_transaction_title'), $this->url()->makeUrl('activitypoint'))
            ->setPhrase(['activitypoint_select_payment_method'])
            ->assign([
                'aStatistics' => $aStatistics,
                'aTransactions' => $aTransactions,
                'aForms' => $aSearch,
                'sCalendarImage' => Phpfox::getParam('activitypoint.url_asset_images') . 'calendar.gif',
                'sFromDate' => $sDefaultDateFrom,
                'sToDate' => $sDefaultDateTo,
                'aPointTypes' => [
                    'Bought' => _p('activitypoint_bought'),
                    'Earned' => _p('activitypoint_earned'),
                    'Received' => _p('activitypoint_received'),
                    'Sent' => _p('activitypoint_sent'),
                    'Spent' => _p('activitypoint_spent'),
                    'Retrieved' => _p('activitypoint_retrieved')
                ],
                'sCurrent' => $sSort,
                'bBeginer' => (int)$aStatistics['current_points']['points'] == 0 && empty($aTransactions),
                'sFromDateYear' => Phpfox::getTime('Y', $sDefaultDateFrom)
            ]);
    }
}