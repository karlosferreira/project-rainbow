<?php

if (array_key_exists('callback_module', $aVals) && $aVals['callback_module'] == 'pages') {
    // temporary save content, because function send of mail clean all => cause issue when use plugin in ajax
    $content = ob_get_contents();
    ob_clean();

    // validate whom to send notification
    $aPage = Phpfox::getService('pages')->getPage($aVals['callback_item_id']);
    if ($aPage) {
        $iLinkId = Phpfox::getService('link.process')->getInsertId();
        $aLink = Phpfox::getService('link')->getLinkById($iLinkId);
        if($aLink) {
            $sLinkUrl = $aLink['redirect_link'];
            $postedUserFullName = Phpfox::getUserBy('full_name');

            $pageUserId = Phpfox::getService('pages')->getUserId($aPage['page_id']);
            if ($this->get('custom_pages_post_as_page')) {
                $pageUser = Phpfox::getService('user')->getUser($pageUserId, 'full_name');
                if ($pageUser) {
                    $postedUserFullName = $pageUser['full_name'];
                }
            }

            // get all admins (include owner), send email and notification
            $aAdmins = Phpfox::getService('pages')->getPageAdmins($aVals['callback_item_id']);
            foreach ($aAdmins as $aAdmin) {
                if ($aLink['user_id'] == $aAdmin['user_id']) { // is owner of link
                    continue;
                }

                if ($aPage['user_id'] == $aAdmin['user_id']) { // is owner of page
                    if ($aLink['user_id'] == $pageUserId) { // post as page
                        continue;
                    }
                    $varPhraseTitle = 'full_name_posted_a_link_on_your_page_title';
                    $varPhraseLink = 'full_name_posted_a_link_on_your_page_link';
                } else {
                    $varPhraseTitle = 'full_name_posted_a_link_on_page_title';
                    $varPhraseLink = 'full_name_posted_a_link_on_page_link';
                }

                $aSubjectPhrase = [$varPhraseTitle, [
                    'full_name' => $postedUserFullName,
                    'title' => $aPage['title']
                ]];
                $aMessagePhrase = [$varPhraseLink, [
                    'full_name' => $postedUserFullName,
                    'title' => $aPage['title'],
                    'link' => $sLinkUrl
                ]];

                Phpfox::getLib('mail')->to($aAdmin['user_id'])
                    ->subject($aSubjectPhrase)
                    ->message($aMessagePhrase)
                    ->notification('pages.email_notification')
                    ->send();

                if (Phpfox::isModule('notification')) {
                    Phpfox::getService('notification.process')->add('pages_comment_link', $iLinkId, $aAdmin['user_id']);
                }
            }
        }
    }

    // return content
    echo $content;
}

