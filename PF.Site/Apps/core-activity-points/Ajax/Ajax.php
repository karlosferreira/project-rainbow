<?php
namespace Apps\Core_Activity_Points\Ajax;

use Phpfox;
use Phpfox_Ajax;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Ajax
 * @package Apps\Core_Activity_Points\Ajax
 */
class Ajax extends Phpfox_Ajax
{
    /**
     * Delete package image
     */
    public function deleteImage()
    {
        Phpfox::isAdmin(true);
        Phpfox::getService('activitypoint.package.process')->deleteImage($this->get('package_id'));
    }

    /**
     * Update package status
     */
    public function updateActivity()
    {
        Phpfox::isAdmin(true);
        Phpfox::getService('activitypoint.package.process')->updateActivity($this->get('package_id'), $this->get('active'));
    }

    /**
     * Show purchase package block
     */
    public function purchasePackage()
    {
        Phpfox::isUser(true);
        $iPackageId = $this->get('package_id');
        Phpfox::getBlock('activitypoint.purchase-package',[
            'iPackageId' => $iPackageId
        ]);
    }

    /**
     * Add or reduce point of user
     */
    public function adjustPoint()
    {
        Phpfox::isAdmin(true);
        $sUserId = $this->get('user_id');
        Phpfox::getBlock('activitypoint.adjust-point',[
           'user_id' => $sUserId
        ]);
    }

    /**
     * Get maximum points for reduce points in admincp
     */
    public function getMaximumPointsForReduce()
    {
        Phpfox::isAdmin(true);
        $iPoint = Phpfox::getService('activitypoint')->getMaximumPointsForReduceAction($this->get('user_id'));
        $this->call('$("#js_maximum_point_for_reduce").val("'.$iPoint.'");');
        if($this->get('action') == "reduce")
        {
            $this->call('$("#point-number",$("#core-activitypoint__adjust_member_points_block")).html('.$iPoint.');');
        }
    }

    /**
     * Execute action(send/reduce) for member with points in admincp
     */
    public function executeAdjustAction()
    {
        Phpfox::isAdmin(true);
        $sUserId = $this->get('user_id');
        $sAction = $this->get('action');
        $iPoint = (int)$this->get('points');
        if(Phpfox::getService('activitypoint.process')->adjustPoints($sUserId, $sAction, $iPoint))
        {
            $this->alert(_p('activitypoint_adjust_points_successfully'));
            $this->call('setTimeout(function(){$Core.reloadPage();}, 1500);');
        }
    }
}