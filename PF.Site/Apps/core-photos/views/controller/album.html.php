<?php 
    defined('PHPFOX') or exit('NO DICE!'); 
?>

{if !PHPFOX_IS_AJAX}
<div class="item_view">
	<div class="item_info">
            {img user=$aAlbum suffix='_120_square'}
        <div class="item_info_author">
            <div>{_p var="By"} {$aAlbum|user:'':'':50}</div>
            <div>{$aAlbum.time_stamp|convert_time}</div>
        </div>
	</div>
    <div class="item-comment mb-2">
        {if $aAlbum.view_id == 0}
            <div>
               {module name='feed.mini-feed-action'}
           </div>
       {/if}
       <span class="item-total-view">
           {if isset($aAlbum.total_photo)}
                <span>{$aAlbum.total_photo}</span>{if $aAlbum.total_photo == 1} {_p('photo')}{else} {_p('photos')}{/if}
            {/if}       
       </span>
    </div>
	{if $aAlbum.hasPermission}
        <div class="item_bar">
            <div class="dropdown">
                <span role="button" data-toggle="dropdown" class="item_bar_action">
                    <i class="ico ico-gear-o"></i>
                </span>
                <ul class="dropdown-menu dropdown-menu-right">
                    {template file='photo.block.menu-album'}
                </ul>
            </div>
        </div>
    {/if}
	<div class="item_description text-center">
    	{$aAlbum.description|highlight:'search'|parse|shorten:200:'feed.view_more':true|split:55}
	</div>

    <div class="detail-photos {if count($aPhotos) && count($aModeViews) > 1}mt-6{/if}">
        {if count($aPhotos)}
            <div class="photo-mode-view-container core-photos-js{if count($aModeViews) < 2} hide{/if}" id="album-photos">
                <span class="photo-mode-view-btn grid" data-mode="grid" title="{_p var='grid_view'}"><i class="ico ico-th-large"></i></span>
                <span class="photo-mode-view-btn casual" data-mode="casual" title="{_p var='casual_view'}"><i class="ico ico-casual"></i></span>
            </div>
        {/if}

        <div id="js_actual_photo_content" class="photo-mode-view-content photo-view-modes-js" data-mode-views="{$sModeViews}" data-mode-view="grid" data-mode-view-default="{$sDefaultModeView}">
            <div id="js_album_content">
                {/if}
                {if $aPhotos}
                    {if !PHPFOX_IS_AJAX}
                        <div class="item-container photo-listing photo-init-pinto-js clearfix" id="photo_collection">
                            {/if}
                            {foreach from=$aPhotos item=aForms}
                                {template file="photo.block.photo_entry"}
                            {/foreach}
                            {if $photoPagingMode == 'loadmore'}
                                {pager}
                            {/if}
                            {if !PHPFOX_IS_AJAX}
                        </div>
                    {/if}
                    {if $photoPagingMode != 'loadmore'}
                        {pager}
                    {/if}
                {else}
                    {if !PHPFOX_IS_AJAX}
                        <div class="extra_info">
                            {_p var='no_photos_found'}
                        </div>
                    {/if}
                {/if}

                {if !PHPFOX_IS_AJAX}
                    {if $bShowModerator}
                        {moderation}
                    {/if}
            </div>
        </div>
    </div>
	<div
        {if $aAlbum.view_id != 0}style="display:none;" class="js_moderation_on"{/if} class="js_moderation_on pt-2 mt-4">
        <div class="item-addthis mb-3">{addthis title=$aAlbum.name description=$sShareDescription}</div>
		{module name='feed.comment'}
	</div>	
</div>
{/if}