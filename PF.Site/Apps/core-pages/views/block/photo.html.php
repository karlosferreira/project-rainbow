<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="pages-profile-banner">
    <div class="profiles_banner {if $aCoverPhoto !== false}has_cover{/if}">
        <div class="profiles_banner_bg">
            <div class="cover_bg"></div>
            {if $aCoverPhoto}
                <a href="{permalink module='photo' id=$aCoverImage.photo_id title=$aCoverImage.title}">
            {/if}
                    <div class="cover" id="cover_bg_container">
                        {if !empty($aCoverPhoto.destination)}
                            {img server_id=$aCoverPhoto.server_id path='photo.url_photo' file=$aCoverPhoto.destination suffix='' title=$aCoverPhoto.title class="visible-lg cover_photo js_background_image"}
                        {else}
                            <img class="_image_ image_deferred visible-lg cover_photo js_background_image" style="display: none !important;">
                        {/if}

                        <span {if !empty($aCoverPhoto.destination)}style="background-image: url({img server_id=$aCoverPhoto.server_id path='photo.url_photo' file=$aCoverPhoto.destination suffix='_1024' return_url=true})"{/if} class="hidden-lg js_background_image is_responsive" {if empty($aCoverPhoto.destination)}style="display: none !important;"{/if}></span>

                        {if empty($aCoverPhoto.destination) && !empty($sDefaultCoverPath)}
                            <span class="js_default_background_image" style="background-image: url({img file=$sDefaultCoverPath return_url=true})"></span>
                        {/if}
                    </div>
            {if $aCoverPhoto}
                </a>
            {/if}
            <div class="item-icon-flag">
                {if $aPage.is_sponsor}
                <div class="sticky-label-icon sticky-sponsored-icon">
                    <span class="flag-style-arrow"></span>
                    <i class="ico ico-sponsor"></i>
                </div>
                {/if}
                {if $aPage.is_featured}
                <div class="sticky-label-icon sticky-featured-icon">
                    <span class="flag-style-arrow"></span>
                    <i class="ico ico-diamond"></i>
                </div>
                {/if}
                {if (isset($sView) && $sView == 'my' && $aPage.view_id == 1)}
                <div class="sticky-label-icon sticky-pending-icon">
                    <span class="flag-style-arrow"></span>
                    <i class="ico ico-clock-o"></i>
                </div>
                {/if}
            </div>

            <div class="cover-reposition-actions" id="js_cover_reposition_actions">
                <button role="button" class="btn btn-default" onclick="$Core.CoverPhoto.reposition.cancel()">{_p var='cancel'}</button>
                <button class="btn btn-primary" onclick="$Core.CoverPhoto.reposition.save()">{_p var='save'}</button>
            </div>
        </div>

        {if $bCanChangeCover}
        <div class="dropdown change-cover-block">
            <a title="{_p var='change_cover'}" role="button" data-toggle="dropdown" class=" btn btn-primary btn-gradient" id="js_change_cover_photo">
                <span class="ico ico-camera"></span>
            </a>

            <ul class="dropdown-menu">
                <li class="cover_section_menu_item">
                    <a href="{url link='pages.'$aPage.page_id'.photo'}">
                        {_p var='choose_from_photos'}
                    </a>
                </li>
                <li class="cover_section_menu_item">
                    <a href="javascript:void(0);" onclick="return $Core.CoverPhoto.openUploadImage();">
                        {_p var='upload_photo'}
                    </a>
                </li>
                {if !empty($aPage.cover_photo_id)}
                    <li class="cover_section_menu_item reposition" role="presentation">
                        <a role="button" onclick="$Core.CoverPhoto.reposition.init('pages', {$aPage.page_id}); return false;">
                            {_p var='reposition'}
                        </a>
                    </li>
                    <li class="cover_section_menu_item">
                        <a href="#" onclick="$.ajaxCall('pages.removeLogo', 'page_id={$aPage.page_id}'); return false;">
                            {_p var='remove_cover_photo'}
                        </a>
                    </li>
                {/if}
            </ul>
        </div>
        {/if}

        <div class="profile-info-block">
            <div class="profile-image">
                <div class="profile_image_holder">
                    {if Phpfox::isModule('photo') && isset($aProfileImage) && $aProfileImage.photo_id}
                    <a href="{permalink module='photo' id=$aProfileImage.photo_id title=$aProfileImage.title}">
                        <div class="img-wrapper">
                            {img server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.pages_image_path suffix='_200_square' no_default=false max_width=200 time_stamp=true}
                        </div>
                    </a>
                    {else}
                    <div class="img-wrapper">
                        {img thickbox=true server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.pages_image_path suffix='_200_square' no_default=false max_width=200 time_stamp=true}
                    </div>
                    {/if}

                    {if $bCanChangePhoto}
                        <form>
                            <label id="js_upload_avatar_action" for="upload_avatar" class="btn-primary btn-gradient" onclick="return Core_Pages.profilePhoto.update({if !empty($aProfileImage.photo_id)}true{else}false{/if});">
                                <span class="ico ico-camera"></span>
                            </label>
                        </form>
                    {/if}
                </div>
            </div>

            <div class="profile-info">
                <div class="profile-extra-info">
                    <h1 title="{$aPage.title|clean}"><a>{$aPage.title|clean}</a></h1>
                    <div class="profile-info-detail">
                        {if $aPage.parent_category_name}
                        <a href="{$aPage.type_link}" class="">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['parent_category_name'])}
                            {_p var=$aPage.parent_category_name}
                            {else}
                            {$aPage.parent_category_name|convert}
                            {/if}
                        </a> »
                        {/if}
                        {if $aPage.category_name}
                        <a href="{$aPage.category_link}" class="">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['category_name'])}
                            {_p var=$aPage.category_name}
                            {else}
                            {$aPage.category_name|convert}
                            {/if}
                        </a> -
                        {/if}
                        <span class="">
                        {$aPage.total_like|number_format} {if $aPage.total_like >= 2} {_p var='likes'}{else}{_p var='like'}{/if}
                    </span>
                    </div>
                </div>

                <div class="profile-actions js_core_action_dropdown_check">
                    <div class="profile-action-block profiles-viewer-actions">
                        {if (Phpfox::getUserParam('pages.can_edit_all_pages') || $aPage.is_admin)}
                            {if isset($aSubPagesMenus) && count($aSubPagesMenus)}
                                {foreach from=$aSubPagesMenus key=iKey name=submenu item=aSubMenu}
                                <a href="{url link=$aSubMenu.url)}" class="btn btn-success">
                                    {if (isset($aSubMenu.title))}
                                        {$aSubMenu.title}
                                    {else}
                                        {_p var=$aSubMenu.var_name}
                                    {/if}
                                </a>
                                {/foreach}
                            {/if}
                        {/if}
                        {plugin call='pages.block_photo_viewer_actions'}
                        {template file='pages.block.joinpage'}
                        <div class="dropdown">
                            <a class="btn" role="button" data-toggle="dropdown">
                                <span class="ico ico-dottedmore-o"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                {plugin call='pages.block_photo_viewer_actions_dropdown'}
                                {if $bCanClaim}
                                    <li>
                                        {if (int)$iClamePageUser ==  Phpfox::getUserId()}
                                            <a href="javascript:void(0);" onclick="$.ajaxCall('pages.claimPageWithSameAdmin','page_id={$aPage.page_id}')">
                                                <span class="ico ico-compose-alt"></span>
                                                {_p var='claim_page'}
                                            </a>
                                        {else}
                                            {if $bIsMessageActive == true }
                                            <a href="#?call=contact.showQuickContact&amp;height=600&amp;width=600&amp;page_id={$aPage.page_id}" class="inlinePopup" title="{_p var='claim_page'}">
                                                <span class="ico ico-compose-alt"></span>
                                                {_p var='claim_page'}
                                            </a>
                                            {else}
                                            <a href="javascript:void(0);" onclick="$.ajaxCall('pages.claimPageWithSameAdmin','page_id={$aPage.page_id}')">
                                                <span class="ico ico-compose-alt"></span>
                                                {_p var='claim_page'}
                                            </a>
                                            {/if}
                                        {/if}
                                    </li>
                                {/if}
                            </ul>
                        </div>
                    </div>
                    <div class="profile-action-block profiles-owner-actions">
                        <div class="dropdown btn-group btn-gear">
                            <a class="btn" role="button" data-toggle="dropdown">
                                <span class="ico ico-gear-o"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                {plugin call='pages.block_photo_owner_actions'}
                                {template file='pages.block.link'}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="profiles-menu set_to_fixed">
        <ul data-component="menu">
            <div class="overlay"></div>
            <li class="profile-image-holder">
                <a href="{$aPage.link}">
                    {if Phpfox::isModule('photo') && isset($aProfileImage) && $aProfileImage.photo_id}
                        {img server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.pages_image_path suffix='_200_square' no_default=false max_width=32 time_stamp=true}
                    {else}
                        {img thickbox=true server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.pages_image_path suffix='_200_square' no_default=false max_width=32 time_stamp=true no_link=true}
                    {/if}
                </a>
            </li>
            {foreach from=$aPageMenus item=aPageMenu}
            <li>
                <a href="{$aPageMenu.url}" {if !empty($aPageMenu.is_active)}class="active"{/if}>
                    {if (isset($aPageMenu.menu_icon))}
                    <span class="{$aPageMenu.menu_icon}"></span>
                    {else}
                    <span class="ico ico-calendar-star-o"></span>
                    {/if}
                    <span>
                    {$aPageMenu.phrase|clean}
                </span>
                </a>
            </li>
            {/foreach}
            <li class="dropdown dropdown-overflow hide explorer">
                <a data-toggle="dropdown" role="button">
                    <span class="ico ico-dottedmore-o"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                </ul>
            </li>
        </ul>
        {plugin call='pages.block_photo_menus'}
    </div>
</div>
{if isset($iCoverPhotoPosition)}
<style type="text/css">
	.profiles_banner_bg .cover img.cover_photo
	{l}
	position: relative;
	left: 0;
	top: {if empty($iCoverPhotoPosition)}0{else}{$iCoverPhotoPosition}px{/if};
	{r}
</style>
{/if}

<script>
  $Core.coverPhotoPositionTop = {if empty($iCoverPhotoPosition)}0{else}{$iCoverPhotoPosition}{/if};
  var currentPageId = parseInt('{$aPage.page_id}');
</script>

{if $bCanChangeCover}
    {template file='profile.block.upload-cover-form'}
{/if}
