<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php if (empty ( $this->_aVars['aUploadCallback']['component_only'] )): ?>
    <div class="form-group js_upload_form_wrapper <?php if (! empty ( $this->_aVars['sCurrentPhoto'] )): ?>show-current<?php endif; ?>" id="js_upload_form_<?php echo $this->_aVars['sType']; ?>_wrapper" data-type="<?php echo $this->_aVars['sType']; ?>">
<?php if (! empty ( $this->_aVars['aUploadCallback']['label'] )): ?>
            <label><?php if (! empty ( $this->_aVars['aUploadCallback']['is_required'] )): ?>*<?php endif;  echo $this->_aVars['aUploadCallback']['label']; ?>:</label>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['sCurrentPhoto'] ) && ! empty ( $this->_aVars['iId'] )): ?>
            <div class="change_photo_block js_upload_form_current" id="js_current_image_wrapper">
<?php if ($this->_aVars['bImageClickable']): ?>
                    <a style="background-image: url('<?php echo $this->_aVars['sCurrentPhoto']; ?>');" href="<?php echo $this->_aVars['sCurrentPhoto']; ?>" class="thickbox"></a>
<?php else: ?>
                    <span style="background-image: url('<?php echo $this->_aVars['sCurrentPhoto']; ?>');"></span>
<?php endif; ?>
                <div class="extra_info">
<?php (($sPlugin = Phpfox_Plugin::get('core.template_block_upload_form_action_1')) ? eval($sPlugin) : false); ?>

                    <a class="text-uppercase fw-bold change_photo" href="javascript:void(0);" onclick="$Core.uploadForm.toggleForm(this);return false;">
                        <i class="ico ico-photo-plus"></i>&nbsp;
<?php echo _p('change_photo'); ?>
                    </a>

<?php (($sPlugin = Phpfox_Plugin::get('core.template_block_upload_form_action_2')) ? eval($sPlugin) : false); ?>

<?php if (empty ( $this->_aVars['aUploadCallback']['is_required'] )): ?>
                        <a href="javascript:void(0);" class="remove" onclick="$Core.uploadForm.deleteImage(this,'<?php echo $this->_aVars['sType']; ?>','<?php echo $this->_aVars['sRemoveField']; ?>'); return false;">
                            <i class="ico ico-trash-o"></i>&nbsp;
<?php echo _p('delete'); ?>
                        </a>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('core.template_block_upload_form_action_3')) ? eval($sPlugin) : false); ?>
                </div>
            </div>
<?php endif; ?>

        <div class="table_right js_upload_form" id="js_upload_form">
<?php endif; ?>
            <div class="<?php if (! empty ( $this->_aVars['aUploadCallback']['style'] )):  echo $this->_aVars['aUploadCallback']['style']; ?>-<?php endif; ?>dropzone-component dont-unbind <?php if (! empty ( $this->_aVars['sDropzoneClass'] )):  echo $this->_aVars['sDropzoneClass'];  endif; ?>" id="<?php echo $this->_aVars['sType']; ?>-dropzone<?php if (! empty ( $this->_aVars['iId'] )): ?>_<?php echo $this->_aVars['iId'];  endif; ?>"
                 data-component="dropzone"
                 data-dropzone-id="<?php echo $this->_aVars['sType'];  if (! empty ( $this->_aVars['iId'] )): ?>_<?php echo $this->_aVars['iId'];  endif; ?>"
                 data-url="<?php echo $this->_aVars['aUploadCallback']['upload_url']; ?>"
                 data-param-name="<?php echo $this->_aVars['aUploadCallback']['param_name']; ?>"
                 data-max-files="<?php echo $this->_aVars['aUploadCallback']['max_file']; ?>"
                 data-clickable=".dropzone-button-<?php echo $this->_aVars['sType'];  if (! empty ( $this->_aVars['iId'] )): ?>_<?php echo $this->_aVars['iId'];  endif; ?>"
                 data-preview-template="#dropzone-preview-template-<?php echo $this->_aVars['sType']; ?>"
                 data-auto-process-queue="<?php echo $this->_aVars['aUploadCallback']['upload_now']; ?>"
                 data-upload-multiple="false"
                 data-item-id="<?php echo $this->_aVars['iId']; ?>"
