<?php

if ($aReturn && Phpfox::isAppActive('P_Reaction')) {
    $iLast = count($aFeeds) - 1;
    if ($iLast >= 0) {
        Phpfox::getService('preaction')->getReactionsPhrase($aFeeds[$iLast]);
    }
}
