<?php
$aPhrases = [
    'oops_something_went_wrong' => _p('oops_something_went_wrong'),
    'notice'                    => _p('notice'),
    'view_previous_comments'    => _p('view_previous_comments'),
    'view_number_more_comments' => _p('view_number_more_comments'),
    'view_one_more_comment'     => _p('view_one_more_comment'),
    'remove_preview'            => _p('remove_preview'),
    'edited'                    => _p('edited'),
    'show_edit_history'         => _p('show_edit_history'),
    'edit_history'              => _p('edit_history'),
    'you'                       => _p('you__l'),
    'stickers'                  => _p('stickers'),
    'pages'                     => _p('pages'),
    'groups'                    => _p('groups'),
    'submit'                    => _p('submit')

];

$sData .= '<script>var comment_phrases = ' . json_encode($aPhrases) . ';</script>';


