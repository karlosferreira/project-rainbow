<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="p-saveditems-alert-item js_saved_alert_item" data-target="saved_alert_item_{$savedId}">
    <div class="p-saveapp-alert-outer">
        <div class="item-inner">
			<span class="item-icon">
				<i class="ico {if $unsaved}ico-bookmark-o{else}ico-bookmark{/if}"></i>
			</span>
            <div class="item-text">
                {if $unsaved}
                {_p var='saveditems_this_item_has_been_unsaved_successfully'}
                {else}
                {_p var='saveditems_item_saved'}
                {/if}
            </div>
            {if empty($unsaved) && (Phpfox::getUserParam('saveditems.can_create_collection') || !empty($collections))}
            {template file='saveditems.block.collection.add-to-collection'}
            {/if}
            <div class="item-recent">
                <a href="{url link='saved'}" target="_blank" onclick="return;">{_p var='saveditems_recent_saved_items'}</a>
            </div>
        </div>
        <div class="item-delete">
            <a href="javascript:void(0);" class="item-delete-btn" onclick="$(this).closest('.js_saved_alert_item').remove();"><i class="ico ico-close"></i></a>
        </div>
    </div>
</div>
