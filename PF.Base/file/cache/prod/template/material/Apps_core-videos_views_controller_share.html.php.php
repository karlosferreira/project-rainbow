<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:36 pm */ ?>
<?php

?>
<div class="pf_process_form pf_video_process_form">
    <span></span>
    <div class="pf_process_bar"></div>
    <div class="extra_info"><?php echo _p('pf_video_uploading_message_share'); ?></div>
</div>
<div class="pf_video_message" style="display:none;">
    <div class="alert alert-success"><?php if (! $this->_aVars['bIsAjaxBrowsing']): ?> <?php echo _p('your_video_has_successfully_been_uploaded_we_are_processing_it_and_you_will_be_notified_when_its_ready_you_can_edit_detail'); ?> <?php else: ?> <?php echo _p('your_video_has_successfully_been_uploaded_we_are_processing_it_and_you_will_be_notified_when_its_ready_you_can_share_you_think_for_your_video');  endif; ?></div>
    <div>
        <a href="#" class="pf_v_upload_success_cancel button btn-sm btn-default"><?php echo _p('cancel_and_remove_video_uploaded'); ?></a>
    </div>
</div>
<div class="alert alert-danger" id="pf_video_add_error_link" style="display: none;color: #ee5a2b;"></div>

<?php if ($this->_aVars['bUploadSuccess']): ?>
    <div id="pf_v_success_message">
        <div class="alert alert-info"><?php echo _p('your_video_has_successfully_been_saved_and_will_be_published_when_we_are_done_processing_it'); ?></div>
        <div class="form-group">
            <a href="#" class="pf_v_success_continue button btn-sm btn btn-primary"><?php echo _p('Continue'); ?></a>
        </div>
    </div>
<?php endif;  if (isset ( $this->_aVars['bAddFalse'] ) && $this->_aVars['bAddFalse']): ?>
    <div id="pf_video_add_error" class="alert alert-danger"><?php echo _p('we_could_not_find_a_video_there_please_try_again'); ?></div>
<?php endif; ?>

<?php if (! $this->_aVars['bIsAjaxBrowsing']): ?>
    <div class="pf_upload_form" <?php if ($this->_aVars['bUploadSuccess']): ?> style="display: none" <?php endif; ?>>
<?php if ($this->_aVars['bAllowVideoUploading']): ?>
            <div class="pf_select_video video_special_close_warning" id="pf_select_video_no_ajax">
<?php Phpfox::getBlock('core.upload-form', array('type' => 'v')); ?>
                <span class="extra_info hide_it">
                    <a href="#" class="pf_v_upload_cancel button btn-sm btn-danger"><?php echo _p('Cancel'); ?></a>
                </span>
            </div>
<?php endif; ?>
        <form method="post" data-add-spin="true" id="core_js_video_form" <?php if (isset ( $this->_aVars['sGetJsForm'] )): ?>onsubmit="<?php echo $this->_aVars['sGetJsForm']; ?>"<?php endif; ?>
              action="<?php if (isset ( $this->_aVars['sModule'] ) && ! empty ( $this->_aVars['sModule'] )):  echo Phpfox::getLib('phpfox.url')->makeUrl('video.share', array('module' => $this->_aVars['sModule'],'item' => $this->_aVars['iItemId']), false, false);  else:  echo Phpfox::getLib('phpfox.url')->makeUrl('video.share', [], false, false);  endif; ?>">
<?php echo $this->_aVars['sCreateJs']; ?>

            <div id="js_custom_privacy_input_holder"></div>

            <div><input type="hidden" name="val[default_image]" value="" id="video_default_image" /></div>
            <div><input type="hidden" name="val[embed_code]" value="" id="video_embed_code"/></div>
<?php if ($this->_aVars['iItemId']): ?>
                <div><input type="hidden" name="val[callback_module]" value="<?php echo $this->_aVars['sModule']; ?>"></div>
                <div><input type="hidden" name="val[callback_item_id]" value="<?php echo $this->_aVars['iItemId']; ?>"></div>
