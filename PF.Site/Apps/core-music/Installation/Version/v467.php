<?php

namespace Apps\Core_Music\Installation\Version;

use Phpfox;


class v467
{
    public function process()
    {
        $aUpdatePhrases = [
            "full_name_commented_on_other_full_name_s_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a" => "{full_name} commented on {other_full_name}'s music playlist \"<a href=\"{link}\">{user_name}</a>\".\r\nTo see the comment thread, follow the link below:\r\n<a href=\"{link}\">{link}</a>",
            "full_name_commented_on_other_full_name_s_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a" => "{full_name} commented on {other_full_name}'s album \"<a href=\"{link}\">{user_name}</a>\".\r\nTo see the comment thread, follow the link below:\r\n<a href=\"{link}\">{link}</a>",
        ];
        Phpfox::getService('language.phrase.process')->updatePhrases($aUpdatePhrases);
    }
}