<?php

namespace Apps\P_StatusBg\Service;

use Phpfox;
use Phpfox_Service;

class Callback extends Phpfox_Service
{
    public function getUploadParams($aParams = null)
    {
        return Phpfox::getService('pstatusbg')->getUploadParams($aParams);
    }

}