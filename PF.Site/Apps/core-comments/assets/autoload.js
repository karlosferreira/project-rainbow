var isAddingComment = false;
var $oLastFormSubmit = null;
var coreCommentSavedRange;

function customInclude (container, value) {
  var returnValue = false;
  var pos = container.indexOf(value);
  if (pos >= 0) {
    returnValue = true;
  }
  return returnValue;
}

$Core.Comment = {
  canCheckValidate: ($Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined"),
  cacheShadownInfo: false,
  shadow: null,
  lastClickObj: null,
  initGif: false,
  selectingSticker: false,
  bIsMobile: false,
  isIOS: customInclude([
    'iPad Simulator',
    'iPhone Simulator',
    'iPod Simulator',
    'iPad',
    'iPhone',
    'iPod'
  ], navigator.platform),
  resizeTextarea: function (oObj) {
    if (!oObj.length) return;
    if (this.cacheShadownInfo === false) {
      this.cacheShadownInfo = true;
      this.shadow = $('<div></div>').css(
        {
          position: 'absolute',
          top: -10000,
          left: -10000,
          wordWrap: 'break-word',
          width: oObj.width(),
          fontSize: oObj.css('fontSize'),
          fontFamily: oObj.css('fontFamily'),
          lineHeight: oObj.css('lineHeight'),
          resize: 'none'
        }).appendTo(document.body);
    }

    var val = oObj.val().replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/&/g, '&amp;')
      .replace(/\n/g, '<br/>');
    var has_br_end = false;
    if (val.endsWith("<br/>")) {
      has_br_end = true;
    }
    this.shadow.html(val);

    var height = this.shadow.height();
    var parent = oObj.closest('.item-box-input');
    var lineHeight = parseInt(oObj.css('lineHeight'));
    var paddingTop = parseInt(oObj.css('paddingTop'));
    if (has_br_end) {
      height += lineHeight;
    }
    if (height > lineHeight) {
      if (!parent.hasClass('box-full')) {
        parent.addClass('box-full');
        this.shadow.css('width', oObj.width());
        height = this.shadow.height();
        if (has_br_end) {
          height += lineHeight;
        }
      }
    } else if (parent.hasClass('box-full')) {
      this.shadow.css('width', oObj.width() - ($Core.Comment.bIsMobile ? parent.find('.mobile-sent-btn').innerWidth() : parent.find('.comment-group-icon').innerWidth()));
      if (this.shadow.height() <= lineHeight) {
        parent.removeClass('box-full');
      }
      this.shadow.css('width', oObj.width());
    }

    // plus comment box height with padding top + padding bottom
    height += (paddingTop * 2);
    oObj.css('height', 'auto');
  },
  checkStickerBtn: function (parent, sticker_rtl) {
    var position_sticker = parent.find('.item-container');
    var position_sticker_change = parent.find('.comment-full-sticker'),
      number_sticker = position_sticker_change.find('.item-header-sticker').length,
      width_sticker = position_sticker_change.find('.item-header-sticker').width(),
      width_full_Sticker = number_sticker * width_sticker - 2;
    var offset_sticker = position_sticker.offset();
    var offset_sticker_change = position_sticker_change.offset();
    if (sticker_rtl === 'right') {
      if (offset_sticker.left <= offset_sticker_change.left) {
        parent.find('.comment-next-sticker').css('display', 'none');
      } else {
        parent.find('.comment-next-sticker').css('display', 'flex');
      }
      if ((offset_sticker.left + position_sticker.outerWidth()) < (offset_sticker_change.left + width_full_Sticker)) {
        parent.find('.comment-prev-sticker').css('display', 'flex');
        parent.find('.item-recent').css('display', 'none');
      }
      if ((offset_sticker.left + position_sticker.outerWidth()) === (offset_sticker_change.left + width_full_Sticker)) {
        parent.find('.comment-prev-sticker').css('display', 'none');
        parent.find('.item-recent').css('display', 'flex');
        parent.find('.comment-next-sticker').css('display', 'flex');
      }
    } else {
      if (offset_sticker.left > offset_sticker_change.left) {
        parent.find('.comment-prev-sticker').css('display', 'flex');
        parent.find('.item-recent').css('display', 'none');
      }
      if ((offset_sticker.left + position_sticker.outerWidth()) >= (offset_sticker_change.left + width_full_Sticker)) {
        parent.find('.comment-next-sticker').css('display', 'none');
      } else {
        parent.find('.comment-next-sticker').css('display', 'flex');
      }
      if (offset_sticker.left === offset_sticker_change.left) {
        parent.find('.comment-prev-sticker').css('display', 'none');
        parent.find('.item-recent').css('display', 'flex');
        parent.find('.comment-next-sticker').css('display', 'flex');
      }
    }

  },
  commentFeedTextareaClick: function ($oObj) {
    $($oObj).addClass('dont-unbind');
    $($oObj).blur(function () {
      $(this).removeClass('dont-unbind');
    });
    $($oObj).css('height', 'auto');
    $($oObj).on('keydown',function (e) {
      if (isAddingComment) {
        return false;
      }
      if(e.keyCode === 8) {
        var value = $(this).html().replace('<br>', '');
        var checkDiv = value.substring(value.length - 2);
        //some check ios for issue ios cursor show at the end input
        if ($Core.Comment.isIOS && checkDiv === '> '){
          $(this).append('&zwnj;');
        }
        if (checkDiv === 'n>' || ($Core.Comment.isIOS && checkDiv.replace(/\u200C/g, '') == '>' )) {
          $(this).find('[id=generated]').last().remove();
          $Core.divContenteditableInteraction.placeCaretAtEnd($(this)[0]);
        }
      }
      if (e.keyCode === 13 && !$Core.Comment.bIsMobile) {
        if (e.ctrlKey || e.metaKey) {
          if (!$($oObj).hasClass('contenteditable')) {
            var val = this.value,
                start = this.selectionStart;

            this.value = val.slice(0, start) + '\n' + val.slice(this.selectionEnd);
            this.selectionStart = this.selectionEnd = start + 1;
          }
        } else if (!e.shiftKey) {
          setTimeout(function () {
            $('.chooseFriend').remove();
          }, 100);
          e.preventDefault();
          $($oObj).parents('form:first').trigger('submit');
          $($oObj).removeClass('dont-unbind');
          var captchaCheckEle = $('#js_captcha_load_for_check');
          if (captchaCheckEle.length) {
            var captchaType = captchaCheckEle.data('type');
            if (captchaType == 'recaptcha') {
              // reset token
              var reCaptchaEle = captchaCheckEle.find('#g-recaptcha');
              if (reCaptchaEle.length && reCaptchaEle.data('type') == 3) {
                $Core.captcha.addRecaptchaToken(reCaptchaEle.data('sitekey'));
              } else if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
              }
            } else {
              // reload image
              captchaCheckEle.find('.captcha').attr('id', 'js_captcha_image').css({opacity: 0.0});
              $('#js_captcha_image').ajaxCall('captcha.reload', 'sId=js_captcha_image&sInput=image_verification');
            }
          }
          $Core.loadInit();
          isAddingComment = true;
          return false;
        }
      }
    }).on('keyup',function () {
      var oFeedFooter = $(this).closest('.js_feed_comment_form_holder');
      if ($Core.Comment.bIsMobile && oFeedFooter.length && ($(this).html() !== '' || $(this).val() !== '')) {
        oFeedFooter.find('.mobile-sent-btn:not(.has-attach)').addClass('active');
        oFeedFooter.find('.mobile-sent-btn').addClass('has-text');
      } else {
        oFeedFooter.find('.mobile-sent-btn:not(.has-attach)').removeClass('active').removeClass('has-text');
        oFeedFooter.find('.mobile-sent-btn').removeClass('has-text');
      }
      $Core.Comment.resizeTextarea($(this));
    }).off('paste').on('paste', function (e) {
      e.stopPropagation();
      e.preventDefault();
      var text = e.originalEvent.clipboardData.getData('text/plain');
      if (typeof $Core === "object" && $Core.hasOwnProperty('htmlEntityEncode') && typeof $Core.htmlEntityEncode === "function") {
        text = $Core.htmlEntityEncode(text);
      } else {
        text = text.replace(/[\u00A0-\u9999<>\&]/g, function(i) {
          return '&#' + i.charCodeAt(0) + ';';
        });
      }
      document.execCommand("insertHTML", false, text);
      $Core.Comment.resizeTextarea($(this));
      $Core.divContenteditableInteraction.updateTextareaValue($oObj);
      $Core.divContenteditableInteraction.placeCaretAtEnd($(this)[0]);
    });

    $($oObj).addClass('js_app_comment_feed_textarea_focus').addClass('is_focus');
    $($oObj).parents('.comment_mini').find('.feed_comment_buttons_wrap:first').show();

    $($oObj).parent().parent().find('.comment_mini_textarea_holder:first').addClass('comment_mini_content');
  },
  deleteAttachment: function (ele, id, type, isEdit) {
    var formId = ele.closest('form').attr('id');
    if (!id) {
      return false;
    }
    ele.closest('.has-photo-sticker').removeClass('has-photo-sticker');
    if (isEdit) {
      ele.closest('.js_app_comment_feed_form').find('.js_feed_comment_attach_change').val(1);
    }
    if ($Core.Comment.bIsMobile && ele.closest('.js_feed_comment_form_holder').length) {
      ele.closest('.js_feed_comment_form_holder').find('.mobile-sent-btn:not(.has-text)').removeClass('active').removeClass('has-attach');
      ele.closest('.js_feed_comment_form_holder').find('.mobile-sent-btn').removeClass('has-attach');
    }
    if (type == 'photo') {
      ele.closest('.js_app_comment_feed_form').find('.js_feed_comment_photo_id').val(0);
      ele.closest('.js_comment_attach_preview').remove();
      if (!isEdit) {
        $.ajaxCall('comment.deleteTempFile', 'id=' + id, 'post');
      }
    } else if (type == 'sticker') {
      ele.closest('.js_app_comment_feed_form').find('.js_feed_comment_sticker_id').val(0);
      ele.closest('.js_comment_attach_preview').remove();
    }

    if ($Core.Comment.canCheckValidate) {
      var editForm = 'js_comment_textarea_edit';
      if ($('.js_comment_textarea_edit').length) {
        $Core.Comment.reloadCommentValidation.validatePhoto(editForm);
      } else if (formId.length){
        $Core.Comment.reloadCommentValidation.validatePhoto(formId);
      }
    }

    return false;
  },
  resetCommentForm: function (feed_id) {
    var input_ele = $('#js_item_feed_' + feed_id).find('.js_feed_comment_form_holder textarea.js_app_comment_feed_textarea'),
        div_input_ele = $('#js_item_feed_' + feed_id).find('.js_feed_comment_form_holder .contenteditable'),
      feed_form = input_ele.closest('.js_app_comment_feed_form');
    div_input_ele.siblings('.mobile-sent-btn').removeClass('active').removeClass('has-text').removeClass('has-attach');
    input_ele.val('').addClass('js_app_comment_feed_textarea_focus');
    div_input_ele.empty();
    div_input_ele.addClass('js_app_comment_feed_textarea_focus').removeAttr('style');
    $Core.Comment.resizeTextarea(input_ele);
    feed_form.find('.js_comment_box').removeClass('has-photo-sticker');
    feed_form.find('.js_feed_comment_photo_id').val(0);
    feed_form.find('.js_feed_comment_sticker_id').val(0);
    feed_form.find('.js_comment_attach_emoticon').removeClass('open');
    feed_form.closest('.js_feed_comment_form_holder').find('.js_comment_emoticon_container').hide();
    feed_form.find('.js_comment_attach_preview').remove();
    if (window.getSelection) {
      window.getSelection().removeAllRanges();
    }
  },
  hideEmoticon: function (ele, isReply) {
    var parent = ele.closest('.js_comment_emoticon_container');
    parent.fadeOut();
    if (isReply === 'true') {
      parent.closest('.js_comment_form_holder').find('.js_comment_attach_emoticon').removeClass('open');
    } else {
      parent.closest('.js_feed_comment_form_holder').find('.js_comment_attach_emoticon').removeClass('open');
    }
  },
  showEmojiTitle: function (ele, code) {
    var parent = ele.closest('.js_comment_emoticon_container');
    parent.find('.js_hover_emoticon_info').html(code);
  },
  emojiInsertHTML: function(value, sCustomClass) {
    var sel, range;
    if (window.getSelection && (sel = window.getSelection()).rangeCount) {
      range = sel.getRangeAt(0);
      if (($(range.startContainer).hasClass('contenteditable') &&  $(range.endContainer).hasClass(sCustomClass))
          || ($(range.endContainer)[0].nodeName === '#text' && !$($(range.endContainer)[0].parentNode).hasClass('generatedMentionTag'))) {
        var nodeValue = value[0]
        range.collapse(true);
        range.insertNode(nodeValue);

        range.setStartAfter(nodeValue);
        range.collapse(true);
        sel.removeAllRanges();
        sel.addRange(range);
      }
    }
  },
  selectEmoji: function (ele, code, isReply, isEdit) {
    var oForm, myField;
    if (isEdit === 'true') {
      oForm = ele.closest('.js_comment_emoticon_container').closest('.js_edit_comment_holder');
      myField = oForm.find('.js_comment_textarea_edit');
    } else {
      if (isReply === 'true') {
        oForm = ele.closest('.js_comment_form_holder');
        myField = oForm.find('div.js_app_comment_feed_textarea');
      } else {
        oForm = ele.closest('.js_feed_comment_form_holder');
        myField = oForm.find('div.js_app_comment_feed_textarea');
      }
    }

    var composeElem = oForm.find('div.contenteditable');
    if (composeElem.length) {
      var sCustomClass;
      var ua = window.navigator.userAgent;
      var isIE = /MSIE|Trident/.test(ua);
      var clonedImg = ele.find('img').clone().addClass('comment_content_emoji s-2').css({'margin': '0px 1px;', 'vertical-align': 'middle', 'pointer-events':'none'});

      var aClasses = composeElem.attr('class').split(/\s+/);
      if( isIE) {
        sCustomClass = aClasses.filter(function(e){
          return e.indexOf('_custom', 0);
        })[0];
      } else {
        sCustomClass = aClasses.find(function(e){
          return e.startsWith('_custom');
        });
      }

      if (composeElem.html() === '') {
        composeElem.append(clonedImg);
        $Core.divContenteditableInteraction.placeCaretAtEnd(oForm.find('div.contenteditable')[0]);
      } else {
        this.emojiInsertHTML(clonedImg, sCustomClass);
      }

      //prevent firefox resize emoji
      if ($.browser.mozilla) {
        document.execCommand('enableObjectResizing', false, 'false');
      }
      $Core.divContenteditableInteraction.updateTextareaValue(oForm.find('div.contenteditable')[0]);
    } else {
      var sValue = '' + code + '';
      if (myField.prop('selectionStart') || myField.prop('selectionStart') === '0') {
        var startPos = myField.prop('selectionStart'),
            endPos = myField.prop('selectionEnd'),
            offset = myField.val().substring(0, startPos).length
                + sValue.length;
        myField.val(myField.val().substring(0, startPos)
            + sValue
            + myField.val().substring(endPos, myField.val().length));
        myField.focus();
        myField[0].setSelectionRange(offset, offset);
      } else {
        myField.val(myField.val() + sValue);
        myField.focus();
        var offset_end = myField.val().length * 2;
        myField[0].setSelectionRange(offset_end, offset_end);
      }
    }
    $Core.Comment.resizeTextarea(myField);

    if ($Core.Comment.canCheckValidate) {
      var formId = ele.closest('form').attr('id');
      if ($('.js_comment_textarea_edit').length) {
        $Core.Comment.reloadCommentValidation.validateEdit();
      } else {
        $Core.Comment.reloadCommentValidation.validate(formId);
      }
    }
    oForm.find('.mobile-sent-btn').addClass('active');
    return true;
  },
  hideComment: function (ele, bUnHide) {
    var obj = $(ele);
    $.ajaxCall('comment.hideComment', $.param({
      id: obj.data('comment-id'),
      owner_id: obj.data('owner-id'),
      parent_id: obj.data('parent-id'),
      un_hide: bUnHide
    }), 'post');
    return false;
  },
  showHiddenComments: function (ele) {
    var obj = $(ele),
      ids = obj.data('hidden-ids'),
      oIds = isNaN(ids) ? ids.split(',') : '';
    if (oIds.length) {
      oIds.forEach(function (e) {
        $('#js_comment_' + e).removeClass('hide');
      });
    } else if (ids > 0) {
      $('#js_comment_' + ids).removeClass('hide');
    }
    obj.closest('.js_hidden_comment_dot').remove();
    $('#js_global_tooltip').remove();
    this.initCanvasForSticker('.core_comment_gif:not(.comment_built)');
    this.hideLineThreeDot();
    return false;
  },
  selectSticker: function (ele, id) {
    if ($Core.Comment.selectingSticker) {
      return false;
    }
    $Core.Comment.selectingSticker = true;
    var obj = $(ele),
      oInput = obj.closest('.js_app_comment_feed_form').find('.js_app_comment_feed_textarea:first');
    if (oInput.length && oInput.val() == '') {
      obj.closest('.js_comment_group_icon').removeClass('open').find('.js_comment_attach_sticker').removeClass('open-list');
      obj.closest('.js_app_comment_feed_form').find('.js_feed_comment_sticker_id').val(id);
      obj.closest('.js_app_comment_feed_form').trigger('submit');
      $Core.Comment.selectingSticker = false;
      return false;
    }
    $('.js_comment_attach_sticker').removeClass('open-list');
    $('.js_comment_group_icon').removeClass('open');
    obj.ajaxCall('comment.appendPreviewSticker', $.param({
      feed_id: obj.data('feed-id'),
      parent_id: obj.data('parent-id'),
      edit_id: obj.data('edit-id'),
      sticker_id: id
    }), 'post', null, function () {
      setTimeout(function () {
        $Core.Comment.selectingSticker = false;
      }, 500);

      if ($Core.Comment.canCheckValidate) {
        var formId = obj.closest('form').attr('id');
        var editForm = 'js_comment_textarea_edit';
        if ($('.js_comment_textarea_edit').length) {
          $Core.Comment.reloadCommentValidation.initialPhoto(editForm);
        } else if (formId.length){
          $Core.Comment.reloadCommentValidation.initialPhoto(formId);
        }
      }

    });
    return false;
  },
  updateMyStickerSet: function (ele, id, is_add) {
    var obj = $(ele);
    obj.addClass('disabled');
    obj.ajaxCall('comment.updateMyStickerSet', $.param({
      id: id,
      is_add: is_add,
      feed_id: obj.data('feed-id'),
      parent_id: obj.data('parent-id'),
      edit_id: obj.data('edit-id'),
    }), 'post', null, function (e, self) {
      self.removeClass('disabled');
    });
  },
  updateLayoutMyStickerSets: function (id, is_add) {
    var total_ele = $('.js_comment_my_sticker_set_total'),
      total = total_ele.data('total'),
      remain = !is_add ? parseInt(total) - 1 : parseInt(total) + 1,
      remove_ele = $('.js_comment_remove_sticker_set_' + id),
      add_ele = $('.js_comment_add_sticker_set_' + id);
    if (!is_add) {
      if (remain <= 0) {
        total_ele.html('(0)').data('total', 0);
        $('#core_comment_sticker_my').find('.js_comment_none_sticker_set').show();
      } else {
        total_ele.html('(' + remain + ')').data('total', remain);
      }
      //Remove set layout
      add_ele.addClass('btn').show();
      remove_ele.removeClass('btn').hide();
      $('.js_comment_my_sticker_set_' + id).remove();
    } else {
      add_ele.removeClass('btn').hide();
      remove_ele.addClass('btn').show();
      $('#core_comment_sticker_my').find('.js_comment_none_sticker_set').hide();
      total_ele.html('(' + remain + ')').data('total', remain);
    }

    return true;
  },
  removePreviewStickerSet: function (ele) {
    var oPopup = $(ele).closest('.js_box');
    oPopup.find('.js_comment_sticker_sets_holder').removeClass('hide');
    oPopup.find('.js_comment_preview_sticker_set_holder').empty().addClass('hide');
    $(ele).remove();
    return false;
  },
  getEditComment: function (id) {
    this.unsetAllEditComment();
    $('.js_comment_text_inner_' + id).find('.js_comment_text_holder').append('<div id="js_edit_comment_loading_' + id + '" class="js_edit_comment_loading content-text">' + $.ajaxProcess(getPhrase('loading_text_editor')) + '</div>');
    $('#js_comment_text_' + id).hide();
    $.ajaxCall('comment.getText', 'comment_id=' + id, 'post');
    return false;
  },
  unsetAllEditComment: function (id) {
    if (typeof id != 'undefined') {
      var ele = $('#js_comment_' + id);
      ele.removeClass('comment-item-edit');
      ele.find('.item-comment-options').removeClass('hide');
      ele.find('.js_comment_text_holder').show();
      ele.find('.js_comment_text_holder .content-text').show();
      ele.find('.comment_mini_action').show();
      ele.find('.js_edit_comment_loading').remove();
      ele.find('.js_edit_comment_holder').remove();

      if ($Core.Comment.canCheckValidate) {
        $Core.Comment.reloadCommentValidation.resetEdit();
      }

      return false;
    }
    $('.js_mini_feed_comment').each(function () {
      var ele = $(this);
      ele.removeClass('comment-item-edit');
      ele.find('.item-comment-options').removeClass('hide');
      ele.find('.js_comment_text_holder').show();
      ele.find('.js_comment_text_holder .content-text').show();
      ele.find('.comment_mini_action').show();
      ele.find('.js_edit_comment_loading').remove();
      ele.find('.js_edit_comment_holder').remove();
    });
    return false;
  },
  appendActionAfterEdit: function (id, remove) {
    if (remove) {
      $('#js_remove_preview_action_' + id).remove();
    } else if (!$('#js_remove_preview_action_' + id).length) {
      $('#js_comment_action_' + id).find('.action-list .item-reply').after(
        '<span class="item-remove-preview" id="js_remove_preview_action_' + id + '">' +
        '<a href="#" onclick="$.ajaxCall(\'comment.removePreview\',\'id=' + id + '\',\'post\'); return false;" class="comment-remove">' + comment_phrases['remove_preview'] + '</a>' +
        '</span>');
    }
    if (!$('#js_view_edit_history_action_' + id).length) {
      $('#js_comment_action_' + id).find('.action-list .item-time').after(
        '<span class="item-history" id="js_view_edit_history_action_' + id + '">' +
        '<a href="#" title="' + comment_phrases['show_edit_history'] + '" class="view-edit-history" onclick="tb_show(\'' + comment_phrases['edit_history'] + '\', $.ajaxBox(\'comment.showEditHistory\', \'id=' + id + '&height=400&width=600\')); return false;" >' + comment_phrases['edited'] + '</a>' +
        '</span>');
    }
    return false;
  },
  initFocusTextarea: function (ele) {
    var oGrand = $(ele).closest('.js_mini_feed_comment'),
      oParent = $(ele).closest('.js_edit_comment_holder');
    if (!oGrand.hasClass('mobile-style')) {
      oParent.find('.js_comment_focus_edit_comment').removeClass('hide');
      oParent.find('.js_comment_not_focus_edit_comment').addClass('hide');
    }
    if (typeof this.shadow !== 'undefined' && this.shadow) {
      this.shadow.css('width', $(ele).width());
      this.shadow.css('fontSize', $(ele).css('fontSize'));
      this.shadow.css('fontFamily', $(ele).css('fontFamily'));
      this.shadow.css('lineHeight', $(ele).css('lineHeight'));
    }
    this.resizeTextarea($('div.js_comment_textarea_edit'));
    this.commentFeedTextareaClick(ele);
  },
  initCanvasForSticker: function (sEle) {
    $(sEle).each(function () {
      var ele = this;
      if (/.*\.gif/.test($(ele).attr('src'))) {
        var canvas = document.createElement('canvas'),
          context = canvas.getContext("2d"),
          width = $(ele).width(),
          height = $(ele).height();
        if (width) {
          canvas.className = "core_comment_canvas_gif";
          canvas.height = height;
          canvas.width = width;
          var image = new Image();
          image.onload = function () {
            var steps = Math.min(Math.floor(image.width / width), 4);
            if (steps > 1) {
              for (var step = 1; step < steps - 1; step++) {
                image = $Core.Comment.scaleIt(image, (1 - 1 / steps));
              }
            }
            context.drawImage(image, 0, 0, width, height);
          };
          image.src = $(ele).attr('src');
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
      }
    });
  },
  scaleIt: function (source, scaleFactor) {
    var c = document.createElement('canvas');
    var ctx = c.getContext('2d');
    var w = source.width * scaleFactor;
    var h = source.height * scaleFactor;
    c.width = w;
    c.height = h;
    ctx.drawImage(source, 0, 0, w, h);
    return (c);
  },
  initStickerAttachBar: function (ele) {
    var parent_sticker = ele.closest('.comment-group-icon'),
      number_sticker = parent_sticker.find('.comment-full-sticker >.item-header-sticker ').length,
      width_sticker = parent_sticker.find('.comment-full-sticker .item-header-sticker').outerWidth(),
      width_full_Sticker = number_sticker * width_sticker - 2,
      width_container_sticker = parent_sticker.find('.header-sticker-list .item-container').outerWidth(),
      number_sticker_show = parseInt(width_container_sticker / width_sticker),
      sticker_next_max = (number_sticker - number_sticker_show) * width_sticker,
      sticker_rtl = 'left';
    if ($("html").attr("dir") === "rtl") {
      sticker_rtl = 'right';
    }
    if (parent_sticker.find('.comment-full-sticker').length) {
      if (width_full_Sticker < width_container_sticker) {
        parent_sticker.find('.comment-next-sticker').css('display', 'none');
      }
    } else {
      parent_sticker.find('.comment-next-sticker').css('display', 'none');
    }
    //Reset sticker next
    parent_sticker.find('.icon-sticker').data('sticker_next', 0);
    parent_sticker.addClass('dont-unbind-children');

    parent_sticker.find('.comment-next-sticker').off('click').on("click", function () {
      var oStickerIcon = parent_sticker.find('.icon-sticker');
      var sticker_next = oStickerIcon.data('sticker_next');
      if (sticker_next >= sticker_next_max) {
        return;
      }
      var parent = $(this).closest('.header-sticker-list');
      if (sticker_rtl === 'right') {
        parent.find('.comment-full-sticker').css({left: left}).animate({"right": "-" + (sticker_next + width_sticker) + "px"}, 300, function () {
          oStickerIcon.data('sticker_next', sticker_next + width_sticker);
          $Core.Comment.checkStickerBtn(parent, sticker_rtl);
        });
      } else {
        parent.find('.comment-full-sticker').css({left: left}).animate({"left": "-" + (sticker_next + width_sticker) + "px"}, 300, function () {
          oStickerIcon.data('sticker_next', sticker_next + width_sticker);
          $Core.Comment.checkStickerBtn(parent, sticker_rtl);
        });
      }

    });
    parent_sticker.find('.comment-prev-sticker').off('click').on("click", function () {
      var oStickerIcon = parent_sticker.find('.icon-sticker');
      var sticker_next = oStickerIcon.data('sticker_next');
      var parent = $(this).closest('.header-sticker-list');
      if (sticker_rtl === 'right') {
        parent.find('.comment-full-sticker').css({left: left}).animate({"right": "-" + (sticker_next - width_sticker) + "px"}, 300, function () {
          oStickerIcon.data('sticker_next', sticker_next - width_sticker);
          $Core.Comment.checkStickerBtn(parent, sticker_rtl);
        });
      } else {
        parent.find('.comment-full-sticker').css({left: left}).animate({"left": "-" + (sticker_next - width_sticker) + "px"}, 300, function () {
          oStickerIcon.data('sticker_next', sticker_next - width_sticker);
          $Core.Comment.checkStickerBtn(parent, sticker_rtl);
        });
      }
    });
  },
  updateCommentCounter: function (module_id, item_id, str) {
    var sId = '#js_feed_like_holder_' + module_id + '_' + item_id + ', #js_feed_mini_action_holder_' + module_id + '_' + item_id;
    if ($(sId).length && $(sId).find('.feed-comment-link .counter').length) {
      $(sId).each(function () {
        var count = $(this).find('.feed-comment-link .counter').first().text();
        if (!count) {
          count = 0;
        }
        if (str == '+') {
          count = parseInt(count) + 1;
        } else {
          count = parseInt(count) - 1;
        }
        count = count <= 0 ? '' : count;
        $(this).find('.feed-comment-link .counter').first().text(count);
      })
    }
    sId = '#js_feed_like_holder_' + module_id + '_comment_' + item_id + ', #js_feed_mini_action_holder_' + module_id + '_comment_' + item_id;
    if ($(sId).length && $(sId).find('.feed-comment-link .counter').length) {
      $(sId).each(function () {
        var count = $(this).find('.feed-comment-link .counter').first().text();
        if (!count) {
          count = 0;
        }
        if (str == '+') {
          count = parseInt(count) + 1;
        } else {
          count = parseInt(count) - 1;
        }
        count = count <= 0 ? '' : count;
        $(this).find('.feed-comment-link .counter').first().text(count);
      })
    }
    var sPagerId = $('#js_feed_comment_pager_' + module_id + '_' + item_id).find('.item-number');
    if (sPagerId.length) {
      var sText = sPagerId.html(),
        oText = sText.split('/');
      var iShown = str == '+' ? parseInt(oText[0]) + 1 : parseInt(oText[0]) - 1,
        iTotal = str == '+' ? parseInt(oText[1]) + 1 : parseInt(oText[1]) - 1;
      sPagerId.html(iShown + '/' + iTotal);
    }
  },
  updateReplyCounter: function (id, str, value, no_total) {
    if (!id) {
      return;
    }
    if (!value) {
      value = 1;
    }
    var sPagerId = $('.js_comment_replies_viewmore_' + id).find('.item-number');
    if (sPagerId.length) {
      sPagerId.each(function () {
        var sText = $(this).html(),
          oText = sText.split('/');
        var iShown = str == '+' ? parseInt(oText[0]) + value : parseInt(oText[0]) - value;
        if (!no_total) {
          var iTotal = str == '+' ? parseInt(oText[1]) + value : parseInt(oText[1]) - value;
        } else {
          var iTotal = parseInt(oText[1]);
        }
        $(this).html(iShown + '/' + iTotal);
      });
    }
  },
  hideLoadedReplies: function (ele, id) {
    var oHolder = $('.js_comment_view_more_replies_' + id),
      oHiddenSpan = oHolder.find('.js_link_href'),
      sHref = oHiddenSpan.data('href'),
      oComment = $('#js_comment_' + id),
      iAdded = oComment.find('.is_added_more').length;
    oComment.find('.reply_is_loadmore').remove();
    if (iAdded) {
      this.updateReplyCounter(id, '-', iAdded, true);
      var oMore = $('.js_comment_view_more_replies_' + id).find('.js_comment_number:first');
      if (oMore.length) {
        oMore.html(parseInt(oMore.html()) + iAdded);
      }
    }
    var sContent = oHiddenSpan.html();
    $(ele).parent().remove();
    if (!oComment.find('.comment_mini_child_content .js_mini_feed_comment').length) {
      oComment.removeClass('has-replies');
    }
    ;
    sHref = sHref.replace(/&max-time=[0-9]{10}/, '&max-time=' + (new Date).getTime().toString().substr(0, 10));
    oHiddenSpan.data('href', sHref);

    oHolder.prepend('<a href="' + sHref + '" class="item-viewmore ajax" onclick="$(this).addClass(\'active\');">' + (sContent != '' ? sContent : '') + '</a>');
    oHolder.siblings('.comment-viewmore').remove();
    oHolder.addClass('comment-viewmore').show();
    $Core.loadInit();
    return false;
  },
  hideLineThreeDot: function () {
    $('.comment_mini_child_holder').find('.js_mini_feed_comment').removeClass('css-hide-line');
    $('.js_hidden_comment_dot').each(function () {
      var oEle = $(this),
        oNext = oEle.next(),
        bMid = false;
      oEle.removeClass('css-hide-line');
      if (oNext.is(':last-child') && oNext.hasClass('js_mini_feed_comment')) {
        oEle.addClass('css-hide-line');
      } else {
        bMid = true;
      }
      var oPrevs = oEle.prevUntil('.js_mini_feed_comment:not(.view-hidden)');
      if (!bMid) {
        if (oPrevs.length) {
          var offset = oPrevs.length - 1;
          $(oPrevs[offset]).prev().addClass('css-hide-line');
        } else if (oEle.prev().hasClass('js_mini_feed_comment') && !oEle.prev().hasClass('js_hidden_comment_dot')) {
          oEle.prev().addClass('css-hide-line');
        }
      }
    });
  },
  loadStickerCollection: function (ele) {
    tb_show(comment_phrases['stickers'],
      $.ajaxBox('comment.loadStickerCollection', $.param({
        height: 400,
        width: 600,
        feed_id: $(ele).data('feed-id'),
        parent_id: $(ele).data('parent-id'),
        edit_id: $(ele).data('edit-id')
      }))
    );
    return false;
  },
  editFormStateInit: function () {
    if ($Core.Comment.canCheckValidate) {
      $Core.Comment.reloadCommentValidation.initialEdit();
    }

    $('.js_comment_textarea_edit').focus(function () {
      $Core.Comment.initFocusTextarea(this);
    }).keyup(function (event) {
      if (event.keyCode == 27) {
        if ($Core.Comment.canCheckValidate) {
          $Core.Comment.reloadCommentValidation.resetEdit();
        }
        return $Core.Comment.unsetAllEditComment();
      }
      if ($Core.Comment.canCheckValidate) {
        $Core.Comment.reloadCommentValidation.validateEdit();
      }

    }).blur(function () {
      var oGrand = $(this).closest('.js_mini_feed_comment'),
        oParent = $(this).closest('.js_edit_comment_holder');
      if (!oGrand.hasClass('mobile-style')) {
        setTimeout(function () {
          oParent.find('.js_comment_focus_edit_comment').addClass('hide');
          oParent.find('.js_comment_not_focus_edit_comment').removeClass('hide');
        }, 300);
      }
    });
  },
  loadingEmoticon: function (oEle, iFeedId, iParentId, iEditId) {
    oEle.addClass('open');
    var sHtml = '<div class="comment-emoji-container comment-group-icon comment-emoji-loading js_loading_emoticon_holder"><div class="item-icon loading"><i class="ico"/></div></div>', oContainer;
    if (iParentId) {
      oContainer = $('#js_comment_form_holder_' + iParentId).find('.js_comment_box .item-box-input');
    } else if (iFeedId) {
      oContainer = $('#js_item_feed_' + iFeedId).find('.js_feed_comment_form_holder .js_comment_box .item-box-input');
    } else {
      oContainer = $('.js_comment_text_inner_' + iEditId).find('.js_edit_comment_holder .js_app_comment_feed_form .item-box-input');
    }
    if (oContainer.siblings('.js_loading_emoticon_holder').length) {
      oContainer.siblings('.js_loading_emoticon_holder').remove();
    } else {
      oContainer.after(sHtml);
    }
  },
  loadedEmoticon: function (sContent, iFeedId, iParentId, iEditId, oEle) {
    var ele = '#comment_emoticon_set_holder';
    if (sContent !== null) {
      sContent = sContent.replace('js_comment_emoticon_container', '[CONTAINER-CLASS]');
      $(ele).length && $('#comment_emoticon_set_holder').remove();
      $('body').append('<div id="comment_emoticon_set_holder" style="display: none">' + sContent + '</div>');
    }
    if (!$(ele).length || (!iParentId && !iFeedId && !iEditId)) {
      return false;
    }
    var newContent = $(ele).html(), oContainer;
    newContent = newContent.replaceAll('[FEED-ID]', iFeedId);
    newContent = newContent.replaceAll('[PARENT-ID]', iParentId);
    newContent = newContent.replaceAll('[EDIT-ID]', iEditId);
    newContent = newContent.replace('[CONTAINER-CLASS]', 'js_comment_emoticon_container');
    newContent = newContent.replaceAll('[COMMENT-EMOTICON-LIST-CLASS]', 'comment-emoji-list');
    newContent = newContent.replaceAll('[EMOJI-IS-REPLY]', iParentId ? 'true' : 'false');
    newContent = newContent.replaceAll('[EMOJI-IS-EDIT]', iEditId ? 'true' : 'false');

    if (iParentId) {
      oContainer = $('#js_comment_form_holder_' + iParentId).find('.js_comment_box .item-box-input');
    } else if (iFeedId) {
      oContainer = $('#js_item_feed_' + iFeedId).find('.js_feed_comment_form_holder .js_comment_box .item-box-input');
    } else {
      oContainer = $('.js_comment_text_inner_' + iEditId).find('.js_edit_comment_holder .js_app_comment_feed_form .item-box-input');
    }
    oContainer.siblings('.js_loading_emoticon_holder').remove();
    oContainer.after(newContent);
  },
  loadingStickerSet: function (oEle, iFeedId, iParentId, iEditId) {
    oEle.addClass('open-list');
    var sHtml = '<div class="dropdown-menu comment-sticker-container comment-sticker-loading js_loading_sticker_holder"><div class="item-icon loading"><i class="ico"/></div></div>';
    if (iParentId) {
      $('.js_comment_icon_sticker_parent_' + iParentId).closest('.js_comment_group_icon').addClass('open').append(sHtml);
    } else if (iFeedId) {
      $('.js_comment_icon_sticker_' + iFeedId).closest('.js_comment_group_icon').addClass('open').append(sHtml);
    } else {
      $('.js_comment_quick_edit_holder_' + iEditId).find('.js_comment_group_icon').addClass('open').append(sHtml);
    }
  },
  loadedStickerSet: function (sContent, iFeedId, iParentId, iEditId, oEle) {
    var ele = '#comment_sticker_set_holder';
    if (sContent !== null) {
      sContent = sContent.replace('js_comment_sticker_container', '[CONTAINER-CLASS]');
      sContent = sContent.replaceAll('core_comment_gif', '[COMMENT-GIF-CLASS]');
      $(ele).length && $('#comment_sticker_set_holder').remove();
      $('body').append('<div id="comment_sticker_set_holder" style="display: none">' + sContent + '</div>');
    }
    if (!$(ele).length || (!iParentId && !iFeedId && !iEditId)) {
      return false;
    }
    //Process by each feed
    var newContent = $(ele).html(), oContainer;
    newContent = newContent.replaceAll('[FEED-ID]', iFeedId);
    newContent = newContent.replaceAll('[PARENT-ID]', iParentId);
    newContent = newContent.replaceAll('[EDIT-ID]', iEditId);
    newContent = newContent.replace('[CONTAINER-CLASS]', 'js_comment_sticker_container');
    newContent = newContent.replaceAll('[COMMENT-GIF-CLASS]', 'core_comment_gif');
    newContent = newContent.replaceAll('[COMMENT-STICKER-LIST-CLASS]', 'comment-sticker-list');
    if (iParentId) {
      oContainer = $('.js_comment_icon_sticker_parent_' + iParentId).closest('.js_comment_group_icon');
    } else if (iFeedId) {
      oContainer = $('.js_comment_icon_sticker_' + iFeedId).closest('.js_comment_group_icon');
    } else {
      oContainer = $('.js_comment_quick_edit_holder_' + iEditId).find('.js_comment_group_icon');
    }
    oContainer.find('.js_loading_sticker_holder').remove();
    oContainer.addClass('open').append(newContent);
    $Core.loadInit();
    if (oEle) {
      $Core.Comment.finalInitStickerSet(oEle);
    }
    return true;
  },
  finalInitStickerSet: function (ele) {
    $Core.Comment.initStickerAttachBar(ele);
    setTimeout(function () {
      $Core.Comment.initCanvasForSticker('.core_comment_gif:not(.comment_built)');
    }, 100);
  },
  updateRecentStickers: function () {
    var holderSticker = $('#comment_sticker_set_holder');
    if (!holderSticker.length) return false;
    $.ajaxCall('comment.updateGlobalRecentStickers');
  },
  reloadCommentValidation: {
    validate: function (formId) {
      var text_comment = $('.js_app_comment_feed_textarea'),
        type = text_comment.attr('name'),
        parentFormId = null;

      if (text_comment.length) {
        text_comment.each(function () {
          if (formId) {
            parentFormId = formId;
            if (parentFormId) {
              if (typeof $Core.reloadValidation.changedEleData[parentFormId] === 'undefined') {
                $Core.reloadValidation.changedEleData[parentFormId] = {};
              }

              $Core.reloadValidation.changedEleData[parentFormId][type] = true;
              $Core.reloadValidation.preventReload();
            }
          } else {
            $(this).on('keyup keydown paste DOMSubtreeModified change', function (e) {
              parentFormId = $(e.target).closest('form').attr('id');
              if (parentFormId) {
                if (typeof $Core.reloadValidation.changedEleData[parentFormId] === 'undefined') {
                  $Core.reloadValidation.changedEleData[parentFormId] = {};
                }

                $Core.reloadValidation.changedEleData[parentFormId][type] = true;
                if ($(this).text() === '') {
                  delete $Core.reloadValidation.changedEleData[parentFormId][type];
                }
                if (e.keyCode === 13) {
                  delete $Core.reloadValidation.changedEleData[parentFormId];
                }
                $Core.reloadValidation.preventReload();
              }
            });
          }
        })
      }
    },
    initialEdit: function (){
      if (typeof $Core.reloadValidation.initEleData['js_comment_textarea_edit'] === 'undefined') {
        $Core.reloadValidation.initEleData['js_comment_textarea_edit'] = {};
      }
      if (typeof $Core.reloadValidation.changedEleData['js_comment_textarea_edit'] === 'undefined') {
        $Core.reloadValidation.changedEleData['js_comment_textarea_edit'] = {};
      }

      if (typeof $Core.reloadValidation.initEleData['js_comment_textarea_edit']['val[text]'] === 'undefined') {
        $Core.reloadValidation.initEleData['js_comment_textarea_edit']['val[text]'] = $('.js_comment_textarea_edit').val();
      }

      if ($('.js_comment_attach_preview').length) {
        $Core.reloadValidation.initEleData['js_comment_textarea_edit']['val[photo]'] = true;
      }
    },
    validateEdit: function (){
      if ($Core.reloadValidation.initEleData['js_comment_textarea_edit']['val[text]'] !== $('.js_comment_textarea_edit').val()) {
        $Core.reloadValidation.changedEleData['js_comment_textarea_edit']['val[text]'] = true;
      } else {
        delete $Core.reloadValidation.changedEleData['js_comment_textarea_edit']['val[text]'];
      }

      $Core.reloadValidation.preventReload();
    },
    validatePhoto: function (obj){
      if ($Core.reloadValidation.changedEleData[obj].hasOwnProperty('val[photo]')) {
        delete $Core.reloadValidation.changedEleData[obj]['val[photo]'];
      }
      else if ($Core.reloadValidation.initEleData[obj].hasOwnProperty('val[photo]')) {
        $Core.reloadValidation.changedEleData[obj]['val[photo]'] = true;
      }

      $Core.reloadValidation.preventReload();

    },
    initialPhoto: function (obj){
      if (typeof $Core.reloadValidation.changedEleData[obj] === 'undefined') {
        $Core.reloadValidation.changedEleData[obj] = {};
      }
      $Core.reloadValidation.changedEleData[obj]['val[photo]'] = true;
      $Core.reloadValidation.preventReload();

    },
    resetEdit: function () {
      if ($Core.reloadValidation.changedEleData['js_comment_textarea_edit']) {
        delete $Core.reloadValidation.changedEleData['js_comment_textarea_edit'];
      }
      $Core.reloadValidation.preventReload();
    }
  }
};

