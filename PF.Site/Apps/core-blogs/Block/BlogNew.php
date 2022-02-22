<?php

namespace Apps\Core_Blogs\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class BlogNew
 * @package Apps\Core_Blogs\Block
 */
class BlogNew extends Phpfox_Component
{
    const IMG_SUFFIX = '_240';
    /**
     * Controller
     */
    public function process()
    {
        $iLimit = $this->getParam('limit', 3);
        if (!$iLimit) {
            return false;
        }
        $iCacheTime = $this->getParam('cache_time', 5);
        $aBlogs = Phpfox::getService('blog')->getNew($iLimit, $iCacheTime);

        // Get image for the blog
        foreach ($aBlogs as &$aRow) {
            if (!empty($aRow['image_path'])) {
                $aRow['image'] = Phpfox::getService('blog')->getImageUrl($aRow['image_path'], $aRow['server_id'],
                    self::IMG_SUFFIX);
            } else {
                list($sDescription, $aImages) = Phpfox::getLib('parse.bbcode')->getAllBBcodeContent($aRow['text'],
                    'img');
                $aRow['text'] = $sDescription;
                $aRow['image'] = empty($aImages) ? '' : str_replace('_view', '', $aImages[0]);
            }
        }

        $this->template()->assign(array(
                'aBlogs' => $aBlogs,
                'sHeader' => _p('recent_blogs'),
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
                'info' => _p('Recent Blogs Limit'),
                'description' => _p('Define the limit of how many recent blogs can be displayed when viewing the blog section. Set 0 will hide this block.'),
                'value' => 3,
                'type' => 'integer',
                'var_name' => 'limit',
            ],
            [
                'info' => _p('Recent Blogs Cache Time'),
                'description' => _p('Define how long we should keep the cache for the <b>Recent Blogs</b> by minutes. 0 means we do not cache data for this block.'),
                'value' => Phpfox::getParam('core.cache_time_default'),
                'options' => Phpfox::getParam('core.cache_time'),
                'type' => 'select',
                'var_name' => 'cache_time',
            ]
        ];
    }
    public function getValidation()
    {
        return [
            'limit' => [
                'def' => 'int',
                'min' => 0,
                'title' => '"Recent Blogs Limit" must be greater than or equal to 0'
            ]
        ];
    }
    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        // Lets clear it from memory
        $this->template()->clean(array(
                'aBlogs',
                'sHeader',
                'sBlockJsId',
                'limit',
                'cache_time'
            )
        );

        (($sPlugin = Phpfox_Plugin::get('blog.component_block_new_clean')) ? eval($sPlugin) : false);
    }
}
