<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="panel panel-default betterads-sponsor-setting">
    <div class="panel-body row">
        <div class="betterads-sponsor-settings-filter">
            <div class="form-group col-sm-6">
                <form action="{url link='current'}" method="get" id="choose-user-group-form">
                    <input type="hidden" name="module_id" value="{$sModuleId}">
                    <label for="val_group_id">{_p var='groups'}</label>
                    <select class="form-control" name="group_id">
                        {foreach from=$aUserGroups item=aUserGroup}
                            <option value="{$aUserGroup.user_group_id}" {if $iGroupId==$aUserGroup.user_group_id}selected{/if}>{$aUserGroup.title}</option>
                        {/foreach}
                    </select>
                </form>
            </div>
            <div class="form-group col-sm-6">
                <label for="val_group_id">{_p var='apps'}</label>
                <select id="filter-apps" class="form-control">
                    <option value="{url link='admincp.ad.sponsor-setting'}">{_p var='all_settings'}</option>
                    {foreach from=$aFilterApps item=product}
                        <optgroup label="{$product.title}">
                            {php}foreach($this->_aVars['product']['apps'] as $this->_aVars['app']):{/php}
                                <option value="{url link='admincp.ad.sponsor-setting' module_id=$app.id group_id=$iGroupId}" {if $sModuleId == $app.id}selected{/if}>{$app.name}</option>
                            {php}endforeach;{/php}
                        </optgroup>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
</div>
<form method="post" class="form user-group-settings">
    <input type="hidden" name="id" value="{$aForms.user_group_id}" />
    {if $sModuleName}
        <h2>{$sModuleName}</h2>
    {/if}
    {template file='user.block.admincp.setting'}
</form>