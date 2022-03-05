<?php

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="featured-photo-list">
    <div class="sticky-label-icon sticky-featured-icon">
        <span class="flag-style-arrow"></span>
        <i class="ico ico-diamond"></i>
    </div>

    <ul class="photo-featured photo-featured-count-{$aFeaturedImages|count}">
        {foreach from=$aFeaturedImages item=aFeaturedImage}
        <li>
            <a {if !$aFeaturedImage.can_view} class="no_ajax_link {if !$aFeaturedImage.can_view}photo-mature{/if}" onclick="tb_show('{_p('warning')}', $.ajaxBox('photo.warning', 'height=300&width=350&link={$aFeaturedImage.link}')); return false;" href="javascript:;" {else} href="{$aFeaturedImage.link}" {/if}
            style="background-image: url(
                {img server_id=$aFeaturedImage.server_id path='photo.url_photo' file=$aFeaturedImage.destination suffix='_500' max_width=500 max_height=500 return_url="true"}
            )">
            </a>
            {if $aFeaturedImage.total_like > 0}
            <span class="photo_like pl-1 pr-1">
                <span class="count"><i class="ico ico-thumbup"></i> {$aFeaturedImage.total_like|short_number}</span>
            </span>
            {/if}
        </li>
        {/foreach}
    </ul>
</div>



{if Phpfox::getParam('photo.ajax_refresh_on_featured_photos')}
<script type="text/javascript">
    setTimeout("$.ajaxCall('photo.refreshFeaturedImage', '', 'GET');", {$iRefreshTime});
</script>
{/if}