<?php if (! empty ( $this->_aVars['bForceConvertFile'] )): ?>
                    data-force-convert-file="1"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['sParentElementId'] )): ?>
                    data-parent-element-id="<?php echo $this->_aVars['sParentElementId']; ?>"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['bKeepHiddenInput'] )): ?>
                    data-keep-hidden-input="1"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['sHiddenInputName'] )): ?>
                    data-hidden-input-name="<?php echo $this->_aVars['sHiddenInputName']; ?>"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['max_size'] )): ?>
                    data-max-size="<?php echo $this->_aVars['aUploadCallback']['max_size']; ?>"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['style'] )): ?>
                    data-upload-style="<?php echo $this->_aVars['aUploadCallback']['style']; ?>"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['submit_button'] )): ?>
                    data-submit-button="<?php echo $this->_aVars['aUploadCallback']['submit_button']; ?>"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['type_list_string'] )): ?>
                    data-accepted-files="<?php echo $this->_aVars['aUploadCallback']['type_list_string']; ?>"
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['on_remove'] )): ?>
                    data-on-remove="<?php echo $this->_aVars['aUploadCallback']['on_remove']; ?>"
<?php endif; ?>
<?php if (empty ( $this->_aVars['aUploadCallback']['js_events'] )): ?>
                    data-on-success="$Core.uploadForm.onSuccessUpload"
<?php else: ?>
<?php if (count((array)$this->_aVars['aUploadCallback']['js_events'])):  foreach ((array) $this->_aVars['aUploadCallback']['js_events'] as $this->_aVars['event'] => $this->_aVars['function']): ?>
                        data-on-<?php echo $this->_aVars['event']; ?>="<?php echo $this->_aVars['function']; ?>"
<?php endforeach; endif; ?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['extra_data'] )): ?>
<?php if (count((array)$this->_aVars['aUploadCallback']['extra_data'])):  foreach ((array) $this->_aVars['aUploadCallback']['extra_data'] as $this->_aVars['name'] => $this->_aVars['value']): ?>
                        data-<?php echo $this->_aVars['name']; ?>="<?php echo $this->_aVars['value']; ?>"
<?php endforeach; endif; ?>
<?php endif; ?>
                >
<?php if (! empty ( $this->_aVars['sCurrentPhoto'] ) && ! empty ( $this->_aVars['iId'] )): ?>
                    <a href="#" class="dismiss_upload js_hide_upload_form" onclick="$Core.uploadForm.dismissUpload(this, '<?php echo $this->_aVars['sType']; ?>');return false;">
                        <i class="ico ico-close-circle"></i>
                    </a>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['style'] ) && $this->_aVars['aUploadCallback']['style'] == 'mini'): ?>
                    <div class="dz-default dz-message dropzone-inner">
<?php if (! empty ( $this->_aVars['aUploadCallback']['first_description'] )): ?>
                            <p><b><?php echo $this->_aVars['aUploadCallback']['first_description']; ?></b></p>
<?php endif; ?>
                        <div class="btn btn-primary dropzone-button dropzone-button-<?php echo $this->_aVars['sType'];  if (! empty ( $this->_aVars['iId'] )): ?>_<?php echo $this->_aVars['iId']; ?> dropzone-button-<?php echo $this->_aVars['sType'];  endif; ?>"><?php echo _p('browse_three_dot'); ?></div>
                    </div>
<?php else: ?>
                    <div class="dz-default <?php if (empty ( $this->_aVars['aUploadCallback']['keep_form'] )): ?>dz-message<?php endif; ?>">
<?php if (! empty ( $this->_aVars['aUploadCallback']['use_browse_button'] )): ?>
                        <div class="dropzone-video-icon"><i class="<?php echo $this->_aVars['aUploadCallback']['upload_icon']; ?>"></i></div>
<?php else: ?>
                        <div class="dropzone-button-outer">
                            <div class="dropzone-button dropzone-button-<?php echo $this->_aVars['sType'];  if (! empty ( $this->_aVars['iId'] )): ?>_<?php echo $this->_aVars['iId']; ?> dropzone-button-<?php echo $this->_aVars['sType'];  endif; ?>"><i class="<?php echo $this->_aVars['aUploadCallback']['upload_icon']; ?>"></i></div>
                        </div>
