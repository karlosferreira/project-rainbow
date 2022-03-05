<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: controller.html.php 64 2009-01-19 15:05:54Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<script>
    if (window.location.href.indexOf('admincp/app/?id=Core_Subscriptions') !== -1) {l}
    window.location.href = '{url link='admincp.subscribe'}';
    {r}
</script>
<div class="manage-plans core-subscriptions-manage-packages">
    <form id="admin-manage-plans-form" method="get" action="{url link='admincp.subscribe.index'}">
        <div class="col-md-6 input-filter">
            <label>{_p var='subscribe_package_type'}</label>
            <select name="val[type]" class="form-control">
                <option value="">{_p var='any'}</option>
                {foreach from=$aTypes key=typekey item=typevalue}
                    <option value="{$typekey}" {if !empty($aSearch.type)}{if $typekey == $aSearch.type}selected="true"{/if}{/if}>{$typevalue}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-md-6 input-filter">
            <label>{_p var='subscribe_package_status'}</label>
            <select name="val[status]" class="form-control">
                <option value="">{_p var='any'}</option>
                {foreach from=$aPackageStatus key=statuskey item=statusvalue}
                <option value="{$statuskey}" {if !empty($aSearch.status)}{if $statuskey == $aSearch.status}selected="true"{/if}{/if}>{$statusvalue}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-md-6 input-filter">
            <label>{_p var='subscribe_statistics_by_time_period'}</label>
            <select name="val[period]" class="form-control" id="period">
               {foreach from=$aPeriod key=periodkey item=periodvalue}
                <option value="{$periodkey}" {if !empty($aSearch.period)}{if $periodkey== $aSearch.period}selected="true"{/if}{/if}>{$periodvalue}</option>
               {/foreach}
            </select>
        </div>
        <div class="dont-unbind-children col-md-6 input-filter date-filter {if !empty($aSearch.period) && $aSearch.period == 'custom'}show{else}hidden{/if}">
            <div class="select-date">
                <div>
                    <label>{_p var='from'}</label>
                    <input name="val[from]" value="{value type='input' id='from'}" class="form-control" id="date-from">
                </div>
                <span>
                    <label></label>
                    <div>-</div>
                </span>
                <div>
                    <label>{_p var='subscribe_to'}</label>
                    <input name="val[to]" value="{value type='input' id='to'}" class="form-control"  id="date-to">
                </div>
            </div>
        </div>
        <div class="col-md-12 action-button">
            <button type="submit" class="btn btn-danger input-filter">{_p var='search'}</button>
        </div>
    </form>
