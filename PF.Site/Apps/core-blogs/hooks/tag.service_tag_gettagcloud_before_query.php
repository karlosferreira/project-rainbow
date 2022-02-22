<?php

if (isset($sCategory) && $sCategory == 'blog') {
    Phpfox::getService('blog')->getConditionsForTagCloud($aWhere);
}