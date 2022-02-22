<?php

if (Phpfox::isAppActive('P_StatusBg') && ($this->getResourceName() == 'feed')) {
    if (isset($this->rawData['status_background'])) {
        $result['status_background'] = $this->rawData['status_background'];
    } elseif (isset($this->rawData['item_id'], $this->rawData['type_id'], $this->rawData['user_id'])) {
        $result['status_background'] = Phpfox::getService('pstatusbg')->getFeedStatusBackground($this->rawData['item_id'], $this->rawData['type_id'], $this->rawData['user_id']);
    }
}
