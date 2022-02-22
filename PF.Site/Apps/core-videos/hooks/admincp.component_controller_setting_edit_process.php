<?php

if(!empty($aVals['value']['pf_video_s3_key']) && !empty($aVals['value']['pf_video_s3_secret']) && !empty($aVals['value']['pf_video_s3_bucket'])) {
    $region = 'us-east-2'; // default
    $oClient = new Aws\S3\S3Client([
        'region' => $region,
        'version' => 'latest',
        'credentials' => [
            'key' => $aVals['value']['pf_video_s3_bucket'],
            'secret' => $aVals['value']['pf_video_s3_bucket'],
        ],
    ]);
    $region = $oClient->determineBucketRegion($aVals['value']['pf_video_s3_bucket']);
    Phpfox::getLib('database')->update(':setting', ['value_actual' => $region], 'var_name="pf_video_s3_region" AND module_id="v"');

    Phpfox::getLib('cache')->remove('setting');
    Phpfox::getLib('cache')->remove('app_settings');

    $aNewSettings = Phpfox::getService('admincp.setting')->get($aCond);
    $this->template()
        ->assign(['aSettings' => $aNewSettings]);
}
