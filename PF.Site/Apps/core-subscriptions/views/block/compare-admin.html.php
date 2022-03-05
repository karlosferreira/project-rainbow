<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: controller.html.php 64 2009-01-19 15:05:54Z phpFox LLC $

[feature-id][title]
[feature-id][package][package-id] = array
[feature-id][package][package-id][radio] = [0|1|2]
[feature-id][package][package-id][text] = text
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="panel panel-default core-subscription-admincp-compare-block">
    <div class="panel-heading">
        <div class="panel-title">{_p var='admin_menu_comparison'}</div>
    </div>
    <div class="panel-body">
        {if !empty($aPackages.packages)}
            <div class="membership-comparison-container">
                <div class="item-title-block fw-bold mb-1">{_p var='plans_comparision'}</div>
                <div id="subscribe_compare_plan">
                    <div id="div_compare_wrapper" class="table-responsive">
                        <table class="table table-bordered" id="_sort" data-sort-url="{url link='subscribe.admincp.order' table='subscribe_compare' field='compare_id'}">
                            <thead>
                                <tr>
                                    <th class="t_center w40"></th>
                                    <th class="t_center w40"></th>
                                    <th class="t_center w40"></th>
                                    <th class="item-feature"></th>
                                    {foreach from=$aPackages.packages item=aPackage}
                                        <th class="item-compare t_center" {if !empty($aPackage.background_color)}style="color: {$aPackage.background_color};"{/if}>
                                            <div class="w120">{_p var=$aPackage.title}</div>
                                            {if !$aPackage.is_active}
                                                <div>{_p var='subscribe_inactive'}</div>
                                            {/if}
                                        </th>
                                    {/foreach}
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$aPackages.features key=sFeature item=aFeatures}
                                    <tr data-sort-id="{$aFeatures.compare_id}">
                                        <td class="sortable" >
                                            <i class="fa fa-sort" style="padding-right: 10px;"></i>
                                        </td>
                                        <td class="t_center"><a class="popup" href="{url link='admincp.subscribe.add-compare' id=$aFeatures.compare_id}" rel="hide_box_title" role="link"><i class="fa fa-edit" style="color: black;"></i></a></td>
                                        <td class="t_center"><a  href="{url link='admincp.subscribe.compare' delete=$aFeatures.compare_id}" data-message="{_p var='are_you_sure' phpfox_squote=true}" class="sJsConfirm"><i class="fa fa-trash" aria-hidden="true"></i></a></td>
                                        <td class="item-feature t_center" style="word-wrap: break-word; word-break: keep-all"><span>{_p var=$sFeature}</span></td>
                                        {foreach from=$aFeatures.data item=aFeature}
                                            <td class="item-compare t_center">
                                                {if (int)$aFeature.option == 1}
                                                    <span class="ico ico-check" style="color: #47c366;"></span>
                                                {elseif (int)$aFeature.option == 2}
                                                    <span class="ico ico-close" style="color: #c8c8c8;"></span>
                                                {else}
                                                    <span>{_p var=$aFeature.text}</span>
                                                {/if}
                                            </td>
                                        {/foreach}
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {/if}
        <div class="form-group">
            <a class="btn btn-success popup" href="{url link='admincp.subscribe.add-compare'}" rel="hide_box_title" role="link">{_p var='subscribe_add_new_feature'}</a>
        </div>
    </div>
</div>
