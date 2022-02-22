<div class="js_core_messages_add_customlist">
    <div class="error-list">
        {if !empty($error)}
            <div class="error_message">
                {$error}
            </div>
        {/if}
    </div>
    {if empty($error)}
        <form method="post" action="{url link='mail.customlist.add'}">
            <input type="hidden" id="js_setting_custom_list_maximum" value="{$iCustomListMaximum}">
            <input type="hidden" id="js_setting_custom_list_member_maximum" value="{$iCustomListMemberMaximum}">
            <input type="hidden" id="js_setting_current_customlist_user" value="{$iCurrentCustomListOfUser}">
            {if $bIsEdit}
            <input type="hidden" name="id" value="{$iId}">
            <input type="hidden" name=val[submit] value="submit">
            {/if}
            {if !$bIsEdit}
                <div class="form-group">
                    <label for="name">
                        {_p var='mail_custom_list_name'}
                    </label>
                    <input class="form-control" name="val[name]" value="{value type='input' id='name'}" placeholder="{_p var='mail_enter_custom_list_name'}" maxlength="50">
                </div>
            {/if}
            <div class="form-group">
                <div id="js_selected_friends" class="hide_it"></div>
                {module name='friend.search' input='invite' hide=true in_form=true friend_module_id='mail'}
            </div>
            <div class="form-group">
                <button type="submit"  class="btn btn-primary" id="js_btn_submit">
                    {if $bIsEdit}
                        {_p var='Update'}
                    {else}
                        {_p var='Create'}
                    {/if}
                </button>
                <button type="submit" class="btn btn-default" onclick="$Core.reloadPage(); return false;">{_p var='Cancel'}</button>
            </div>
        </form>
    {/if}
</div>