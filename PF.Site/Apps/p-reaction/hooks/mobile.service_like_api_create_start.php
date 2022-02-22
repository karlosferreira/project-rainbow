<?php

if(Phpfox::isAppActive('P_Reaction')) {
    $defaultLike = Phpfox::getService('preaction')->getDefaultLike();
    $reactionId = $this->request()->get('reaction_id', $defaultLike['id']);
    $result = Phpfox::getService('like.process')->add($params['item_type'], $params['item_id'], null, null, [], '', $reactionId, true);
}
