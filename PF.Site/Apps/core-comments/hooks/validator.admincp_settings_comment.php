<?php

$aValidation['comments_to_check'] = [
    'def'   => 'int:required',
    'min'   => '1',
    'title' => _p('"Comments To Check" must be greater than 0'),
];
$aValidation['total_minutes_to_wait_for_comments'] = [
    'def'   => 'int',
    'min'   => '0',
    'title' => _p('"Comment Minutes to Wait Until Next Check" be greater than or equal to 0'),
];
$aValidation['comments_show_on_activity_feeds'] = [
    'def'   => 'int',
    'min'   => '0',
    'title' => _p('"Number of comment will be shown on activity feeds" must be greater than or equal to 0'),
];
$aValidation['comments_show_on_item_details'] = [
    'def'   => 'int',
    'min'   => '0',
    'title' => _p('"Number of comment will be shown on item details" must be greater than or equal to 0'),
];
$aValidation['comment_replies_show_on_activity_feeds'] = [
    'def'   => 'int',
    'min'   => '0',
    'title' => _p('"Number of replies will be shown on each comment on activity feeds" must be greater than or equal to 0'),
];
$aValidation['comment_replies_show_on_item_details'] = [
    'def'   => 'int',
    'min'   => '0',
    'title' => _p('"Number of replies will be shown on each comment on item details" must be greater than or equal to 0'),
];