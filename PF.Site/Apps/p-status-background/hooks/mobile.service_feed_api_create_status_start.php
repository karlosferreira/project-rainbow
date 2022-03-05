<?php

if (Phpfox::isAppActive('P_StatusBg')) {
    $background = $this->request()->get('status_background_id');
    if (!empty($background)) {
        $values['status_background_id'] = $background;
    }
}
