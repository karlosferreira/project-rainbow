<?php

namespace Apps\PHPfox_Groups\Block;

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
        $aFeaturedGroups = Phpfox::getService('groups')->getFeatured($iLimit, $iCacheTime);

        // If not images were featured lets get out of here
        if (!count($aFeaturedGroups)) {
            return false;
        }
        // If this is not AJAX lets display the block header, footer etc...
        if (!PHPFOX_IS_AJAX) {
            $this->template()->assign(array(
                    'sHeader' => _p('featured_groups'),
                    'sBlockJsId' => 'featured_group',
                )
            );
        }
        // Assign template vars
        $this->template()->assign(array(
                'aFeaturedGroups' => $aFeaturedGroups,
                'sDefaultCoverPath' => Phpfox::getParam('groups.default_cover_photo')
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
                'info' => _p('Featured Groups Limit'),
                'description' => _p('Define the limit of how many featured groups can be displayed when viewing the group section. Set 0 will hide this block'),
                'value' => 4,
                'type' => 'integer',
                'var_name' => 'limit',
            ],
            [
                'info' => _p('Featured Groups Cache Time'),
                'description' => _p('Define how long we should keep the cache for the <b>Featured Groups</b> by minutes. 0 means we do not cache data for this block.'),
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
                'title' => _p('"Featured Groups Limit" must be greater than or equal to 0')
            ],
        ];
    }
    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('groups.component_block_featured_clean')) ? eval($sPlugin) : false);
    }
}