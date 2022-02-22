if (typeof can_post_video_on_profile == 'undefined') {
  var can_post_video_on_profile = 0;
}
if (typeof can_post_video_on_page == 'undefined') {
  var can_post_video_on_page = 0;
}
if (typeof can_post_video_on_group == 'undefined') {
  var can_post_video_on_group = 0;
}
if (typeof v_phrases == 'undefined') {
  var v_phrases = {};
}

var videoUpload = function (e) {
  $('#pf_video_add_error').hide();
  var pf_v_video_url = $('.pf_v_video_url');
  $('.process-video-upload').addClass('button_not_active').attr('disabled', true).val(v_phrases.uploading);
  pf_v_video_url.hide();

  $('#pf_video_id_temp').remove();
  pf_v_video_url.prepend(
    '<div><input id="pf_video_id_temp" type="hidden" name="val[pf_video_id]" value=""></div>');

  $('.pf_select_video').hide();
  $('.pf_v_video_submit').hide();

  $('.pf_process_form').show();

  $('.pf_select_video .extra_info').addClass('hide_it');

  var f = $('.pf_select_video').parents('form:first');
  f.find('.upload_message_danger').remove();
  f.find('.error_message').remove();
  $('#pf_select_video_no_ajax').find('.upload_message_danger').remove();

  var files = e.target.files || e.dataTransfer.files;
  if (files.length) {
    for (var i = 0, f; f = files[i]; i++) {
      var file = f;
      var data = new FormData();
      data.append('ajax_upload', file);
      $.ajax({
        type: 'POST',
        url: PF.url.make('/video/upload'),
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
          var xhr = $.ajaxSettings.xhr();
          xhr.upload.onprogress = function (e) {
            var percent = Math.floor(e.loaded / e.total * 100);
            if (percent < 98) {
              $('.pf_process_form > span').width(percent + '%');
            }
          };
          return xhr;
        },
        headers: {
          'X-File-Name': encodeURIComponent(file.name),
          'X-File-Size': file.size,
          'X-File-Type': file.type,
          'X-Post-Form': $(($('#video_page_upload').length
            ? '#video_page_upload'
            : '#js_activity_feed_form')).getForm(),
        },
        error: function (error) {
          var eJson = {};
          if (typeof (error.responseJSON) !== 'undefined') {
            eJson = error.responseJSON;
          }
          $('#pf_video_id_temp').remove();
          $('.pf_select_video').show();
          $('.pf_process_form').hide();
          $('.pf_upload_form').show();
          var f = $('.pf_select_video').parents('form:first');
          if (typeof (eJson.error) !== 'undefined') {
            f.prepend('<div class="alert alert-danger upload_message_danger">' +
              eJson.error + '</div>');
            $('#pf_select_video_no_ajax').prepend('<div class="alert alert-danger upload_message_danger">' +
              eJson.error + '</div>');
          }
          $('.select-video').val('');
          $('.pf_process_form > span').width('2%');
          $('.pf_select_video .extra_info').removeClass('hide_it');
        },
        success: function (data) {
          $Core.Video.toggleLocationPlace(true);
          $Core.Video.addShareVideoBtnInFeed();

          // upload form
          $('.pf_v_video_submit').show();

          $('.select-video').val('');
          $('.pf_process_form > span').width('100%');
          $('#pf_video_id_temp').val(data.id);
          $('.pf_video_caption').show();
          $('.pf_video_caption > div.table').show();

          $('.pf_process_form').hide();
          $('.pf_select_video').hide();
          $('.pf_video_message').show();

          // activity form
          $('.process-video-upload').removeClass('button_not_active').attr('disabled', false).val(v_phrases.share);
        },
      });
    }
  } else {
    $('#pf_video_id_temp').remove();
    $('.pf_select_video').show();
    $('.pf_process_form').hide();
    $('.pf_upload_form').show();
    $('.select-video').val('');
    $('.pf_process_form > span').width('2%');
    $('.pf_select_video .extra_info').removeClass('hide_it');
  }
};

var core_videos_onchangeDeleteCategoryType = function (type) {
  if (type == 2) {
    $('#category_select').show();
  } else {
    $('#category_select').hide();
  }
};

var core_videos_load_videos = function () {
  $('#page_route_video #content').show();
  $('#page_route_v #content').show();
};

$Event(function () {
  if ($('#page_route_video').length || $('#page_route_v').length) {
    core_videos_load_videos();
  }
});

