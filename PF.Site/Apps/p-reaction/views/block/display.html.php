<?php
    defined('PHPFOX') or exit('NO DICE!');
?>
{if isset($ajaxLoadLike) && $ajaxLoadLike}
<div id="js_like_body_{$aFeed.feed_id}" class="p-reaction-like-body">
{/if}
    {if !empty($aFeed.feed_like_phrase)}
        <div class="activity_like_holder p-reaction-activity-like" id="activity_like_holder_{$aFeed.feed_id}">
            <div class="p-reaction-list-mini dont-unbind-children">
                {if !empty($aFeed.most_reactions)}
                    {for $i = 0; $i <= 2; $i++}
                        {if isset($aFeed.most_reactions[$i])}
                            <div class="p-reaction-item js_reaction_item">
                                <a href="javascript:void(0)" class="item-outer"
                                   data-toggle="p_reaction_toggle_user_reacted_cmd"
                                   data-action="p_reaction_show_list_user_react_cmd"
                                   data-type_id="{$aFeed.like_type_id}"
                                   data-item_id="{if isset($aFeed.like_item_id)}{$aFeed.like_item_id}{else}{$aFeed.item_id}{/if}"
                                   data-feed_id="{if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}0{/if}"
                                   data-total_reacted="{$aFeed.most_reactions[$i].total_reacted}"
                                   data-react_id="{$aFeed.most_reactions[$i].id}"
                                   data-table_prefix="{if isset($aFeed.feed_table_prefix)}{$aFeed.feed_table_prefix}{elseif defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE')}pages_{/if}"
                                >
                                    <img src="{$aFeed.most_reactions[$i].full_path}" alt="">
                                </a>
                                <div class="p-reaction-tooltip-user js_p_reaction_tooltip">
                                    <div class="item-title">{_p var=$aFeed.most_reactions[$i].title|clean} ({$aFeed.most_reactions[$i].total_reacted|short_number})</div>
                                    <div class="item-tooltip-content js_p_reaction_preview_reacted">
                                        <div class="item-user">{_p var='loading_three_dot'}</div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/for}
                {/if}
            </div>
            {$aFeed.feed_like_phrase}
            {if isset($aFeed.feed_total_like) && $aFeed.feed_total_like}
                <a href="javascript:void(0)" class="p-reaction-total" style="display: none;"
                   data-action="p_reaction_show_list_user_react_cmd"
                   data-type_id="{$aFeed.like_type_id}"
                   data-item_id="{if isset($aFeed.like_item_id)}{$aFeed.like_item_id}{else}{$aFeed.item_id}{/if}"
                   data-feed_id="{if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}0{/if}"
                   data-react_id="0"
                   data-table_prefix="{if isset($aFeed.feed_table_prefix)}{$aFeed.feed_table_prefix}{elseif defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE')}pages_{/if}">
                    {$aFeed.feed_total_like|short_number}
                </a>
            {/if}
        </div>
    {else}
        <div class="activity_like_holder activity_not_like">
            {_p var='when_not_like'}
        </div>
    {/if}
{if isset($ajaxLoadLike) && $ajaxLoadLike}
</div>
{/if}
