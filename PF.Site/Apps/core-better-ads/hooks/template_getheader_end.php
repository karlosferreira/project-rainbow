<?php
if(Phpfox::isAppActive('Core_BetterAds') && isset($sData)) {
    $aRecommendSizes = Phpfox::getService('ad.get')->getRecommendImageSizes();
    $sData .= '<script>var betteradsRecommendSizes = [];';
    foreach ($aRecommendSizes as $iBlock => $aRecommendSize) {
        $sData .= "betteradsRecommendSizes[$iBlock] = [];";
        foreach ($aRecommendSize as $iTypeId => $sizes) {
            $sData .= "betteradsRecommendSizes[$iBlock][$iTypeId] = '$sizes';";
        }
    }
    $sData .= '</script>';
}

