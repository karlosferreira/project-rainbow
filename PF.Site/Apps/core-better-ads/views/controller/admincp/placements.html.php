<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
{if count($aPlacements)}
<div class="table-responsive">
<table class="table table-admin">
	<thead>
        <tr>
            <th class="w20"></th>
            <th>{_p var='better_ads_title'}</th>
            <th class="w200">{_p var='position'}</th>
            <th class="w200">{_p var='type'}</th>
            <th class="t_center w200">{_p var='ads'}</th>
            <th class="t_center w200">{_p var='better_ads_active'}</th>
        </tr>
    </thead>
	<tbody>
    {foreach from=$aPlacements key=iKey item=aPlacement}
    <tr class="{if is_int($iKey/2)} tr{else}{/if}">
        <td class="t_center">
            <a class="js_drop_down_link" title="{_p var='better_ads_manage'}"></a>
            <div class="link_menu">
                <ul class="dropdown">
                    <li><a href="{url link='admincp.ad.addplacement' ads_id=$aPlacement.plan_id}">{_p var='edit'}</a></li>
                    <li><a role="button" onclick="tb_show('', $.ajaxBox('ad.deleteCategory', 'height=400&width=600&placement_id={$aPlacement.plan_id}')); return false;">{_p var='delete'}</a></li>
                </ul>
            </div>
        </td>
        <td>{$aPlacement.title|clean}</td>
        <td>{_p var='block' x=$aPlacement.block_id}</td>
        <td>{$aPlacement.type}</td>
        <td class="t_center">{if $aPlacement.total_campaigns > 0}<a href="{url link='admincp.ad' location=$aPlacement.plan_id}">{/if}{$aPlacement.total_campaigns}{if $aPlacement.total_campaigns > 0}</a>{/if}</td>
        <td class="t_center">
            <div class="js_item_is_active"{if !$aPlacement.is_active} style="display:none;"{/if}>
                <a href="#?call=ad.updateAdPlacementActivity&amp;id={$aPlacement.plan_id}&amp;active=0" class="js_item_active_link" title="{_p var='better_ads_deactivate'}"></a>
            </div>
            <div class="js_item_is_not_active"{if $aPlacement.is_active} style="display:none;"{/if}>
                <a href="#?call=ad.updateAdPlacementActivity&amp;id={$aPlacement.plan_id}&amp;active=1" class="js_item_active_link" title="{_p var='better_ads_activate'}"></a>
            </div>
        </td>
    </tr>
{/foreach}
    </tbody>
</table>
</div>
{else}
<div class="alert alert-info">
	{_p var='better_ads_no_placements_found'}.
</div>
{/if}