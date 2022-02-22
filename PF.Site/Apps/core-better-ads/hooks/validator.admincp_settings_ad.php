<?php
defined('PHPFOX') or exit('NO DICE!');

$aValidation['better_ads_number_ads_per_location'] = [
    'def' => 'int:required',
    'min' => '1',
    'title' => _p('number_of_ad_on_each_location_must_be_greater_than_0'),
];
