<?php
    defined('PHPFOX') or exit('NO DICE!');
?>
<div class="p-reaction-container p-reaction-container-js">
    <a role="button"
       data-toggle="p_reaction_toggle_cmd"
       data-label1="{_p var='like'}"
       data-label2="{_p var='unlike'}"
       data-liked="{if $aLike.like_is_liked}1{else}0{/if}"
       data-type_id="{$aLike.like_type_id}"
       data-item_id="{$aLike.like_item_id}"
       data-reaction_color="{$aDefaultLike.color}"
       data-reaction_id="{$aDefaultLike.id}"
       data-reaction_title="{_p var=$aDefaultLike.title|clean}"
       data-full_path="{$aDefaultLike.full_path}"
       data-feed_id="{if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}0{/if}"
       data-is_custom="{if $aLike.like_is_custom}1{else}0{/if}"
       data-table_prefix="{if isset($aFeed.feed_table_prefix)}{$aFeed.feed_table_prefix}{elseif defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE')}pages_{/if}"
       class="js_like_link_toggle {if $aLike.like_is_liked}liked{else}unlike{/if} p-reaction-link" style="-webkit-user-select: none; -webkit-touch-callout: none;">
        {if $aLike.like_is_liked && !empty($aUserReacted)}
            <div class="p-reaction-icon-outer"><img src="{$aUserReacted.full_path}" alt="" class="p-reaction-icon" oncontextmenu="return false;"> </div>{$aUserReacted|preaction_color_title}
        {else}
            <div class="p-reaction-icon-outer"></div>
            <strong class="p-reaction-title"></strong>
        {/if}
    </a>
    {if !empty($aReactions) && count($aReactions) > 1}
        <div class="p-reaction-list">
            {foreach from=$aReactions item=aReaction}
            <div class="p-reaction-item dont-unbind" data-toggle="tooltip" data-placement="top" data-original-title="{_p var=$aReaction.title|clean}">
                <a class="item-outer"
                   data-toggle="p_reaction_toggle_cmd"
                   data-label1="{_p var='like'}"
                   data-label2="{_p var='unlike'}"
                   data-liked="{if $aLike.like_is_liked}1{else}0{/if}"
                   data-type_id="{$aLike.like_type_id}"
                   data-reaction_color="{$aReaction.color}"
                   data-reaction_id="{$aReaction.id}"
                   data-reaction_title="{_p var=$aReaction.title|clean}"
                   data-full_path="{$aReaction.full_path}"
                   data-item_id="{$aLike.like_item_id}"
                   data-feed_id="{if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}0{/if}"
                   data-is_custom="{if $aLike.like_is_custom}1{else}0{/if}"
                   data-table_prefix="{if isset($aFeed.feed_table_prefix)}{$aFeed.feed_table_prefix}{elseif defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE')}pages_{/if}"
                   style="-webkit-user-select: none;">
                    <img src="{$aReaction.full_path}" alt="">
                </a>
            </div>
            {/foreach}
        </div>
    {/if}
</div>
{if $aLike.like_type_id == 'feed_mini' && !empty($aLike.like_is_custom)}
    {if isset($aFeed.feed_table_prefix)}
        {assign var='sPrefixTable' value=$aFeed.feed_table_prefix}
    {elseif defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE')}
        {assign var='sPrefixTable' value='pages_' }
    {/if}
    {module name='preaction.reaction-list-mini' type_id='feed_mini' item_id=$aLike.like_item_id table_prefix=$sPrefixTable}
{/if}