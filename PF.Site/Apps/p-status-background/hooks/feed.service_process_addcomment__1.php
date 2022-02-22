<?php

if (Phpfox::isAppActive('P_StatusBg') && !empty($aVals['status_background_id'])) {
    Phpfox::getService('pstatusbg.process')->addBackgroundForStatus($this->_aCallback['feed_id'], $iStatusId,
        $aVals['status_background_id'], Phpfox::getUserId(), $this->_aCallback['module']);
}
