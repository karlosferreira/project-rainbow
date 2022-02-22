<?php
if (Phpfox::getUserParam('saveditems.can_save_item') && Phpfox::getUserBy('profile_page_id') == 0) {
    $exceptionTypes = Phpfox::getService('saveditems')->getExceptionalTypes();
    $typeId = $this->_aVars['aFeed']['type_id'];
    if (!in_array($typeId, $exceptionTypes)) {
        $typeCount = explode('_', $typeId);
        $moduleId = !empty($typeCount) ? $typeCount[0] : '';
        if (Phpfox::isModule($moduleId) && (in_array($typeId, ['user_status', 'link']) || Phpfox::hasCallback($moduleId,
                    'globalUnionSearch'))) {
            $isSaved = Phpfox::getService('saveditems')->isSaved($typeId, $this->_aVars['aFeed']['item_id']);
            echo '<li class="js_saved_item_' . $this->_aVars['aFeed']['feed_id'] . '">';
            Phpfox_Template::instance()->assign([
                'saveItemParams' => [
                    'id' => $this->_aVars['aFeed']['feed_id'],
                    'type_id' => !empty($typeId) ? $typeId : $this->_aVars['aFeed']['like_type_id'],
                    'item_id' => $this->_aVars['aFeed']['item_id'],
                    'link' => urlencode($this->_aVars['aFeed']['feed_link']),
                    'is_saved' => $isSaved,
                ]
            ])->getTemplate('saveditems.block.save-action');
            echo "</li>";
        }
    }
}