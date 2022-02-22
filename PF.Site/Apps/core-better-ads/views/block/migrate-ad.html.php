<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="migrate-ad">
    {if empty($aPlacements)}
    <div class="alert alert-warning">{_p var='you_havent_created_any_placement_to_import'}.</div>
    {else}
    <form method="post" id="import-ad-form" onsubmit="$Core.Ads.processImport(this);return false;">
        {if !empty($iId)}
        <div><input type="hidden" name="import" value="{$iId}"></div>
        {/if}
        {if !empty($aIds)}
            {foreach from=$aIds item=iId}
            <div><input type="hidden" name="import[]" value="{$iId}"></div>
            {/foreach}
        {/if}
        <div class="form-group">
            <label for="placement">{_p var='select_placement_to_import'}</label>
            <select name="placement" id="placement" class="form-control">
                {foreach from=$aPlacements item=aPlacement}
                <option value="{$aPlacement.plan_id}">{$aPlacement.title}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">{_p var='import'}</button>
        </div>
    </form>
    {/if}
</div>
