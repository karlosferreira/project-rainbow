<?php

namespace Apps\Core_MobileApi\Version1_7\Api\Security\Poll;

use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;


class PollAccessControl extends \Apps\Core_MobileApi\Api\Security\Poll\PollAccessControl
{
    /**
     * @inheritdoc
     *
     * @param $resource PollResource
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        $granted = true;
        // Check Pages/Group permission
        if ($this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('poll.view_browse_polls');
                    break;
                case self::ADD:
                    $granted = ($this->appContext->hasPermission('poll.share_polls')
                        && $this->appContext->hasPermission('poll.view_browse_polls'));
                    break;
            }
        }

        return $granted;
    }

}