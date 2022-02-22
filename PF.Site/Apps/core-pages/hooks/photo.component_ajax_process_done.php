<?php

if (isset($aCallback['module']) && $aCallback['module'] == 'pages') {
    // temporary save content, because function send of mail clean all => cause issue when use plugin in ajax
    $content = ob_get_contents();
    ob_clean();

    // validate whom to send notification
    $aPage = Phpfox::getService('pages')->getPage($aPhoto['group_id']);
    if ($aPage && (int)$aPhoto['view_id'] == 0) {
        $sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        $postedUserFullName = Phpfox::getUserBy('full_name');

        $pageUserId = Phpfox::getService('pages')->getUserId($aPage['page_id']);
        if ($this->get('custom_pages_post_as_page')) {
            $pageUser = Phpfox::getService('user')->getUser($pageUserId, 'full_name');
            if ($pageUser) {
                $postedUserFullName = $pageUser['full_name'];
            }
        }

        // get all admins (include owner) and send notification
        $aAdmins = Phpfox::getService('pages')->getPageAdmins($aPage['page_id']);
        foreach ($aAdmins as $aAdmin) {
            if (($aPhoto['user_id'] == $aAdmin['user_id'])
                || ($aPage['user_id'] == $aAdmin['user_id'] && $aPhoto['user_id'] == $pageUserId)) { // is owner of photo
                continue;
            }

            $isOwner = $aPage['user_id'] == $aAdmin['user_id'];
            $varPhraseTitle = $isOwner ? 'pages_email_full_name_post_some_images_on_your_page_title' : 'email_full_name_post_some_images_on_page_title';
            $varPhraseLink = $isOwner ? 'full_name_post_some_images_on_your_page_title_link' : 'full_name_post_some_images_on_page_title_link';

            Phpfox::getLib('mail')->to($aAdmin['user_id'])
                ->subject([$varPhraseTitle, [
                    'full_name' => $postedUserFullName,
                    'title' => $aPage['title']
                ]])
                ->message([$varPhraseLink, [
                    'full_name' => $postedUserFullName,
                    'link' => $sLink,
                    'title' => $aPage['title']
                ]])
                ->notification('pages.email_notification')
                ->send();

            if (Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('pages_post_image', $aPhoto['photo_id'], $aAdmin['user_id']);
            }
        }
    }

    // return content
    echo $content;
}
