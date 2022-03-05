<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: invitations.html.php 3215 2011-10-05 14:40:56Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if count($aInvites)}
    {if !PHPFOX_IS_AJAX}
    <form class="form" method="post" action="{url link='current'}" id="js_form">
        <div class="invitation-container">
    {/if}
        {foreach from=$aInvites name=invite item=aInvite}
        <div id="js_invite_{$aInvite.invite_id}" class="invitation-item js_selector_class_{$aInvite.invite_id}">
            {item name="Invitation"}
            <div class="moderation_row">
                <label class="item-checkbox">
                    <input type="checkbox" class="js_global_item_moderate" name="item_moderate[]" value="{$aInvite.invite_id}" id="check{$aInvite.invite_id}" />
                    <i class="ico ico-square-o"></i>
                </label>
            </div>
            <div class="item-title">
                {$aInvite.count}. {$aInvite.email}
            </div>
            <div class="item-delete">
                <a href="{url link='current' del=$aInvite.invite_id}"><span class="ico ico-trash-o"></span></a>
            </div>
            {/item}
        </div>
        {/foreach}
    {if Phpfox::getParam('invite.pendings_to_show_per_page') > 0}
        {pager}
    {/if}
    {if !PHPFOX_IS_AJAX}
        </div>
    </form>
    {/if}
    {moderation}
{else}
    {if !PHPFOX_IS_AJAX}
        <div class="extra_info">
            {_p var='there_are_no_pending_invitations'}
            <ul class="action">
                <li><a href="{url link='invite'}">{_p var='invite_your_friends'}</a></li>
            </ul>
        </div>
    {/if}
{/if}