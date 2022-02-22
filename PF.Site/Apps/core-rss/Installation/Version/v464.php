<?php

namespace Apps\Core_RSS\Installation\Version;

class v464
{
    public function __construct()
    {

    }

    public function process()
    {
        if (method_exists('\Apps\Core_Blogs\Service\Blog', 'getForRssFeed')
            && method_exists('\Apps\Core_Blogs\Service\Category\Category', 'getForRssFeedGroup')) {
            //Update RSS
            $aBlogRss = db()->select('*')->from(':rss')->where('module_id = \'blog\'')->executeRows();
            if (count($aBlogRss)) {
                $aUpdates = [];
                foreach ($aBlogRss as $aRss) {
                    if (!$aRss['php_group_code']) {
                        $aUpdates[$aRss['feed_id']] = [
                            'php_view_code' => '$aRows = Phpfox::getService(\'blog\')->getForRssFeed();'
                        ];
                    } elseif (strpos($aRss['php_group_code'], '$aCategories =') !== false) {
                        $aUpdates[$aRss['feed_id']] = [
                            'php_group_code' => 'Phpfox::getService(\'blog.category\')->getForRssFeedGroup($aRow);',
                            'php_view_code' => '$aRows = Phpfox::getService(\'blog.category\')->getBlogByCategoryForRssFeed($aFeed,$sDescription);',
                        ];
                    }
                }
                foreach ($aUpdates as $iFeedId => $aUpdate) {
                    db()->update(':rss', $aUpdate, 'feed_id =' . $iFeedId);
                }
            }
        }
    }
}