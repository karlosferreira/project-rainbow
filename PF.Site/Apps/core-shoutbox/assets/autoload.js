function autosize() {
  var el = $('#shoutbox_text_message_field').get(0);
  setTimeout(function () {
    el.style.cssText = 'height:auto; padding:8px';
    // for box-sizing other than "content-box" use:
    el.style.cssText = 'height:' + el.scrollHeight + 'px';
  }, 0);
}

function parseMessage(data) {
  //Init html
  var templateText = "<div class=\"row msg_container base_sent\" id=\"shoutbox_message_" + data.shoutbox_id + "\" data-value=\"" + data.shoutbox_id + "\">\
        <div class=\"msg_container_row shoutbox-item item-sent\">\
           <div class=\"shoutbox_action\">\
                <div class=\"shoutbox-like\">\
                    <a class=\"btn-shoutbox-like js_shoutbox_like unlike\" title=\"" + data.like_title + "\" data-type=\"like\" data-id=\"" + data.shoutbox_id + "\" onclick=\"appShoutbox.processLike(this);\"></a>\
                </div>\
                <div class=\"dropdown item-action-more js-shoutbox-action-more dont-unbind\">\
                    <a role=\"button\" data-toggle=\"dropdown\" href=\"#\"  aria-expanded=\"true\">\
                        <span class=\"ico ico-dottedmore\"></span>\
                    </a>\
                    <ul class=\"dropdown-menu dropdown-menu-right dont-unbind\">\
                        <li>\
                            <a  href=\"\" onclick=\"appShoutbox.quote(this);\" data-value=\"" + data.shoutbox_id + "\" title=\"" + data.quote_hover_title + "\"><i class=\"ico ico-quote-circle-alt-left-o\" aria-hidden=\"true\"></i> " + data.quote_hover_title + " </a>\
                        </li>";
  if (data.can_edit) {
    templateText += "<li>\
            <a href=\"\" class=\"\" onclick=\"appShoutbox.openEditPopup(this);\" data-phrase=\"" + data.edit_title + "\" data-value=\"" + data.shoutbox_id + "\" title=\"" + data.hover_title + "\"><i class=\"ico ico-pencil\" aria-hidden=\"true\"></i>" + data.hover_title + "</a>\
            <li>";
  }
  if (data.can_delete) {
    templateText += "<li>\
        <a href=\"\" class=\"\" onclick=\"appShoutbox.dismiss(this);\" data-value=\"" + data.shoutbox_id + "\" title=\"" + data.dismiss_hover_title + "\"><i class=\"ico ico-trash-o\ aria-hidden=\"true\"></i>" + data.dismiss_hover_title + "</a>\
        <li>";
  }
  templateText += "</ul></div></div><div class=\"item-outer" + (data.can_delete ? " can-delete" : "") + "\">\
                <div class=\"item-media-source\"></div>\
                <div class=\"item-inner\">\
                    <div class=\"title_avatar item-shoutbox-body msg_body_sent\" title=\"" + data.user_full_name + "\">\
                        <div class=\" item-title\">\
                            <a href=\"" + data.user_profile_link + "\" title=\"" + data.user_full_name + "\">\
                                " + data.user_full_name + "\
                            </a>\
                        </div>\
                        <div class=\"messages_body item-message\">\
                            <div class=\"item-message-info item_view_content\">\
                                " + data.text + "\
                            </div>\
                        </div>\
                    </div>\
                    <span class=\"js_shoutbox_text_total_like item-count-like\"></span>\
                    <div class=\"item-time\">\
                    <span class=\"message_convert_time\" data-id=\"" + data.time_stamp + "\"> " + data.parsed_time + "</span>\
                    <span class=\"item-edit-info js_edited_text hide\"></span>\
                    </div>\
                </div>\
            </div>\
        </div>\
    </div>\
    ";
  var replaceArray = {
    '__base_type__': data.base_type,
    '__item_sent_receive_type__': data.item_sent_receive_type,
    '__can_delete_class__': data.can_delete_class,
    '__user_avatar__': data.user_avatar,
    '__msg_body_receive_sent__': data.msg_body_receive_sent,
    '__can_delete_hide_class__': data.can_delete_hide_class
  };
  //Update variable
  for (var key in replaceArray) {
    templateText = templateText.replace(key, replaceArray[key]);
  }
  return templateText;
}


