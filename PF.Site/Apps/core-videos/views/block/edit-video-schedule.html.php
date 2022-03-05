<?php

defined('PHPFOX') or exit('NO DICE!');

?>
<div><input type="hidden" name="val[group_id]" value="{$iGroupId}"/></div>
<div class="core-video-schedule-manage" id="js_schedule_manage_video">
    <div class="block">
        <div class="content item-container core-video-schedule-item" id="js_schedule_video_info">
            <div class="item-outer">
                <div class="item-media">
                    {if isset($aImage)}
                        <div class="item-media-src" style="background-image: url('{img server_id=$aImage.server_id path='v.url_image' file=$aImage.image_path suffix='_1024' return_url=true}');"></div>
                    {else}
                        <div class="item-media-src" style="background-image: url('{$sImageUrl}');"></div>
                    {/if}
                    <div class="item-close">
                        <input type="hidden" class="close_warning" name="val[change_video]" value="0" id="js_schedule_change_video">
                        <a href="javascript:void(0)" id="js_schedule_upload_video" onclick="$Core.Video.showScheduleUploadSection(); return false;" class="item-btn">
                            <i class="ico ico-close"></i>
                        </a>
                    </div>
                </div>
                <div class="item-inner">
                    <div class="item-title">
                        <span>{if !empty($aForms.title)}{$aForms.title|clean}{else}{_p var='untitled_video'}{/if}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="manage-form-upload" id="js_schedule_video_upload" style="display: none">
            {if $bAllowVideoUploading}
                <div class="pf_select_video video_special_close_warning" id="pf_select_video_no_ajax">
                    {module name='core.upload-form' type='v'}
                    <span class="extra_info hide_it">
                        <a href="#" class="pf_v_upload_cancel button btn-sm btn-danger">{_p('Cancel')}</a>
                    </span>
                </div>
            {/if}
            <div class="alert alert-danger" id="pf_video_add_error_link" style="display: none;color: #ee5a2b;"></div>
            <div class="pf_v_video_url">
                <div class="table form-group">
                    <div class="table_right">
                        <input class="form-control close_warning" type="text" oninput="$('.pf_v_url_cancel').hide();" name="val[url]" id="video_url" size="40" placeholder="{if $bAllowVideoUploading}{_p('or paste a URL')}{else}{_p('Paste a URL')}{/if}"/>
                    </div>
                    <span class="extra_info hide_it">
                    <a href="#" class="pf_v_url_cancel">{_p('Cancel')}</a>
                    <span style="display: none;" class="form-spin-it pf_v_url_processing"><i class="fa fa-spin fa-circle-o-notch"></i></span>
                </span>
                </div>
            </div>
            <div class="pf_video_caption" style="display:none;">
                <div class="table">
                    <div class="table_right">
                        <input class="form-control" type="text" placeholder="{_p('video_title')}" name="val[title]" value="" id="title" size="40" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>