<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if $aForms.view_id == 1}
    {template file='core.block.pending-item-action'}
{/if}

<div class="photos_view dont-unbind js_photos_view" data-photo-id="{$aForms.photo_id}">
    <div class="photos_view_loader">
        <i class="fa fa-spin fa-circle-o-notch"></i>
    </div>
	<div class="image_load_holder dont-unbind" data-image-src="{img id='js_photo_view_image' server_id=$aForms.server_id path='photo.url_photo' suffix='' file=$aForms.destination title=$aForms.title return_url=true}" data-image-src-alt="{img id='js_photo_view_image' server_id=$aForms.server_id path='photo.url_photo' suffix='_1024' file=$aForms.destination title=$aForms.title return_url=true}"></div>
	{if PHPFOX_IS_AJAX_PAGE}
	     <span id="js_back_btn" class="_a_back hide"><i class="ico ico-arrow-left"></i>{_p var='back'}</span>
	{/if}
	
    {literal}
        <script>
            var preLoadImages = false,
                preSetActivePhoto = false,
                photoBackBtn = document.getElementById('js_back_btn');
            if (typeof checkFirstAccessToPhotoDetailByAjaxMode === "undefined" && photoBackBtn !== null) {
                photoBackBtn.classList.remove('hide');
                checkFirstAccessToPhotoDetailByAjaxMode = 1;
            }
        </script>
    {/literal}
</div>
<div class="core-photos-view-action-container">
    <div class="photo_tag_in_photo js_tagged_section" style="display: none">
        <p>-- {_p var='with'}</p> <span id="js_photo_in_this_photo" class="ml-1"></span>
    </div>
    
    <div class="photos-action-wrapper">
        {if (Phpfox::getUserParam('photo.can_tag_own_photo') && $aForms.user_id == Phpfox::getUserId() && Phpfox::getUserParam('photo.how_many_tags_on_own_photo') > 0) || (Phpfox::getUserParam('photo.can_tag_other_photos') && Phpfox::getUserParam('photo.how_many_tags_on_other_photo'))}
            <div class="photos_tag">
                <a class="btn btn-default btn-sm btn-icon" href="#" id="js_tag_photo" onclick="$('.js_tagged_section').addClass('edit'); $(this).parent().addClass('active');">
                    <span><i class="ico ico-price-tags"></i></span>
                    <b class="text-capitalize">{_p var="tag_photo"}</b>
                </a>
            </div>
        {/if}

        {if $aForms.hasAction}
            <div class="photos-action-more dropdown">
                <span role="button" data-toggle="dropdown" class="item_bar_action">
                    <i class="ico ico-dottedmore-vertical-o"></i>
                </span>
                <ul class="dropdown-menu dropdown-menu-right">
                    {if $aForms.user_id == Phpfox::getUserId() || (Phpfox::getUserParam('photo.can_download_user_photos') && $aForms.allow_download)}
                        <li class="photos_download">
                            <a href="{permalink module='photo' id=$aForms.photo_id title=$aForms.title action=download}" id="js_download_photo" class="no_ajax">
                                <i class="ico ico-download-alt"></i>{_p var="download"}
                            </a>
                        </li>
                    {/if}
                    {if Phpfox::isUser() && Phpfox::getUserId() == $aForms.user_id}
                        <li>
                            <a href="#" class="photo_make_as_profile" data-processing="false" onclick="$.ajaxCallOne(this, 'photo.makeProfilePicture', 'photo_id={$aForms.photo_id}'); return false;">
                                <i class="ico ico-user-circle"></i>{_p var='make_profile_picture'}
                            </a>
                        </li>
                        <li>
                            <a href="#" class="photo_make_as_cover" data-processing="false" onclick="$.ajaxCallOne(this, 'photo.makeCoverPicture', 'photo_id={$aForms.photo_id}'); return false;">
                                <i class="ico ico-photo"></i>{_p var='make_cover_photo'}
                            </a>
                        </li>
                    {/if}
                    {if isset($aCallback) && ($aCallback.module_id == 'pages' || $aCallback.module_id == 'groups') && $aForms.canSetCover}
                        <li>
                            <a href="#" class="photo_make_as_cover" onclick="$Core.Photo.setCoverPhoto({$aForms.photo_id}, {$aCallback.item_id}, '{$aCallback.module_id}'); return false;" >
                                <i class="ico ico-photo"></i>
                                {if isset($aCallback.set_default_phrase)}
                                    {$aCallback.set_default_phrase}
                                {else}
                                    {_p var='set_as_page_s_cover_photo'}
                                {/if}
                            </a>
                        </li>
                    {/if}

                    {if empty($aForms.noRotation) && ((Phpfox::getUserParam('photo.can_edit_own_photo') && $aForms.user_id == Phpfox::getUserId()) || Phpfox::getUserParam('photo.can_edit_other_photo'))}
                        <li role="separator" class="divider"></li>
                        <li class="rotate-left">
                            <a href="#" onclick="$('#photo_view_ajax_loader').show(); $('#menu').remove(); $('#noteform').hide(); $('#js_photo_view_image').imgAreaSelect({left_curly} hide: true {right_curly}); $('#js_photo_view_holder').hide(); $.ajaxCall('photo.rotate', 'photo_id={$aForms.photo_id}&amp;photo_cmd=left&amp;currenturl=' + $('#js_current_page_url').html()); return false;">
                                <i class="ico ico-rotate-left"></i>{_p var='rotate_left'}
                            </a>
                        </li>
                        <li class="rotate-right">
                            <a href="#" onclick="$('#photo_view_ajax_loader').show(); $('#menu').remove(); $('#noteform').hide(); $('#js_photo_view_image').imgAreaSelect({left_curly} hide: true {right_curly}); $('#js_photo_view_holder').hide(); $.ajaxCall('photo.rotate', 'photo_id={$aForms.photo_id}&amp;photo_cmd=right&amp;currenturl=' + $('#js_current_page_url').html()); return false;">
                                <i class="ico ico-rotate-right"></i>{_p var='rotate_right'}
                            </a>
                        </li>
                    {/if}
                </ul>
            </div>
        {/if}
    </div>
