<?php

namespace Apps\Core_Events\Job;

use Core\Queue\JobAbstract;
use Phpfox;

class AddNotificationForPostStatusInEvent extends JobAbstract
{
    public function perform()
    {
        $aParams = $this->getParams();
        $iFeedId = $aParams['iFeedId'];
        $aEvent = $aParams['aEvent'];
        $sType = $aParams['sType'];

        Phpfox::getService('event.process')->sendNotificationForPostStatusInEvent($aEvent, $iFeedId, $sType);

        $this->delete();
    }
}