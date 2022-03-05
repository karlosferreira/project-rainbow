<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="hidden" id="current_user_avatar">
    {img user=$aUser suffix='_120_square' class="img-responsive" title=$aUser.full_name}
</div>
<div class="panel-body msg_container_base shoutbox-container " id="msg_container_base" data-error-quote-message="{_p var='shoutbox_you_only_quote_once'}">
    {foreach from=$aShoutboxes key=sKey value=aShoutbox}
        <div class="row msg_container {if $aShoutbox.type=='s'} base_sent {else} base_receive {/if}" id="shoutbox_message_{$aShoutbox.shoutbox_id}" data-value="{$aShoutbox.shoutbox_id}">
            <div class="msg_container_row shoutbox-item {if $aShoutbox.type=='s'} item-sent {else} item-receive {/if}">
                <div class="shoutbox_action">
                    {if $aShoutbox.canEdit || ($aShoutbox.canDeleteOwn || $aShoutbox.canDeleteAll) || Phpfox::isUser()}
                        {if Phpfox::isUser()}
                            <div class="shoutbox-like">
                                <a class="btn-shoutbox-like js_shoutbox_like {if $aShoutbox.is_liked}liked{else}unlike{/if}" title="{if $aShoutbox.is_liked}{_p var='unlike'}{else}{_p var='like'}{/if}" data-type="{if $aShoutbox.is_liked}unlike{else}like{/if}" data-id="{$aShoutbox.shoutbox_id}" onclick="appShoutbox.processLike(this);"></a>
                            </div>
                        {/if}
                        {if $bCanShare || $aShoutbox.canEdit || $aShoutbox.canDeleteOwn || $aShoutbox.canDeleteAll}
                            <div class="dropdown item-action-more js-shoutbox-action-more dont-unbind">
                                <a role="button" data-toggle="dropdown" href="#" class="" aria-expanded="true">
                                    <span class="ico ico-dottedmore"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right dont-unbind">
                                    {if $bCanShare}
                                        <li>
                                            <a href="javascript:void();" onclick="appShoutbox.quote(this);" data-value="{$aShoutbox.shoutbox_id}" title="{_p var='quote'}"><i class="ico ico-quote-circle-alt-left-o" aria-hidden="true"></i> {_p var='quote'}</a>
                                        </li>
                                    {/if}
                                    {if $aShoutbox.canEdit}
                                        <li>
                                            <a href="javascript:void();" onclick="appShoutbox.openEditPopup(this);" data-phrase="{_p var='shoutbox_edit_message'}" data-value="{$aShoutbox.shoutbox_id}" title="{_p var='edit'}"><i class="ico ico-pencil" aria-hidden="true"></i> {_p var='edit'}</a>
                                        </li>
                                    {/if}
                                    {if $aShoutbox.canDeleteOwn || $aShoutbox.canDeleteAll}
                                        <li>
                                            <a href="javascript:void();"  onclick="appShoutbox.dismiss(this);" data-value="{$aShoutbox.shoutbox_id}" title="{_p var='delete'}"><i class="ico ico-trash-o" aria-hidden="true"></i> {_p var='delete'}</a>
                                        </li>
                                    {/if}
                                </ul>
                            </div>
                        {/if}
                    {/if}
                </div>
                <div class="item-outer {if $aIsAdmin || $iUserId == $aShoutbox.user_id}can-delete{/if}">
                    <div class="item-media-source">
                        {img user=$aShoutbox suffix='_120_square' width=32 height=32 class="img-responsive" title=$aShoutbox.full_name}
                    </div>
                    <div class="item-inner">
                        <div class="title_avatar item-shoutbox-body {if $aShoutbox.type=='r'} msg_body_receive {elseif $aShoutbox.type=='s'} msg_body_sent {/if} " title="{$aShoutbox.full_name|clean}">
                            <div class=" item-title">
                                <a href="{url link=$aShoutbox.user_name}" title="{$aShoutbox.full_name|clean}">
                                    {$aShoutbox.full_name|clean}
                                </a>
                            </div>
                            <div class="messages_body item-message">
                                <div class="item-message-info item_view_content">
                                    {if isset($aShoutbox.quoted_text)}
                                        <div class="item-quote-content">
                                            <div class="quote-user">{$aShoutbox.quoted_full_name|clean}</div>
                                            <div class="quote-message">{$aShoutbox.quoted_text|parse}</div>
                                        </div>
                                    {/if}
                                    {$aShoutbox.text|parse}
                                </div>
                            </div>
                            
                        </div>
                        <span class="js_shoutbox_text_total_like item-count-like">{if (int)$aShoutbox.total_like > 0}<a href="javascript:void(0);" onclick="appShoutbox.showLikedMembers({$aShoutbox.shoutbox_id});">{$aShoutbox.total_like} {if (int)$aShoutbox.total_like > 1}{_p var='likes'}{else}{_p var='like'}{/if}{/if}</a></span>
                         <div class="item-time">
                            <span class="message_convert_time" data-id="{$aShoutbox.timestamp}">{$aShoutbox.timestamp|convert_time}</span><span class="item-edit-info js_edited_text {if !$aShoutbox.is_edited}hide{/if}">{if $aShoutbox.is_edited}{_p var='shoutbox_edited'}{/if}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
</div>
{if $bCanShare}
    <div class="panel-footer">
        <form onsubmit="return false;">
            <div class="input-group">
                <textarea rows='1' data-toggle="shoutbox" data-name="text" maxlength="255" id="shoutbox_text_message_field" type="text" class="form-control chat_input" placeholder="{_p var='write_message'}"/></textarea>
            </div>
            <div class="item-footer-sent">
                <div class="item-count"><span id="pf_shoutbox_text_counter">0</span>/255</div>
                <span class="item-btn-sent">
                <ul class="global_attachment_list" data-id="shoutbox_text_message_field"></ul>
                <button data-name="shoutbox-submit" class="btn btn-primary btn-xs" id="btn-chat"><i class="ico ico-paperplane" aria-hidden="true"></i></button>
            </span>
            </div>
        </form>
    </div>
{/if}
<input type="hidden" value="{$sModuleId}" data-toggle="shoutbox" data-name="parent_module_id">
<input type="hidden" value="{$iItemId}" data-toggle="shoutbox" data-name="parent_item_id">
<div id="shoutbox_error_notice" data-title="{_p var='notice'}" data-message="{_p var='type_something_to_chat'}"></div>
