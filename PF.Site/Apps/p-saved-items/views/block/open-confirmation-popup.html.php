<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="p-saveditems-unsave-confirmation-popup-content">
    <div class="item-outer">
        <div class="item-desc">{_p var='saveditems_unsave_from_collection_notice'}</div>
        <div class="item-action">
            <a class="item-manage-btn" href="{url link='saved'}" title="{_p var='saveditems_manage_this_item'}" target="_blank" onclick="return;">
                <i class="ico ico-gear-o"></i>
                <span class="ml-1">{_p var='saveditems_manage_this_item'}</span>
            </a>
        </div>
    </div>
    <div class="item-btn-bottom">
        <button class="btn btn-default btn-sm" onclick="js_box_remove(this);">{_p var='cancel_uppercase'}</button>
        <button class="btn btn-primary btn-sm" onclick="return appSavedItem.processItem({l}type_id: '{$type_id}', item_id: {$item_id}, link: '{$link}', is_save: 0, feed_id: {$feed_id}, unsave_confirmation: 1{r});">{_p var='confirm'}</button>
    </div>
</div>

{literal}
<script>
    $Ready(function() {
        if($('.p-saveditems-unsave-confirmation-popup-content').length > 0){
            $('.p-saveditems-unsave-confirmation-popup-content').closest('.js_box').addClass('p-saveditems-unsave-confirmation-popup');
        }
    });
</script>
{/literal}