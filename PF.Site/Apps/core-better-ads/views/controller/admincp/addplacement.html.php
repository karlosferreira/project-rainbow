<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<form method="post" action="{url link='admincp.ad.addplacement'}">
    <div class="panel panel-default">
        {if $bIsEdit}
        <input type="hidden" name="ads_id" value="{$aForms.plan_id}" />
        {/if}
        <div class="panel-body">
            <div class="form-group">
                <label for="title" class="required">
                    {_p var='better_ads_title'}
                </label>
                <input type="text" name="val[title]" id="title" value="{value id='title' type='input'}" size="40" class="form-control" required autofocus/>
            </div>

            <div class="form-group">
                <label for="location" class="required">{_p var='block_actual'}</label>
                <select name="val[block_id]" id="location" class="form-control">
                    {foreach from=$aPlanBlocks item=i}
                        <option value="{$i}"{value type='select' id='block_id' default=$i}>{_p var='better_ads_block_id' id=$i}</option>
                    {/foreach}
                </select>
                <div class="help-block">
                    <a href="#?call=ad.sample&amp;fullmode=true&amp;click=1" class="inlinePopup" title="{_p var='better_ads_sample_layout'}"><span class="ico ico-eye-o"></span> {_p var='view_sample_layout'}</a>
                </div>
            </div>

            <div class="form-group">
                <label for="disallow_controller">{_p var='disallowed_pages'}</label>
                <select name="val[disallow_controller][]" class="form-control" size="10" id="disallow_controller" multiple>
                    <optgroup label="{_p var='handles_non_pages'}">
                        <option value="non_pages" {if isset($aForms.disallow_controller) && in_array('non_pages', $aForms.disallow_controller)}selected{/if}>{_p var='non_pages'}</option>
                    </optgroup>
                    {foreach from=$aControllers key=sName item=aController}
                        <optgroup label="{$sName|translate:'module'}">
                            {foreach from=$aController item=aCont}
                                <option value="{$aCont.m_connection}" {if isset($aForms.disallow_controller) && in_array($aCont.m_connection, $aForms.disallow_controller)}selected{/if}>
                                    {_p var='controller_'$aCont.m_connection} ({$aCont.m_connection})
                                </option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </div>

            <div class="form-group">
                <label class="required">{_p var='better_ads_price'}</label>
                {module name='core.currency' currency_field_name='val[cost]'}
            </div>

            <div class="form-group">
                <label for="is_cpm" class="required">{_p var='better_ads_placement_type'}</label>
                <select name="val[is_cpm]" id="is_cpm" class="form-control">
                    <option value="0"{value type='select' id='is_cpm' default='0'}>{_p var='better_ads_ppc_pay_per_click'}</option>
                    <option value="1"{value type='select' id='is_cpm' default='1'}>{_p var='better_ads_cpm_cost_per_mille'}</option>
                </select>
            </div>

            <div class="form-group">
                <label>{_p var='user_group'}</label>
                {foreach from=$aUserGroups item=aUserGroup}
                <div class="custom-checkbox-wrapper">
                    <label>
                        <input type="checkbox" name="val[user_group][]" value="{$aUserGroup.user_group_id}"{if !empty($aForms.user_group) && is_array($aForms.user_group)}{if in_array($aUserGroup.user_group_id, $aForms.user_group)} checked="checked" {/if}{else} checked="checked" {/if}/>
                        <span class="custom-checkbox"></span>
                        {$aUserGroup.title|convert|clean}
                    </label>
                </div>
                {/foreach}
            </div>

            <div class="form-group">
                <label>{_p var='is_active'}</label>
                <div class="item_is_active_holder">
                    <span class="js_item_active item_is_active"><input type="radio" name="val[is_active]" value="1" {value type='radio' id='is_active' default='1' selected='true'}/> {_p var='yes'}</span>
                    <span class="js_item_active item_is_not_active"><input type="radio" name="val[is_active]" value="0" {value type='radio' id='is_active' default='0'}/> {_p var='no'}</span>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <input type="submit" value="{_p var='submit'}" class="btn btn-primary"/>
        </div>
    </div>
</form>