<?php endif; ?>
                        <div class="dropzone-content-outer">
                            <div class="dropzone-content-info">
<?php if (! empty ( $this->_aVars['aUploadCallback']['first_description'] )): ?>
                                    <h4><?php echo $this->_aVars['aUploadCallback']['first_description']; ?></h4>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['type_description'] )): ?>
                                    <p><?php echo $this->_aVars['aUploadCallback']['type_description']; ?></p>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['max_size_description'] )): ?>
                                    <p><?php echo $this->_aVars['aUploadCallback']['max_size_description']; ?></p>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['extra_description'] ) && is_array ( $this->_aVars['aUploadCallback']['extra_description'] )): ?>
<?php if (count((array)$this->_aVars['aUploadCallback']['extra_description'])):  foreach ((array) $this->_aVars['aUploadCallback']['extra_description'] as $this->_aVars['sDescription']): ?>
                                        <p><?php echo $this->_aVars['sDescription']; ?></p>
<?php endforeach; endif; ?>
<?php endif; ?>
                            </div>
<?php if (! empty ( $this->_aVars['aUploadCallback']['use_browse_button'] )): ?>
                                <div class="btn btn-primary btn-gradient dropzone-button dropzone-button-<?php echo $this->_aVars['sType'];  if (! empty ( $this->_aVars['iId'] )): ?>_<?php echo $this->_aVars['iId']; ?> dropzone-button-<?php echo $this->_aVars['sType'];  endif; ?>"><?php echo _p('browse_three_dot'); ?></div>
<?php endif; ?>
                        </div>
                        <a href="javascript:void(0)" id="dropzone-cancel-upload" class="dont-unbind" style="display: none"><?php echo _p('stop_uploading'); ?></a>
                    </div>
<?php if (empty ( $this->_aVars['aUploadCallback']['keep_form'] )): ?>
                        <div class="dropzone-button outer dropzone-button-<?php echo $this->_aVars['sType'];  if (! empty ( $this->_aVars['iId'] )): ?>_<?php echo $this->_aVars['iId']; ?> dropzone-button-<?php echo $this->_aVars['sType'];  endif; ?>">
                            <div class="inner">
                                <i class="<?php echo $this->_aVars['aUploadCallback']['upload_icon']; ?>"></i>
                            </div>
                        </div>
<?php endif; ?>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('core.template_block_upload_form_dropzone_component')) ? eval($sPlugin) : false); ?>

            </div>

<?php if (! empty ( $this->_aVars['aUploadCallback']['style'] ) && $this->_aVars['aUploadCallback']['style'] == 'mini'): ?>
                <div class="extra_info">
<?php if (! empty ( $this->_aVars['aUploadCallback']['type_description'] )): ?>
                        <p class="help-block"><?php echo $this->_aVars['aUploadCallback']['type_description']; ?></p>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aUploadCallback']['max_size_description'] )): ?>
                        <p class="help-block">
<?php echo $this->_aVars['aUploadCallback']['max_size_description']; ?>
                        </p>
<?php endif; ?>
                </div>
<?php endif; ?>
            <!-- Dropzone template -->
            <div id="dropzone-preview-template-<?php echo $this->_aVars['sType']; ?>" style="display: none;">
<?php if (! empty ( $this->_aVars['aUploadCallback']['preview_template'] )): ?>
<?php echo $this->_aVars['aUploadCallback']['preview_template']; ?>
<?php else: ?>
                    <div class="dz-preview dz-file-preview">
                        <div class="dz-image"><img data-dz-thumbnail /></div>
                        <div class="dz-filename"><span data-dz-name ></span></div>
                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                        <div class="dz-error-message"><span data-dz-errormessage></span></div>
                        <span class="dz-error-icon hide"><i class="ico ico-info-circle-alt"></i></span>
                        <input class="dz-form-file-id" type="hidden" id="js_upload_form_file_<?php echo $this->_aVars['sType']; ?>" />
                    </div>
<?php endif; ?>
            </div>
<?php if (empty ( $this->_aVars['aUploadCallback']['component_only'] )): ?>
        </div>
    </div>
<?php endif; ?>
