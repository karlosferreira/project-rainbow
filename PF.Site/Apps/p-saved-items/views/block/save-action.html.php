<?php
?>
{if $saveItemParams.is_saved}
<a href="javascript:void(0);" onclick="return appSavedItem.processItem({l}type_id: '{$saveItemParams.type_id}', item_id: {$saveItemParams.item_id}, link: '{$saveItemParams.link}', is_save: 0, feed_id: {$saveItemParams.id}{r});">
    <span class="ico ico-bookmark-o"></span>{_p var='saveditems_unsave'}
</a>
{else}
<a href="javascript:void(0);" onclick="return appSavedItem.processItem({l}type_id: '{$saveItemParams.type_id}', item_id: {$saveItemParams.item_id}, link: '{$saveItemParams.link}', is_save: 1, feed_id: {$saveItemParams.id}{r});">
    <span class="ico ico-bookmark-o"></span>{_p var='save'}
</a>
{/if}
