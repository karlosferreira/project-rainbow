/**
 * Created by minhhai on 3/1/18.
 */
var bSendingAjax = false;
var iTotalError = 0;
var admin_Comment = {
  initCanvasGif: function (sEle) {
    $(sEle).each(function () {
      var ele = this;
      if (/.*\.gif/.test($(ele).attr('src'))) {
        var canvas = document.createElement('canvas'),
          context = canvas.getContext("2d"),
          width = $(ele).closest('div').width(),
          height = $(ele).closest('div').height();
        if (!width) {
          return false;
        }
        canvas.className = "core_comment_canvas_gif";
        canvas.height = height;
        canvas.width = width;
        var image = new Image();
        image.src = $(ele).attr('src');
        image.onload = function () {
          context.drawImage(image, 0, 0, width, height);
        };
        canvas.onclick = function () {
          var _e = $(this),
            _ele = $(ele);
          _e.hide();
          _ele.show();
          setTimeout(function () {
            _e.show();
            _ele.hide();
          }, 5000);
        };
        canvas.onmouseover = function () {
          var _e = $(this),
            _ele = $(ele);
          _e.hide();
          _ele.show();
          setTimeout(function () {
            _e.show();
            _ele.hide();
          }, 5000);
        };
        $(ele).hide();
        $(ele).parent().append(canvas);
        $(ele).addClass('comment_built');
      }
    });
  },
  dropzoneOnSending: function (data, xhr, formData) {
    $('#js_sticker_set_form').find('input').each(function () {
      formData.append($(this).prop('name'), $(this).val());
    });
  },
  dropzoneOnSuccess: function (ele, file, response) {
    this.processResponse(ele, file, response);
  },

  dropzoneOnError: function (ele, file) {
    iTotalError++;
    return false;
  },
  dropzoneOnRemoveFile: function (ele, file) {
    if (file.status == 'error') {
      iTotalError--;
    }
  },
  dropzoneQueueComplete: function () {
    bSendingAjax = false;
    var set_id = $('#js_sticker_set_id').val();
    if (!iTotalError) {
      window.location.href = getParam('sBaseURL') + 'admincp/comment/add-sticker-set/' + '?id=' + set_id;
    } else {
      $('#js_sticker_set_submit').removeClass('disabled').removeAttr('disabled');

      setTimeout(function () {
        $('#comment-dropzone').find('.dz-preview:not(.dz-error)').remove();
        admin_Comment.refreshStickers(set_id);
      }, 500);
    }
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
          $Core.dropzone.setFileError('comment', file, response.errors[i]);
          return;
        }
      }
      $('#comment-dropzone').removeClass('dz-started');
    }
    return file.previewElement.classList.add('dz-success');
  },
  resetFileToQueue: function () {
    var _i, _len,
      _files = $Core.dropzone.instance['comment'].files;
    if (_files.length) {
      for (_i = 0, _len = _files.length; _i < _len; _i++) {
        _files[_i].status = "queued";
      }
    }
  },
  refreshStickers: function (id) {
    $.ajaxCall('comment.refreshStickers', 'id=' + id);
    return true;
  }
};

$Ready(function () {
  setTimeout(function () {
    admin_Comment.initCanvasGif('.core_comment_gif:not(.comment_built)');
  }, 1000);
  if ($('#js_sticker_set_form').length) {
    $('#js_sticker_set_form').off('submit').on('submit', function () {
      if (iTotalError) {
        tb_show(oTranslations['error'], '', null, oTranslations['please_remove_all_error_files_first']);
        return false;
      }

      if (bSendingAjax) return false;

      $('#js_sticker_set_submit').addClass('disabled').attr('disabled', 'disabled');
      bSendingAjax = true;
      js_box_remove('.js_box');
      $Core.ajax('comment.addStickerSet', {
        type: 'POST',
        params: {
          title: $('#js_sticker_set_title').val(),
          id: $('#js_sticker_set_id').val(),
        },
        success: function (sOutput) {
          var response = JSON.parse(sOutput),
            _files = $Core.dropzone.instance['comment'].files;
          if (typeof response.error != 'undefined') {
            $('#js_sticker_set_submit').removeClass('disabled').removeAttr('disabled');
            tb_show(oTranslations['error'], '', null, response.error);
            bSendingAjax = false;
          }
          if (typeof response.id != 'undefined') {
            if (_files.length) {
              $('#js_sticker_set_id').val(response.id);
              $Core.dropzone.instance['comment'].processQueue();
            } else {
              $('#js_sticker_set_submit').removeClass('disabled').removeAttr('disabled');
              if ($('#js_sticker_set_id').val() > 0) {
                tb_show(oTranslations['notice'], '', null, oTranslations['sticker_set_updated_successfully']);
                bSendingAjax = false;
              } else {
                tb_show(oTranslations['notice'], '', null, oTranslations['sticker_set_added_successfully']);
                window.location.href = getParam('sBaseURL') + 'admincp/comment/add-sticker-set/' + '?id=' + response.id;
              }
            }
          }
          setTimeout(function () {
            js_box_remove($('.js_box'));
          }, 2500);
        }
      });
      return false;
    });
  }
  if (typeof $Core.dropzone.instance['comment'] != 'undefined') {
    $Core.dropzone.instance['comment'].on('processingqueue', function (files) {
      if (!files.length) {
        var set_id = $('#js_sticker_set_id').val();
        window.location.href = getParam('sBaseURL') + 'admincp/comment/add-sticker-set/' + '?id=' + set_id;
      }
    })
  }
});