</div>
{if count($aPackages)}
    <div class="panel panel-default table-responsive">
        <table class="table table-admin" id="_sort" data-sort-url="{url link='subscribe.admincp.order' table='subscribe_package' field='package_id'}">
            <thead>
                <tr>
                    <th></th>
                    <th class="t_center w30">{_p var='id'}</th>
                    <th class="t_center">{_p var='subscribe_package_title'}</th>
                    <th class="t_center">{_p var='subscribe_cost'}</th>
                    <th class="t_center w100">{_p var='type'}</th>
                    <th class="t_center w60">{_p var='status'}</th>
                    <th class="t_center w160">{_p var='subscribe_last_updated'}</th>
                    <th class="t_center w80">{_p var='sub_active'}</th>
                    <th class="t_center w80">{_p var='expired'}</th>
                    <th class="t_center w80">{_p var='canceled'}</th>
                    <th class="t_center w20">{_p var='settings'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$aPackages key=iKey item=aPackage}
                    <tr data-sort-id="{$aPackage.package_id}">
                        <td class="sortable" >
                            <i class="fa fa-sort"></i>
                        </td>
                        <td class="t_center w60">{$aPackage.package_id}</td>
                        <td class="t_center"><a href="{url link='admincp.subscribe.add' id={$aPackage.package_id}">{_p var=$aPackage.title} {if !empty($aPackage.is_popular)}({_p var='most_popular'}){/if}</a></td>
                        <td class="t_center w80">{$aPackage.default_cost|currency:$aPackage.currency_id}</td>
                        <td class="t_center">{$aPackage.type}</td>
                        <td class="on_off">
                            <div class="js_item_is_active" {if !$aPackage.is_active}style="display:none"{/if}>
                            <a href="#?call=subscribe.updateActivity&amp;package_id={$aPackage.package_id}&amp;active=0" class="js_item_active_link" title="{_p var='deactivate'}"></a>
                            </div>
                            <div class="js_item_is_not_active" {if $aPackage.is_active}style="display:none"{/if}>
                            <a href="#?call=subscribe.updateActivity&amp;package_id={$aPackage.package_id}&amp;active=1" class="js_item_active_link" title="{_p var='activate'}"></a>
                            </div>
                        </td>
                        <td class="t_center">{if !empty($aPackage.time_updated)}{$aPackage.time_updated|convert_time}{/if}</td>
                        <td class="t_center">{if !empty($aPackage.statistic_completed)}<a href="{url link='admincp.subscribe.list' search[title]=$aPackage.title search[status]='completed' search[period]=$sDefaultPeriod search[from]=$aSearch.from search[to]= $aSearch.to}">{$aPackage.statistic_completed.total|number_format}</a>{else}<a href="javascript:void(0)">0</a>{/if}</td>
                        <td class="t_center">{if !empty($aPackage.statistic_expire)}<a href="{url link='admincp.subscribe.list' search[title]=$aPackage.title search[status]=$aPackage.statistic_expire.status search[period]=$sDefaultPeriod search[from]=$aSearch.from search[to]= $aSearch.to}">{$aPackage.statistic_expire.total|number_format}</a>{else}<a href="javascript:void(0)">0</a>{/if}</td>
                        <td class="t_center">{if !empty($aPackage.statistic_cancel)}<a href="{url link='admincp.subscribe.list' search[title]=$aPackage.title search[status]=$aPackage.statistic_cancel.status search[period]=$sDefaultPeriod search[from]=$aSearch.from search[to]= $aSearch.to}">{$aPackage.statistic_cancel.total|number_format}</a>{else}<a href="javascript:void(0)">0</a>{/if}</td>
                        <td class="t_center">
                            <a role="button" class="js_drop_down_link" title="{_p var='manage'}"></a>
                            <div class="link_menu">
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="{url link='admincp.subscribe.add' id={$aPackage.package_id}">{_p var='edit_package'}</a></li>
                                    <li><a href="{url link='admincp.subscribe' delete={$aPackage.package_id}" class="sJsConfirm" data-message="{_p var='are_you_sure' phpfox_squote=true}">{_p var='delete_package'}</a></li>
                                    {if !$aPackage.is_popular}
                                        <li><a href="javascript:void(0)" onclick="$.ajaxCall('subscribe.markPackagePopular','id={$aPackage.package_id}')">{_p var='subscribe_mark_as_most_popular'}</a></li>
                                    {/if}
                                    <li><a href="{url link='admincp.subscribe.list' search[title]={$aPackage.title}">{_p var='subscribe_view_subscriptions'}</a></li>
                                </ul>
                            </div>
                        </td>

                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{elseif empty($bError) && !$bHasPackage}
    <div class="alert alert-empty col-md-12">
        <h4>{_p var='no_packages_have_been_added'}</h4><br/>
        <a class="btn btn-info" href="{url link='admincp.subscribe.add'}">{_p var='create_a_new_package'}</a>
    </div>
{else}
    <div class="alert alert-empty col-md-12">
        <h4>{_p var='no_packages_have_been_added'}</h4><br/>
    </div>
{/if}
<script type="text/javascript">
    var calendar_image = "<?php echo Phpfox::getParam('subscribe.app_url').'assets/images/calendar.gif';?>";
    var isBESubscription = true;
</script>