<?php
    defined('PHPFOX') or exit('NO DICE!');
?>

{if !empty($aPackages.packages) && !empty($aPackagePermissionIds)}
    <div class="membership-comparison-container">
        <div class="item-title-block fw-bold">{_p var='plans_comparision'}</div>
        <div id="subscribe_compare_plan">
            <div id="div_compare_wrapper" class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="item-feature"></th>
                            {foreach from=$aPackages.packages item=aPackage}
                                {if $aPackage.membership_permission}
                                <th class="item-compare" title="{_p var=$aPackage.title}" style="background-color: {$aPackage.background_color};">{_p var=$aPackage.title}</th>
                                {/if}
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="item-feature"><span>{_p var='membership'}</span></td>
                            {foreach from=$aPackages.packages item=aPackage}
                            {if $aPackage.membership_permission}
                                <td class="item-compare">
                                    <span>{$aPackage.membership_title}</span>
                                </td>
                            {/if}
                            {/foreach}
                        </tr>
                        {foreach from=$aPackages.features key=sFeature item=aFeatures}
                            <tr>
                                <td class="item-feature"><span>{_p var=$sFeature}</span></td>
                                {foreach from=$aFeatures.data key=package_id item=aFeature}
                                    {if in_array($package_id, $aPackagePermissionIds)}
                                        <td class="item-compare">
                                            {if (int)$aFeature.option == 1}
                                            <span class="ico ico-check" style="color: #47c366;"></span>
                                            {elseif (int)$aFeature.option == 2}
                                            <span class="ico ico-close" style="color: #c8c8c8;"></span>
                                            {else}
                                            <span>{_p var=$aFeature.text}</span>
                                            {/if}
                                        </td>
                                    {/if}
                                {/foreach}
                            </tr>
                        {/foreach}

                        {if !empty($iPackageCompareCount) && !empty($aPackagePermissionIds)}
                            <tr>
                                <td class="item-feature"></td>
                                {foreach from=$aPackages.packages item=aPackage}
                                {if $aPackage.membership_permission}
                                    {if empty($aPackage.purchased_by_current_user)}
                                    <td class="item-button">
                                        <div class="text-center wapper">
                                            <a class="btn btn-primary" href="{if Phpfox::isUser()}javascript:void(0);{else}{url link='user.register' selected_package_id=$aPackage.package_id}{/if}" onclick="{if Phpfox::isUser() && (int)$aPackage.recurring_period > 0}tb_show('{_p var='subscribe_select_renew_method_title' phpfox_squote=true}', $.ajaxBox('subscribe.renew', 'height=400&amp;width=650&amp;id={$aPackage.package_id}'));{else}tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;id={$aPackage.package_id}'));{/if}">{_p var='subscribe_select_plan'}
                                            </a>
                                            {if isset($aPackage.default_cost) && $aPackage.default_cost != '0.00'}
                                            <p class="fw-bold price">
                                                {$aPackage.default_cost|currency:$aPackage.default_currency_id}
                                            </p>
                                            <p class="mb-0 recurring">
                                                {if !empty($aPackage.default_recurring_cost)}
                                                {$aPackage.default_recurring_cost}
                                                {else}
                                                {_p var='subscribe_one_time'}
                                                {/if}
                                            </p>
                                            {elseif isset($aPackage.price)}
                                            {foreach from=$aPackage.price item=sCurrency name=iCost}
                                            <span>{$sCurrency.currency_id}: <span class="fw-bold price">{$sCurrency.cost}</span></span>
                                            {/foreach}
                                            {else}
                                            <p class="fw-bold price">
                                                {_p var='free'}
                                            </p>
                                            <p class="mb-0 recurring">
                                                {if !empty($aPackage.default_recurring_cost)}
                                                {$aPackage.default_recurring_cost}
                                                {else}
                                                {_p var='subscribe_one_time'}
                                                {/if}
                                            </p>
                                            {/if}

                                        </div>
                                    </td>
                                    {else}
                                    <td class="item-button current-plant">
                                        <div class="text-center wapper">
                                            <p class="mb-0 text">{_p var='This is your current plan'}</p>
                                        </div>
                                    </td>
                                    {/if}
                                {/if}
                                {/foreach}
                            </tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{else}
<div class="alert alert-empty">
    {_p var='subscribe_no_packages_found_or_no_feature_for_packages'}
</div>
{/if}