function _getShoutboxContent() {
  var first = $(".msg_container_base>.base_receive").last().attr('data-value');
  var module_id = $('[data-name="parent_module_id"]').val();
  var item_id = $('[data-name="parent_item_id"]').val();
  if (typeof module_id === 'undefined') {
    return false;
  }
  var queryString = {
    'last': first,
    'parent_module_id': module_id,
    'parent_item_id': item_id,
    'type': 'pull'
  };

  $.ajax(
    {
      type: 'POST',
      url: oParams.shoutbox_polling,
      data: queryString,
      timeout: 5 * 60 * 1000,//5 minutes
      success: function (data) {
        localStorage.shoutbox_data = jQuery.parseJSON(data);
        r_data(jQuery.parseJSON(data), false);
        $Behavior.shoutbox_dropdown_custom();
      }
    }
  ).always(function () {
    setTimeout(function () {
      _getShoutboxContent();
    }, oParams.shoutbox_sleeping_time);
    window.loadTime();
  });
}

function _convertTime(timestamp) {
  if (timestamp == 0) {
    return false;
  }
  var n = new Date();
  var c = new Date(timestamp * 1000);
  var now = Math.round(n.getTime() / 1000);
  var iSeconds = Math.round(now - timestamp);
  var iMinutes = Math.round(iSeconds / 60);
  var hour = Math.round(parseFloat(iMinutes) / 60.0);
  if (hour >= 48) {
    return false;
  }
  if (iMinutes < 1) {
    return oTranslations['just_now'];
  }
  if (iMinutes < 60) {
    if (iMinutes === 0 || iMinutes === 1) {
      return oTranslations['a_minute_ago'];
    }
    return iMinutes + ' ' + oTranslations['minutes_ago'];
  }
  if (hour < 24) {
    if (hour === 0 || hour === 1) {
      return oTranslations['a_hour_ago'];
    }
    return hour + ' ' + oTranslations['hours_ago'];
  }
  if (hour < 48 && ((n.getDay()) - 1) == c.getDay()) {
    return oTranslations['yesterday'] + ', ' + c.getHours() + ':' + c.getMinutes();
  }
}

function shoutboxSubmit() {
  var formTextEle = $('[data-toggle="shoutbox"][data-name="text"]');
  var n = new Date();
  var formTextElevalue = formTextEle.val().replace(/\s/g, '');
  if (formTextElevalue == '') {
    window.parent.sCustomMessageString = $('#shoutbox_error_notice').data('message');
    tb_show($('#shoutbox_error_notice').data('title'), $.ajaxBox('core.message', 'height=150&width=300'));
  } else {
    var formItem = $('[data-toggle="shoutbox"]');
    var seconds = new Date().getTime() / 1000;
    var queryString = {
      'type': 'push',
    };
    $(formItem).each(function (index) {
      var elementValue = $(this).val();
      queryString[$(this).attr("data-name")] = elementValue.replace(/<\/?[^>]+(>|$)/g, "");
    });
    //clear form text
    formTextEle.val('');
    autosize();
    $('#pf_shoutbox_text_counter').html(0);
    $.ajax(
      {
        type: 'POST',
        url: oParams.shoutbox_polling,
        data: queryString,
        timeout: 5 * 60 * 1000,//5 minutes
        success: function (data) {
          var result = jQuery.parseJSON(data);
          if (typeof result.shoutbox_id !== 'undefined') {
            var ele = $('.msg_container_base');
            var sText = parseMessage(result);
            ele.append(sText);
            scroll_bottom();
            $('.item_view_content').not('.twa_built').each(function (i, d) {
              var t = $(this);
              t.addClass('twa_built');
              $(d).emoji();
            });
            $(formTextEle).trigger('input');
            $Behavior.shoutbox_dropdown_custom();
          } else {
            $Core.slideAlert('.msg_container_base', result.error, 'danger');
          }

        }
      }
    ).always(function () {
      window.loadTime();
    }).fail(function () {
      $("[data-id='" + seconds + "']").removeAttr('data-id').html('Error');
    });
    scroll_bottom()
  }
}

