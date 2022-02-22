var PReaction = {
  isClicked: false,
  isHover: false,
  openTimer: null,
  closeTimer: null,
  shownReact: false,
  isMobile: false,
  initReaction: function () {
    //auto position tooltip
    $('.comment_mini_action .p-reaction-list-mini').off('mouseover').on('mouseover', function () {
      var pos = $(this).offset().top + $(this).outerHeight();
      var window_top = $(window).scrollTop();
      var window_bottom = window_top + $(window).height();
      var height = $(this).find('.p-reaction-tooltip-total').height();
      if ((window_bottom - pos) < (height + 10)) {
        $(this).find('.p-reaction-tooltip-total').addClass('reverse');
      } else {
        $(this).find('.p-reaction-tooltip-total').removeClass('reverse');
      }

    });
    $('.comment_mini_action .p-reaction-container-js').parent().addClass('p-reaction-container-outer');
    //popup check scroll
    if ($('.p-reaction-popup-box').is(':visible')) {
      PReaction.checkScrollPopup();
    }
    //end
    if ((/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))) {
      $('.js_p_reaction_tooltip,.p-reaction-list').addClass('is-mobile');
      $('.p-reaction-item').removeAttr('data-toggle');
      //check scroll
      PReaction.isMobile = true;
      $('.js_p_reaction_popup_nav').scroll(function () {
        PReaction.checkScrollPopup();
      });
      $('.p-reaction-container-js').off('touchstart').on('touchstart', function () {
        var _this = $(this);
        clearTimeout(PReaction.closeTimer);
        PReaction.openTimer = setTimeout(function () {
          if (_this.hasClass('open')) return false;
          if (_this.hasClass('is_clicked')) {
            _this.removeClass('is_clicked');
            return false;
          }
          $('.p-reaction-container-js').not([_this]).removeClass('open').find('.p-reaction-list .p-reaction-item').removeClass('animate');
          _this.addClass('open');
          _this.find('.p-reaction-list .p-reaction-item').each(function (index, element) {
            setTimeout(function () {
              $(element).addClass('animate');
            }, index * 30);
          });
          PReaction.shownReact = true;
        }, 500);
      }).on('oncontextmenu', function () {
        return false;
      });
      $(document).on('touchstart', function (event) {
        var oObj = $(event.target);
        if (!oObj.hasClass('p-reaction-container-js') && !oObj.closest('.p-reaction-container-js').length) {
          $('.p-reaction-container-js').removeClass('open');
          $('.p-reaction-container-js').find('.p-reaction-list .p-reaction-item').removeClass('animate');
          PReaction.shownReact = false;
        }
      })
    } else {
      $(".p-reaction-popup-header").mCustomScrollbar({
        theme: "minimal-dark",
        axis: "x",
        callbacks: {
          onScroll: function () {
            PReaction.checkScrollPopup();
          }
        }
      }).addClass('dont-unbind-children');
      $('.p-reaction-container-js').hover(function () {
        var _this = $(this);
        clearTimeout(PReaction.closeTimer);
        PReaction.openTimer = setTimeout(function () {
          if (_this.hasClass('is_clicked') || _this.hasClass('open')) {
            return false;
          }
          $('.p-reaction-container-js').not([_this]).removeClass('open').find('.p-reaction-list .p-reaction-item').removeClass('animate');
          _this.addClass('open');
          _this.find('.p-reaction-list .p-reaction-item').each(function (index, element) {
            setTimeout(function () {
              $(element).addClass('animate');
            }, index * 30);
          });
        }, 500);
      }, function () {
        var _this = $(this);
        clearTimeout(PReaction.openTimer);
        PReaction.closeTimer = setTimeout(function () {
          _this.removeClass('is_clicked');
          _this.removeClass('open');
          _this.find('.p-reaction-list .p-reaction-item').removeClass('animate');
        }, 500);
      });
    }
    //init tooltip
    
     $('.p-reaction-item[data-toggle="tooltip"]').tooltip({
      template: '<div class="tooltip p-reaction-custom-tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
     });
    /**
     * click on like toggle
     */
    $(document).on('click', '[data-toggle="p_reaction_toggle_cmd"]', function (event) {
      if($('.p-reaction-list .tooltip').length > 0){
        $('.p-reaction-list .tooltip').remove();
      }
      if (PReaction.isClicked) {
        return false;
      }
      PReaction.isClicked = true;
      var element = $(this),
        obj = element.data(),
        react_icon = obj.full_path,
        liked = !!obj.liked,
        extras = '',
        re_react = !element.hasClass('js_like_link_toggle') && liked,
        _liked = !liked || re_react,
        method = (_liked) ? 'like.add' : 'like.delete';
      if ($(event.target).hasClass('p-reaction-title')) {
        clearTimeout(PReaction.openTimer);
      } else {
        element.closest('.p-reaction-container-js').addClass('is_clicked');
      }
      if (PReaction.shownReact && PReaction.isMobile && (!_liked || $(event.target).hasClass('js_like_link_toggle') || $(event.target).closest('.p-reaction-icon-outer').length)) {
        PReaction.isClicked = false;
        return false;
      }

      if (!$('body').hasClass('_is_guest_user')) {
        if (element.parents('.comment-mini-content-commands').length) {
          var allElement = $('.comment-mini-content-commands').find('[data-toggle="p_reaction_toggle_cmd"][data-feed_id="' + obj.feed_id + '"][data-type_id="' + obj.type_id + '"]'),
            oDisplayLink = $('.comment-mini-content-commands').find('[data-toggle="p_reaction_toggle_cmd"][data-feed_id="' + obj.feed_id + '"][data-type_id="' + obj.type_id + '"].js_like_link_toggle');
          allElement.data('liked', _liked);
          allElement.removeClass('unlike liked').addClass(!_liked ? 'unlike' : 'liked');
          allElement.find('span').text(_liked ? obj.label2 : obj.label1);
          oDisplayLink.find('.p-reaction-icon-outer').html(_liked ? '<img src="' + react_icon + '" class="p-reaction-icon" oncontextmenu="return false;"/>' : '');
          // <strong class=p-reaction-title style="color:\#' + obj.reaction_color + '">' + obj.reaction_title + '</strong>'
          oDisplayLink.find('.p-reaction-title').html(_liked ? obj.reaction_title : '');
          oDisplayLink.find('.p-reaction-title').css('color', _liked ? '#' + obj.reaction_color : 'black');
        }
        else {
          element = element.hasClass('js_like_link_toggle') ? element : element.closest('.p-reaction-container-js').find('.js_like_link_toggle');
          element.data('liked', _liked);
          element.removeClass('unlike liked').addClass(!_liked ? 'unlike' : 'liked');
          element.find('span').text(!liked ? obj.label2 : obj.label1);
          element.html(_liked ? '<div class="p-reaction-icon-outer"><img alt="" src="' + react_icon + '" class="p-reaction-icon" oncontextmenu="return false;"/> </div><strong class="p-reaction-title" style="color:\#' + obj.reaction_color + '">' + obj.reaction_title + '</strong>' : '');
        }
      }

      var i = element.parents('.comment_mini_content_holder:first');
      if (i.hasClass('_is_app')) {
        extras += 'custom_app_id=' + i.data('app-id') + '&';
      }
      element.closest('.p-reaction-container-js').removeClass('open');
      element.closest('.p-reaction-container-js').find('.p-reaction-list .p-reaction-item').removeClass('animate');

      element.ajaxCall(method, extras
        + 'type_id=' + obj.type_id
        + '&item_id=' + obj.item_id
        + '&parent_id=' + obj.feed_id
        + '&custom_inline=' + obj.is_custom
        + '&table_prefix=' + obj.table_prefix
        + '&reaction_id=' + obj.reaction_id
        + '&is_re_react=' + re_react,
        'GET', null, function (e, self) {
          PReaction.isClicked = false;
          PReaction.shownReact = false;
          if (obj.type_id === 'feed_mini') {
            PReaction.updateMostReactionOnComment(self, obj.item_id, obj.type_id, obj.table_prefix);
          }
        }
      );
    });
    $(document).on('click', '[data-action="p_reaction_show_list_user_react_cmd"]', function () {
      if (PReaction.isClicked) {
        return false;
      }
      PReaction.isClicked = true;
      clearTimeout(PReaction.openTimer);
      var element = $(this),
        obj = element.data();

      tb_show('', $.ajaxBox('preaction.showListReactOnItem', $.param({
        'type': obj.type_id,
        'item_id': obj.item_id,
        'react_id': obj.react_id,
        'table_prefix': obj.table_prefix
      })));
      PReaction.isClicked = false;
    });
    $('[data-toggle="p_reaction_toggle_user_reacted_cmd"]').on('mouseover', function () {
      if (PReaction.isHover) {
        return false;
      }
      PReaction.isHover = true;
      var element = $(this),
        obj = element.data();
      if (element.closest('.js_reaction_item').find('.js_p_reaction_preview_reacted').prop('built_list')) {
        return false;
      }
      element.ajaxCall('preaction.showReactedUser', $.param({
        'type': obj.type_id,
        'item_id': obj.item_id,
        'table_prefix': obj.table_prefix,
        'total_reacted': obj.total_reacted,
        'react_id': obj.react_id
      }), 'POST', null, function (e, self) {
        var oHtml = JSON.parse(e);
        if (oHtml.length) {
          self.closest('.js_reaction_item').find('.js_p_reaction_preview_reacted').html(oHtml).prop('built_list', true);
        }
        PReaction.isHover = false;
      });
    }).on('mouseout', function () {
      PReaction.isHover = false;
    });
    if ($('.js_p_reaction_display_in_detail').length) {
      $('.js_feed_comment_border').find('.js_comment_like_holder:not(.js_p_reaction_display_in_detail)').remove();
      $('.js_comment_like_holder.js_p_reaction_display_in_detail').show();
    }
  },
  updateMostReactionOnComment: function (obj, iItemId, sType, sPrefix) {
    if (sType !== 'feed_mini') {
      return false;
    }
    obj.ajaxCall('preaction.updateMostReactionOnComment', $.param({
      'type': sType,
      'item_id': iItemId,
      'table_prefix': sPrefix
    }), 'post', null, function (e, self) {
      var oHtml = JSON.parse(e);
      var oReactCont = self.closest('.comment_mini_action').find('.p-reaction-container-js');
      oReactCont.siblings('.p-reaction-list-mini').remove();
      if (oHtml.length) {
        oReactCont.after(oHtml);
      }
    });
    return true;
  },
  checkScrollPopup: function () {
    var popup_header = $('.p-reaction-popup-header'),
      width_header = popup_header.width(),
      number_item = $('.js_p_reaction_popup_nav li').length,
      width_item = $('.js_p_reaction_popup_nav li').width(),
      width_all_item = number_item * width_item,
      position_header = popup_header.offset(),
      position_first_item = $('.js_p_reaction_popup_nav li:first-child').offset(),
      position_last_item = $('.js_p_reaction_popup_nav li:last-child').offset();

    if ($("html").attr("dir") === "rtl") {
      if (width_all_item > width_header) {
        if ((position_last_item.left) < (position_header.left)) {
          popup_header.addClass('overlay-end');
        } else {
          popup_header.removeClass('overlay-end');
        }
        if ((position_first_item.left + width_item) > (position_header.left + width_header)) {
          popup_header.addClass('overlay-start');
        } else {
          popup_header.removeClass('overlay-start');
        }
      }
    } else {
      if (width_all_item > width_header) {
        if ((position_last_item.left + width_item) > (position_header.left + width_header)) {
          popup_header.addClass('overlay-end');
        } else {
          popup_header.removeClass('overlay-end');
        }
        if (position_first_item.left < position_header.left) {
          popup_header.addClass('overlay-start');
        } else {
          popup_header.removeClass('overlay-start');
        }
      }
    }
  },
  setTabColor: function (ele) {
    $('.js_p_reaction_popup_nav').find('a[data-toggle="tab"]').removeAttr('style');
    $('.js_p_reaction_popup_nav').find('.item-number').removeAttr('style');
    $(ele).parent().addClass('active');
    $(ele).attr('style', 'border-bottom: 3px solid #' + $(ele).data('color') + ' !important;');
    $(ele).find('.item-number').attr('style', 'color:#' + $(ele).data('color') + ' !important;');
  }
};

$Ready(PReaction.initReaction);