$Behavior.commentInteraction = function () {
  // update width of comment box when focus to input
  $('.js_app_comment_feed_textarea').focus(function () {
    if (typeof $Core.Comment.shadow !== 'undefined' && $Core.Comment.shadow) {
      $Core.Comment.shadow.css('width', $(this).width());
      $Core.Comment.shadow.css('fontSize', $(this).css('fontSize'));
      $Core.Comment.shadow.css('fontFamily', $(this).css('fontFamily'));
      $Core.Comment.shadow.css('lineHeight', $(this).css('lineHeight'));
    }
    $Core.Comment.commentFeedTextareaClick(this);
  });

  $('.js_app_comment_feed_textarea').click(function () {
    if ($(this).html() === '') {
      $Core.divContenteditableInteraction.placeCaretAtEnd(this);
    }
  })
  
  // Update height of comment box when resize window size
  $(window).resize(function () {
    $('.js_app_comment_feed_textarea').each(function () {
      if (typeof $Core.Comment.shadow !== 'undefined' && $Core.Comment.shadow) {
        $Core.Comment.shadow.css('width', $(this).width());
      }
      $Core.Comment.resizeTextarea($(this));
    });
  });

  //sticker list
  $('body').off('click').on('click', function (e) {
    if (!$('.comment-group-icon.dropup').is(e.target)
      && $('.comment-group-icon.dropup').has(e.target).length === 0
      && $('.open').has(e.target).length === 0
      && !$('.fa-close').is(e.target)
      && !$(e.target).hasClass('ico-arrow-left')
      && !$(e.target).closest('.js_box').length
    ) {
      $('.comment-group-icon.dropup').removeClass('open');
      $('.comment-group-icon.dropup .icon-sticker').removeClass('open-list');
    }
  });

  $('.nav.comment-sticker-header li').off('click').on('click', function (event) {
    var parent_actived_sticker = $(this).closest('.comment-sticker-container');
    var actived_sticker = parent_actived_sticker.find('li.active');
    var content_sticker = $(this).find('a').attr('href');
    actived_sticker.removeClass('active');
    parent_actived_sticker.find('.tab-content.comment-sticker-content > div').removeClass('active');
    parent_actived_sticker.find(content_sticker).addClass('active');
    $(this).addClass('active');
  });

  //init scroll bar
  if (!(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))) {
    //Init scrollbar
    $(".comment-emoji-list").mCustomScrollbar({
      theme: "minimal-dark",
    }).addClass('dont-unbind-children');

    $(".comment-sticker-list").mCustomScrollbar({
      theme: "minimal-dark",
    }).addClass('dont-unbind-children');

    $(".comment-store-list, .comment-store-preview-main").mCustomScrollbar({
      theme: "minimal-dark",
    }).addClass('dont-unbind-children');

    $(".comment-edit-history-container").mCustomScrollbar({
      theme: "minimal-dark",
    }).addClass('dont-unbind-children');

    PF.event.on('before_cache_current_body', function () {
      $('.mCustomScrollbar').mCustomScrollbar('destroy');
    });
  } else {
    $('.comment-item-edit').addClass('mobile-style');
    $('.comment-footer').addClass('mobile-style');
    $('.comment-wrapper .js_hover_title').removeClass('js_hover_title');
    $('.comment-wrapper .js_hover_info').remove();
    $Core.Comment.bIsMobile = true;
  }

  $('.js_app_comment_feed_form').unbind().submit(function () {
    var t = $(this);
    t.addClass('in_process');
    var edit_id = $(this).data('edit-id');
    if (typeof edit_id != 'undefined' && edit_id > 0) {
      $(this).find('.js_feed_comment_process_form:first').show();
      $(this).ajaxCall('comment.updateText', null, null, null, function (e, self) {
        if($Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined") {
          $Core.Comment.reloadCommentValidation.resetEdit();
        }
        $(self).find('textarea').blur();
        isAddingComment = false;
        $('.js_feed_comment_process_form').fadeOut();
      });
      return false;
    }
    if ($Core.exists('#js_captcha_load_for_check')) {
      $('div#js_captcha_load_for_check').removeClass('built');
      $('#js_captcha_load_for_check').addClass('built').css({
        top: t.offset().top,
        left: '50%',
        'margin-left': '-' +
          (($('#js_captcha_load_for_check').width() / 2) + 12) + 'px',
        display: 'block',
      }).detach().appendTo('body').find('.captcha').attr('id', 'js_captcha_image').css({opacity: 0.0});

      $('#js_captcha_load_for_check.built').find('')

      $('#js_captcha_image').ajaxCall('captcha.reload',
        'sId=js_captcha_image&sInput=image_verification');
      $oLastFormSubmit = $(this);

      $('div#js_captcha_load_for_check:not(.built)').remove();

      return false;
    }

    if (function_exists('' + Editor.sEditor + '_wysiwyg_feed_comment_form')) {
      eval('' + Editor.sEditor + '_wysiwyg_feed_comment_form(this);');
    }

    $(this).parent().parent().find('.js_feed_comment_process_form:first').show();
    $(this).ajaxCall('comment.add', null, null, null, function (e, self) {
      $(self).find('div.contenteditable').blur();
      isAddingComment = false;
      $('.js_feed_comment_process_form').fadeOut();
    });

    $(this).find('.error_message').remove();
    $(this).find('textarea:first').removeClass('dont-unbind');
    coreCommentSavedRange = null;

    return false;
  });

  $('.js_comment_feed_new_reply').off('click').on('click', function () {
    var oEle = $(this)
        .parents('#js_comment_' + $(this).attr('rel') + ':first'),
      oParent = oEle.find('.js_comment_form_holder:first'),
      oGrand = oParent.parent(),
      iOwnerId = $(this).data('owner-id'),
      iCurrentUser = $(this).data('current-user'),
      isReply = $(this).data('parent-id') > 0 || $(this).closest('.item-outer').length;
    var oEleExist = $('#js_comment_form_holder_' + $(this).attr('rel')).find('.js_app_comment_feed_textarea:first');
    if (oEleExist.length) {
      if (oEleExist.val().replace(/\n/g, '') == '') {
        if (iOwnerId != iCurrentUser && isReply) {
          var sOwner = $(this).closest('.js_mini_feed_comment').find('.user_profile_link_span:first > a').text();
          oEleExist.val('[user=' + iOwnerId + ']' + sOwner + '[/user] ');
          //upgrade for div.contenteditable
          var oDivEleExist = $('#js_comment_form_holder_' + $(this).attr('rel')).find('div.js_app_comment_feed_textarea:first');
          if(oDivEleExist.length && oDivEleExist.text() == '') {
            //upgrade for div.contenteditable
            var span = $('<span />').attr({'id':'generated', 'class':'generatedMentionTag', 'contenteditable':false, 'data-type':'user', 'data-id': iOwnerId}).html(sOwner);
            oDivEleExist.empty();
            oDivEleExist.append(span).append('&zwnj;');
          }
        }
      }
      oEleExist.focus();
      return false;
    }
    oParent.detach().appendTo(oGrand);
    if ((Editor.sEditor == 'tiny_mce' || Editor.sEditor == 'tinymce') &&
      isset(tinyMCE) && isset(tinyMCE.activeEditor)) {
      $('.js_app_comment_feed_form').find('.js_feed_comment_parent_id:first').val($(this).attr('rel'));
      tinyMCE.activeEditor.focus();
      if (typeof ($.scrollTo) == 'function') {
        $.scrollTo('.js_app_comment_feed_form', 800);
      }
      return false;
    }

    var oCommentForm = $(this).parents('.js_feed_comment_border:first').find('.js_feed_core_comment_form:first'),
      sCommentForm = oCommentForm.html(),
      parent_id = $(this).attr('rel');
    oParent.html(sCommentForm);
    oParent.find('div.contenteditable').empty();
    //Is on mobile
    if (oCommentForm.closest('.js_feed_comment_form_holder').hasClass('mobile-style')) {
      oParent.addClass('mobile-style');
      oParent.find('.js_comment_box').append('<div class="comment-group-btn-icon"><div class="comment-btn"><button class="btn btn-primary btn-xs">' + comment_phrases['submit'] + '</button></div></div>');
    }
    oParent.addClass('comment-item comment-item-reply comment-reply-new');
    oParent.find('.js_feed_comment_parent_id:first').val(parent_id);
    $Core.Comment.resizeTextarea(oParent.find('.js_app_comment_feed_textarea:first'));
    oParent.find('.js_app_comment_feed_textarea:first').focus();
    oParent.find('.js_app_comment_feed_textarea:first')
      .attr('placeholder', oTranslations['write_a_reply']).val('');
    $Core.Comment.commentFeedTextareaClick(
      oParent.find('.js_app_comment_feed_textarea:first'));

    $('.js_feed_add_comment_button .error_message').remove();
    $('.js_comment_attach_sticker').removeClass('open-list');
    $('.js_comment_group_icon').removeClass('open');
    oParent.find('.button_set_off:first').show().removeClass('button_set_off');
    if (!$(this).data('is-single')) {
      oParent.closest('.js_mini_feed_comment').addClass('has-replies');
    }
    oParent.find('.js_comment_attach_photo').data('parent-id', parent_id);
    oParent.find('.js_attach_photo_input_file').data('parent-id', parent_id);
    oParent.find('.js_comment_attach_emoticon').data('parent-id', parent_id).removeClass('open');
    oParent.find('.js_comment_emoticon_container').remove();
    oParent.find('.js_comment_attach_sticker').data('parent-id', parent_id).removeClass('open-list').addClass('js_comment_icon_sticker_parent_' + parent_id).removeClass('js_comment_icon_sticker_' + oParent.find('.js_comment_attach_sticker').data('feed-id'));
    oParent.find('.comment-box').removeClass('comment-box').addClass('comment-box-reply').removeClass('has-photo-sticker');
    oParent.find('.js_comment_attach_preview').remove();
    oParent.find('.js_comment_sticker_container').remove();
    oParent.find('.js_preview_link_attachment_custom_form').remove();
    oParent.closest('.comment-container-reply').find('.comment_mini_child_content').append('<div class="js_comment_add_reply"></div>');
    oParent.siblings('.js_comment_view_more_reply_wrapper').append('<div class="js_comment_add_reply"></div>');
    if (iOwnerId != iCurrentUser && isReply) {
      var sOwnerName = $(this).closest('.js_mini_feed_comment').find('.user_profile_link_span:first > a').text();
      oParent.find('.js_app_comment_feed_textarea:first').val('[user=' + iOwnerId + ']' + sOwnerName + '[/user] ');
      //upgrade for div.contenteditable
      var oSpan = $('<span />').attr({'id':'generated', 'class':'generatedMentionTag', 'contenteditable':false, 'data-type':'user', 'data-id': iOwnerId}).html(sOwnerName);
      oParent.find('div.js_app_comment_feed_textarea:first').append(oSpan).append('&zwnj;');
    }
    let form_id = oParent.find('.form').attr('id');
    oParent.find('.form').attr('id', form_id + '_' + parent_id);
    $Core.loadInit();
    return false;
  });

  $('.js_comment_attach_photo').off('mousedown').on('mousedown', function () {
    $Core.Comment.lastClickObj = $(this);
    $(this).find('.js_attach_photo_input_file:first').trigger('click');
  });

  $('.js_attach_photo_input_file').off('change').on('change', function () {
    var input = $(this),
      files = input[0].files,
      feed_id = input.data('feed-id'),
      parent_id = input.data('parent-id'),
      edit_id = input.data('edit-id');
    $Core.Comment.selectingSticker = true;
    $Core.Comment.lastClickObj.addClass('loading');
    if (files.length) {
      var data = new FormData(),
        file = files[0];
      data.append('file', file);
      data.append('type', 'comment_comment');
      $.ajax({
        url: PF.url.make('core/upload-temp'),
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        headers: {
          'X-File-Name': encodeURIComponent(file.name),
          'X-File-Size': file.size,
          'X-File-Type': file.type,
        },
        type: 'POST',
        error: function (error) {
          tb_show(comment_phrases['notice'], '', null, error);
          if (typeof $Core.Comment.lastClickObj == 'object') {
            $Core.Comment.lastClickObj.removeClass('loading');
          }
          $Core.Comment.selectingSticker = false;
        },
        success: function (data) {
          var oData = JSON.parse(data);
          input.val('');
          if (!oData.file) {
            if (typeof $Core.Comment.lastClickObj == 'object') {
              $Core.Comment.lastClickObj.removeClass('loading');
            }
            $Core.Comment.selectingSticker = false;
            if (oData.error) {
              tb_show(comment_phrases['notice'], '', null, oData.error);
            } else {
              tb_show(comment_phrases['notice'], '', null, comment_phrases['oops_something_went_wrong']);
            }
            return false;
          }
          if (parent_id) {
            var obj_parent = $('#js_comment_form_holder_' + parent_id);
            obj_parent.find('.js_feed_comment_photo_id').val(oData.file);
            obj_parent.find('.js_comment_attach_photo').removeClass('loading');
            obj_parent.find('.js_comment_group_icon').removeClass('open').find('.js_comment_attach_sticker').removeClass('open-list');
            obj_parent.find('.item-edit-content').parents('.comment-box-reply').addClass('has-photo-sticker');
          } else if (feed_id) {
            var obj_feed = $('#js_item_feed_' + feed_id);
            obj_feed.find('.js_feed_comment_form_holder .js_feed_comment_photo_id').val(oData.file);
            obj_feed.find('.js_comment_attach_photo').removeClass('loading');
            obj_feed.find('.js_comment_group_icon').removeClass('open').find('.js_comment_attach_sticker').removeClass('open-list');
            obj_feed.find('.js_feed_core_comment_form .item-edit-content').parents('.comment-box').addClass('has-photo-sticker');
          } else if (edit_id) {
            var obj_edit = $('.js_comment_quick_edit_holder_' + edit_id);
            obj_edit.find('.js_feed_comment_photo_id').val(oData.file);
            obj_edit.find('.js_feed_comment_attach_change').val(1);
            obj_edit.find('.js_comment_attach_photo').removeClass('loading');
            obj_edit.find('.js_comment_group_icon').removeClass('open').find('.js_comment_attach_sticker').removeClass('open-list');
            obj_edit.find('.item-edit-content').parents('.comment-box-edit').addClass('has-photo-sticker');
          } else {
            tb_show(comment_phrases['notice'], '', null, comment_phrases['oops_something_went_wrong']);
            if (typeof $Core.Comment.lastClickObj == 'object') {
              $Core.Comment.lastClickObj.removeClass('loading');
            }
            $Core.Comment.selectingSticker = false;
            return false;
          }
          $.ajaxCall('comment.appendPreviewPhoto', $.param({
            feed_id: feed_id,
            parent_id: parent_id,
            edit_id: edit_id,
            id: oData.file
          }), 'post');

          if ($Core.Comment.canCheckValidate) {
            var formId = input.closest('form').attr('id');
            var editForm = 'js_comment_textarea_edit';
            if ($('.js_comment_textarea_edit').length) {
              $Core.Comment.reloadCommentValidation.initialPhoto(editForm);
            } else if (formId.length){
              $Core.Comment.reloadCommentValidation.initialPhoto(formId);
            }
          }

          return true;
        }
      });
    }
  });

  $('.js_comment_attach_sticker').off('click').on('click', function () {
    var _this = $(this);
    if (_this.hasClass('loading') || $Core.Comment.selectingSticker) {
      return false;
    }
    var feed_id = _this.data('feed-id'),
      parent_id = typeof _this.data('parent-id') != 'undefined' ? _this.data('parent-id') : 0,
      edit_id = typeof _this.data('edit-id') != 'undefined' ? _this.data('edit-id') : 0,
      sticker_ele = $('.js_sticker_set_' + feed_id + '_' + parent_id + '_' + edit_id + ':first'),
      general_sticker_holder = $('#comment_sticker_set_holder'),
      icon_group = sticker_ele.closest('.js_comment_group_icon');
    if (sticker_ele.length) {
      if (_this.hasClass('open-list') && sticker_ele.length) {
        icon_group.removeClass('open');
        _this.removeClass('open-list');
      } else {
        $('.js_comment_attach_sticker').removeClass('open-list');
        $('.js_comment_group_icon').removeClass('open');
        icon_group.addClass('open');
        _this.addClass('open-list');
      }
      return false;
    } else if (general_sticker_holder.length && general_sticker_holder.html().length) {
      $('.js_comment_attach_sticker').removeClass('open-list');
      $('.js_comment_group_icon').removeClass('open');
      icon_group.addClass('open');
      $Core.Comment.loadingStickerSet(_this, feed_id, parent_id, edit_id);
      setTimeout(function () {
        $Core.Comment.loadedStickerSet(null, feed_id, parent_id, edit_id, _this);
      }, 0);
      return false;
    }
    $('.js_comment_attach_sticker').removeClass('open-list');
    $('.js_comment_group_icon').removeClass('open');
    $Core.Comment.loadingStickerSet(_this, feed_id, parent_id, edit_id);
    _this.ajaxCall('comment.loadAttachSticker', $.param({
      feed_id: feed_id,
      parent_id: parent_id,
      edit_id: edit_id
    }), 'post', null, function (e, self) {
      $Core.Comment.finalInitStickerSet(self);
    });
    return false;
  });
  $('.js_comment_attach_emoticon').off('click').on('click', function () {
    var _this = $(this);
    if (_this.hasClass('loading')) {
      return false;
    }

    if (coreCommentSavedRange != null) {
      if (window.getSelection) {//non IE and there is already a selection
        var s = window.getSelection();
        if (s.rangeCount > 0) {
          s.removeAllRanges();
        }
        s.addRange(coreCommentSavedRange);
      }
      else if (document.createRange) {//non IE and no selection
        window.getSelection().addRange(coreCommentSavedRange);
      }
      else if (document.selection) {//IE
        coreCommentSavedRange.select();
      }
    }

    var feed_id = _this.data('feed-id'),
      parent_id = typeof _this.data('parent-id') != 'undefined' ? _this.data('parent-id') : 0,
      edit_id = typeof _this.data('edit-id') != 'undefined' ? _this.data('edit-id') : 0,
      general_emoji_holder = $('#comment_emoticon_set_holder'),
      emoji_ele = $('.js_emoticon_container_' + feed_id + '_' + parent_id + '_' + edit_id + ':first');
    if (emoji_ele.length) {
      if (_this.hasClass('open')) {
        emoji_ele.fadeOut();
        _this.removeClass('open');
      } else {
        $('.js_comment_emoticon_container').hide();
        $('.js_comment_attach_emoticon').removeClass('open');
        emoji_ele.fadeIn();
        _this.addClass('open');
      }
      return false;
    } else if (general_emoji_holder.length && general_emoji_holder.html().length) {
      $('.js_comment_emoticon_container').fadeOut();
      $('.js_comment_attach_emoticon').removeClass('open');
      $Core.Comment.loadingEmoticon(_this, feed_id, parent_id, edit_id);
      $Core.Comment.loadedEmoticon(null, feed_id, parent_id, edit_id, _this);
      return false;
    }

    $Core.Comment.loadingEmoticon(_this, feed_id, parent_id, edit_id);
    _this.ajaxCall('comment.loadAttachEmoticon', $.param({
      feed_id: feed_id,
      parent_id: parent_id,
      edit_id: edit_id
    }), 'post', null, function (e, self) {
      self.removeClass('loading');
      self.addClass('open');
    });
    return false;
  });
  $('.js_comment_view_more_reply_holder').each(function () {
    var oEle = $(this),
      oHiddenSpan = oEle.find('.js_link_href'),
      sContent = oHiddenSpan.html(),
      sHref = oHiddenSpan.data('href');
    oEle.find('.comment-viewmore').prepend('<a href="' + sHref + '" class="item-viewmore ajax" onclick="$(this).addClass(\'active\');">' + (sContent != '' ? sContent : '') + '</a>');
    var oContent = oEle.html();
    oEle.closest('.comment-container-reply').append(oContent);
    oEle.remove();
    $Behavior.onAjaxSubmit();
  });
  $Core.Comment.hideLineThreeDot();
  //Draw canvas for gif sticker
  $Core.Comment.initCanvasForSticker('.core_comment_gif:not(.comment_built)');
  if (typeof $Core.dropzone.instance['photo_feed'] != 'undefined') {
    $Core.dropzone.dropzone_id = 'photo_feed';
  }
};