function scroll_bottom() {
  var div = $(".msg_container_base");
  if (div.length) {
    div.scrollTop(div[0].scrollHeight);
  }
}

function r_data(params, prepend) {
  if (typeof params.shoutbox_id === "undefined") {
    return;
  }
  if ($('#shoutbox_message_' + params.shoutbox_id).length) {
    return;
  }
  var appendContent = '<div class="row msg_container base_receive" id="shoutbox_message_' + params.shoutbox_id + '" data-value="' + params.shoutbox_id + '">';
  appendContent += "<div class=\"msg_container_row shoutbox-item  item-receive\">";
  if (params.can_show_action) {
    appendContent += "<div class=\"shoutbox_action\">";
    if (params.can_quote) {
      appendContent += "<div class=\"shoutbox-like\">" +
        "<a class=\"btn-shoutbox-like js_shoutbox_like " + (params.is_liked ? 'liked' : 'unlike') + "\" title=\"" + (params.is_liked ? params.unlike_title : params.like_title) + "\" data-type=\"" + (params.is_liked ? 'unlike' : 'like') + "\" data-id=\"" + params.shoutbox_id + "\" onclick=\"appShoutbox.processLike(this);\"></a>" +
        "</div>";
    }
    appendContent += "<div class=\"dropdown item-action-more js-shoutbox-action-more dont-unbind\">";
    appendContent += "<a role=\"button\" data-toggle=\"dropdown\" href=\"#\" class=\"\" aria-expanded=\"true\"><span class=\"ico ico-dottedmore\"></span></a><ul class=\"dropdown-menu dropdown-menu-right dont-unbind\">";
    if (params.can_quote) {
      appendContent += "<li><a href=\"\" class=\"quote\" onclick=\"appShoutbox.quote(this);\" data-value=\"" + params.shoutbox_id + "\" title=\"" + params.quote_hover_title + "\"><i class=\"ico ico-quote-circle-alt-left-o\" aria-hidden=\"true\"></i>" + params.quote_hover_title + "</a></li>";
    }
    if (params.can_edit) {
      appendContent += "<li><a href=\"\" class=\"\" onclick=\"appShoutbox.openEditPopup(this);\" data-phrase=\"" + params.edit_title + "\" data-value=\"" + params.shoutbox_id + "\" title=\"" + params.hover_title + "\"><i class=\"ico ico-pencil\" aria-hidden=\"true\"></i>" + params.hover_title + "</a></li>";
    }
    if (params.can_delete) {
      appendContent += '<li><a href=\"\" class=\"\" onclick="appShoutbox.dismiss(this);" data-value="' + params.shoutbox_id + '" title="' + params.dismiss_hover_title + '"><i class="ico ico-trash-o" aria-hidden="true"></i>' + params.dismiss_hover_title + '</a></li>';
    }
    appendContent += '</ul></div></div>';
  }
  appendContent += "<div class=\"item-outer" + (params.can_delete ? " can-delete" : "") + "\">";
  appendContent += "<div class=\"item-media-source\">";
  appendContent += params.user_avatar;
  appendContent += "</div>";
  appendContent += "<div class=\"item-inner\">";
  appendContent += "<div class=\"title_avatar item-shoutbox-body  msg_body_receive \" title=\"" + params.user_full_name + "\">";
  appendContent += "<div class=\"item-title \">";
  appendContent += "<a href=\"" + params.user_profile_link + "\" title=\"" + params.user_full_name + "\">";
  appendContent += params.user_full_name;
  appendContent += "</a>";
  appendContent += "</div>";
  appendContent += "<div class=\"messages_body item-message \">";
  appendContent += "<div class=\"item-message-info item_view_content\">";
  appendContent += params.text;
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "<span class=\"js_shoutbox_text_total_like item-count-like\">" + (parseInt(params.total_like) > 0 ? ('<a href="javascript:void(0);" onclick="appShoutbox.showLikedMembers(' + params.shoutbox_id + ');">' + params.total_like + ' ' + (parseInt(params.total_like) == 1 ? params.like_title : params.likes_title) + '</a>') : '') + "</span>";
  appendContent += '<div class=\"item-time\"><span class="message_convert_time item-time" data-id="' + params.timestamp + '">' + ((params.parsed_time != 'undefined') ? params.parsed_time : '') + '</span><span class=\"item-edit-info js_edited_text' + (parseInt(params.is_edited) == 1 ? '' : ' hide') + '\">' + (parseInt(params.is_edited) == 1 ? params.edited_title : '') + '</span></div>';
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "</div>";

  var ele = $('.msg_container_base');
  if (prepend) {
    ele.prepend(appendContent);
  } else {
    ele.append(appendContent);
    scroll_bottom()
  }

  window.loadTime();
}

