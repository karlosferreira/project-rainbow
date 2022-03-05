<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if Phpfox::isUser() && Phpfox::isModule('friend') && empty($is_friend)}
    {if !$is_friend && isset($aUser.is_friend_request) && $aUser.is_friend_request == 3}
        <a href="javascript:void(0);" onclick="return $Core.processFriendRequest.addAsFriend('{$user_id}');" title="{_p var='confirm_friend_request'}" class="btn btn-md btn-default btn-round js_confirm_request">
            <span class="mr-1 ico ico-user2-check-o"></span>
            {_p var='confirm'}
        </a>
    {elseif empty($is_ignore_request) && Phpfox::getUserParam('friend.can_add_friends') && Phpfox::getService('user.privacy')->hasAccess('' . $user_id . '', 'friend.send_request')}
        <a href="javascript:void(0);" onclick="return $Core.processFriendRequest.addAsFriend('{$user_id}');" title="{_p var='add_as_friend'}" class="btn btn-md btn-default btn-round js_add_friend">
            <span class="mr-1 ico ico-user1-plus-o"></span>
            {_p var='add_as_friend'}
        </a>
    {/if}
{/if}

{if Phpfox::isModule('friend') && !empty($is_friend)}
    <a href="javascript:void(0);" data-toggle="dropdown" class="btn btn-md btn-default btn-round has-caret js_friend_status" title="{_p var='friend_request_sent'}">
        {if $is_friend === true}
            <span class="mr-1 ico ico-check"></span>
            {_p var='friend'} <span class="ml-1 ico ico-caret-down"></span>
        {else}
            <span class="mr-1 ico ico-clock-o mr-1 friend-request-sent"></span>
            {_p var='request_sent'} <span class="ml-1 ico ico-caret-down"></span>
        {/if}
    </a>
{/if}

<ul class="dropdown-menu dropdown-center">
    {if Phpfox::getService('user')->canSendMessage('' . $aUser.user_id . '', $is_friend)}
        <li>
            <a href="javascript:void(0);" onclick="$Core.composeMessage({left_curly}user_id: {$aUser.user_id}{right_curly}); return false;">
                <span class="mr-1 ico ico-pencilline-o"></span>
                {_p var='message'}
            </a>
        </li>
    {/if}
    <li>
        <a href="#?call=report.add&amp;height=220&amp;width=400&amp;type=user&amp;id={$aUser.user_id}" class="inlinePopup" title="{_p var='report_this_user'}">
            <span class="ico ico-warning-o mr-1"></span>{_p var='report_this_user'}
        </a>
    </li>
    {if Phpfox::isModule('friend') && isset($is_friend) && $is_friend === true}
        <li class="item-delete js_remove_friend">
            <a href="javascript:void(0);" onclick="$Core.processFriendRequest.callUnfriend({$aUser.user_id}); return false;">
                <span class="mr-1 ico ico-user2-del-o"></span>{_p var='remove_friend'}
            </a>
        </li>
    {elseif Phpfox::isModule('friend') && !empty($is_friend) && !empty($request_id)}
        <li class="item-delete js_cancel_request">
            <a href="javascript:void(0);" onclick="$Core.processFriendRequest.callRequestCancel({$request_id}); return false;">
                <span class="mr-1 ico ico-user2-del-o"></span>{_p var='cancel_request'}
            </a>
        </li>
    {/if}
</ul>
