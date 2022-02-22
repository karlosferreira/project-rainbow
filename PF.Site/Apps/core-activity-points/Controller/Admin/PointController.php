<?php
namespace Apps\Core_Activity_Points\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;
use Phpfox_Search;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class PointController
 * @package Apps\Core_Activity_Points\Controller\Admin
 */
class PointController extends Phpfox_Component
{
    public function process()
    {
        $oSearch = Phpfox_Search::instance();
        $aSearch = $this->request()->get('search');
        if(!empty($aSearch['user']))
        {
            $oSearch->setCondition(' AND ('.(is_numeric($aSearch['user']) ? 's.user_id = '.(int)$aSearch['user'] : 'u.full_name LIKE "%'.$aSearch['user'].'%"').')');
        }
        $oSearch->setCondition(' AND (u.profile_page_id = 0)');
        $sSort = !empty($this->request()->get('sort')) ? $this->request()->get('sort') : 'a.activity_points DESC';
        $iPage = $this->request()->getInt('page');
        if(empty($iPage))
        {
            $iPage = 1;
        }
        $iSize = 20;
        list($iCnt, $aMemberPoints) = Phpfox::getService('activitypoint')->getMemberPointsForAdmin($oSearch->getConditions(), $iPage, $iSize, $sSort);
        Phpfox_Pager::instance()->set(array(
            'page' => $iPage,
            'size' => $iSize,
            'count' => $iCnt,
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ));
        $this->template()->setTitle(_p('activitypoint_member_points'))
                        ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
                        ->setBreadCrumb('module_activitypoint', $this->url()->makeUrl('admincp.app', ['id' => 'Core_Activity_Points']))
                        ->setBreadCrumb(_p('activitypoint_member_points'), $this->url()->makeUrl('admincp.activitypoint.point'))
                        ->setPhrase([
                            'activitypoint_send_point', 'activitypoint_reduce_point', 'activitypoint_error_message_admincp_adjust_point', 'activitypoint_point_actions', 'activitypoint_error_message_can_not_reduce_point', 'activitypoint_error_message_can_not_send_point', 'activitypoint_notify_maximum_point_for_reduce', 'activitypoint_notify_maximum_point_for_send', 'activitypoint_cannot_send_negative_point_number'
                        ])
                        ->assign([
                            'aMemberPoints' => $aMemberPoints,
                            'sCurrent' => $sSort,
                        ]);
    }
}