<?php

namespace Apps\P_SavedItems\Api\Security;

use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\P_SavedItems\Api\Resource\SavedItemsResource;
use Phpfox;

class SavedItemsAccessControll extends AccessControl
{
    const SAVE = "save";
    const UNSAVE = "unsave";
    const OPEN = "open";
    const UNOPEN = "unopen";
    const ADD_COLLECTION = "add_collection";

    protected $supports;

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([self::SAVE, self::OPEN]);
    }

    /**
     * @inheritdoc
     * @param $resource SavedItemsResource
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }

        $granted = false;
        $params = $this->getParameters();

        if (Phpfox::isUser() && Phpfox::getUserBy('profile_page_id') == 0) {
            if (self::ADD_COLLECTION == $permission) {
                $granted = true;
            } elseif (in_array($permission,
                    [self::SAVE, self::UNSAVE]) && $this->isGrantedSetting('saveditems.can_save_item')) {
                if (!empty($params['item_id']) && !empty($params['item_type'])) {
                    $isSaved = Phpfox::getService('saveditems')->isSaved($params['item_type'], $params['item_id']);
                    switch ($permission) {
                        case self::SAVE:
                            $granted = !$isSaved;
                            break;
                        case self::UNSAVE:
                            $granted = $isSaved;
                            break;
                    }
                }
            } elseif (in_array($permission, [self::OPEN, self::UNOPEN]) && !empty($params['saved_id'])) {
                $isUnopened = Phpfox::getService('saveditems')->isUnopened($params['saved_id']);
                switch ($permission) {
                    case self::OPEN:
                        $granted = $isUnopened;
                        break;
                    case self::UNOPEN:
                        $granted = !$isUnopened;
                        break;
                }
            }
        }

        return $granted;
    }
}