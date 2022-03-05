<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="item-collection-checkbox">
    <div class="checkbox p-saveitems-checkbox-custom">
        <label data-toggle="saveditems-collection" data-id="{$savedId}" data-collection="{$collectionId}" data-feed="{if !empty($feedId)}{$feedId}{/if}" onclick="appSavedItem.addItemToCollection(this);">
            <input type="checkbox" {if $checked}checked="true"{/if}/><i class="ico ico-square-o mr-1"></i><div class="item-text">{$collectionName}</div>
        </label>
    </div>
    <a class="item-view" href="{url link='saved.collection.'$collectionId}" target="_blank" onclick="return;">{_p var='view'}</a>
</div>
