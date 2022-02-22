<?php

namespace Apps\Core_MobileApi\Job;

use Core\Queue\JobAbstract;

class PushNotification extends JobAbstract
{
    public function perform()
    {
        \Phpfox::getService('mobile.device')->executePushNotification($this->getParams());
        $this->delete();
    }

}