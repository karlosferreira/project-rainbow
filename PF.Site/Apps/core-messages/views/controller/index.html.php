<?php
    defined('PHPFOX') or exit('NO DICE!');
?>

<div class="core-messages-index-js">
    <div class="core-messages {if $bIsComposeForCustomlist}active-messages{/if}" id="js_core_messages_content">
        <input type="hidden" value="{if $bCanLoadMore}2{else}1{/if}" id="js_load_more_page">
        <input type="hidden" value="{if $bCanLoadMore}1{else}0{/if}" id="js_check_load_more">
        <input type="hidden" value="{$bIsComposeForCustomlist}" id="js_compose_for_customlist">

        <div class="core-messages__left-side">
            <div class="core-messages__search">
                <form id="js_message_filter" class="w-full" method="GET" action="{url link='mail'}">
                    <input type="hidden" id="js_search_view" name="search[view]" value="{value type='input' id='view'}">
                    <input type="hidden" id="js_search_custom" name="search[custom]" value="{value type='input' id='custom'}">
                    <input class="form-control input-sm core-messages__search-input {if !empty($aForms.title)}search-page{/if}" type="text" id="js_search_title" name="search[title]" placeholder="{_p var='mail_search_conversation'}" value="{value type='input' id='title'}">
                </form>
                <span class="check-out-search {if empty($aForms.title)}hidden{/if}" onclick="coreMessagesHelper.redirect('{url link='mail'}');"><i class="ico ico-close" aria-hidden="true"></i></span>
                <span class="core-messages__search-icon"><i class="ico ico-search-o" aria-hidden="true"></i></span>
                {if $bCanComposeMessage}
                    <span role="button" class="core-messages__search-new" title="{_p var='New Message'}" onclick="$.ajaxCall('mail.loadComposeController', ''); coreMessageScreen.checkScreenForMobile();">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                    </span>
                {/if}
            </div>
            <div class="core-messages__filter d-flex justify-content-between">
                <div class="dropdown">
                    <div class="d-inline-flex align-items-center cursor-point" data-toggle="dropdown">
                        <p class="core-messages__filter-text core-messages__filter-text-js fw-bold mb-0">{$sDefaultFolderTitle}</p>
                        <i class="ico ico-caret-down ml-h1"></i>
                    </div>
                    <ul class="dropdown-menu">
                        {foreach from=$aDefaultFolders item=aDefaultFolder}
                            <li class="js_filter_default_folder {if ($sView == $aDefaultFolder.view) && empty($sCustomList)}is_selected_folder{/if}" data-view="{$aDefaultFolder.view}" data-title="{$aDefaultFolder.title}"><a href="javascript:void(0)">{$aDefaultFolder.title}</a></li>
                        {/foreach}
                        {if count($aFolders)}
                            <li role="separator" class="divider"></li>
                            {foreach from=$aFolders item=aFolder}
                                <li data-id="{$aFolder.folder_id}" class="js_filter_custom_list {if $sCustomList == $aFolder.folder_id}is_selected_folder{/if}"  data-title="{$aFolder.name}"><a href="javascript:void()">{$aFolder.name}</a></li>
                            {/foreach}
                        {/if}
                    </ul>
                </div>
                <div class="d-inline-flex align-items-center">
                    <a href="javascript:void()" class="mark-all-read fz-12" onclick="$.ajaxCall('mail.markallread', 'reload=1')">{_p var='mark_all_read'}</a>
                    <span class="mx-h1 text-gray">&bull;</span>
                    <a class="custom-list fz-12" href="{url link='mail.customlist'}">{_p var='mail_lists'}</a>
                </div>
            </div>
            {if $iTotalMessages}
                <div class="core-messages__mass-actions">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="core-messages__select-all">
                            <label class="mb-0 fz-12 fw-normal text-gray">
                                <input type="checkbox" id="js_select_all_conversation">
                                <i class="ico ico-square-o mr-h1" aria-hidden="true"></i>
                                {_p var='select_all'}
                            </label>
                        </div>
                        <div class="core-messages__select-action hidden" id="js_core_messages_conversation_select_action">
                            <div class="dropdown">
                                <div class="d-inline-flex align-items-center cursor-point" data-toggle="dropdown">
                                    <p class="core-messages__filter-text core-messages__filter-text-js fw-bold mb-0">{_p var='Actions'}</p>
                                    <i class="ico ico-caret-down ml-h1"></i>
                                </div>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    {foreach from=$aMassActions key=actionkey item=action}
                                        <li class="{if $actionkey=='delete'}item_delete{/if}">
                                           <a href="javascript:void(0)" data-action="{$actionkey}" class="js_conversation_mass_actions">{$action}</a>
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <form method="post" action="{url link='mail'}" id="js_core_messages_form_mass_actions">
                        <div id="js_mass_action_ids"></div>
                    </form>
                </div>
            {/if}
            <div class="message-container core-messages__list" id="js_core_messages_conversation_list">
                    <ul class="core-messages__list-wapper dont-unbind-children" id="js_conversation_load_more">
                        {if count($aMails)}
                            {foreach from=$aMails item=aMail name=mail key=mailkey}
                                {template file='mail.block.message-item'}
                            {/foreach}
                        {elseif !PHPFOX_IS_AJAX && empty($aMails)}
                            <div class="extra_info js_conversations_not_found px-1 py-1 text-center core-messages__no-message">
                                <i class="ico ico-inbox" aria-hidden="true"></i>
                                <div>
                                    {_p var='no_messages_found_here'}
                                </div>
                            </div>
                        {/if}
                    </ul>
            </div>
        </div>

        <div class="core-messages__right-side">
            <div class="core-messages__title d-flex justify-content-between align-items-center pl-2 pt-h1 pb-h1 pr-h1" id="js_core_messages_content_title">
                {$sTitleContentDefault}
            </div>
            <div class="core-messages__conversation" id="js_core_messages_action_content" >
                {$sChatContentDefault}
            </div>
            {if $bCanComposeMessage}
                <div class="core-messages__footer" id="js_core_messages_content_footer">
                    {template file='mail.block.message-footer'}
                </div>
            {/if}
        </div>
    </div>
</div>