</div>
<div class="core-photos-view-title header-page-title item-title {if isset($aTitleLabel.total_label) && $aTitleLabel.total_label > 0}header-has-label-{$aTitleLabel.total_label}{/if}">
    {if Phpfox::getParam('photo.photo_show_title')}
        <a href="{$aForms.link}" class="ajax_link">{$aForms.title|clean}</a>
    {/if}
    <div class="photo-icon">
        {if (isset($sView) && $sView == 'my' || isset($bIsDetail)) && $aForms.view_id == 1}
            <div class="sticky-label-icon sticky-pending-icon">
                <span class="flag-style-arrow"></span>
                <i class="ico ico-clock-o"></i>
            </div>
        {/if}
        {if $aForms.is_sponsor}
            <div class="sticky-label-icon sticky-sponsored-icon">
                <span class="flag-style-arrow"></span>
                <i class="ico ico-sponsor"></i>
            </div>
        {/if}
        {if $aForms.is_featured}
            <div class="sticky-label-icon sticky-featured-icon">
                <span class="flag-style-arrow"></span>
                <i class="ico ico-diamond"></i>
            </div>
        {/if}
    </div>
</div>
<div class="item_view">
    <div class="item_info">
        {img user=$aForms suffix='_120_square'}
        <div class="item_info_author">
            <div class="photo-author">{$aForms|user:'':'':50} {_p var='on'} {$aForms.time_stamp|convert_time}</div>
            <div><span>{$aForms.total_view|number_format}</span>{if $aForms.total_view == 1} {_p var='view_lowercase'}{else} {_p var='views_lowercase'}{/if}
            </div>
        </div>
    </div>
    {if $aForms.hasPermission}
        <div class="item_bar">
            <div class="dropdown">
                <span role="button" data-toggle="dropdown" class="item_bar_action">
                    <i class="ico ico-gear-o"></i>
                </span>
                <ul class="dropdown-menu dropdown-menu-right">
                    {template file='photo.block.menu'}
                </ul>
            </div>
        </div>
    {/if}
</div>
