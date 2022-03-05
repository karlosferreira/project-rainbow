<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="groups-profile-banner">
    <div class="profiles_banner {if $aCoverPhoto !== false}has_cover{/if}">
        <div class="profiles_banner_bg">
            <div class="cover_bg"></div>
            {if $aCoverPhoto}
                <a href="{permalink module='photo' id=$aCoverPhoto.photo_id title=$aCoverPhoto.title}">
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
                {if ($sView == 'my' && $aPage.view_id == 1)}
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
                        <a href="{url link='groups.'$aPage.page_id'.photo'}">
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
                            <a role="button" onclick="$Core.CoverPhoto.reposition.init('groups', {$aPage.page_id}); return false;">
                                {_p var='reposition'}
                            </a>
                        </li>
                        <li class="cover_section_menu_item">
                            <a href="#"
                               onclick="$.ajaxCall('groups.removeLogo', 'page_id={$aPage.page_id}'); return false;">
                                {_p('remove_cover_photo')}
                            </a>
                        </li>
                    {/if}
                </ul>
            </div>
        {/if}

        <div class="profile-info-block groups-profile">
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
                            <label title="{_p var='change_picture'}" for="upload_avatar" class="btn-primary btn-gradient" onclick="return $Core.Groups.profilePhoto.update({if !empty($aProfileImage.photo_id)}true{else}false{/if});">
                                <span class="ico ico-camera"></span>
                            </label>
                        </form>
                    {/if}
                </div>
            </div>
            <div class="profile-info">
                <div class="profile-extra-info ">
                    <h1 title="{$aPage.title|clean}"><a>{$aPage.title|clean}</a></h1>
                    <div class="profile-info-detail">
                    <span class="">
                        {if $aPage.reg_method == 0}
                        <i class="fa fa-privacy fa-privacy-0"></i>&nbsp;{_p var='public_group'}
                        {elseif $aPage.reg_method == 1}
                        <i class="fa fa-unlock-alt" aria-hidden="true"></i>&nbsp;{_p var='closed_group'}
                        {elseif $aPage.reg_method == 2}
                        <i class="fa fa-lock" aria-hidden="true"></i>&nbsp;{_p var='secret_group'}
                        {/if}
                    </span>
                        -
                        <span class="">
                        {if !empty($aPage.parent_category_link)}
                            <a href="{$aPage.parent_category_link}">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['parent_category_name'])}
                                {_p var=$aPage.parent_category_name}
                            {else}
                                {$aPage.parent_category_name|convert}
                            {/if}
                            </a>
                        {/if}
                        {if !empty($aPage.parent_category_link) && !empty($aPage.category_link)}»{/if}
                        {if !empty($aPage.category_link)}
                            <a href="{$aPage.category_link}">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['category_name'])}
                                {_p var=$aPage.category_name}
                            {else}
                                {$aPage.category_name|convert}
                            {/if}
                            </a>
                        {/if}
                    </span>
                        -
                        <span class="">
                        {$aPage.total_like|number_format} {if $aPage.total_like != 1} {_p('Members')}{else}{_p('Member')}{/if}
                    </span>
                    </div>
                </div>

                <div class="profile-actions js_core_action_dropdown_check">
                     <div class="profile-action-block profiles-viewer-actions">
                        {if !empty($aSubPagesMenus)}
                            {foreach from=$aSubPagesMenus key=iKey name=submenu item=aSubMenu}
                                <a href="{url link=$aSubMenu.url)}" class="btn btn-success btn-round">
                                    {if (isset($aSubMenu.title))}
                                        {$aSubMenu.title}
                                    {else}
                                        {_p var=$aSubMenu.var_name}
                                    {/if}
                                </a>
                            {/foreach}
                        {/if}
                        {plugin call='groups.block_photo_viewer_actions'}
                        {template file='groups.block.joinpage'}
                        <div class="dropdown">
                            <a class="btn" role="button" data-toggle="dropdown">
                                <i class="ico ico-dottedmore-o"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                {plugin call='groups.block_photo_viewer_actions_dropdown'}
                            </ul>
                        </div>
                    </div>
                    <div class="profile-action-block profiles-owner-actions">
                        <div class="dropdown btn-group btn-gear">
                            <a class="btn" role="button" data-toggle="dropdown">
                                <i class="ico ico-gear-o"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                {plugin call='groups.block_photo_owner_actions'}
                                {template file='groups.block.link'}
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
            {foreach from=$aGroupMenus item=aGroupMenu}
                <li>
                    <a href="{$aGroupMenu.url}" {if !empty($aGroupMenu.is_active)}class="active"{/if}>
                        {if (isset($aGroupMenu.menu_icon))}
                        <span class="{$aGroupMenu.menu_icon}"></span>
                        {else}
                        <span class="ico ico-calendar-star-o"></span>
                        {/if}
                        <span>
                        {$aGroupMenu.phrase|clean}
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
        {plugin call='groups.block_photo_menus'}
    </div>
</div>
<!-- Below css for reposition feature -->
<style type="text/css">
    .profiles_banner_bg .cover img.cover_photo {l}
        position: relative;
        left:0;
        top: { $iCoverPhotoPosition }px;
    {r}
</style>

<script>
    $Core.coverPhotoPositionTop = {if empty($iCoverPhotoPosition)}0{else}{$iCoverPhotoPosition}{/if};
    var currentGroupId = parseInt('{$aPage.page_id}');
</script>

{if $bCanChangeCover}
    {template file='profile.block.upload-cover-form'}
{/if}
