<?php
$aValidation = [
    'chat_group_member_maximum' => [
        'def' => 'int:required',
        'min' => '2',
        'max' => '10',
        'title' => _p('mail_numbers_of_group_members_validation'),
    ]
];