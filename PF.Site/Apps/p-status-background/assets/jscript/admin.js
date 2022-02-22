var pstatusbg_admin = {
    iTotalError: 0,
    isSubmit: false,
    dropzoneOnSending: function (data, xhr, formData) {
        $('#js_collection_form').find('input').each(function () {
            formData.append($(this).prop('name'), $(this).val());
        });
    },
    dropzoneOnSuccess: function (ele, file, response) {
        this.processResponse(ele, file, response);
    },

    dropzoneOnError: function (ele, file) {
        pstatusbg_admin.iTotalError++;
        return false;
    },
    dropzoneOnRemoveFile: function (ele, file) {
        if (file.status == 'error') {
            pstatusbg_admin.iTotalError--;
        }
    },
    dropzoneOnInit: function(ele) {
        var dropzoneId = $(ele).data('dropzone-id');
        setTimeout(function(){
            if (typeof $Core.dropzone.instance[dropzoneId] != 'undefined') {
                $Core.dropzone.instance[dropzoneId].options.thumbnailWidth = 120;
                $Core.dropzone.instance[dropzoneId].options.thumbnailHeight = 67.5;
            }
        },1000);
    },
    dropzoneQueueComplete: function () {
        if (!pstatusbg_admin.isSubmit) return false;
        var id = $('#js_collection_id').val();
        setTimeout(function(){
            if (!pstatusbg_admin.iTotalError) {
                $('#js_collection_form').submit();
            } else {
                $('#js_collection_submit').removeClass('disabled').removeAttr('disabled');

                $('#p-statusbg-dropzone').find('.dz-preview:not(.dz-error)').remove();
                pstatusbg_admin.refreshBackgrounds(id);
            }
            pstatusbg_admin.isSubmit = false;
        },1000);
    },
    processResponse: function (t, file, response) {
        response = JSON.parse(response);
        if (typeof response.id !== 'undefined') {
            file.item_id = response.id;
        }
        // show error message
        if (typeof response.errors != 'undefined') {
            for (var i in response.errors) {
                if (response.errors[i]) {
                    $Core.dropzone.setFileError('pstatusbg', file, response.errors[i]);
                    return;
                }
            }
            $('#p-statusbg-dropzone').removeClass('dz-started');
        }
        return file.previewElement.classList.add('dz-success');
    },
    refreshBackgrounds: function(id) {
        var _i, _len, _new = [],
            _files = $Core.dropzone.instance['pstatusbg'].files;
        if (_files.length) {
            for (_i = 0, _len = _files.length; _i < _len; _i++) {
                if (_files[_i].status != 'success') {
                    _new.push(_files[_i]);
                }
            }
            $Core.dropzone.instance['pstatusbg'].files = _new;
        }
        $.ajaxCall('pstatusbg.refreshBackgrounds','id='+id);
        return true;
    }
};

$Ready(function(){
    if ($('#js_collection_form').length) {
        $('#js_p_statusbg_is_default input[name="val[is_default]"]').on('change',function(){
           var input = $(this),
               isChecked = input.prop('checked'),
               active_input = $('#js_p_statusbg_is_active input[name="val[is_active]"]'),
               isActiveChecked = active_input.prop('checked');
           if (isChecked) {
               $('#js_p_statusbg_is_default_help').show();
               if (!isActiveChecked) {
                   $('#js_p_statusbg_is_active .js_item_active:first').trigger('click');
               }
               $('#js_p_statusbg_is_active').addClass('p-statusbg-disabled-click');
           } else {
               $('#js_p_statusbg_is_default_help').hide();
               if (isActiveChecked) {
                   $('#js_p_statusbg_is_active .js_item_active:first').trigger('click');
               }
               $('#js_p_statusbg_is_active').removeClass('p-statusbg-disabled-click');
           }
        });
        $('#js_p_statusbg_is_active input[name="val[is_active]"]').on('change',function(){
            var input = $(this),
                isChecked = input.prop('checked'),
                default_input = $('#js_p_statusbg_is_default input[name="val[is_default]"]'),
                isDefaultChecked = default_input.prop('checked');
            if (!isDefaultChecked) {
                if (isChecked) {
                    $Core.ajax('PStatusBg.getTotalActiveCollection', {
                        type: 'POST',
                        params: {
                            id: $('#js_collection_id').val()
                        },
                        success: function (sOutput) {
                            var oResult = JSON.parse(sOutput);
                            if (oResult.total_active == 2) {
                                $('#js_p_statusbg_is_active_help').show();
                                $('#js_p_statusbg_is_active .js_item_active:first').trigger('click');
                            } else {
                                $('#js_p_statusbg_is_active_help').hide();
                            }
                        }
                    });
                }
            } else {
                $('#js_p_statusbg_is_active_help').hide();
            }
        });
        $('#js_collection_submit').off('click').on('click',function() {
            pstatusbg_admin.isSubmit = true;
            var oForm = $('#js_collection_form');
            $(this).addClass('disabled').attr('disabled', true);
            if (pstatusbg_admin.iTotalError) {
                tb_show(oTranslations['error'], '', null, oTranslations['please_remove_all_error_files_first']);
                $(this).removeClass('disabled').removeAttr('disabled');
                pstatusbg_admin.isSubmit = false;
                return false;
            }
            if (oForm.find('input[type="text"]:first').val() == "") {
                tb_show(oTranslations['error'], '', null, oTranslations['title_of_collection_is_required']);
                $(this).removeClass('disabled').removeAttr('disabled');
                pstatusbg_admin.isSubmit = false;
                return false;
            }
            if (typeof $Core.dropzone.instance['pstatusbg'] != 'undefined') {
                var _files = $Core.dropzone.instance['pstatusbg'].files;
                if (_files.length) {
                    $Core.dropzone.instance['pstatusbg'].processQueue();
                } else {
                    oForm.submit();
                }
            } else {
                oForm.submit();
            }
            return true;
        });
    }
});