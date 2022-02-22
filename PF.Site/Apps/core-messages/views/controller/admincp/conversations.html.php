{literal}
<style rel="stylesheet">
    .core-messages-admincp-conversations .last-message
    {
        color: #0f6755;
    }
    .core-messages-admincp-conversations .js_pager_buttons
    {
        text-align: center;
    }
    .core-messages-admincp-conversations .form-group
    {
        padding-left: 0;
        padding-right: 0;
    }
</style>
{/literal}
<div class="core-messages-admincp-conversations">
    <div class="search">
        <form id="js_form_search_conversation" method="get" action="{url link='admincp.mail.conversations'}">
            <div class="form-group col-md-6">
                <label for="keyword">{_p var='Keyword'}</label>
                <input type="text" class="form-control" name="search[keyword]" value="{value type='input' id='keyword'}">
            </div>
            <div class="form-group col-md-12">
                <button class="btn btn-primary">{_p var='Search'}</button>
            </div>
        </form>
    </div>
    {if is_array($aConversations) && count($aConversations)}
        <div class="panel panel-default table-responsive">
            <table class="table table-admin">
                <thead>
                <tr>
                    <th class="t_center w80">{_p var='ID'}</th>
                    <th>{_p var='Conversation Title'}</th>
                    <th class="t_center w20">{_p var='Settings'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$aConversations key=iKey item=aConversation}
                    <tr>
                        <td class="w80">#{$aConversation.thread_id}</td>
                        <td><a href="{url link='admincp.mail.messages' id=$aConversation.thread_id search[keyword]=$aForms.keyword}">{$aConversation.thread_name}</a></td>
                        <td class="t_center w20">
                            <a role="button" class="js_drop_down_link" title="{_p var='manage'}"></a>
                            <div class="link_menu">
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="{url link='admincp.mail.messages'  id=$aConversation.thread_id search[keyword]=$aForms.keyword}" target="_blank">{_p var='View Detail'}</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
        {pager}
    {else}
        <div class="alert alert-empty col-md-12">
            {_p var='Conversations not found'}
        </div>
    {/if}
</div>
