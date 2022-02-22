<?php
if (!Phpfox::getParam('photo.photo_allow_create_feed_when_add_new_item') && isset($aAcceptedTypes)) {
    foreach ($aAcceptedTypes as $key => $acceptedType) {
        if ($acceptedType == 'photo') {
            unset($aAcceptedTypes[$key]);
            break;
        }
    }
}