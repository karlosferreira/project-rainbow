$Core.Photo = {
  iItemId: 1,
  sModule: '',
  iAlbumId: 0,
  iTotal: 0,
  iTotalError: 0,
  bMassEdit: 0,
  oUploadedId: [],
  firstResponse: true,
  aPhotos: [],
  aFileData: [],
  aEditScheduleFileData: [],
  sAjax: '',
  bSubmit: false,
  bSkipUpload: false,
  keyPressed: false,
  canCheckReloadValidate: ($Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== 'undefined'),
  updateUploadingPhotoLimitationOnFeed: function(params) {
    if (parseInt(params['total']) === 0) {
      if ($('.activity_feed_form_attach li a[rel="global_attachment_photo"]').length) {
        $('.activity_feed_form_attach li a[rel="global_attachment_photo"]').slideUp();
      }
      if ($('#global_attachment_photo').length) {
        $('#global_attachment_photo').slideUp();
      }
    } else {
      if ($Core.hasOwnProperty('dropzone')
        && typeof $Core.dropzone === "object"
        && $Core.dropzone.instance.hasOwnProperty('photo_feed')) {
        $Core.dropzone.instance.photo_feed.options.maxFiles = parseInt(params['total']);
        if ($('#photo_feed-dropzone').find('.dropzone-content-info p:last').length) {
          $('#photo_feed-dropzone').find('.dropzone-content-info p:last').html(params['message']);
        }
      }
    }
  },
  // custom button
  setCoverPhoto: function (iPhotoId, iItemId, sModuleId) {
    $.ajaxCall(sModuleId + '.setCoverPhoto', 'photo_id=' + iPhotoId +
      '&page_id=' + iItemId);
  },
  togglePhotoStream: function () {
    $('.photos_stream,#show_photos_tab').toggleClass('hide');

  },
  slidePhotoStream: function (obj) {
    $(obj).siblings('.not-active').removeClass('not-active');
    if ($(obj).hasClass('last_clicked_button')) {
      return;
    }
    var stream = $(obj).parent('.photos_stream').find('div.photos:first'),
      streamWidth = stream.width(),
      screenWidth = window.innerWidth - 150;
    if (window.matchMedia('(max-width: 767px)').matches) {
      var screenWidth = window.innerWidth - 110;
    }
    if (streamWidth <= screenWidth) {
      return;
    }
    var streamLeft = stream.outerWidth(true) - stream.outerWidth();

    if (streamLeft < 0) {
      streamLeft = streamLeft * (-1);

    }
    var id = $(obj).attr('id');
    if (id == 'prev_photos') {
      streamLeft = streamLeft - screenWidth;
      if (streamLeft < 0) {
        streamLeft = 0;
      }
    }
    else {
      streamLeft = streamLeft + screenWidth;
      if (streamLeft > streamWidth) {
        return;
      }
    }
    var checkLast = function () {
      var cLeft = stream.outerWidth(true) - stream.outerWidth(),
        bIsFirst = cLeft == 0;
      if (cLeft < 0) {
        cLeft = cLeft * (-1);
      }
      cLeft = cLeft + screenWidth;
      if (cLeft > streamWidth || bIsFirst) {
        $(obj).addClass('last_clicked_button');
        $(obj).addClass('not-active');
      } else {
        $(obj).removeClass('last_clicked_button');
        $(obj).removeClass('not-active');
      }
    };
    if (window.rtl == 'ltr') {
      stream.animate({
        marginLeft: '-' + streamLeft,
      }, 500, checkLast);
    }
    if (window.rtl == 'rtl') {
      stream.animate({
        marginRight: '-' + streamLeft,
      }, 500, checkLast);
    }

  },
  updateAddNewAlbum: function (album_id) {
    var ele = $('#js_new_album');
    if (ele.length <= 0) return false;
    var value = ele.val();
    if (value != "") {
      value += ',' + album_id;
    }
    else {
      value = album_id;
    }
    ele.val(value);
  },
  deletePhoto: function (ele) {

    if (!ele.data('id')) return false;

    $Core.jsConfirm({message: ele.data('message')}, function () {
      $.ajaxCall('photo.deletePhoto', 'id=' + ele.data('id') + '&is_detail=' + ele.data('is-detail'));
    }, function () {
    });

    return false;
  },
  deleteAlbumPhoto: function (ele) {

    if (!ele.data('id')) return false;

    $Core.jsConfirm({message: ele.data('message')}, function () {
      NProgress.start();
      $.ajaxCall('photo.deleteAlbumPhoto', 'id=' + ele.data('id') + '&is_detail=' + ele.data('is-detail'));
    }, function () {
    });

    return false;
  },
  toggleEditAction: function (ele, type) {
    var parent = $(ele).closest('.photo-edit-item');
    switch (type) {
      case 'download':
        if ($(ele).prop('checked')) {
          parent.find('.item-allow-download').removeClass('active');
        }
        else {
          parent.find('.item-allow-download').addClass('active');
        }
        break;
      case 'album':
        if ($(ele).val() > 0) {
          parent.find('.photo_edit_holder').addClass('success');
        }
        else if (!$(ele).data('album_id')) {
          parent.find('.photo_edit_holder').removeClass('success');
        }
        break;
      case 'category':
        if ($(ele).val() != null) {
          parent.find('.item-categories').addClass('success');
        }
        else {
          parent.find('.item-categories').removeClass('success');
        }
    }
  },

  reloadValidationSharePhoto: {
    parentFormId: $('#js_photo_form').length ? 'js_photo_form' : 'js_activity_feed_form',
    validate: function (hardReload) {
      var parentObject = this;

      if ($('#' + parentObject.parentFormId).length) {
        if (!$Core.reloadValidation.changedEleData.hasOwnProperty(parentObject.parentFormId)) {
          $Core.reloadValidation.changedEleData[parentObject.parentFormId] = {};
        }
        if ($('#' + parentObject.parentFormId + ' .dz-image-preview').length > 0 || hardReload) {
          $Core.reloadValidation.changedEleData[parentObject.parentFormId]['photo-dropzone'] = true;
        } else {
          delete $Core.reloadValidation.changedEleData[parentObject.parentFormId]['photo-dropzone'];
        }

        $Core.reloadValidation.preventReload();
      }
    }
  },

  // DROPZONE IN PHOTO.ADD
  // ======================================================
  dropzoneOnSending: function (data, xhr, formData) {
    $Core.Photo.bSubmit = true;
    $('#js_photo_done_upload').hide();
    $('#js_photo_form').find('input, select').each(function () {
      formData.append($(this).prop('name'), $(this).val());
    });
  },
  removeUploadedPhoto: function (id) {
    $Core.Photo.iTotal--;
    $Core.Photo.oUploadedId = $.grep($Core.Photo.oUploadedId, function (value) {
      return value != id;
    });
  },
  processResponse: function (t, file, response) {
    response = JSON.parse(response);

    if (typeof response.id !== 'undefined') {
      file.item_id = response.id;
    }

    if (typeof response.errors === 'object') {
      for (var i in response.errors) {
        if (response.errors[i]) {
          $Core.dropzone.setFileError('photo', file, response.errors[i]);
          return;
        }
      }
    }

    // upload photo successfully
    if (typeof response.ajax !== 'undefined' && typeof response.photo_info !== 'undefined') {
      $Core.Photo.sAjax = response.ajax;
      $Core.Photo.aPhotos.push(JSON.parse(response.photo_info));
    }

    $Core.Photo.iTotal++;
    $Core.Photo.iAlbumId = response.album;
    $Core.Photo.oUploadedId.push(response.id);
    return file.previewElement.classList.add('dz-success');
  },
  dropzoneOnSuccess: function (ele, file, response) {
    $Core.Photo.processResponse(ele, file, response);
  },
  dropzoneOnAddedFile: function () {
    $Core.Photo.bSubmit = false;
    $('#js_photo_done_upload button').text(oTranslations['done']);
    $Core.Photo.bSkipUpload = false;
    $('#js_photo_done_upload').show();

    if ($Core.Photo.canCheckReloadValidate && $('#js_photo_form').length) {
      $Core.Photo.reloadValidationSharePhoto.validate(true);
    }
  },
  dropzoneOnAddedFileInFeed: function () {
    if ($Core.Photo.canCheckReloadValidate && $('#js_activity_feed_form').length) {
      $Core.Photo.reloadValidationSharePhoto.validate(true);
    }
  },
  dropzoneOnError: function () {
    $Core.Photo.iTotalError++;
  },
  dropzoneOnErrorInFeed: function () {
    $bButtonSubmitActive = false;
    $('.activity_feed_form_button .button').addClass('button_not_active');
    $Core.Photo.iTotalError++;
  },
  dropzoneOnComplete: function () {
    if (!$Core.Photo.bSubmit) return;
    if ($Core.Photo.iTotalError > 0) {
      $('#js_photo_done_upload').show();
      $('#js_photo_done_upload button').text(oTranslations['continue']);
      $Core.Photo.bSkipUpload = true;
      $('#photo-dropzone').find('.dz-preview').not('.dz-error').fadeOut(1000);
      return false;
    }
    if ($Core.Photo.sAjax && $Core.Photo.aPhotos.length > 0) {
      var ajax = $Core.Photo.sAjax + '&photos=' + JSON.stringify($Core.Photo.aPhotos);
      $.ajaxCall('photo.process', ajax);
      $Core.Photo.sAjax = '';
      $Core.Photo.aPhotos = [];
    }

    $Core.Photo.redirectCompleteUpload();
  },
  redirectCompleteUpload: function () {
    $.ajax({
      url: PF.url.make('photo/message'),
      data: {
        valid: $Core.Photo.iTotal,
        upload_ids: JSON.stringify($Core.Photo.oUploadedId),
        album: $Core.Photo.iAlbumId,
        module: $Core.Photo.sModule,
        item: $Core.Photo.iItemId
      }
    }).success(function (data) {
      var oData = JSON.parse(data);

      if ($Core.Photo.canCheckReloadValidate && $('#js_photo_form').length) {
        $Core.reloadValidation.reset(true);
      }

      if (oData.sUrl != "") {
        window.location.href = oData.sUrl;
      } else {
        window.location.href = getParam('sBaseURL') + 'photo';
      }
    });
  },
  // END OF DROPZONE IN PHOTO.ADD
  // ===============================================

  // DROPZONE PHOTO IN FEED
  // =====================================================
  dropzoneOnSendingInFeed: function (data, xhr, formData) {
    $('#js_activity_feed_form, #js_activity_feed_edit_form').find('input, textarea').each(function () {
      formData.append($(this).prop('name'), $(this).val());
    });
  },
  processResponseInFeed: function (t, file, response) {
    response = JSON.parse(response);

    // show error message
    if (typeof response.errors === 'object') {
      for (var i in response.errors) {
        if (response.errors[i]) {
          $Core.dropzone.setFileError('photo_feed', file, response.errors[i]);
          $sCacheFeedErrorMessage.push(file.name + ': ' + response.errors[i]);
        }
      }
    }

    if (typeof response.is_edit_schedule !== "undefined" && response.is_edit_schedule) {
      $Core.Photo.aEditScheduleFileData.push(response.file_data);
      $Core.Photo.editSchedule.uploadPhoto(JSON.stringify(response.file_data), response.schedule_id);
    } else {
      // upload photo successfully
      if (typeof response.ajax !== 'undefined') {
        $Core.Photo.sAjax = response.ajax;
      }

      if (typeof response.photo_info !== 'undefined') {
        $Core.Photo.aPhotos.push(JSON.parse(response.photo_info));
      }

      if (typeof response.file_data !== 'undefined') {
        $Core.Photo.aFileData.push(JSON.parse(JSON.stringify(response.file_data)));
      }
    }
    return file.previewElement.classList.add('dz-success');
  },
  dropzoneOnSuccessInFeed: function (ele, file, response) {
    // process response
    $Core.Photo.processResponseInFeed(ele, file, response);
  },
  dropzoneOnCompleteInFeed: function () {
    if ($Core.Photo.sAjax && $Core.Photo.aPhotos.length > 0) {
      if ($Core.Photo.aPhotos.length) {
        $.each($Core.Photo.aPhotos, function(key, value) {
          if (value.hasOwnProperty('name')) {
            $Core.Photo.aPhotos[key]['name'] = encodeURIComponent(value['name']);
          }
        });
      }
      var ajax = $Core.Photo.sAjax + '&photos=' + JSON.stringify($Core.Photo.aPhotos);
      ajax = ajax + '&filedata=' + JSON.stringify($Core.Photo.aFileData);
      $.fn.ajaxCall('photo.process', ajax, true, 'POST', function () {
        $Core.Photo.dropzoneOnFinishInFeed();
      });
      $Core.Photo.sAjax = '';
      $Core.Photo.aPhotos = [];
      $Core.Photo.aFileData = [];
    }
  },
  dropzoneOnCompleteInEditSchedule: function () {
    $Core.Photo.sAjax = '';
    $Core.Photo.aPhotos = [];
    $Core.Photo.aFileData = [];
    $Core.Photo.dropzoneOnFinishInFeed();
  },
  dropzoneOnFinishInFeed: function () {
    if ($Core.Photo.iTotalError > 0) {
      $bButtonSubmitActive = false;
      $('.activity_feed_form_button .button').addClass('button_not_active');
      if (typeof $ActivityFeedCompleted !== 'undefined') {
        $ActivityFeedCompleted.resetPhotoDropzone();
      }
    } else {
      $bButtonSubmitActive = true;
    }
  },
  dropzoneOnRemovedFileInFeed: function (ele, file) {
    $('div#activity_feed_upload_error').empty().hide();
    if (file.status == 'error' && $Core.Photo.iTotalError > 0) {
      $Core.Photo.iTotalError--;
    }
    if (!$Core.Photo.iTotalError) {
      $bButtonSubmitActive = true;
      $('.activity_feed_form_button .button').removeClass('button_not_active');
    }
    //handle remove photo in form upload of edit schedule feature
    if(typeof file.xhr !== "undefined" && file.hasOwnProperty('xhr') && $Core.Photo.aEditScheduleFileData.length) {
      var res = file.xhr.response;
      res = JSON.parse(res);
      if (res && res.hasOwnProperty('file_data') && typeof res['file_data'] === "object") {
        var sAjax = res['ajax'].split("&");
        var iTempImageId = res['file_data']['file_image_id'];
        var bIsSchedule = parseInt(sAjax[0].replace(/(.*)=/,""));
        var iScheduleId = parseInt(sAjax[1].replace(/(.*)=/,""));
        if (bIsSchedule) {
          $.ajaxCall('photo.deleteScheduleImage', 'file_data=' + JSON.stringify(res['file_data']) + '&schedule_id=' + iScheduleId);
          $Core.Photo.editSchedule.removePhoto(iTempImageId, iScheduleId);
        }
      }
    }
    if ($Core.Photo.canCheckReloadValidate) {
      $Core.Photo.reloadValidationSharePhoto.validate();
    }
  },
  // END OF DROPZONE PHOTO IN FEED
  // ==============================================
  toggleViewContentCollapse: function () {
    //viewmore less content in detail
    if ($('.js_core_photos_view_content_collapse').length) {
      var collapse_desc = $('.js_core_photos_view_content_collapse .item_description'),
        collapse_category = $('.js_core_photos_view_content_collapse .item-category');

      if ($('.js_core_photos_view_content_collapse >div').length > 2) {
        $('.js_core_photos_view_content_collapse').addClass('collapsed');
        $('.core-photos-view-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
      }
      if (collapse_desc.length) {
        if (20 < collapse_desc.height()) {
          collapse_desc.addClass('truncate-text');
          $('.js_core_photos_view_content_collapse').addClass('collapsed');
          $('.core-photos-view-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
        }
      }
      if (collapse_category.length) {
        if (40 < collapse_category.height()) {
          collapse_category.addClass('truncate-text');
          $('.js_core_photos_view_content_collapse').addClass('collapsed');
          $('.core-photos-view-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
        }
      }
      $('.js-core-photo-action-collapse .js-item-btn-toggle-collapse').off('click').on('click', function () {
        $('.js_core_photos_view_content_collapse').toggleClass('collapsed');
        if ($(this).hasClass('item-viewmore-btn')) {
          $(this).closest('.js-core-photo-action-collapse').removeClass('has-viewmore').addClass('has-viewless');
        } else if ($(this).hasClass('item-viewless-btn')) {
          $(this).closest('.js-core-photo-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
        }
      });
    }
  },
  init: function () {
    window.rtl = $('html').attr('dir');
    $('.pf-dropdown-not-hide-photo').off('click').click(function (event) {
      event.stopPropagation();
    });
    $('.pf-dropdown-not-hide-photo').find('span[data-dismiss="dropdown"]').on('click', function () {
      $(this).parents('.dropdown').trigger('click');
    });
    $('.photo-edit-item').find('.item-delete').on('click', function () {
      $(this).parents('.photo-edit-item-inner').addClass('delete');
    });
    $('.photo-edit-item').find('.delete-reverse').on('click', function () {
      $(this).parents('.photo-edit-item-inner').removeClass('delete');
      $(this).parents('.photo-edit-item-inner').find('.item-media.hide .item-delete input').removeAttr('checked').trigger('change');
    });
    if ($('a[rel="global_attachment_photo"]').length) {
      $('a[rel="global_attachment_photo"]').data('allow-checkin', 1);
    }

    if (!$('#page_photo_view').length) {
      $('.note , .notep').remove();
    }

    var $imageLoadHolder = $('.image_load_holder');
    if ($imageLoadHolder.length && !preLoadImages) {
      preLoadImages = true;
      if (!$('.photos_stream').length) {
        var images = '', imageCount = 0;

        if (typeof aPhotos != 'undefined' && aPhotos.length > 0) {
          $.each(aPhotos, function (index, value) {
            imageCount++;
            images += '<a class="stream_photo ' + value.class + '" href="' + value.link +
              '" data-photo-id="' + value.photo_id + '">' + value.html + '</a>';
          });
        }
        else if (cacheCurrentBody !== null && typeof(cacheCurrentBody.contentObject) == 'string') {
          $(cacheCurrentBody.contentObject).find('.photo-listing-item').each(function () {
            var t = $(this), src = t.find('a.item-media');
            t.addClass('pre_load');
            imageCount++;
            images += '<a class="stream_photo ' + t.data('class') + '" href="' + t.data('url') +
              '" data-photo-id="' + t.data('photo-id') + '"><span style="background-image:url(\'' + src.css('background-image').replace(/^url(?:\(['"]?)(.*?)(?:['"]?\))/, '$1') +
              '\')"></span></a>';
          });
        }
        else if (localStorage.getItem('photo_view_imageCount')) {
          images = localStorage.getItem('photo_view_images');
          imageCount = parseInt(localStorage.getItem('photo_view_imageCount'));
        }

        if (imageCount > 0) {
          if (imageCount > 99) {
            $('#content').prepend('<span id="show_photos_tab" class="hide"><a class="btn btn-primary " href="javascript:void(0)" onclick="$Core.Photo.togglePhotoStream()"><i class="ico ico-photos"></i>99+</a></span>');
          } else {
            $('#content').prepend('<span id="show_photos_tab" class="hide"><a class="btn btn-primary " href="javascript:void(0)" onclick="$Core.Photo.togglePhotoStream()"><i class="ico ico-photos"></i>' + imageCount + '</a></span>');
          }
          $('#content').prepend('<div class="photos_stream"><div class="photos">' + images + '</div></div>');
          var photos = $('.photos_stream .photos', '#content').first();
          photos.parent().append(
            '<a id="hide_photos" class="btn btn-primary" href="javascript:void(0)" onclick="$Core.Photo.togglePhotoStream()"><i class="ico ico-close"></i></a>');
          if (photos && (imageCount * 90) > $(window).width()) {
            photos.parent().prepend(
              '<a id="prev_photos" class="btn btn-primary last_clicked_button not-active" href="javascript:void(0)" onclick="$Core.Photo.slidePhotoStream(this)"><i class="ico ico-angle-left"></i></a><a id="next_photos" class="btn btn-primary" href="javascript:void(0)" onclick="$Core.Photo.slidePhotoStream(this)"><i class="ico ico-angle-right"></i></a>');
          }

          window.onbeforeunload = function () {
            localStorage.setItem('photo_view_images', images);
            localStorage.setItem('photo_view_imageCount', imageCount.toString());
          }
        }
      }

      var img = new Image(), src = $imageLoadHolder.data('image-src'),
        imgAlt = new Image(), srcAlt = $imageLoadHolder.data('image-src-alt');
      imgAlt.onload = function () {
        $imageLoadHolder.html('<img src="' + srcAlt + '" id="js_photo_view_image">');
        $('body').addClass('photo_is_active');
        $Core.loadInit();
      };

      img.onload = function () {
        $imageLoadHolder.html('<img src="' + src + '" id="js_photo_view_image">');
        $('body').addClass('photo_is_active');
        $Core.loadInit();
      };
      img.onerror = function () {
        imgAlt.src = srcAlt;
      };
      img.src = src;
    }

    if (!$imageLoadHolder.length) {
      $('.photos_stream').remove();
      $('#show_photos_tab').remove();
    }

    if ($('.photos_stream').length > 0) {
      $('#page_photo_view').addClass('has-photo-tab');
    } else {
      $('#page_photo_view').removeClass('has-photo-tab');
    }
    if ($('.photos_stream').length && $imageLoadHolder.length && !preSetActivePhoto) {
      preSetActivePhoto = true;
      $('.photos_stream a.active').removeClass('active');
      if ($('.photos_view').data('photo-id')) {
        var currentPhoto = ($('.photos_stream a[data-photo-id="' +
          $('.photos_view').data('photo-id') + '"]').length > 0)
          ? $('.photos_stream a[data-photo-id="' +
            $('.photos_view').data('photo-id') + '"]').first()
          : null;
        if (currentPhoto != null) {
          currentPhoto.addClass('active');
          var nextPhoto = currentPhoto.next('.stream_photo');
          if (nextPhoto.length > 0) {
            var html = '<a id="next_photo" class="button btn-primary photo_btn" href="' +
              nextPhoto.attr('href') +
              '"><i class="ico ico-angle-right"></i></a>';
            $imageLoadHolder.parent().append(html);
          }
          var prevPhoto = currentPhoto.prev('.stream_photo');
          if (prevPhoto.length > 0) {
            var html = '<a id="previous_photo" class="button btn-primary photo_btn" href="' +
              prevPhoto.attr('href') + '"><i class="ico ico-angle-left"></i></a>';
            $imageLoadHolder.parent().append(html);
          }
        }
      }
      $Core.Photo.keyPressed = false;
      $(document).unbind('keydown').keydown(function (e) {
        if ($Core.Photo.keyPressed) {
          return;
        }
        if ($('textarea:focus').length
          || $('input[type="text"]:focus').length) {
          return; // exit this handler for other keys
        }
        switch (e.which) {
          case 37: // left
            if ($('#previous_photo').length) {
              $Core.Photo.keyPressed = true;
              $('#previous_photo').trigger('click');
            }
            break;
          case 39: // right
            if ($('#next_photo').length) {
              $Core.Photo.keyPressed = true;
              $('#next_photo').trigger('click');
            }
            break;
          default:
            return;
        }
        e.preventDefault();
      });
    }

    let nextPhotoBtn = $('#next_photo'),
        prevPhotoBtn = $('#previous_photo');
    if (nextPhotoBtn.length) {
      nextPhotoBtn.on('click', function() {
        $(this).css('pointer-events', 'none');
      });
    }
    if (prevPhotoBtn.length) {
      prevPhotoBtn.on('click', function() {
        $(this).css('pointer-events', 'none');
      });
    }

    if ($('.js_photo_active_items').length > 0) {
      $('.js_photo_active_items').each(function () {
        if (!$(this).prop('built')) {
          $(this).prop('built', true);
          var aParts = explode(',', $(this).html());
          for (i in aParts) {
            if (empty(aParts[i])) {
              continue;
            }
            $(this).parents('.js_category_list_holder:first').find('.js_photo_category_' + aParts[i] + ':first').attr('selected', true);
          }
        }
      });
    }

    $('#js_photo_album_select').change(function () {
      if (empty(this.value)) {
        $('#js_photo_privacy_holder').slideDown();
      }
      else {
        $('#js_photo_privacy_holder').slideUp();
        $('#js_photo_done_upload').data('album', this.value);
      }
    });

    $('#js_photo_done_upload').on('click', function () {
      if (typeof $Core.dropzone.instance['photo'] !== 'object') {
        return;
      }
      if ($Core.Photo.bSkipUpload) {
        $Core.Photo.redirectCompleteUpload();
        return;
      }
      if ($Core.Photo.iTotalError > 0) {
        tb_show(oTranslations['notice'], '', null, oTranslations['upload_failed_please_remove_all_error_files_and_try_again']);
        return false;
      }
      $Core.dropzone.instance['photo'].processQueue();
    });

    if ($('._a_multiple_back').length) {
      $('._a_multiple_back').off('click').on('click', function () {
        $('#noteform').remove();
      });
    }

    if ($('.core-photos-js').length) {
      core_photo_mode_view.init($('.core-photos-js').attr('id'));
    }
    $Core.Photo.toggleViewContentCollapse();
  },

  editSchedule: {
    toggleScheduleUploadSection: function() {
      $('.photo-upload-schedule').toggle();
    },
    removePhoto: function (imageId, scheduleId) {
      var deletedInput = $('#js_schedule_deleted_photo' + scheduleId),
          currentDelete = deletedInput.val();
      $('.js_schedule_photo_holder_' + imageId).remove();
      deletedInput.val(currentDelete + ',' + imageId).trigger('change');
      return true;
    },
    uploadPhoto: function (data, scheduleId) {
      var newInput = $("#js_schedule_new_photo" + scheduleId), currentNew = newInput.val();
      newInput.val(currentNew + '|' + data).trigger('change');
      return true;
    }
  }
};

if (typeof $Core.Photo == 'undefined') {
  $Core.Photo = {};
}

PF.event.on('on_show_cache_feed_error_message', function () {
  if ($sCurrentForm == 'global_attachment_photo') {
    $('#activity_feed_upload_error').html('');
    $bButtonSubmitActive = false;
    $('.activity_feed_form_button .button').addClass('button_not_active');
  }
});

if (typeof $ActivityFeedCompleted !== 'undefined') {
  $ActivityFeedCompleted.resetPhotoDropzone = function () {
    if (typeof $Core.dropzone.instance.photo_feed !== 'undefined') {
      $Core.dropzone.instance.photo_feed.removeAllSuccessFiles();
    }
  }
}

PF.event.on('on_page_column_init_end', function () {
  localStorage.removeItem('photo_view_images');
  localStorage.removeItem('photo_view_imageCount');
});

$Ready(function () {
  $Core.Photo.init();
  if ($Core.hasPushState()) {
    window.addEventListener("popstate", function (e) {
      if (typeof $Core.dropzone.instance['photo'] === 'object') {
        $Core.Photo.iTotalError = 0;
        $Core.dropzone.instance['photo'].files = [];
      }
    });
  }
});

var core_photos_onchangeDeleteCategoryType = function (type) {
  if (type == 2)
    $('#category_select').show();
  else
    $('#category_select').hide();
};

var core_photo_mode_view = {
  init: function (page_id) {
    $('#' + page_id + ' .photo-mode-view-btn').off('click').on('click', function () {
      //Get data-mode
      var photo_viewmode_data = $(this).data('mode');
      var parent = $(this).parent();

      //Remove class active
      parent.find('.photo-mode-view-btn').removeClass('active');

      //Add class active
      $(this).addClass('active');

      // find block need to
      var mode_view_container = parent.siblings('.photo-view-modes-js');
      mode_view_container.attr('data-mode-view-default', photo_viewmode_data);
      mode_view_container.attr('data-mode-view', photo_viewmode_data);

      if (photo_viewmode_data == 'casual') {
        core_photo_casual_view.init(mode_view_container);
      }
      else {
        core_photo_casual_view.destroy(mode_view_container);
      }
      // Set cookie
      setCookie(page_id + '-mode-view', photo_viewmode_data);
    });

    var photo_viewmode_data = getCookie(page_id + '-mode-view');
    var mode_views = $('.photo-view-modes-js').data('mode-views');
    if (!photo_viewmode_data || !mode_views.includes(photo_viewmode_data)) {
      photo_viewmode_data = $('.photo-view-modes-js').data('mode-view-default');
    }
    if (!$('#' + page_id + ' .photo-mode-view-btn.' + photo_viewmode_data).hasClass('active')) {
      $('#' + page_id + ' .photo-mode-view-btn.' + photo_viewmode_data).trigger('click');
    }
  }
};

var core_photo_casual_view = {
  init: function (mode_view_container) {
    if (!mode_view_container.hasClass('photo-init-pinto')) {
      mode_view_container.addClass('photo-init-pinto');
      mode_view_container.imagesLoaded(function () {
        mode_view_container.find('.photo-init-pinto-js').masonry({
          itemSelector: '.photo-listing-item',
          columnWidth: '.photo-listing-item',
          percentPosition: true
        });
        mode_view_container.find('.photo-listing-item').addClass('casual-view');
      });
    }
  },
  destroy: function (mode_view_container) {
    if (mode_view_container.hasClass('photo-init-pinto')) {
      mode_view_container.find('.photo-init-pinto-js').masonry('destroy');
      mode_view_container.removeClass('photo-init-pinto');
      mode_view_container.find('.photo-listing-item').removeClass('casual-view');
    }
  },
  reloadItems: function (mode_view_container) {
    if (mode_view_container.hasClass('photo-init-pinto')) {
      mode_view_container.imagesLoaded(function () {
        var itemsContent = mode_view_container.find('.photo-listing-item:not(.casual-view)');
        if (itemsContent.length) {
          mode_view_container.find('.photo-init-pinto-js').masonry("appended", itemsContent.get()).masonry();
          itemsContent.addClass('casual-view');
        }
        else {
          mode_view_container.find('.photo-init-pinto-js').masonry('reloadItems').masonry();
        }
      });
    }
  }
};

PF.event.on('ajaxLoadMorePagingSuccess', function (moreContent) {
  if (moreContent && $('.core-photos-js').length) {
    var page_id = $('.core-photos-js').attr('id');
    if (page_id) {
      var photo_viewmode_data = getCookie(page_id + '-mode-view');
      var mode_views = $('.photo-view-modes-js').data('mode-views');
      if (!photo_viewmode_data || !mode_views.includes(photo_viewmode_data)) {
        photo_viewmode_data = $('.photo-view-modes-js').data('mode-view-default');
      }
      if (photo_viewmode_data == 'casual') {
        var mode_view_container = $('#' + page_id + ' .photo-mode-view-btn.' + photo_viewmode_data).parent().siblings('.photo-view-modes-js');
        core_photo_casual_view.reloadItems(mode_view_container);
      }
    }
  }
});

$Behavior.reorderPhotosInAlbum = function () {
  $('.js_core_photos_sortable_album_photos').sortable({
    handle: '.js_core_photo_drag_sort',
    update: function (event, ui) {
      var album_id = $(this).data('id');
      var data = $(this).sortable('serialize');
      $.ajaxCall('photo.reorderAlbumPhotos', data + '&album_id=' + album_id);
    }
  });
};

$Behavior.reinitMoreEventsForBackAction = function() {
  if ($('.js_photos_view').length && $('.js_photos_view').find('._a_back').length) {
    $('.js_photos_view').find('._a_back').on('click', function() {
      if (typeof checkFirstAccessToPhotoDetailByAjaxMode !== "undefined") {
        checkFirstAccessToPhotoDetailByAjaxMode = undefined;
      }
    });
  }
}

PF.event.on('on_page_change_end', function() {
  if (typeof $Core.dropzone.instance['photo_feed'] === 'object') {
    $Core.Photo.iTotalError = 0;
    $Core.dropzone.instance['photo_feed'].files = [];
  }
  if (typeof checkFirstAccessToPhotoDetailByAjaxMode !== "undefined" && (!$('#page_photo_view').length || !$('.js_photos_view').length)) {
    checkFirstAccessToPhotoDetailByAjaxMode = undefined;
  }
});