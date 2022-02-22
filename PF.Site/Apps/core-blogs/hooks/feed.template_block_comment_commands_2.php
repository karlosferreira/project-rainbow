<?php
defined('PHPFOX') or exit('NO DICE!');

if ((Phpfox_Module::instance()->getFullControllerName() == 'blog.view')) {
    $aItem = $this->getVar('aItem');
    if (isset($aItem['post_status']) && $aItem['post_status'] == BLOG_STATUS_DRAFT) {
        $aFeed = $this->getVar('aFeed');
        $aFeed['no_share'] = 1;
        $this->assign('aFeed', $aFeed);
    }
}
