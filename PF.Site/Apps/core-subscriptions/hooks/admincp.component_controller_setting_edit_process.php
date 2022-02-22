<?php
defined('PHPFOX') or exit('NO DICE!');

if(isset($sModuleId) && $sModuleId == 'subscribe')
{
    $this->template()->setHeader('cache', [
        'head' => ['colorpicker/css/colpick.css' => 'static_script'],
    ]);
}