$Behavior.commentFeedLoader = function () {
  /**
   * Click on adding a new comment link.
   */
  $('.js_feed_entry_add_comment').off('click').click(function () {
    $('.js_app_comment_feed_textarea').each(function () {
      if ($(this).val() == $('.js_comment_feed_value').html()) {
        $(this).removeClass('js_app_comment_feed_textarea_focus');
        $(this).val($('.js_comment_feed_value').html());
      }

      $(this).parents('.comment_mini').find('.feed_comment_buttons_wrap').hide();
    });

    $(this).parents('.js_parent_feed_entry:first').find('.comment_mini_content_holder').show();
    $(this).parents('.js_parent_feed_entry:first').find('.feed_comment_buttons_wrap').show();

    if ($(this).parents('.js_parent_feed_entry:first').find('.js_app_comment_feed_textarea').val() == $('.js_comment_feed_value').html()) {
      $(this).parents('.js_parent_feed_entry:first').find('.js_app_comment_feed_textarea').val('');
    }
    $(this).parents('.js_parent_feed_entry:first').find('.js_app_comment_feed_textarea')
      .focus().addClass('js_app_comment_feed_textarea_focus');
    $(this).parents('.js_parent_feed_entry:first').find('.comment_mini_textarea_holder').addClass('comment_mini_content');

    var iTotalComments = 0;
    $(this).parents('.js_parent_feed_entry:first').find('.js_mini_feed_comment').each(function () {
      iTotalComments++;
    });

    if (iTotalComments > 2) {
      $.scrollTo($(this).parents('.js_parent_feed_entry:first').find('.js_app_comment_feed_textarea_browse:first'), 340);
    }

    return false;
  });

  var selectors = '.js_app_comment_feed_textarea, .js_app_comment_feed_textarea_focus';
  $Core.attachFunctionTagger(selectors);
  $Core.Comment.resizeTextarea($('.js_comment_textarea_edit'));

  if (!String.prototype.endsWith) {
    String.prototype.endsWith = function (searchString, position) {
      var subjectString = this.toString();
      if (typeof position !== 'number' || !isFinite(position)
        || Math.floor(position) !== position || position > subjectString.length) {
        position = subjectString.length;
      }
      position -= searchString.length;
      var lastIndex = subjectString.indexOf(searchString, position);
      return lastIndex !== -1 && lastIndex === position;
    };
  }
};

$Behavior.initValidateComment = function () {
  if ($Core.Comment.canCheckValidate) {
    $Core.Comment.reloadCommentValidation.validate();
  }
}

$Behavior.getCaretOffsetComment = function () {
  $('.comment-textarea-edit').on('click change keyup keydown paste',function(){
    if (window.getSelection) {//non IE Browsers
      coreCommentSavedRange = window.getSelection().getRangeAt(0);
    }
    else if(document.selection) {//IE
      coreCommentSavedRange = document.selection.createRange();
    }
  });
}
