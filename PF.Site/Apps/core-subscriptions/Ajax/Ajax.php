<?php

namespace Apps\Core_Subscriptions\Ajax;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Error;
use Phpfox_Url;

class Ajax extends Phpfox_Ajax
{
    /**
     * show popup for cancelling subscription
     */
    public function showPopupCancelSubscription()
    {
        Phpfox::isUser(true);
        $iPurchaseId = $this->get('purchase_id');
        Phpfox::getBlock('subscribe.cancel-subscription', [
            'iPurchaseId' => $iPurchaseId
        ]);
        if (!Phpfox_Error::isPassed()) {
            echo '<div class="error_message">' . implode('<br />', Phpfox_Error::get()) . '</div>';
        }
    }

    public function viewCancelReason()
    {
        Phpfox::isAdmin(true);
        $iPurchaseId = $this->get('purchase_id');
        Phpfox::getBlock('subscribe.cancel-reason', [
            'iPurchaseId' => $iPurchaseId
        ]);
    }

    public function cancelSubscription()
    {
        Phpfox::isUser(true);
        $iPurchase = $this->get('purchase_id');
        $iUserGroupFailure = $this->get('user_group_failure');
        $iUserId = $this->get('user_id');
        $iPackageId = $this->get('package_id');
        $iReasonId = $this->get('reason_id');
        Phpfox::getService('subscribe.reason')->cancelSubscriptionByUser($iPurchase, $iUserGroupFailure, $iUserId, $iPackageId, $iReasonId);
        $this->call("window.location.href = '" . Phpfox_Url::instance()->makeUrl('subscribe.list') . "';"); //redirect to my subscription
    }

    public function markPackagePopular()
    {
        Phpfox::isAdmin(true);
        $iPackageId = $this->get('id');
        Phpfox::getService('subscribe')->markPackagePopular($iPackageId);
        $this->call('$Core.reloadPage();');
    }

    public function renew()
    {
        Phpfox::isUser(true);
        $this->error(false);
        $iPackageId = $this->get('id');
        Phpfox::getBlock('subscribe.renew-payment', [
            'iPackageId' => $iPackageId
        ]);

        if (!Phpfox_Error::isPassed()) {
            echo '<div class="error_message">' . implode('<br />', Phpfox_Error::get()) . '</div>';
        }
    }

    public function upgrade()
    {
        $this->error(false);
        $iRenewType = $this->get('renew_type', 0);
        $iPurchaseId = $this->get('purchase_id', 0);
        Phpfox::getBlock('subscribe.upgrade', ['bIsThickBox' => true, 'iRenewType' => $iRenewType, 'iPurchaseId' => $iPurchaseId]);

        if (!Phpfox_Error::isPassed()) {
            echo '<div class="error_message">' . implode('<br />', Phpfox_Error::get()) . '</div>';
        }
    }

    public function listUpgrades()
    {
        Phpfox::getBlock('subscribe.list');

        $this->html('#' . $this->get('temp_id') . '', $this->getContent(false));
        $this->call('$(\'#' . $this->get('temp_id') . '\').parent().show();');
    }

    public function listUpgradesOnSignup()
    {
        Phpfox::getBlock('subscribe.list', ['on_signup' => true]);

        $this->call('<script> $Core.loadInit(); </script>');
    }

    public function ordering()
    {
        if (Phpfox::getService('subscribe.process')->updateOrder($this->get('val'))) {

        }
    }

    public function orderReason()
    {
        Phpfox::getService('subscribe.purchase.process')->updateOrder($this->get('val'));
    }

    public function orderCompare()
    {
        Phpfox::getService('subscribe.compare.process')->updateOrderCompare($this->get('val'));
    }

    public function updateActivity()
    {
        if (Phpfox::getService('subscribe.process')->updateActivity($this->get('package_id'), $this->get('active'))) {

        }
    }

    public function updateActivityCancelReason()
    {
        Phpfox::getService('subscribe.reason.process')->updateReasonActivity($this->get('reason_id'), $this->get('active'));
    }

    public function deleteImage()
    {
        Phpfox::getService('subscribe.process')->deleteImage($this->get('package_id'));
    }

    public function updatePurchase()
    {
        Phpfox::isAdmin(true);

        if (Phpfox::getService('subscribe.purchase.process')->updatePurchase($this->get('purchase_id'), $this->get('from'), $this->get('to'))) {
            $this->call('$Core.reloadPage();');
        }
    }

    public function change2FreePackage()
    {
        if (Phpfox::getService('subscribe.purchase.process')->change2Free()) {
            $this->call('window.location.href = \'' . Phpfox_Url::instance()->makeUrl('') . '\';');
        }
    }
}