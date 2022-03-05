<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{literal}
<style type="text/css">
    .table-responsive .content-photo {
        padding-top: 5px;
    }
    .table-responsive .content-text {
        word-break: break-word;
        word-wrap: break-word;
        white-space: pre-line;
    }
    .table-responsive .content-sticker {
        padding-top: 5px;
    }
    .table-responsive .content-photo .item-photo img {
        max-height: 150px;
    }
    .table-responsive .content-sticker {
        line-height: 0;
    }
    .table-responsive .content-sticker .item-sticker img {
        max-height: 80px;
    }
    .table-responsive .content-text .item-tag-emoji img {
        max-width: 16px;
    }
</style>
{/literal}
<form method="post" id="manage_pending_comments" action="{url link='admincp.comment.pending-comments'}">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {_p var='pending_comments'}
            </div>
        </div>
        <div class="panel-body">
            {if count($aComments)}
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w40 js_checkbox">
                                <input type="checkbox" name="val[ids]" value="" id="js_check_box_all" class="main_checkbox" /></th>
                            </th>
                            <th class="t_center w40"></th>
                            <th class="w180">{_p var='date'}</th>
                            <th class="w180">{_p var='user'}</th>
                            <th class="w180">{_p var='item'}</th>
                            <th class="">{_p var='content'}</th>
                        </tr>
                        {foreach from=$aComments item=aComment}
                            {template file='comment.block.admin.entry'}
                        {/foreach}
                    </table>
                </div>
                <div class="panel-footer">
                    <input type="submit" name="val[approve_selected]" id="approve_selected" disabled value="{_p('approve_selected')}" class="sJsConfirm sJsCheckBoxButton btn btn-success disabled" data-message="{_p var='are_you_sure_you_want_to_approve_selected_comments'}"/>
                    <input type="submit" name="val[deny_selected]" id="deny_selected" disabled value="{_p('deny_selected')}" class="sJsConfirm sJsCheckBoxButton btn btn-danger disabled" data-message="{_p var='are_you_sure_you_want_to_deny_selected_comments'}"/>
                </div>
            {else}
                <div class="alert alert-info">
                    {_p var='no_comments'}
                </div>
            {/if}
        </div>
    </div>
    {if count($aComments)}
        {pager}
    {/if}
</form>