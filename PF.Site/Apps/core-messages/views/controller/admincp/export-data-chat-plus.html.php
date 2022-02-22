<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title">{_p var='export_data_to_chat_plus'}</div>
    </div>
    <div class="panel-body">
        {if isset($bNoChatPlus) && $bNoChatPlus}
            <div class="alert alert-danger">
                {_p var='chat_plus_is_not_enabled'}
            </div>
        {elseif isset($bImported) && $bImported}
            <div class="alert alert-success">
                {_p var='exported_data_to_chat_plus'}
            </div>
        {else}
            <form method="post" action="{url link='admincp.mail.export-data-chat-plus'}">
                <p class="help-block">{_p var='export_data_from_messages_to_chat_plus_instruction'}</p>
                <button name="export" value="1" class="btn btn-primary">{_p var='submit'}</button>
            </form>
        {/if}
    </div>
</div>