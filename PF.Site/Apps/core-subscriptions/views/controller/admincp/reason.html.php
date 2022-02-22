<div class="panel panel-default core-subscriptions-admincp-reason-index">
    <div class="panel-heading col-md-12">
        <div class="panel-title col-md-6 main-title">{_p var='subscribe_menu_cancel_reason_title'}</div>
        <div class="panel-title col-md-6 add-button-title">
            <a class="btn btn-success popup" rel="hide_box_title" role="link" href="{url link='admincp.subscribe.add-reason'}">{_p var='subscribe_add_new_reason'}</a>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" action="{url link='admincp.subscribe.reason'}">
            <div class="form-group col-md-6">
                <label for="statistic">{_p var='subscribe_statistics_by_cancel_date'}</label>
                {filter key='period'}
            </div>
            <div class="dont-unbind-children form-group col-md-6 input-filter date-filter {if !empty($aSearch.period) && $aSearch.period == 'custom'}show{else}hidden{/if}">
                <div class="select-date">
                    <div class="date-from">
                        <label>{_p var='from'}</label>
                        <input name="search[from]" value="{$aSearch.from}" class="form-control" id="date-from">
                    </div>

                    <div class="seperate"> - </div>

                    <div class="date-to">
                        <label>{_p var='subscribe_to'}</label>
                        <input name="search[to]" value="{$aSearch.to}" class="form-control"  id="date-to">
                    </div>

                </div>
            </div>
            <div class="form-group col-md-12">
                <button type="submit" class="btn btn-danger">{_p var='search'}</button>
            </div>
        </form>
    </div>

</div>
{if count($aReasons)}
<div class="panel panel-default table-responsive">
    <table class="table table-admin" id="_sort" data-sort-url="{url link='subscribe.admincp.order' table='subscribe_reason' field='reason_id'}">
        <thead>
        <tr>
            <th class="t_center"></th>
            <th class="t_center">{_p var='reason'}</th>
            <th class="t_center">{_p var='subscribe_number_of_subscriptions'}</th>
            <th class="t_center">{_p var='action'}</th>
            <th class="t_center">{_p var='sub_active'}</th>
        </tr>
        </thead>
        <tbody>
            {foreach from=$aReasons item=aReason}
            <tr data-sort-id="{$aReason.reason_id}">
                <td class="sortable" >
                    <i class="fa fa-sort pr-1"></i>
                </td>
                <td class="t_center">
                    {if !empty($aReason.is_default)}{_p var='subscribe_default'}{/if} {$aReason.title_parsed|clean|shorten:80:'...'}
                </td>
                <td class="t_center">{if !empty($aReason.total)}<a href="{url link='admincp.subscribe.list' search[status]='cancel' search[reason]=$aReason.reason_id}">{$aReason.total}</a>{else}0{/if}</td>
                <td class="t_center">
                    <a href="{url link='admincp.subscribe.add-reason' id=$aReason.reason_id}" class="popup" rel="hide_box_title" role="link">{_p var='edit'}</a>
                    {if empty($aReason.is_default)}
                    <span>/</span>
                    <a href="{url link='admincp.subscribe.delete-reason' id=$aReason.reason_id}" class="popup" rel="hide_box_title" role="link">{_p var='delete'}</a>
                    {/if}
                </td>

                <td class="on_off">
                    <div class="js_item_is_active"{if !$aReason.is_active} style="display:none;"{/if}>
                        <a href="#?call=subscribe.updateActivityCancelReason&amp;reason_id={$aReason.reason_id}&amp;active=0" class="js_item_active_link" title="{_p var='deactivate'}"></a>
                    </div>
                    <div class="js_item_is_not_active"{if $aReason.is_active} style="display:none;"{/if}>
                        <a href="#?call=subscribe.updateActivityCancelReason&amp;reason_id={$aReason.reason_id}&amp;active=1" class="js_item_active_link" title="{_p var='activate'}"></a>
                    </div>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{pager}
{else}
<p class="alert alert-empty">
    {_p var='subscribe_no_cancel_reason_found'}
</p>
{/if}
<script>
    var calendar_image = "<?php echo Phpfox::getParam('subscribe.app_url').'assets/images/calendar.gif';?>";
</script>