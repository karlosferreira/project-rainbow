<?php
namespace Apps\PHPfox_Groups\Block;

use Phpfox;
use Phpfox_Component;

/**
 * Class FeedGroupBlock
 * @package Apps\PHPfox_Groups\Block
 */
class FeedGroupBlock extends Phpfox_Component
{
    public function process()
    {
        if ($iFeedId = $this->getParam('this_feed_id')) {
            $aGroup = $this->getParam('custom_param_feed_group_' . $iFeedId);
            if(empty($aGroup)) {
                return false;
            }

            $aGroup['text_parsed'] = strip_tags($aGroup['text_parsed']);
            $this->template()->assign([
                'aGroup' => array_merge($aGroup, [
                    'is_requested' => Phpfox::getService('groups')->joinGroupRequested($aGroup['page_id'], Phpfox::getUserId())
                ]),
            ]);
        }
    }
}
