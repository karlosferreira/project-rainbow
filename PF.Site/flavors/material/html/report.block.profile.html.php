<?php

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="profile-block-action">
    <div class="item-inner">
        <div class="item-info-main">
            <div class="item-image">
                {img user=$aUser suffix='_120_square'}
            </div>
            <div class="item-info">
                <div class="item-title"><a href="{if !empty($aUser.link)}{$aUser.link}{else}#{/if}">{$aUser.full_name|clean}</a></div>
            </div>
            {if $bIsFriend}
                {if Phpfox::getUserParam('friend.link_to_remove_friend_on_profile')}
                    <div class="dropdown item-btn-user">
                        <button class="btn btn-primary btn-round dropdown-toggle btn-icon" type="button" data-toggle="dropdown"><span class="ico ico-check"></span> {_p var='friend'} <span class="ico ico-caret-down"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                <a role="button" onclick="$Core.jsConfirm({l}{r}, function(){l} $.ajaxCall('friend.delete', 'friend_user_id={$aUser.user_id}&reload=1');{r}, function(){l}{r}); return false;">
                                    <span class="mr-1 ico ico-user2-del-o"></span>
                                    {_p var='remove_friend'}
                                </a>
                            </li>
                        </ul>
                    </div>
                {/if}
            {elseif $bIsRequest}
                <a href="javascript:void(0)" onclick="$.ajaxCall('friend.removePendingRequest', 'id={$aUser.is_friend_request_id}','GET');" class="btn btn-sm btn-default btn-round">
                    <span class="ico ico-ban mr-1"></span>
                    {_p var='cancel_request'}
                </a>
            {elseif empty($bIsIgnoreRequest) && Phpfox::getUserParam('friend.can_add_friends') && Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'friend.send_request')}
                <a href="#" onclick="return $Core.addAsFriend({$aUser.user_id});" title="{_p var='add_as_friend'}" class="btn btn-sm btn-primary btn-round add_as_friend_button">
                    <span class="mr-1 ico ico-user1-plus-o"></span> {_p var='add_as_friend'}
                </a>
            {/if}
        </div> 
    </div>
    <ul class="item-action">
        {if Phpfox::getUserParam('user.can_block_other_members') && isset($aUser.user_group_id) && Phpfox::getUserGroupParam('' . $aUser.user_group_id . '', 'user.can_be_blocked_by_others')}
            <li>
                <a href="#?call=user.block&amp;height=120&amp;width=400&amp;user_id={$aUser.user_id}" class="inlinePopup js_block_this_user" title="{if $bIsBlocked}{_p var='unblock_this_user'}{else}{_p var='block_this_user'}{/if}">
                    <span class="ico ico-ban"></span>
                    {if $bIsBlocked}
                        {_p var='unblock_this_user'}
                    {else}
                        {_p var='block_this_user'}
                    {/if}
                </a>
            </li>
        {/if}
        <li>
            <a href="#?call=report.add&amp;height=220&amp;width=400&amp;type=user&amp;id={$aUser.user_id}" class="inlinePopup" title="{_p var='report_this_user'}"><span class="ico ico-warning-o"></span>{_p var='report_this_user'}</a>
        </li>
        {if isset($bShowRssFeedForUser)}
            <li>
                <a href="{url link=''$aUser.user_name'.rss'}" class="no_ajax_link">
                    <span class="ico ico-rss-o"></span>
                    {_p var='subscribe_via_rss'}
                </a>
            </li>
        {/if}
    </ul>
</div>