function s_data(params, prepend) {
  if (typeof params.shoutbox_id === "undefined") {
    return;
  }
  if ($('#shoutbox_message_' + params.shoutbox_id).length) {
    return;
  }
  var appendContent = '<div class="row msg_container base_sent" id="shoutbox_message_' + params.shoutbox_id + '" data-value="' + params.shoutbox_id + '">';
  appendContent += "<div class=\"msg_container_row shoutbox-item  item-sent\">"
  if (params.can_show_action) {
    appendContent += "<div class=\"shoutbox_action\">";
    if (params.can_quote) {
      appendContent += "<div class=\"shoutbox-like\">" +
        "<a class=\"btn-shoutbox-like js_shoutbox_like " + (params.is_liked ? 'liked' : 'unlike') + "\" title=\"" + (params.is_liked ? params.unlike_title : params.like_title) + "\" data-type=\"" + (params.is_liked ? 'unlike' : 'like') + "\" data-id=\"" + params.shoutbox_id + "\" onclick=\"appShoutbox.processLike(this);\"></a>" +
        "</div>";
    }
    appendContent += "<div class=\"dropdown item-action-more js-shoutbox-action-more dont-unbind\">";
    appendContent += "<a role=\"button\" data-toggle=\"dropdown\" href=\"#\" class=\"\" aria-expanded=\"true\"><span class=\"ico ico-dottedmore\"></span></a><ul class=\"dropdown-menu dropdown-menu-right dont-unbind\">";
    if (params.can_quote) {
      appendContent += "<li><a href=\"\" class=\"quote\" onclick=\"appShoutbox.quote(this);\" data-value=\"" + params.shoutbox_id + "\" title=\"" + params.quote_hover_title + "\"><i class=\"ico ico-quote-circle-alt-left-o\" aria-hidden=\"true\"></i>" + params.quote_hover_title + "</a></li>";
    }
    if (params.can_edit) {
      appendContent += "<li><a href=\"\" class=\"\" onclick=\"appShoutbox.openEditPopup(this);\" data-phrase=\"" + params.edit_title + "\" data-value=\"" + params.shoutbox_id + "\" title=\"" + params.hover_title + "\"><i class=\"ico ico-pencil\" aria-hidden=\"true\"></i>" + params.hover_title + "</a></li>";
    }
    if (params.can_delete) {
      appendContent += '<li><a href=\"\" class=\"\" onclick="appShoutbox.dismiss(this);" data-value="' + params.shoutbox_id + '" title="' + params.dismiss_hover_title + '"><i class="ico ico-trash-o" aria-hidden="true"></i>' + params.dismiss_hover_title + '</a></li>';
    }
    appendContent += '</ul></div></div>';
  }
  appendContent += "<div class=\"item-outer" + (params.can_delete ? " can-delete" : "") + "\">";
  appendContent += "<div class=\"item-media-source\">";
  appendContent += params.user_avatar;
  appendContent += "</div>";
  appendContent += "<div class=\"item-inner\">";
  appendContent += "<div class=\"title_avatar item-shoutbox-body  msg_body_sent \" title=\"" + params.user_full_name + "\">";
  appendContent += "<div class=\"item-title \">";
  appendContent += "<a href=\"" + params.user_profile_link + "\" title=\"" + params.user_full_name + "\">";
  appendContent += params.user_full_name;
  appendContent += "</a>";
  appendContent += "</div>";
  appendContent += "<div class=\"messages_body item-message \">";
  appendContent += "<div class=\"item-message-info item_view_content\">";
  appendContent += params.text;
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "<span class=\"js_shoutbox_text_total_like item-count-like\">" + (parseInt(params.total_like) > 0 ? ('<a href="javascript:void(0);" onclick="appShoutbox.showLikedMembers(' + params.shoutbox_id + ');">' + params.total_like + ' ' + (parseInt(params.total_like) == 1 ? params.like_title : params.likes_title) + '</a>') : '') + "</span>";
  appendContent += '<div class=\"item-time\"><span class="message_convert_time item-time" data-id="' + params.timestamp + '">' + ((params.parsed_time != 'undefined') ? params.parsed_time : '') + '</span><span class=\"item-edit-info js_edited_text' + (parseInt(params.is_edited) == 1 ? '' : ' hide') + '\">' + (parseInt(params.is_edited) == 1 ? params.edited_title : '') + '</span></div>';
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "</div>";
  appendContent += "</div>";


  var ele = $('.msg_container_base');
  if (prepend) {
    ele.prepend(appendContent);
  } else {
    ele.append(appendContent);
    scroll_bottom()
  }
}

