<?php
$aValidation = [
    'max_upload_size_groups' => [
        'title' => _p('validator_max_file_size_for_photos_upload'),
        'def' => 'int:required',
        'min' => 0
    ],
    'points_groups' => [
        'title' => _p('validator_activity_points_received_when_creating_a_new_group'),
        'def' => 'int:required',
        'min' => 0
    ],
    'max_groups_created' => [
        'title' => _p('validator_user_group_setting_max_groups_created'),
        'def' => 'int:required',
        'min' => 0
    ],
    'flood_control' => [
        'title' => _p('validator_user_group_setting_flood_control'),
        'def' => 'int:required',
        'min' => 0
    ]
];
