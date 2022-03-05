<?php

if(Phpfox::isAppActive('P_Reaction')) {
    $like = $aFeed = Phpfox::getService('like')->getAll($itemType, $itemId);
}
