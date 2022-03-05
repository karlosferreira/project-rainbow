<?php

namespace Apps\Core_Pages\Block;

use Phpfox;
use Phpfox_Plugin;
use Phpfox_Component;

class Featured extends Phpfox_Component
{
    public function process()
    {

        $iLimit = $this->getParam('limit', 4);
        if(!(int)$iLimit)
        {
            return false;
        }
        $iCacheTime = $this->getParam('cache_time', 5);
        // Get the featured random page
        $aFeaturedPages = Phpfox::getService('pages')->getFeatured($iLimit, $iCacheTime);

        // If not images were featured lets get out of here
        if (!count($aFeaturedPages)) {
            return false;
        }
        // If this is not AJAX lets display the block header, footer etc...
        if (!PHPFOX_IS_AJAX) {
            $this->template()->assign(array(
                    'sHeader' => _p('featured_pages'),
                    'sBlockJsId' => 'featured_page',
                )
            );
        }
        // Assign template vars
        $this->template()->assign(array(
                'aFeaturedPages' => $aFeaturedPages,
                'iRefreshTime' => '',
                'sDefaultCoverPath' => Phpfox::getParam('pages.default_cover_photo')
            )
        );
        return 'block';
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            [
                'info' => _p('Featured Pages Limit'),
                'description' => _p('Define the limit of how many featured pages can be displayed when viewing the pages section. Set 0 will hide this block'),
                'value' => 4,
                'type' => 'integer',
                'var_name' => 'limit',
            ],
            [
                'info' => _p('Featured Pages Cache Time'),
                'description' => _p('Define how long we should keep the cache for the <b>Featured Pages</b> by minutes. 0 means we do not cache data for this block.'),
                'value' => Phpfox::getParam('core.cache_time_default'),
                'options' => Phpfox::getParam('core.cache_time'),
                'type' => 'select',
                'var_name' => 'cache_time',
            ]
        ];
    }
    /**
     * @return array
     */
    public function getValidation()
    {
        return [
            'limit' => [
                'def' => 'int',
                'min' => 0,
                'title' => _p('"Featured Pages Limit" must be greater than or equal to 0')
            ],
        ];
    }
    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('pages.component_block_featured_clean')) ? eval($sPlugin) : false);
    }
}