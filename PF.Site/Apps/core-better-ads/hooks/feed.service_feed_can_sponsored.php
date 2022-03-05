<?php
//hide feature sponsor on feed in case ad is enabled
if (!Phpfox::isAppActive('Core_BetterAds')) {
    $bPluginInChange = false;
}
