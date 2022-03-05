<?php
if (isset($this->_aCallback['module']) && $this->_aCallback['module'] == 'groups' && Phpfox::getUserId() != $this->_aCallback['email_user_id']) {
    $sLink = $this->_aCallback['link'] . 'comment-id_' . $iStatusId . '/';

    // get and send email/notification to all admins of group
    $aGroup = \Phpfox::getService('groups')->getPage($this->_aCallback['item_id']);
    $aAdmins = Phpfox::getService('groups')->getPageAdmins($this->_aCallback['item_id']);
    foreach ($aAdmins as $aAdmin) {
        if (Phpfox::getUserId() == $aAdmin['user_id']) {
            continue;
        }
        Phpfox::getLib('mail')->to($aAdmin['user_id'])
            ->subject(['email_full_name_wrote_a_comment_on_group_subject', [
                'full_name' => \Phpfox::getUserBy('full_name'),
                'title' => $aGroup['title']
            ]])
            ->message(['full_name_wrote_a_comment_on_group_link', [
                'full_name' => \Phpfox::getUserBy('full_name'),
                'title' => $aGroup['title'],
                'link' => $sLink
            ]])
            ->notification('groups.email_notification')
            ->send();
        if (Phpfox::isModule('notification')) {
            Phpfox::getService('notification.process')->add('groups_comment', $iStatusId, $aAdmin['user_id']);
        }
    }
}
