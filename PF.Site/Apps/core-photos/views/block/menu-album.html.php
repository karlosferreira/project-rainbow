<?php
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{if $aAlbum.canEdit}
    <li>
        <a href="{url link='photo.edit-album' id=$aAlbum.album_id}" id="js_edit_this_album"><i class="ico ico ico-pencilline-o mr-1"></i>{_p var='edit_this_album'}</a>
    </li>
{/if}

{if $aAlbum.canUpload}
    <li>
        <a href="{if (empty($aAlbum.module_id))}{url link='photo.add' album=$aAlbum.album_id}{else}{url link='photo.add' module=$aAlbum.module_id item=$aAlbum.group_id album=$aAlbum.album_id}{/if}"><i class="ico ico-upload-alt mr-1"></i>{_p var='upload_photos_to_album'}</a>
    </li>
{/if}

{if $aAlbum.canDelete}
    <li class="item_delete">
        <a data-is-detail="{if isset($bIsAlbumDetail)}1{else}0{/if}" data-id="{$aAlbum.album_id}" href="javascript:void(0);" id="js_delete_this_album" data-message="{_p('are_you_sure_you_want_to_delete_this_album_permanently')}" onclick="$Core.Photo.deleteAlbumPhoto($(this));"><i class="ico ico-trash-o mr-1"></i>{_p var='delete_this_album'}</a>
    </li>
{/if}

{if $aAlbum.canFeature}
    <li id="js_feature_photo_album_{$aAlbum.album_id}">
        <a href="javascript:void(0)" onclick="$.ajaxCall('photo.featureAlbum','album_id={$aAlbum.album_id}&type={if !$aAlbum.is_featured }1{else}0{/if}'); return false;">
            {if !$aAlbum.is_featured }
            <i class="ico ico-diamond mr-1"></i>{_p var='photo_album_feature'}
            {else}
            <i class="ico ico-diamond mr-1"></i>{_p var='photo_album_unfeature'}
            {/if}
        </a>
    </li>
{/if}

{if $aAlbum.canSponsor}
    <li id="js_sponsor_photo_album_{$aAlbum.album_id}">
        <a href="javascript:void(0)" onclick="$.ajaxCall('photo.sponsorAlbum','album_id={$aAlbum.album_id}&type={if !$aAlbum.is_sponsor}1{else}0{/if}');  return false;">
            {if !$aAlbum.is_sponsor}
            <i class="ico ico-sponsor mr-1"></i>{_p var='photo_album_sponsor'}
            {else}
            <i class="ico ico-sponsor mr-1"></i>{_p var='photo_album_unsponsor'}
            {/if}
        </a>
    </li>
{elseif $aAlbum.canSponsorPurchase}
    <li id="js_sponsor_photo_album_{$aAlbum.album_id}">
        {if !$aAlbum.is_sponsor}
        <a href="{permalink module='ad.sponsor' id=$aAlbum.album_id}section_photo_album/"><i class="ico ico-sponsor mr-1"></i>{_p var='photo_album_sponsor'}</a>
        {else}
        <a href="javascript:void(0)" onclick="$.ajaxCall('photo.sponsorAlbum','album_id={$aAlbum.album_id}&type=0'); return false;"><i class="ico ico-sponsor mr-1"></i>{_p var='photo_album_unsponsor'}</a>
        {/if}
    </li>
{/if}


{plugin call='photo.template_block_menu_album'}