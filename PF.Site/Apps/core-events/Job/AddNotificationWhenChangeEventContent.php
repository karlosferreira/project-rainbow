<?php

namespace Apps\Core_Events\Job;

use Core\Queue\JobAbstract;
use Phpfox;

class AddNotificationWhenChangeEventContent extends JobAbstract
{
    public function perform()
    {
        $aParams = $this->getParams();
        $aEvent = $aParams['aEvent'];
        $iSenderUserId = $aParams['iSenderUserId'];

        Phpfox::getService('event.process')->sendNotificationWhenEventChange($aEvent, $iSenderUserId);

        $this->delete();
    }
}