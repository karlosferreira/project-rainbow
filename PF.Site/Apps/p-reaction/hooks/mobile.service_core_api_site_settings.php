<?php

if (Phpfox::isAppActive('P_Reaction')) {
    $reactions = (new Apps\P_Reaction\Service\Api\PReactionApi())->findAll(['is_get_array' => true]);
    $data['general']['preaction'] = $reactions;
}
