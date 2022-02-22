<?php
if (Phpfox::isAppActive('P_Reaction')) {
    if ($this->getResourceName() == 'feed' || $this->getResourceName() == 'comment') {
        $itemId = $this->getItemId();
        if($this->getResourceName() == 'comment') {
            $itemId = $this->id;
        }
        if (!empty($this->getIsLiked())) {
            $result['user_reacted'] = (new Apps\P_Reaction\Service\Api\PReactionApi())->getUserReacted($itemId, $this->like_type_id);
        }
        if ((isset($this->rawData['feed_total_like']) && (int)$this->rawData['feed_total_like'] > 0) ||
            (isset($this->rawData['total_like']) && (int)$this->rawData['total_like'] > 0)
        ) {
            list(, $mostReactions) = Phpfox::getService('preaction')->getMostReaction($this->like_type_id, $itemId, (isset($this->rawData['feed_table_prefix']) ? $this->rawData['feed_table_prefix'] : ''));
            if (count($mostReactions)) {
                foreach ($mostReactions as $most_reaction) {
                    $result['most_reactions'][] = Apps\P_Reaction\Api\Resource\PReactionResource::populate($most_reaction)->displayShortFields()->toArray();
                }
            }
        }
    }

    if ($this->load_feed_param && !empty($result['feed_param']) && is_array($result['feed_param'])) {
        $result['feed_param']['user_reacted'] = (new Apps\P_Reaction\Service\Api\PReactionApi())->getUserReacted($result['feed_param']['item_id'], $result['feed_param']['like_type_id']);
        $likes = Phpfox::getService('like')->getAll($result['feed_param']['like_type_id'], $result['feed_param']['item_id'], (isset($this->rawData['feed_table_prefix']) ? $this->rawData['feed_table_prefix'] : ''));
        if (isset($likes['likes'])) {
            if (!empty($likes['likes']['most_reactions'])) {
                foreach ($likes['likes']['most_reactions'] as $most_reaction) {
                    $result['feed_param']['most_reactions'][] = Apps\P_Reaction\Api\Resource\PReactionResource::populate($most_reaction)->displayShortFields()->toArray();
                }
            }
            if (isset($likes['likes']['phrase'])) {
                $result['feed_param']['like_phrase'] = html_entity_decode($likes['likes']['phrase'], ENT_QUOTES);
            }
        }
    }
}