window.loadTime = function () {
  $('.message_convert_time').each(function (key) {
    if ($(this).attr('data-id') > 0) {
      var time = _convertTime($(this).attr('data-id'));
      if (time !== false) {
        $(this).html(time);
      }
    }
  });
};

$Ready(function () {

  var textarea = document.getElementById('shoutbox_text_message_field');
  if (textarea) {
    textarea.addEventListener('keydown', autosize);
    $('.emoji-list li').on('click', autosize);
  }

  $('[data-name="shoutbox-submit"]').off('click').click(function () {
    shoutboxSubmit();
  });
  $('#shoutbox_text_message_field').off('keypress').keypress(function (e) {
    if (e.which == 13) {
      shoutboxSubmit();
      return false;
    }
  });
  $('.msg_container_base').on('scroll', function () {
    if (!$('#shoutbox_loading_new').length) {
      if ($(this).scrollTop() == 0) {
        var last = $(".msg_container_base>.msg_container").first().attr('data-value');
        var queryString = {
          'last': last,
          'parent_module_id': $('[data-name="parent_module_id"]').val(),
          'parent_item_id': $('[data-name="parent_item_id"]').val(),
          'type': 'more'
        };

        $.ajax(
          {
            type: 'POST',
            url: oParams.shoutbox_polling,
            data: queryString,
            beforeSend: function () {
              $('.msg_container_base').prepend('<div id="shoutbox_loading_new"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i></div>');
            },
            timeout: 5 * 60 * 1000,//5 minutes
            success: function (data) {
              var objectData = jQuery.parseJSON(data);
              if (typeof objectData.empty != "undefined") {
                $('#shoutbox_loading_new:not(".stop")').remove();
                $('.msg_container_base').prepend('<div id="shoutbox_loading_new" class="stop"></div>');
              } else {
                $.each(objectData, function (key, value) {
                  if (typeof value.type != "undefined") {
                    if (value.type == 'r') {
                      r_data(value, true);
                    } else if (value.type == 's') {
                      s_data(value, true);
                    }
                  }
                });
                $('.item_view_content').not('.twa_built').each(function (i, d) {
                  var t = $(this);
                  t.addClass('twa_built');
                  $(d).emoji();
                });
              }
            }//success
          }
        ).always(function () {
          $('#shoutbox_loading_new:not(".stop")').remove();
          window.loadTime();
          $Behavior.shoutbox_dropdown_custom();
        });
      }
    }
  });

  window.loadTime();
// };
});

