<?php
	defined('PHPFOX') or exit('NO DICE!');
?>
{$sCreateJs}

<script type="text/javascript">
    oTranslations['mail_number_of_members_over_limitation'] = "{$numberOfMembersOverLimitation}";
</script>

<div id="js_ajax_compose_error_message"></div>
<div id="js_core_messages_compose_message" class="core-messages__addmember">
    <span id="back-to-list-js" class="back-to-list hidden"><i class="ico ico-arrow-left-circle-o" aria-hidden="true"></i></span>
    <input type="hidden" id="js_check_numbers_member_for_group" value="{$iGroupMemberMaximum}">
    <input type="hidden" id="js_compose_message_friend_title" value="{$sFriendPhrase}">
    <input type="hidden" id="js_compose_message_custom_list_title" value="{$sCustomlistPhrase}">
	<form class="form js_ajax_compose_message" method="post" action="{url link='mail.compose'}" id="js_form_mail" data-type="{if !empty($iPageId)}claim-page{/if}">
        <input type="hidden" value="{value type='input' id='message'}" id="message" name="val[message]">
        {if isset($iPageId)}
            <div><input type="hidden" name="val[page_id]" value="{$iPageId}"></div>
            <div><input type="hidden" name="val[sending_message]" value="{$iPageId}"></div>
        {/if}
        {token}
        <div><input type="hidden" name="val[attachment]" class="js_attachment" value="{value type='input' id='attachment'}" /></div>
        {if PHPFOX_IS_AJAX && isset($aUser.user_id)}
        <div><input type="hidden" name="id" value="{$aUser.user_id}" /></div>
        <div><input type="hidden" name="val[to][]" value="{$aUser.user_id}" /></div>
        <div class="form-group mb-0">
            {template file='mail.block.message-footer'}
        </div>

        {else}
            <div class="core-messages-advanced-compose">
                <div class="search-friend-component mb-1">
                    <p class="mb-0 pb-0 text-gray-dark mr-1">{_p var='To'}:</p>
                    <span id="js_core_messages_custom_search_friend_placement">
                        {if !empty($sSelectedCustomlist)}
                            {$sSelectedCustomlist}
                        {/if}
                    </span>
                    {if empty($aCustomlist)}
                    <div id="js_core_messages_custom_search_friend">
                        <input type="text" id="js_core_messages_search" class="search_friend_input" placeholder="{_p var='mail_search_friends_custom_list_by_name'}" autocomplete="off" onfocus="coreMessages.search.buildList();" onkeyup="coreMessages.search.getSearch(this);" style="width:100%;" class="form-control" >
                        <div class="js_core_messages_search_list search-friend-list" style="display: none;"></div>
                    </div>
                    {/if}
                </div>
            </div>
            {if !empty($bIsAjaxPopup)}
            <div class="form-group">
                {template file='mail.block.message-footer'}
            </div>
            {/if}
        {/if}
        {if Phpfox::isModule('captcha') && Phpfox::getUserParam('mail.enable_captcha_on_mail')}
            {module name='captcha.form' sType='mail'}
        {/if}
	</form>
</div>

{if isset($sMessageClaim)}
<?php $this->_aVars['sMessageClaim'] = html_entity_decode($this->_aVars['sMessageClaim'], ENT_QUOTES); ?>
	<script type="text/javascript">
		$('#js_compose_new_message #js_compose_message_textarea').val('{$sMessageClaim}');
	</script>
{/if}

{if PHPFOX_IS_AJAX}
{literal}
<script>

</script>
{/literal}
{/if}

{literal}
<script type="text/javascript">
    $Behavior.core_messages_compose_message = function () {
        if ($Core.hasPushState()) {
            window.addEventListener("popstate", function (e) {
                if($('#js_core_messages_compose_message').closest('.js_box_content').length)
                {
                    var oDomObj = $('#js_core_messages_compose_message').get(0);
                    js_box_remove(oDomObj);
                }
            });
        }
    }
</script>
{/literal}