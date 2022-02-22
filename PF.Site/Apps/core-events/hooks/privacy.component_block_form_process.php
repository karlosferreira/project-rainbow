<?php
$bIsModuleEvent = $this->getParam('is_module_event');

if ($bIsModuleEvent) {
    $aInviteeOnlyPrivacy = [
        'phrase' => _p('invitee_only_privacy'),
        'value'  => '5'
    ];

    foreach ($aPrivacyControls as $key => $value) {
        if ($value['value'] == 3) {
            array_splice($aPrivacyControls, $key, 0, [$aInviteeOnlyPrivacy]);
        }
    }
}