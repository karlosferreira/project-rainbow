<?php
namespace Apps\Core_Marketplace\Installation\Version;

use Core\Lib;
use Phpfox;

/**
 * Class v462
 * @package Apps\Core_Marketplace\Installation\Version
 */
class v462
{
    public function process()
    {
        if (db()->tableExists(Phpfox::getT('rss')) && db()->tableExists(Phpfox::getT('rss_group'))) {
            $this->importToRssFeed();
        }
    }
    public function importToRssFeed()
    {
        $aRssGroup = [
            'module_id' => 'marketplace',
            'product_id' => 'phpfox',
            'name_var' => 'marketplace',
            'is_active' => 1,
            'ordering' => 0,
        ];

        $aRssData = [
            'module_id' => 'marketplace',
            'product_id' => 'phpfox',
            'feed_link' => 'marketplace',
            'php_view_code' => '$aRows = Phpfox::getService(\'marketplace\')->getForRssFeed();',
            'is_site_wide' => 1,
            'is_active' => 1,
            'title' => 'Latest Listings',
            'description' => 'List of the latest listings.'
        ];

        $iCnt = db()->select('COUNT(*)')
            ->from(':rss_group')
            ->where('module_id = \'marketplace\' AND product_id = \'phpfox\'')
            ->execute('getSlaveField');

        if(!$iCnt) {
            $iGroupId = db()->insert(':rss_group', $aRssGroup);
            Lib::phrase()->addPhrase('rss_group_name_' . $iGroupId, 'Marketplace');
            Lib::phrase()->addPhrase('marketplace_rss_title_' . $iGroupId, $aRssData['title']);
            Lib::phrase()->addPhrase('marketplace_rss_description_' . $iGroupId, $aRssData['description']);
            db()->update(':rss_group', ['name_var' => 'marketplace.rss_group_name_' . $iGroupId],
                'group_id =' . $iGroupId);
            $aRssData['title_var'] = 'marketplace_rss_title_' . $iGroupId;
            $aRssData['description_var'] = 'marketplace_rss_description_' . $iGroupId;
            $aRssData['group_id'] = $iGroupId;
            unset($aRssData['title']);
            unset($aRssData['description']);
            db()->insert(':rss', $aRssData);
        } else {
            // update phrase
            $iGroupId = db()->select('group_id')->from(':rss_group')
                ->where('module_id = \'marketplace\' AND product_id = \'phpfox\'')
                ->execute('getSlaveField');
            Lib::phrase()->addPhrase('rss_group_name_' . $iGroupId, 'Marketplace');
            $aRss = db()->select('feed_id, title_var, feed_link, php_view_code')->from(':rss')
                ->where(['module_id' => 'marketplace'])->executeRows();
            foreach ($aRss as $rss) {
                if ($aRssData['php_view_code'] != $rss['php_view_code']) {
                    continue;
                }
                $rssTitle = 'marketplace_rss_title_' . $iGroupId;
                $rssDescription = 'marketplace_rss_description_' . $iGroupId;
                Lib::phrase()->addPhrase($rssTitle, $aRssData['title']);
                Lib::phrase()->addPhrase($rssDescription, $aRssData['description']);
                db()->update(':rss', ['title_var' => $rssTitle, 'description_var' => $rssDescription],
                    ['feed_id' => $rss['feed_id']]);
                break;
            }
        }
    }
}