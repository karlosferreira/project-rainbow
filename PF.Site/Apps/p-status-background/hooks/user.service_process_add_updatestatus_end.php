<?php

if (Phpfox::isAppActive('P_StatusBg') && $iStatusId && !empty($aVals['status_background_id'])) {
    Phpfox::getService('pstatusbg.process')->addBackgroundForStatus('user_status', $iStatusId,
        $aVals['status_background_id'], Phpfox::getUserId(), 'user');
}
