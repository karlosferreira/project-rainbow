<?php

namespace Apps\P_SavedItems\Api\Security;

use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Phpfox;

class SavedItemsCollectionAccessControl extends AccessControl
{
    const CREATE = "create";
    const UPDATE = "update";

    protected $supports;

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([self::CREATE, self::UPDATE]);
    }

    /**
     * @inheritdoc
     * @param $resource BlogResource
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }

        $granted = false;

        $isOwner = false;
        // Item Owner always able to do any permission
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }

        if (Phpfox::getUserBy('profile_page_id') == 0) {
            switch ($permission) {
                case self::CREATE:
                    $granted = Phpfox::getUserParam('saveditems.can_create_collection');
                    break;
                case self::UPDATE:
                    $granted = Phpfox::getUserParam('saveditems.can_edit_collection') && $isOwner;
                    break;
                case self::DELETE:
                    $granted = Phpfox::getUserParam('saveditems.can_delete_collection') && $isOwner;
                    break;
            }
        }

        return $granted;
    }
}