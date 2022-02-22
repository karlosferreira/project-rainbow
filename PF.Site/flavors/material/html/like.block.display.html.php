<?php
    defined('PHPFOX') or exit('NO DICE!');
?>

{if isset($ajaxLoadLike) && $ajaxLoadLike}
<div id="js_like_body_{$aFeed.feed_id}">
{/if}
    {if !empty($aFeed.feed_like_phrase)}
        <div class="activity_like_holder" id="activity_like_holder_{$aFeed.feed_id}">
            {$aFeed.feed_like_phrase}
        </div>
    {else}
        <div class="activity_like_holder activity_not_like">
            {_p var='when_not_like'}
        </div>
    {/if}
{if isset($ajaxLoadLike) && $ajaxLoadLike}
</div>
{/if}
