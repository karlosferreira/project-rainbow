<?php

namespace Apps\Core_Blogs\Installation\Version;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v468
 * @package Apps\Core_Blogs\Installation\Version
 */
class v468
{
    public function process()
    {
        $this->importToRssFeed();
    }

    public function importToRssFeed()
    {
        if (db()->tableExists(Phpfox::getT('rss')) && db()->tableExists(Phpfox::getT('rss_group'))) {
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