$(document).on('input', '#shoutbox_text_message_field', function () {
  appShoutbox.processQuote("push");
});

var appShoutbox = {
  dismiss: function (obj) {
    $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
      var id = $(obj).attr("data-value");
      $.ajaxCall('shoutbox.delete', 'id=' + id, 'GET');
      $('#shoutbox_message_' + id).fadeOut();
    });
  },
  quote: function (obj) {
    var id = $(obj).attr("data-value");
    $('#shoutbox_text_message_field').val("[quote=" + id + "] ").focus();
    $('#pf_shoutbox_text_counter').html($('#shoutbox_text_message_field').val().length);
  },
  openEditPopup: function (obj) {
    var iShoutboxId = $(obj).data('value');
    tb_show($(obj).data('phrase'), $.ajaxBox('shoutbox.openEditPopup', 'shoutbox_id=' + iShoutboxId));
  },
  changeText: function (iShoutboxId) {
    $('#shoutbox_message_' + iShoutboxId + ' .item_view_content').emoji();
  },
  deleteQuotedMessage: function (obj) {
    $(obj).parent().remove();
  },
  processLike: function (obj) {
    $.ajaxCall('shoutbox.processLike', 'type=' + $(obj).data('type') + '&shoutbox_id=' + $(obj).data('id'));
  },
  preventAddEmojiTextOverLimit: function () {
    var oTextArea = $(this).closest('#js_edit_shoutbox_message_content').length ? $('#shoutbox_edit_message_input') : $('#shoutbox_text_message_field');
    if (oTextArea.length) {
      var currentText = oTextArea.val();
      if (currentText.length > 255) {
        var emojiText = $(this).find('span').html();
        var currentText = currentText.substring(0, currentText.length - emojiText.length);
        oTextArea.val(currentText);
        oTextArea.trigger('input');
      }
      else {
        oTextArea.trigger('input');
      }
    }
  },
  processQuote: function (type) {
    var regex = new RegExp("\[quote=[0-9]+\]", "g");
    var textarea = type == 'push' ? $('#shoutbox_text_message_field') : $('#shoutbox_edit_message_input');
    var parent = type == 'push' ? '#msg_container_base' : '#js_edit_shoutbox_message_content';
    var text = textarea.val();
    var isValid = true;
    var countMatch = 0;
    if (type == "push") {
      text = text.replace(regex, function (match) {
        countMatch++;
        if (countMatch > 1) {
          isValid = false;
          return '';
        }
        return match;
      });
      $('#pf_shoutbox_text_counter').html(text.length);
      if (!isValid) {
        $Core.slideAlert(parent, $(parent).data('error-quote-message'), 'danger');
      }
    }
    else {
      var hasQuoted = $('#js_edit_shoutbox_message_content').find('.js_quoted_message_container').length;
      text = text.replace(regex, function (match) {
        countMatch++;
        if ((hasQuoted && countMatch > 0) || (!hasQuoted && countMatch > 1)) {
          isValid = false;
          return '';
        }
        return match;
      });
      $('#pf_shoutbox_edit_text_counter').html(text.length);
      if (!isValid) {
        $('#js_edit_shoutbox_message_content').find('.error_message').html($(parent).data('error-quote-message')).removeClass('hide');
      }
    }
    textarea.val(text);
  },
  showLikedMembers: function (shoutbox_id) {
    $Core.box('like.browse', 450, 'type_id=shoutbox&item_id=' + shoutbox_id);
  },
};
$Behavior.edit_message = function () {
  if ($('#js_block_border__apps__shoutbox_block_chat').length && !$('#js_block_border__apps__shoutbox_block_chat').hasClass('built')) {
    _getShoutboxContent();
    scroll_bottom();
    $('#js_block_border__apps__shoutbox_block_chat').addClass('built');
  }
  $('.js_quoted_message_container').hover(function () {
    $(this).find('.js_btn_delete_quoted').removeClass('hide');
  }, function () {
    $(this).find('.js_btn_delete_quoted').addClass('hide');
  });
  if ($('#js_block_border__apps__shoutbox_block_chat').length) {
    $('.emoji-list li').on('click', appShoutbox.preventAddEmojiTextOverLimit);
  }
  if ($('.js_edit_shoutbox_message_content').length) {
    var oEditMessageContent = $('.js_edit_shoutbox_message_content');
    oEditMessageContent.find('.emoji-list li').on('click', appShoutbox.preventAddEmojiTextOverLimit);
  }
}

