<?php
if (Phpfox::isAppActive('PHPfox_Videos') && $feedTypeIdUpdate == 'v') {
    Phpfox::getService('v.process')->updateHashtag($iStatusId, null, null, $sStatus);
}