<?php endif; ?>

            <div class="pf_v_video_url">
                <div class="table form-group">
                    <div class="table_right">
                        <input class="form-control close_warning" type="text" oninput="$('.pf_v_url_cancel').hide();" name="val[url]" id="video_url" size="40" placeholder="<?php if ($this->_aVars['bAllowVideoUploading']):  echo _p('or paste a URL');  else:  echo _p('Paste a URL');  endif; ?>"/>
                    </div>
                    <span class="extra_info hide_it">
                        <a href="#" class="pf_v_url_cancel"><?php echo _p('Cancel'); ?></a>
                        <span style="display: none;" class="form-spin-it pf_v_url_processing"><i class="fa fa-spin fa-circle-o-notch"></i></span>
                    </span>
                </div>
            </div>
            <div class="table_clear"></div>

            <div class="form-group">
                    <label for="title">*<?php echo _p('title'); ?>:</label>
                    <input class="form-control close_warning" type="text" name="val[title]" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['title']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['title']) : (isset($this->_aVars['aForms']['title']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['title']) : '')); ?>
" id="title" size="40" required/>
            </div>
<?php (($sPlugin = Phpfox_Plugin::get('video.template_controller_add_textarea_start')) ? eval($sPlugin) : false); ?>
            <div class="form-group close_warning">
                <label for="text"><?php echo _p('description'); ?>:</label>
                <div class="editor_holder"><?php echo Phpfox::getLib('phpfox.editor')->get('text', array (
  'id' => 'text',
));  Phpfox::getBlock('attachment.share', array('id'=> 'text')); ?></div>
            </div>
            <div class="form-group js_core_init_selectize_form_group">
                <label for="text"><?php echo _p('categories'); ?>:</label>
<?php Phpfox::getBlock('v.add_category_list', array()); ?>
            </div>
<?php if (empty ( $this->_aVars['sModule'] )): ?>
            <div class="form-group special_close_warning">
                    <label for="text"><?php echo _p('privacy'); ?>:</label>
<?php if (Phpfox ::isModule('privacy')): ?>
<?php Phpfox::getBlock('privacy.form', array('privacy_name' => 'privacy','privacy_info' => 'video_control_who_can_see_this_video','default_privacy' => 'v.default_privacy_setting')); ?>
<?php endif; ?>
            </div>
<?php endif; ?>
            <div class="pf_v_video_submit">
                <div class="table_clear">
                    <ul class="table_clear_button">
<?php (($sPlugin = Phpfox_Plugin::get('video.template_controller_add_submit_buttons')) ? eval($sPlugin) : false); ?>
                        <li><input type="submit" name="val[update]" value="<?php echo _p('save'); ?>" class="button btn-primary" /></li>
                    </ul>
                    <div class="clear"></div>
                </div>
            </div>
        
</form>

    </div>
<?php else: ?>
<?php if ($this->_aVars['bAllowVideoUploading']): ?>
        <div class="pf_select_video video_special_close_warning">
<?php Phpfox::getBlock('core.upload-form', array('type' => 'v')); ?>
            <span class="extra_info hide_it">
                <a href="#" class="pf_v_upload_cancel button btn-sm btn-danger"><?php echo _p('Cancel'); ?></a>
            </span>
        </div>
<?php endif; ?>
    <div class="pf_v_video_url">
        <div class="table form-group">
            <div class="table_right">
                <input class="form-control" oninput="$('.pf_v_url_cancel').hide();" type="text" name="val[url]" id="video_url" size="40" placeholder="<?php if ($this->_aVars['bAllowVideoUploading']):  echo _p('or paste a URL');  else:  echo _p('Paste a URL');  endif; ?>"/>
            </div>
            <span class="extra_info hide_it">
                <a href="#" class="pf_v_url_cancel"><?php echo _p('Cancel'); ?></a>
                <span style="display: none;" class="form-spin-it pf_v_url_processing"><i class="fa fa-spin fa-circle-o-notch"></i></span>
            </span>
        </div>
    </div>

    <div class="pf_video_caption" style="display:none;">
        <div class="table">
            <div class="table_right">
                <input class="form-control" type="text" placeholder="<?php echo _p('video_title'); ?>" name="val[title]" value="" id="title" size="40" />
            </div>
        </div>
    </div>
    <div id="pf_v_share_success_message" style="display: none">
        <div class="alert alert-info">
<?php echo _p('your_video_has_successfully_been_saved_and_will_be_published_when_we_are_done_processing_it'); ?>
            <div class="form-group pt-1">
                <a href="#" class="pf_v_message_cancel button btn-sm btn-default pull-right"><?php echo _p('Continue'); ?></a>
            </div>
        </div>
    </div>
<?php endif; ?>
