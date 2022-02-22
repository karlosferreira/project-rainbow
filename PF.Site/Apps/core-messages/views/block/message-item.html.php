<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<li class="core-messages__list-item d-flex align-items-center  mail_holder{if !$bIsSentbox && !$bIsTrash && $aMail.viewer_is_new} mail_is_new{/if} {if $aMail.is_select}is_selected_thread{/if}"  id="js_message_{$aMail.thread_id}">
    <div class="core-messages__checkbox">
        <label class="px-1 mb-0">
            <input type="checkbox" name="conversation_action[]" value="{$aMail.thread_id}" class="js_conversation_item_check">
            <i class="ico ico-square-o" aria-hidden="true"></i>
        </label>
    </div>
    <div class="core-messages__list-body d-inline-flex align-items-center js_item_click" data-url="{url link='mail.thread' id=$aMail.thread_id}{if $bIsSentbox}view_sent/{/if}" data-id="{$aMail.thread_id}" data-view="{$sView}">
        <div class="core-messages__list-photo">
            {if $aMail.is_group}
                {if $aMail.total_avatar > 1}
                    {foreach from=$aMail.avatar_for_group item=aUserAvatar}
                        {img user=$aUserAvatar suffix='_120_square' max_width=50 max_height=50}
                    {/foreach}
                {else}
                    {img user=$aMail.avatar_for_group suffix='_120_square' max_width=50 max_height=50}
                {/if}
            {else}
                {if $aMail.user_id == Phpfox::getUserId()}
                    {img user=$aMail suffix='_120_square' max_width=50 max_height=50}
                {else}
                    {if (isset($aMail.user_id) && !empty($aMail.user_id))}
                        {img user=$aMail suffix='_120_square' max_width=50 max_height=50}
                    {/if}
                {/if}
            {/if}
        </div>
        <div class="core-messages__list-inner">
            <div class="core-messages__list-owner d-flex justify-content-between">
                <a href="javascript:void(0)" class="mail_link mr-2 {if $bIsSentbox}view_sent/{/if}" id="js_mail_title_{$aMail.thread_id}">{$aMail.thread_name}</a>
                <div class="core-messages__list-time wp-nor">
                    <span class="fz-12 text-gray">{$aMail.time_stamp|convert_time}</span>
                    {if !$bIsSentbox && !$bIsTrash}
                        <span class="core-messages__list-mark-read  unread js_mail_mark_unread {if $aMail.viewer_is_new}hidden{/if}"><a href="#" class="mail_read js_hover_title js_mail_mark_unread_action"><span class="js_hover_info">{_p var='mark_as_unread'}</span></a></span>
                        <span class="core-messages__list-mark-read   js_mail_mark_read {if !$aMail.viewer_is_new}hidden{/if}"><a href="#" class="mail_read js_hover_title js_mail_mark_read_action"><span class="js_hover_info">{_p var='mark_as_read'}</span></a></span>
                    {/if}
                </div>
            </div>
            <div class="core-messages__list-content d-flex align-items-baseline justify-content-between mt-h1">
                {if Phpfox::getParam('mail.show_preview_message')}
                <p class="mb-0 fz-12 text-gray-dark mail_text">
                        {if $aMail.show_text_html}{$aMail.preview|stripbb}{else}{$aMail.preview|cleanbb|clean}{/if}
                </p>
                {/if}

                {if $sView != "trash" && $sView != "spam"}
                <a href="javascript:void()" class="js_hover_title noToggle" onclick="$.ajaxCall('mail.delete', 'id={$aMail.thread_id}', 'GET'); $(this).parents('li:first').slideUp(); return false;">
                    <span class="ico ico-inbox"></span>
                    <span class="js_hover_info">
                            {_p var='archive'}
                    </span>
                </a>
                {/if}
            </div>
        </div>
    </div>
</li>
