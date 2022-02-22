<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if $hasCollection}
    {if !empty($collections)}
        {if !PHPFOX_IS_AJAX}
        <div class="p-saveditems-collections-container">
            {/if}
            {foreach from=$collections item=collection}
                {template file='saveditems.block.collection.item-entry'}
            {/foreach}
            {if $canPaging}
                {pager}
            {/if}
            {if !PHPFOX_IS_AJAX}
        </div>
        {/if}
    {/if}
{else}
<div class="p-saveditems-collection-empty">
    <div class="p-saveditems-outer">
        <div class="p-saveditems-media-wrapper">
            <span class="p-saveditems-media-link" href="#">
                <span class="p-saveditems-media-src"
                      style="background-image: url({param var='core.path_actual'}/PF.Site/Apps/p-saved-items/assets/images/collection-empty-image.png)"></span>
            </span>
        </div>
        <div class="p-saveditems-inner">
            <div class="p-saveditems-title">
                {_p var='saveditems_you_have_not_created_any_collection_yet'}
            </div>
            <button class="btn btn-primary"
                    onclick="tb_show('{_p var='saveditems_new_collection'}', $.ajaxBox('saveditems.showCreateCollectionPopup'));">
                <i class="ico ico-plus"></i>{_p var='saveditems_create_collection'}
            </button>
        </div>
    </div>
</div>
{/if}