$Ready(function () {
  (function (eles) {
    eles.each(function (index, ele) {
      var $ele = $(ele),
        parent = $ele.parent();
      $ele.data('built', true);
      $ele.css("width", parent.width());
      $ele.css("height", parent.width() / (16 / 9));
    });
  })($('.facebook_iframe').not('.built'));

  if ($('#page_route_video').length || $('#page_route_v').length) {
    core_videos_load_videos();
  }
  //toggle content detail page
  $Core.Video.toggleViewContentCollapse();

  if (typeof can_post_video === "undefined" || can_post_video === 1) {
    // Upload routine for videos
    var m = $(
        '#page_core_index-member .activity_feed_form_attach, #panel .activity_feed_form_attach'),
      p = $('#page_pages_view .activity_feed_form_attach'),
      g = $('#page_groups_view .activity_feed_form_attach'),
      up = $('#page_profile_index .activity_feed_form_attach'),
      v = $('.select-video-upload'),
      b = $('#pf_upload_form_input');

    if (m.length && !v.length && can_post_video_on_profile == 1) {
      var html = '<li><a href="#" class="select-video-upload" rel="custom"><span class="activity-feed-form-tab">' +
        v_phrases.video + '</span></a></li>';
      m.append(html);
    }

    if (p.length && !v.length && can_post_video_on_page == 1) {
      var html = '<li><a href="#" class="select-video-upload" rel="custom"><span class="activity-feed-form-tab">' +
        v_phrases.video + '</span></a></li>';
      p.append(html);
    }

    if (g.length && !v.length && can_post_video_on_group == 1) {
      var html = '<li><a href="#" class="select-video-upload" rel="custom"><span class="activity-feed-form-tab">' +
        v_phrases.video + '</span></a></li>';
      g.append(html);
    }

    if (up.length && !v.length && can_post_video_on_profile == 1) {
      var html = '<li><a href="#" class="select-video-upload" rel="custom"><span class="activity-feed-form-tab">' +
        v_phrases.video + '</span></a></li>';
      up.append(html);
    }
  }

  $('.activity_feed_form_attach a:not(.select-video-upload)').on('click',function () {
    $('.process-video-upload').remove();
    $('.activity_feed_form .upload_message_danger').remove();
    $('.activity_feed_form .error_message').remove();
  });

  $('.select-video-upload').on('click',function () {
    if (typeof can_post_video === "number" && can_post_video === 0) {
      return false;
    }

    $('.activity_feed_form_attach a.active').removeClass('active');
    $(this).addClass('active');
    $('.global_attachment_holder_section').hide().removeClass('active');
    $('.activity_feed_form_button').show();
    $('.activity_feed_form_button_position').show();
    $('#activity_feed_submit').hide();
    $('.error_message').hide();
    // hide tag friend
    $('#btn_display_with_friend').hide();

    //update status textarea
    if ($('#global_attachment_status textarea').length && $('.activity_feed_form_button_status_info textarea').length) {
      var sStatusUpdateTextarea = $('#global_attachment_status textarea').val();
      var sStatusUpdateValue = $('#global_attachment_status_value').html();
      if (((typeof $bButtonSubmitActive == "boolean") && $bButtonSubmitActive) && (sStatusUpdateTextarea != sStatusUpdateValue) && !empty(sStatusUpdateTextarea)) {
        $('.activity_feed_form_button_status_info textarea').val(sStatusUpdateTextarea);
        $bButtonSubmitActive = false;
      }
    }

    var btn_display_check_in = $('#btn_display_check_in');
    if (typeof can_checkin_in_video === 'undefined' || !can_checkin_in_video) {
      btn_display_check_in.hide();
      btn_display_check_in.removeClass('is_active');
      $('#js_add_location, #js_location_input, #js_location_feedback').hide();
      $('#hdn_location_name, #val_location_name ,#val_location_latlng').val('');
    } else {
      $('#btn_display_check_in').show();
    }

    $('#activity_feed_textarea_status_info').attr('placeholder', $('<div />').html(v_phrases.say).text());

    var l = $('#global_attachment_videos');
    if (l.length == 0) {
      var m = $(
        '<div id="global_attachment_videos" class="global_attachment_holder_section" style="display:block;"><div style="text-align:center;"><i class="fa fa-spin fa-circle-o-notch"></i></div></div>');
      $('.activity_feed_form_holder').prepend(m);

      $.ajax({
        url: PF.url.make('/video/share'),
        contentType: 'application/json',
        data: 'is_ajax_browsing=1',
        success: function (e) {
          m.html(e.content);
          m.find('._block').remove();

          $('.process-video-upload').remove();
          $Core.loadInit();

          var uv = $('.pf_v_video_url'), fv = $('.activity_feed_form_button');

          if (uv.length) {
            $(document).on('keydown change', '#video_url', function () {
              var _this = this, isNotChange = true, eleName = $(_this).attr('name');

              setTimeout(function () {
                var eleVal = $(_this).val();
                if (!empty(eleVal)) {
                  isNotChange = false;

                  if (typeof $Core.reloadValidation.changedEleData['js_activity_feed_form'] === 'undefined') {
                    $Core.reloadValidation.changedEleData['js_activity_feed_form'] = {};
                  }

                  $Core.reloadValidation.changedEleData['js_activity_feed_form'][eleName] = true;
                }

                if (isNotChange && $Core.reloadValidation.changedEleData['js_activity_feed_form'].hasOwnProperty(eleName)) {
                  delete $Core.reloadValidation.changedEleData['js_activity_feed_form'][eleName];
                }

                $Core.reloadValidation.preventReload();
              }, 100);
            });

            $('.pf_v_url_cancel').on('click', function () {
              delete $Core.reloadValidation.changedEleData['js_activity_feed_form']['val[url]'];
              $Core.reloadValidation.preventReload();
            });
          }

          if (fv.length) {
            $(document).on('keydown change', '#activity_feed_textarea_status_info',function () {
              var _this = this, isNotChange = true, eleName = $(_this).attr('name');

              setTimeout(function () {
                if (!empty($(_this).val())) {
                  isNotChange = false;
                  if (typeof $Core.reloadValidation.changedEleData['js_activity_feed_form'] === 'undefined') {
                    $Core.reloadValidation.changedEleData['js_activity_feed_form'] = {};
                  }
                  $Core.reloadValidation.changedEleData['js_activity_feed_form'][eleName] = true;
                }
                if (isNotChange && $Core.reloadValidation.changedEleData['js_activity_feed_form'].hasOwnProperty(eleName)) {
                  delete $Core.reloadValidation.changedEleData['js_activity_feed_form'][eleName];
                }
                $Core.reloadValidation.preventReload();
              }, 100);
            });
          }

        },
      });
    } else {
      l.show();
    }

    $Core.Video.toggleLocationPlace(true);
    return false;
  });

  $('.process-video-upload').on('click',function () {
    var t = $(this);
    if (t.hasClass('button_not_active')) {
      return false;
    }
    var f = $(this).parents('form:first');
    t.addClass('button_not_active');
    t.hide();
    t.before(
      '<span class="form-spin-it video_form_processing"><i class="fa fa-spin fa-circle-o-notch"></i></span>');
    f.find('.error_message').remove();
    f.find('.upload_message_danger').remove();
    $('#pf_video_add_error_link').hide();
    $('#pf_video_add_error_link').html('');
    var form_params = f.serializeArray();
    var params = {};
    for (var i = 0; i < form_params.length; i++) {
      params[form_params[i].name] = form_params[i].value;
    }
    var isEditSchedule = $(this).closest('#js_edit_schedule_form').length;
    params['is_ajax_post'] = 1;
    if (isEditSchedule) {
      $('#js_activity_feed_edit_form').submit();
      return false;
    }
    $Core.ajax('v.shareFeed', {
      type: 'POST',
      params: params,
      success: function (e) {
        if (e) {
          e = $.parseJSON(e);
        }
        if (typeof (e.error) == 'string') {
          f.prepend(e.error);
          t.show();
          t.parent().find('.form-spin-it').remove();
          return;
        }
        $('.form-spin-it').remove();
        eval(e.run);
      },
    });
    return false;
  });

  if (typeof b !== "undefined" && b.length && !b.hasClass('built')) {
    b.addClass('built');
    b.prepend(
      '<input id="divFileInput" type="file" class="select-video feed-attach-form-file">');

    $('#divFileInput.select-video')[0].addEventListener('change', videoUpload);
  }

  var url_changed = function () {
    $('#pf_video_add_error').hide();
    $('.pf_v_video_url .extra_info').removeClass('hide_it');
    $('.pf_select_video').slideUp();
    $Core.Video.toggleLocationPlace(true);
    $Core.Video.addShareVideoBtnInFeed();
    $('#video_url').trigger('change');
  };

  $('#video_url').off('keyup').on('keyup',function () {
    if ($(this).val().length === 0) {
      $('.pf_v_url_cancel').show();
    }
  });

  $('#video_url').off('change').on('change',function () {
    var url = $(this).val();
    url = url.replace(/\\|\'|\(\)|\"|$|\#|%|<>/gi, '');
    if (!url) return false;
    $('.pf_v_url_cancel').hide();
    $('.pf_v_url_processing').show();
    var oThis = $(this);
    $Core.ajax('v.validationUrl',
      {
        type: 'POST',
        params:
          {
            url: url,
          },
        success: function (sOutput) {
          $('.pf_v_url_cancel').show();
          $('.pf_v_url_processing').hide();
          var oOutput = $.parseJSON(sOutput);
          if (oOutput.status == 'SUCCESS') {
            // activity form
            $('.process-video-upload').removeClass('button_not_active');

            // upload form
            $('.pf_v_video_submit').show();
            $('#pf_video_add_error_link').hide();
            $('#pf_video_add_error_link').html('');

            if (oOutput.title != '') {
              $('#title').val(oOutput.title);
            }
            if (oOutput.description != '') {
              $('#text').val(oOutput.description);
              if (typeof (CKEDITOR) !== 'undefined') {
                if (typeof (CKEDITOR.instances['text']) !== 'undefined') {
                  oOutput.description = oOutput.description.replace(/(?:\r\n|\r|\n)/g, '<br />');
                  CKEDITOR.instances['text'].setData(oOutput.description);
                }
              }
            }
            if (oOutput.default_image != '') {
              $('#video_default_image').val(oOutput.default_image);
            }
            if (oOutput.embed_code != '') {
              $('#video_embed_code').val(oOutput.embed_code);
            }
          } else {
            $('.pf_v_video_submit').hide();
            if (typeof (oOutput.error_message) !== 'undefined') {
              $('#pf_video_add_error_link').html(oOutput.error_message);
              $('#pf_video_add_error_link').show();
              oThis.closest('form').find('.error_message').remove();
            } else {
              $('#pf_video_add_error_link').hide();
            }

          }
        },
      });
  });

  $('#video_url').off('focus').on('focus', url_changed);

  $('.pf_v_url_cancel').on('click',function () {
    $(this).parent().addClass('hide_it');
    // upload form
    var pf_video_add_error_link = $('#pf_video_add_error_link');
    pf_video_add_error_link.hide();
    pf_video_add_error_link.html('');
    $('.pf_select_video').slideDown();
    $('.pf_v_video_url #video_url').val('');
    var f = $(this).parents('form:first');
    f.find('.error_message').remove();
    f.find('.upload_message_danger').remove();
    $('.process-video-upload').hide();
    $('#title').val('');
    $('#text').val('');
    if (typeof (CKEDITOR) !== 'undefined') {
      if (typeof (CKEDITOR.instances['text']) !== 'undefined') {
        CKEDITOR.instances['text'].setData('');
      }
    }
    $('#video_default_image').val('');
    $('#video_embed_code').val('');

    return false;
  });

  $('.pf_v_upload_cancel').on('click',function () {
    $(this).parent().addClass('hide_it');
    $('.pf_v_video_url').slideDown();
    var f = $(this).parents('form:first');
    f.find('.upload_message_danger').remove();
    f.find('.error_message').remove();
    $('#pf_select_video_no_ajax').find('.upload_message_danger').remove();
    $('#pf_video_id_temp').val('');

    return false;
  });

  $('.pf_v_message_cancel').on('click',function () {
    // reset dropzone
    if (typeof $Core.dropzone.instance.v !== 'undefined') {
      $Core.dropzone.instance.v.removeAllSuccessFiles();
    }
    // hide success message
    $('#pf_v_share_success_message').fadeOut('fast', function () {
      // show upload form
      $('.js_upload_form_file_v').closest('.js_upload_form').fadeIn();
      // share video via url
      $('#video_url').fadeIn();
    });

    $('.pf_v_video_url').show();
    $('.pf_select_video').show();
    $('.pf_video_message').hide();
    $('.process-video-upload').remove();
    $('#pf_video_id_temp').remove();
    $('#title').val('');

    return false;
  });

  $('.pf_v_success_continue').on('click',function () {
    $('#pf_v_success_message').hide();
    $('.pf_upload_form').slideDown();
    $('#title').val('');
    $('#text').val('');
    if (typeof (CKEDITOR) !== 'undefined') {
      if (typeof (CKEDITOR.instances['text']) !== 'undefined') {
        CKEDITOR.instances['text'].setData('');
      }
    }
    $('#video_default_image').val('');
    $('#video_embed_code').val('');
    if (typeof $Core.reloadValidation === "object") {
      $Core.reloadValidation.reset(true, 'core_js_video_form', true);
      $Core.reloadValidation.preventReload(true);
      $Core.reloadValidation.init();
      $Core.reloadValidation.store();
    }
    return false;
  });

  $('.pf_v_upload_success_cancel').on('click',function () {
    var pf_video_id = $('#pf_video_id_temp').val();
    $('.pf_video_message').hide();
    $('.pf_v_video_submit').hide();
    $('.pf_video_caption').hide();
    $('.process-video-upload').remove();
    $('#pf_video_id_temp').remove();
    $('.pf_process_form > span').width('2%');
    $('.pf_v_video_url').slideDown();
    $('.pf_select_video').slideDown();
    $Core.ajax('v.cancelUpload',
      {
        type: 'POST',
        params:
          {
            pf_video_id: pf_video_id,
          },
        success: function (sOutput) {
        },
      });
    return false;
  });

  $Core.Video.initVideoPlayer();
  $Core.Video.initVideoEmbed();
});

$Core.Video = {
  canCheckValidate: ($Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined"),
  reloadPageAfterCreateVideoUrl: function(url) {
      if (this.canCheckValidate) {
          $Core.reloadValidation.reset(true);
      }
      if (typeof url === "string") {
          window.location.href = url;
      } else {
          location.reload();
      }
  },
  toggleLocationPlace: function (useLocationAtStatusInfo) {
    var statusInfo = $('.activity_feed_form_button_status_info');
    if (statusInfo.length === 0) {
      return;
    }
    var textInput = statusInfo.find('textarea');
    if (textInput.length && empty(textInput.val()) && typeof $sCssHeight !== "undefined") {
      textInput.css({height: $sCssHeight})
    }
    if (typeof useLocationAtStatusInfo === 'boolean' && useLocationAtStatusInfo) {
      statusInfo.show();
      $('#js_location_feedback').addClass('hide');
    } else {
      statusInfo.hide();
      $('#js_location_feedback').removeClass('hide').show();
    }
  },
  processUploadSuccess: function (ele, file, response) {
    // show user status textarea
    $Core.Video.toggleLocationPlace(true);
    // show submit button
    $('.pf_v_video_submit').show();
    // append to form
    $('#core_js_video_form').append('<div><input id="pf_video_id_temp" type="hidden" name="val[pf_video_id]" value="' +
      response.id + '"></div>');
    // append to share in feed form
    $('#js_activity_feed_form').append('<div><input id="pf_video_id_temp" type="hidden" name="val[pf_video_id]" value="' +
      response.id + '"></div>');
    // append to schedule edit form
    $('#js_activity_feed_edit_form').append('<div><input id="pf_video_id_temp" type="hidden" name="val[pf_video_id]" value="' +
        response.id + '"></div>');

    $('.pf_video_caption').show();
    $('.process-video-upload').removeClass('button_not_active').attr('disabled', false).val(v_phrases.share);

    // remove error message
    $('[data-dz-errormessage]').html('');

    // add share video button
    if (!($('.pf_select_video').closest('.pf_upload_form').length)) {
      this.addShareVideoBtnInFeed(true);
    }

  },
  processError: function (ele, file, response) {
    this.addShareVideoBtnInFeed();
  },
  processAddedFile: function (ele, file, response) {
    // hide video input
    if ($Core.Video.canCheckValidate) {
      $Core.Video.reloadVideoValidation.validate(true);
    }

    $('#video_url').slideUp();
    if (typeof $Core.dropzone.instance["v"] !== "undefined") {
      //Trigger queue
      setTimeout(function() {
        $Core.dropzone.instance["v"].processQueue();
      });
    }
  },
  addShareVideoBtnInFeed: function (btnIsActive) {
    $('.process-video-upload').remove();
    if(!$('#activity_feed_submit').closest('.button_position_edit_schedule').length) {
      $('#activity_feed_submit').before('<a href="#" class="btn btn-gradient btn-sm btn-primary ' +
          (typeof btnIsActive === 'boolean' && btnIsActive
              ? ''
              : 'button_not_active') + ' process-video-upload">' +
          v_phrases.share + '</a>');
    }
    $Core.loadInit();
  },
  showScheduleUploadSection: function() {
    $('#js_schedule_upload_video').hide();
    $('#js_schedule_video_upload').show();
    $('#js_schedule_video_info').hide();
    $('#js_schedule_change_video').val(1).trigger('change');
  },
  processAfterSharingVideoInFeed: function (message, isSchedule, isUrl) {
    if (typeof isUrl === "undefined" || !isUrl) {
      $('#pf_v_share_success_message').show();
    } else {
      $('.pf_v_url_cancel').trigger('click');
    }
    $('.pf_video_message').hide();
    $('.pf_v_video_info').hide();
    // empty status
    var statusInfo = $('#activity_feed_textarea_status_info');
    if (statusInfo.length) {
      if (statusInfo.hasClass('contenteditable')) {
        statusInfo.empty();
        statusInfo.siblings('textarea').val('');
      } else {
        statusInfo.val('');
      }
    }

    // empty tag friends
    if (typeof $Core.FeedTag !== 'undefined' &&
      typeof $Core.FeedTag.iFeedId !== 'undefined' &&
      $('#feed_input_tagged_' + $Core.FeedTag.iFeedId).length) {
      $('#feed_input_tagged_' + $Core.FeedTag.iFeedId).val('');
      $('.js_feed_tagged_items').html('');
      $('.js_tagged_review').html('').hide().removeClass('tagged_review');
      $('.js_feed_compose_tagging').hide();
      $('.js_btn_display_with_friend').removeClass('is_active');
    }
    // empty schedule
    if (typeof $Core.FeedSchedule !== "undefined") {
      $Core.FeedSchedule.emptyScheduleForm();
    }
    //remove e-gift after submit
    if ($('#js_core_egift_id').length) {
      $('#js_core_egift_id').val('');
    }
    $('.activity_feed_form_button_position').show();
    $('#hdn_location_name, #val_location_name ,#val_location_latlng, #video_url').val('');
    $('.js_location_feedback').html('').hide();
    $('#btn_display_check_in').removeClass('is_active');

    $('.pf_video_caption').hide();
    $('.js_upload_form_file_v').closest('.js_upload_form').slideUp();
    //Reset reload validation
    if (typeof $Core.reloadValidation === "object") {
      $Core.reloadValidation.reset(true, 'js_activity_feed_form');
      $Core.reloadValidation.preventReload(true);
    }
    if (isSchedule && message) {
      $('.pf_v_message_cancel').trigger('click');
      window.parent.sCustomMessageString = message;
      tb_show(oTranslations['notice'], $.ajaxBox('core.message', 'height=150&width=300'));
      setTimeout('tb_remove()', 2000);
      $Core.resetActivityFeedForm();
      $Core.loadInit();
    }
  },
  processRemoveButton: function () {
    $('.pf_video_caption').fadeOut('fast');
    $('#video_url').fadeIn();
    $('#pf_video_id_temp').remove();
    $('.pf_v_video_submit').hide();

    if ($Core.Video.canCheckValidate) {
      $Core.Video.reloadVideoValidation.validate(false);
    }
  },
  playVideo: function (player) {
    if (player.paused) {
      player.play();
      $(player).parent().addClass('video-playing');
    } else {
      player.pause();
      $(player).parent().removeClass('video-playing');
    }
  },
  toggleViewContentCollapse: function () {
    //viewmore less content in detail
    if ($('.js_core_videos_view_content_collapse').length) {
      var collapse_desc = $('.js_core_videos_view_content_collapse .video-content'),
        collapse_category = $('.js_core_videos_view_content_collapse .video_category');

      if (collapse_desc.length) {
        if (55 < collapse_desc.height()) {
          collapse_desc.addClass('truncate-text');
          $('.js_core_videos_view_content_collapse').addClass('collapsed');
          $('.core-videos-view-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
        }
      }
      if (collapse_category.length) {
        if (35 < collapse_category.height()) {
          collapse_category.addClass('truncate-text');
          $('.js_core_videos_view_content_collapse').addClass('collapsed');
          $('.core-videos-view-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
        }
      }
      $('.js-core-video-action-collapse .js-item-btn-toggle-collapse').off('click').on('click', function () {
        console.log('xx');
        $('.js_core_videos_view_content_collapse').toggleClass('collapsed');
        if ($(this).hasClass('item-viewmore-btn')) {
          $(this).closest('.js-core-video-action-collapse').removeClass('has-viewmore').addClass('has-viewless');
        } else if ($(this).hasClass('item-viewless-btn')) {
          $(this).closest('.js-core-video-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
        }
      });
    }
  },
  videoJSPlayerObjects: [],
  initVideoPlayer: function (reInit) {
    var videoPlayer = $('.js-pf-video-embed'), path = getParam('sJsHome').replace('PF.Base', 'PF.Site');
    if (!videoPlayer.length) {
      return false;
    }
    if (typeof videojs === "undefined") {
      var script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = path + 'Apps/core-videos/assets/videojs/videojs.js';
      document.body.appendChild(script);

      var scriptIe = document.createElement('script');
      scriptIe.type = 'text/javascript';
      scriptIe.src = path + 'Apps/core-videos/assets/videojs/videojs-ie8.min.js';
      document.body.appendChild(scriptIe);
    }
    if (!$('#pf-video-videojs-css').length) {
      var style = document.createElement('link');
      style.href = path + 'Apps/core-videos/assets/videojs/videojs.css';
      style.type = 'text/css';
      style.rel = 'stylesheet';
      style.id = 'pf-video-videojs-css';
      document.body.appendChild(style);
    }

    var initVideoInterval = setInterval(function () {
      if (typeof videojs !== "undefined") {
        //Remove old player
        if (reInit) {
          $('.pf-video-player').remove();
          $('.js-pf-video-embed').removeClass('built');
        }
        $('.js-pf-video-embed:not(.built)').each(function () {
          var _this = $(this), unique = (new Date()).getTime() + Math.floor(Math.random() * 1000),
              isStreamingVideo = _this.hasClass('js-pf-video-mux-embed') || _this.data('m3u8'),
              videoClass = 'video-player-' + unique, videoEleId = 'player_' + _this.data('video-id') + '_' + unique;
          _this.addClass('built');
          _this.parent().prepend('<video data-video-id="' + _this.data('video-id') + '" id="' + videoEleId + '" class="pf-video-player ' + videoClass + ' video-js" style="' + _this.attr('style') + '" poster="' + _this.data('poster') + '"></video>');
          var videoJS = videojs(document.querySelector('.' + videoClass), {
            controls: true,
            autoplay: false,
            preload: 'auto',
            responsive: true,
            sources: [{
              type: isStreamingVideo ? 'application/x-mpegURL' : 'video/mp4',
              src: _this.data('src')
            }]
          });
          videoJS.on('loadstart', function () {
            var videoTag = $('#' + this.id()), resolutionX = videoTag.parent().find('.js-pf-video-embed').data('resolution-x'), resolutionY = videoTag.parent().find('.js-pf-video-embed').data('resolution-y');
            videoTag.closest('.item-media-outer').css({'padding-bottom': 0, height: 'auto'});
            videoTag.css({opacity: 1, visibility: 'visible'});
            if (resolutionX && resolutionY) {
              var newHeight = videoTag.closest('.fb_video_player ').width() * resolutionY / resolutionX;
              if (newHeight && ($Core.Video.isMobile || newHeight <= $(window).height() * 0.70)) {
                videoTag.css({height: newHeight.toString() + 'px'});
                videoTag.closest('.item-media-outer').find('video').css({height: (videoTag.closest('.fb_video_player ').width() * resolutionY / resolutionX).toString() + 'px'})
              }
            }
          });
          videoJS.on('play', function () {
            var playerId = this.id(), videoTag = $('#' + playerId), videoId = videoTag.data('video-id');
            $Core.Video.videoJSPlayerObjects.forEach(function (item) {
              if (item.id() !== playerId) {
                item.pause();
              }
            })
            //Trigger update total view
            if (videoId && !$('#page_v_play').length && !videoTag.prop('played')) {
              videoTag.prop('played', true);
              $.ajaxCall('v.updateVideoTotalView', 'video_id=' + videoId, 'POST');
            }
          });
          $Core.Video.videoJSPlayerObjects.push(videoJS);
        });
        clearInterval(initVideoInterval);
      }
    }, 200);
    $('._a_back').on('mouseup', function () {
      //Trigger when go back
      $Core.Video.initVideoPlayer(true);
    });
  },
  facebookEmbedObjects: [],
  isMobile: /iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i.test(navigator.userAgent),
  initFacebookEmbed: function () {
    setTimeout(function () {
      if (typeof FB !== "undefined") {
        window.fbAsyncInit = function () {
          FB.init({
            appId: typeof v_facebook_app_id !== "undefined" ? v_facebook_app_id : '571530043470390',
            xfbml: true,
            version: 'v9.0'
          });

          // Get Embedded Video Player API Instance
          FB.Event.subscribe('xfbml.ready', function (msg) {
            if (msg.type === 'video') {
              var video_embed = msg.instance, embed_id = msg.id, embed_holder = $('#' + embed_id);
              if (embed_holder.length && !embed_holder.hasClass('built')) {
                embed_holder.addClass('built');
                var width = embed_holder.children().width(), height = embed_holder.children().height();
                if (width < height) {
                  embed_holder.closest('.item-media-outer').css({'padding-bottom': '0', 'height': 'auto'});
                  var windowHeight = $(window).height();
                  if (height < windowHeight || $Core.Video.isMobile) {
                    if ($Core.Video.isMobile) {
                      embed_holder.parent().css({'width': '100%'});
                    } else {
                      embed_holder.parent().css({'width': (((windowHeight * 0.70) * width) / height).toFixed(3) + 'px'});
                    }
                  } else {
                    embed_holder.parent().css({'width': (100 / parseFloat((height / width).toFixed(3))) + '%'});
                  }

                  //Don't remove below codes
                  // if (window.matchMedia('(max-width: 768px)').matches) {
                  //   embed_holder.parent().css({'width': (110 / parseFloat((height / width).toFixed(3))) + '%'});
                  //   embed_holder.closest('.item-media-outer').css({'padding-bottom': '110%'});
                  // } else {
                  //   embed_holder.closest('.item-media-outer').css({'padding-bottom': '0', 'height': 'auto'});
                  //   var windowHeight = $(window).height();
                  //   if (windowHeight * 0.66 < height) {
                  //     embed_holder.parent().css({'width': (((windowHeight * 0.66) * width) / height).toFixed(3) + 'px'});
                  //   } else {
                  //     embed_holder.parent().css({'width': '100%'});
                  //   }
                  // }
                } else if (width === height) {
                  embed_holder.closest('.item-media-outer').css({'padding-bottom': '100%'});
                } else if (width && height) {
                  embed_holder.closest('.item-media-outer').css({'padding-bottom': (parseFloat((height/width).toFixed(3)) * 100) + '%'});
                }
                //Wait for init
                setTimeout(function () {
                  embed_holder.parent().css({
                    'opacity': 1,
                    'height': 'auto'
                  });
                  embed_holder.css('opacity', 1);
                  embed_holder.find('iframe').removeClass('built');
                }, 500);
              }
              $Core.Video.facebookEmbedObjects.push(video_embed);
              video_embed.subscribe('startedPlaying', function() {
                $Core.Video.facebookEmbedObjects.forEach(function (item) {
                  if (item.$1 !== video_embed.$1) {
                    item.pause();
                  }
                })
              });
            }
          });
        };
        window.fbAsyncInit();
      }
    }, 500);
  },
  initVideoEmbed: function (reInit) {
    var iframeHolder = $('.fb_video_iframe'), path = getParam('sJsHome').replace('PF.Base', 'PF.Site');
    if (!iframeHolder.length || $('#page_v_play').length) {
      return false;
    }
    if (typeof ($.fn.iframeTracker) === "undefined") {
      var scriptTracker = document.createElement('script');
      scriptTracker.type = 'text/javascript';
      scriptTracker.src = path + 'Apps/core-videos/assets/videojs/jquery.iframetracker.js';
      document.body.appendChild(scriptTracker);
    }
    var initIframeInterval = setInterval(function () {
      if (typeof ($.fn.iframeTracker) !== "undefined") {
        clearInterval(initIframeInterval);
        if (reInit) {
          iframeHolder.find('iframe').removeClass('built').prop('played', false);
        }
        iframeHolder.addClass('dont-unbind-children').find('iframe:not(.built)').each(function () {
          $(this).addClass('built');
          $(this).iframeTracker(null);
          $(this).iframeTracker({
            blurCallback: function() {
              if (!$(this).prop('played') && this._videoId) {
                $.ajaxCall('v.updateVideoTotalView', 'video_id=' + this._videoId, 'POST');
              }
              $(this).prop('played', true);
            },
            overCallback: function(element) {
              this._videoId = $(element).parents('.fb_video_iframe').data('video-id');
            },
            _videoId: null
          });
        })
      }
    }, 200);
    $('._a_back').on('mouseup', function () {
      //Trigger when go back
      $Core.Video.initVideoEmbed(true);
    });
  },
  reloadVideoValidation: {
    validate: function (hard_reload) {
      var v_drop_zone = $('#v-dropzone');
      if (v_drop_zone.length) {
        var parentFormId = $('#page_v_share').length ? 'core_js_video_form' : v_drop_zone.closest('form').attr('id');
        if (typeof $Core.reloadValidation.changedEleData[parentFormId] === 'undefined') {
          $Core.reloadValidation.changedEleData[parentFormId] = {};
        }
        if ($('#pf_video_id_temp').length || hard_reload) {
          if (typeof $Core.reloadValidation.changedEleData[parentFormId] === 'undefined') {
            $Core.reloadValidation.changedEleData[parentFormId] = {};
          }
          $Core.reloadValidation.changedEleData[parentFormId]['v_drop_zone'] = true;
        } else if ($Core.reloadValidation.changedEleData[parentFormId].hasOwnProperty('v_drop_zone')) {
          delete $Core.reloadValidation.changedEleData[parentFormId]['v_drop_zone'];
        }

        $Core.reloadValidation.preventReload();
      }
    }
  }
};

$(document).on('click', '.dz-upload-again', function () {
  $('#v-dropzone .dropzone-button-v').trigger('click');
});

PF.event.on('on_document_ready_end', function () {
  $Core.Video.initFacebookEmbed();
});

PF.event.on('on_page_change_end', function () {
  $Core.Video.initFacebookEmbed();
  $Core.Video.facebookEmbedObjects = [];
  $Core.Video.videoJSPlayerObjects = [];
});

PF.event.on('on_page_load_init_end', function () {
  $Core.Video.initFacebookEmbed();
});