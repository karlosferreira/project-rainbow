<div class="js_core_messages_custom_list dont-unbind-children">
    <div class="core-messages" id="js_core_messages_content">
        <input type="hidden" value="{if $bCanLoadMore}2{else}1{/if}" id="js_load_more_page">
        <input type="hidden" value="{if $bCanLoadMore}1{else}0{/if}" id="js_check_load_more">
        <div class="core-messages__left-side">
            <div class="core-messages__search">
                <form action="{url link='mail.customlist'}" method="get" id="js_form_custom_list_search" class="w-full">
                    <input type="text" name="search[name]" class="form-control input-sm core-messages__search-input {if !empty($aForms.name)}search-page{/if}" id="js_search_name" placeholder="{_p var='mail_search_list_placeholder'}" value="{value type='input' id='name'}">
                </form>
                <span class="check-out-search {if empty($aForms.name)}hidden{/if}" onclick="coreMessagesHelper.redirect('{url link='mail.customlist'}');"><i class="ico ico-close" aria-hidden="true"></i></span>
                <span class="core-messages__search-icon">
                    <i class="ico ico-search-o"></i>
                </span>
                {if $bCanComposeMessage}
                    <span class="core-messages__search-new"><a href="javascript:void(0)" onclick="$.ajaxCall('mail.loadAddCustomList'); coreMessageScreen.checkScreenForMobile();"><i class="ico ico-plus-circle-o"></i></a></span>
                {/if}
            </div>
            <div class="back-parent d-flex">
                <div class="core-messages__filter-back d-inline-flex align-items-center"><a class="fa fa-angle-left" href="{url link='mail'}" title="{_p var='back'}"></a><span class="fw-bold">{_p var='mail_custom_list_title_upper_first_letter'}</span></div>
            </div>
            {if $iTotal}
                <div class="core-messages__filter d-flex justify-content-between">
                    <div class="core-messages__select-all">
                        <label class="mb-0 fz-12 fw-normal text-gray">
                            <input type="checkbox" id="js_check_all_custom_list">
                            <i class="ico ico-square-o mr-h1" aria-hidden="true"></i>
                            {_p var='select_all'}
                        </label>
                    </div>
                    <div class="dropdown hidden" id="js_core_messages_customlist_actions">
                        <span data-toggle="dropdown" class="cursor-point">
                            {_p var='Actions'}
                            <i class="ico ico-caret-down ml-h1"></i>
                        </span>
                        <ul class="dropdown-menu dropdown-menu-right">
                           <li class="item-delete">
                               <a data-action="delete" class="js_custom_list_mass_action">
                                   <i class="ico ico-trash-o mr-1"></i>
                                   {_p var='Delete'}
                               </a>
                           </li>
                        </ul>
                    </div>
                </div>
            {/if}
            <div class="message-container core-messages__list" id="js_core_messages_custom_list_content">
                <div class="core-messages__list-wapper dont-unbind-children js_core_messages_custom_list_load_more" id="js_core_messages_custom_list_load_more">
                    {if count($aCustomList)}
                        {foreach from=$aCustomList key=key item=aCustom}
                            {template file='mail.block.customlist.custom-item'}
                        {/foreach}
                    {else}
                        <div class="px-1 py-2 text-center mb-0 text-center core-messages__no-message">
                            <i class="ico ico-inbox" aria-hidden="true"></i>
                            <div>
                                {_p var='mail_custom_list_not_found'}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
        <div class="core-messages__right-side">
            <div class="core-messages__title d-flex justify-content-between align-items-center pl-2 pt-h1 pb-h1 pr-h1" id="js_title_content">
                {if !empty($sTitleDefault)}
                    {$sTitleDefault}
                {/if}
            </div>
            <div class="content pr-2 pl-2 mt-2" id="js_content">
                {if !empty($sContentDefault)}
                    {$sContentDefault}
                {/if}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var aCustomlistMembers = {$aCustomListMembers};
</script>