$Behavior.shoutbox_dropdown_custom = function () {
  var dropdownShoutbox;

  function checkscroll() {
    var eOffsetNew = $('.js-shoutbox-action-more.open').offset(),
      parentOffset = $('#js_block_border__apps__shoutbox_block_chat >.content').offset(),
      eHeightNew = $('.js-shoutbox-action-more.open').outerHeight();
    //element when moved
    var eDropdownTarget = $('#js_block_border__apps__shoutbox_block_chat >.content').children('.dropdown-menu');
    if ($('.js-shoutbox-action-more.open').length > 0) {
      //update new postion when scroll shoutbox
      var eNewTop = eOffsetNew.top + eHeightNew - parentOffset.top;
      eDropdownTarget.css({
        'top': eNewTop
      });
    }
    if (eDropdownTarget.length > 0) {
      var eTop = eDropdownTarget.offset().top,
        pTop = $('#js_block_border__apps__shoutbox_block_chat >.content').offset().top + $('#js_block_border__apps__shoutbox_block_chat >.content .shoutbox-container').outerHeight();
      if (eTop > pTop || eNewTop < 0) {
        $(this).find('.dropdown.open').removeClass('open').trigger('hidden.bs.dropdown');

      }
    }
  }

  $('.shoutbox-container').on('scroll', checkscroll);
  $('.js-shoutbox-action-more .dropdown-menu a').off('click').on('click', function (event) {
    event.preventDefault();
  });
  $('.js-shoutbox-action-more').on('show.bs.dropdown', function (e) {
    $(this).closest('.shoutbox_action').addClass('has-open-dropdown');

    // grab the menu
    dropdownShoutbox = $(e.target).find('.dropdown-menu');

    // detach it and append it to the body
    $('#js_block_border__apps__shoutbox_block_chat >.content').append(dropdownShoutbox.detach());

    // grab the new offset position
    var eOffset = $(e.target).offset(),
      parentOffset = $('#js_block_border__apps__shoutbox_block_chat >.content').offset(),
      eHeight = $(e.target).outerHeight(),
      eLeft = eOffset.left - dropdownShoutbox.outerWidth() + $(e.target).outerWidth() + 8 - parentOffset.left;
      if(eLeft < 0){
          eLeft = eLeft + dropdownShoutbox.outerWidth() - 32;
          dropdownShoutbox.removeClass("dropdown-menu-right").addClass("dropdown-menu-left");
      }
    // make sure to place it where it would normally go (this could be improved)
    dropdownShoutbox.css({
      'display': 'block',
      'top': eOffset.top + eHeight - parentOffset.top,
      'left': eLeft,
      'opacity': '1',
      'visibility': 'visible',
      'right': 'auto',
      'transform': 'none',
      'margin': '0',
      'transition': 'none'
    });

  }).on('hidden.bs.dropdown', function (e) {
    $(this).closest('.shoutbox_action').removeClass('has-open-dropdown');

    // grab the menu
    dropdownShoutbox = $(e.target).closest('#js_block_border__apps__shoutbox_block_chat >.content').children('.dropdown-menu');

    $(e.target).append(dropdownShoutbox.detach());
    dropdownShoutbox.hide();

  });


}

