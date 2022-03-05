<?php

namespace Apps\Core_Comments\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class RssController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (!Phpfox::isAppActive('Core_RSS')) {
            $this->url()->send('');
        }
        $sType = $this->request()->get('type');
        $iItemId = $this->request()->getInt('item');
        $aRss = Phpfox::getService('comment')->getForRss($sType, $iItemId);
        Phpfox::getService('rss')->output($aRss);

        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_controller_rss_clean')) ? eval($sPlugin) : false);
    }
}
