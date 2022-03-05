<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="item_view " id="photo-detail-view">
    <div class="core-photos-view-content-collapse-container">
        <div class="core-photos-view-content-collapse js_core_photos_view_content_collapse ">
            {if $aForms.description}
                <div class="item_description item_view_content">
                    {$aForms.description|parse}
                </div>
            {/if}
            <div class="item-extra-info">
                {if !empty($aForms.album_id)}
                    <div class="item-album-info">
                        {_p var='in_album'}: <a href="{$aForms.album_url}">{$aForms.album_title|convert|clean|split:45|shorten:75:'...'}</a>
                    </div>
                {/if}
                {if !empty($aForms.sCategories)}
                    <div class="item-category">
                        {_p var="Categories"}: {$aForms.sCategories}
                    </div>
                {/if}
                {if Phpfox::isModule('tag') && isset($aForms.tag_list)}
                    {module name='tag.item' sType='photo' sTags=$aForms.tag_list iItemId=$aForms.photo_id iUserId=$aForms.user_id}
                {/if}
                <div class="item-size">
                    <div class="item-size-stat">
                        <span class="item-title">{_p var='dimension'}:</span>
                        <span class="item-number">{$aForms.width} x {$aForms.height}</span>
                    </div>
                    <div class="item-size-stat">
                        <span class="item-title">{_p var='file_size'}:</span>
                        <span class="item-number">{$aForms.file_size|filesize}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="core-photos-view-action-collapse js-core-photo-action-collapse">
            <a class="item-viewmore-btn js-item-btn-toggle-collapse">{_p var="view_more"} <i class="ico ico-caret-down"></i></a>
            <a class="item-viewless-btn js-item-btn-toggle-collapse">{_p var="view_less"} <i class="ico ico-caret-up"></i></a>
        </div>
    </div>
    <div class="js_moderation_on">
        <div class="item-addthis mb-3 pt-2">{addthis url=$aForms.link title=$aForms.title description=$sShareDescription}</div>
        {if Phpfox::isModule('feed') && Phpfox::getParam('feed.enable_check_in') && Phpfox::getParam('core.google_api_key') != '' && !empty($aForms.location_name)}
            <div class="activity_feed_location">
                <span class="activity_feed_location_at">{_p('at')} </span>
                <span class="js_location_name_hover activity_feed_location_name" {if isset($aForms.location_latlng) && isset($aForms.location_latlng.latitude)}onmouseover="$Core.Feed.showHoverMap('{$aForms.location_latlng.latitude}','{$aForms.location_latlng.longitude}', this);"{/if}>
                <span class="ico ico-checkin"></span>
                <a href="https://maps.google.com/maps?daddr={$aForms.location_latlng.latitude},{$aForms.location_latlng.longitude}" target="_blank">{$aForms.location_name}</a>
                </span>
            </div>
        {/if}
        <div class="item-detail-feedcomment">
            {module name='feed.comment'}
        </div>
    </div>
</div>
<script type="text/javascript">
    var bChangePhoto = true;
    var aPhotos = {$sPhotos};
    var oPhotoTagParams =  {l}{$sPhotoJsContent}{r};
    $Behavior.tagPhoto = function()
    {l}
        setTimeout(function() {l}
            $Core.photo_tag.init(oPhotoTagParams);
        {r}, 500);
        $("#page_photo_view input.v_middle" ).focus(function() {l}
            $(this).parent('.table_right').addClass('focus');
            $(this).parents('.table').siblings('.cancel_tagging').addClass('focus');
        {r});
        $("#page_photo_view input.v_middle" ).focusout(function() {l}
            $(this).parent('.table_right').removeClass('focus');
            $(this).parents('.table').siblings('.cancel_tagging').removeClass('focus');
        {r});
    {r};

    $Behavior.removeImgareaselectBox = function()
    {l}
        {literal}
            if ($('body#page_photo_view').length == 0 || ($('body#page_photo_view').length > 0 && bChangePhoto == true)) {
                bChangePhoto = false;
                $('.imgareaselect-outer').hide();
                $('.imgareaselect-selection').each(function() {
                    $(this).parent().hide();
                });
            }
        {/literal}
    {r};
</script>

{if $bLoadCheckin}
    <script type="text/javascript">
        var bCheckinInit = false;
        $Behavior.prepareInit = function()
        {l}
            if($Core.Feed !== undefined)
            {l}
                $Core.Feed.sIPInfoDbKey = '';
                $Core.Feed.sGoogleKey = '{param var="core.google_api_key"}';
                {if isset($aVisitorLocation)}
                    $Core.Feed.setVisitorLocation({$aVisitorLocation.latitude}, {$aVisitorLocation.longitude} );
                {else}

                {/if}
                $Core.Feed.googleReady('{param var="core.google_api_key"}');
            {r}
        {r}
    </script>
{/if}