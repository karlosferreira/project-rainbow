<?php

if (array_key_exists('callback_module', $aVals) && $aVals['callback_module'] == 'groups') {
    // temporary save content, because function send of mail clean all => cause issue when use plugin in ajax
    $content = ob_get_contents();
    ob_clean();

    // validate whom to send notification
    $aGroup = Phpfox::getService('groups')->getPage($aVals['callback_item_id']);
    if ($aGroup) {
        $iLinkId = Phpfox::getService('link.process')->getInsertId();
        $aLink = Phpfox::getService('link')->getLinkById($iLinkId);
        if($aLink) {
            $sLinkUrl = $aLink['redirect_link'];
            $postedUserFullName = Phpfox::getUserBy('full_name');

            // get all admins (include owner), send email and notification
            $aAdmins = Phpfox::getService('groups')->getPageAdmins($aVals['callback_item_id']);
            foreach ($aAdmins as $aAdmin) {
                if ($aLink['user_id'] == $aAdmin['user_id']) { // is owner of link
                    continue;
                }

                if ($aGroup['user_id'] == $aAdmin['user_id']) { // is owner of group
                    $varPhraseTitle = 'full_name_posted_a_link_on_your_group_title';
                    $varPhraseLink = 'full_name_posted_a_link_on_your_group_link';
                } else {
                    $varPhraseTitle = 'email_full_name_posted_a_link_on_group_subject';
                    $varPhraseLink = 'full_name_posted_a_link_on_group_link';
                }

                $aSubjectPhrase = [$varPhraseTitle, [
                    'full_name' => $postedUserFullName,
                    'title' => $aGroup['title']
                ]];
                $aMessagePhrase = [$varPhraseLink, [
                    'full_name' => $postedUserFullName,
                    'title' => $aGroup['title'],
                    'link' => $sLinkUrl
                ]];

                Phpfox::getLib('mail')->to($aAdmin['user_id'])
                    ->subject($aSubjectPhrase)
                    ->message($aMessagePhrase)
                    ->notification('groups.email_notification')
                    ->send();

                if (Phpfox::isModule('notification')) {
                    Phpfox::getService('notification.process')->add('groups_comment_link', $iLinkId, $aAdmin['user_id']);
                }
            }
        }
    }

    // return content
    echo $content;
}

