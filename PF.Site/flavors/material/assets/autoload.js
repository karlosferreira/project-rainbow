var $Material = {
  profileMenu: false,
  subMenu: false,
  mainMenu: false,
  columnHeight: {
    'left': {
      'top': 0,
      'left': 0
    },
    'right': {
      'top': 0,
      'left': 0
    }
  },
  checkColumnHeight: false,
  columnOffset: {
    'left': {},
    'right': {},
    'middle': {}
  },

  updateMainNav: function() {
    var selectedMenu = $('.site_menu:first li a.menu_is_selected:last');
    var html = selectedMenu.length ? selectedMenu.html() : '';
    $('.js-btn-collapse-main-nav').each(function() {
      if ($(this).hasClass('link')) {
        html = selectedMenu.length ? selectedMenu.clone() : '';
        $(this).empty().append(html);
      }
      else {
        $(this).html(html);
      }
    });

  },

  initFixedElement: function(){
    var getOffsetTop = function(ele) {
      if ($(ele).length == 0) return false;
      if (!$(ele).is(':visible')) return false;
      return $(ele).offset().top;
    };
    var getOffsetBottom = function(ele) {
      if ($(ele).length == 0) return false;
      if (!$(ele).is(':visible')) return false;
      if ($(ele).css('position') == 'fixed') return false;
      return $(ele).offset().top + $(ele).height();
    };

    $Material.setBodyData('header', 'fixed');
    $Material.setBodyData('submenu', '');
    $Material.setBodyData('profile', '');
    $Material.setBodyData('left', '');
    $Material.setBodyData('right', '');

    if ($Core.exists('._is_pages_view')) {
      $Material.profileMenu = getOffsetTop('._is_pages_view .profiles-menu');
    }
    else if ($Core.exists('._is_groups_view')) {
      $Material.profileMenu = getOffsetTop('._is_groups_view .profiles-menu');
    }
    else {
      $Material.profileMenu = getOffsetTop('._is_profile_view .profiles-menu');
    }

    $Material.subMenu = getOffsetTop('#js_block_border_core_menusub');
    $Material.mainMenu = getOffsetBottom('.main-navigation');
    var header_top_space = 0;
    var fix_elems = ['#section-header .sticky-bar', '#js_block_border_core_menusub', '._is_profile_view .profiles-menu', '._is_pages_view .profiles-menu', '._is_groups_view .profiles-menu'];
    $.each(fix_elems, function(i, ele) {
      if ($(ele).length > 0 && $(ele).css('position') == 'fixed') {
        header_top_space += $(ele).height();
      }
    });

    $Material.headerTopSpace = header_top_space;
    $Material.setColumnHeight('left');
    $Material.setColumnHeight('right');
    $Material.setColumnHeight('middle');

    $Material.setColumnOffset('left');
    $Material.setColumnOffset('right');

    if ($('#shoutbox_error').length > 0) {
      $('#shoutbox_error').detach().appendTo('body');
    }
  },

  fixedColumnLR: function(){
    var elem_left		= $('#main .layout-left');
    var elem_right 	= $('#main .layout-right');

    if (elem_left.length > 0) {
      $Material.setColumnFixed('left',elem_left);
    }

    if (elem_right.length > 0) {
      $Material.setColumnFixed('right',elem_right);
    }
  },
  setColumnHeight: function(column) {
    var elem	= $('#main .layout-' + column);
    var elem_height = elem.height();
    if(column == 'right' && $Material.isOnTablet()) {
      elem_height = elem_height + $('#main .layout-left').height();
    }
    var height = $Material.columnHeight[column];
    if ($('#main').hasClass('empty-' + column)) {
      elem_height = 0;
    }
    if (elem_height != height) {
      $Material.columnHeight[column] = elem_height;
    }

    if ($Material.checkColumnHeight === false) {
      $Material.checkColumnHeight = true;
      setInterval(function(){
        $Material.setColumnHeight('middle');
        $Material.setColumnHeight('left');
        $Material.setColumnHeight('right');
      }, 250);
    }


    return elem_height;
  },
  getColumnHeight: function(column) {
    var height = $Material.columnHeight[column];
    if (height == 0) {
      return $Material.setColumnHeight(column);
    }
    return height;
  },

  // Function Set Header, Sub menu, Profile fixed.
  setSectionFixed: function(topOffset, datafixed, header_top_space){
    if (topOffset === false) {
      return false;
    }

    if ($('body').css('position') == 'fixed') {
      return false;
    }

    var old_stat = $('body').attr('data-'+datafixed);
    var new_stat = '';
    if($(window).scrollTop() + header_top_space > topOffset) {
      new_stat = 'fixed';
    }
    if (old_stat != new_stat) {
      $('body').attr('data-'+datafixed, new_stat);
      $Material.setColumnOffset('left');
      $Material.setColumnOffset('right');
      var menu_more_fixed= $('.profiles-menu ul[data-component="menu"]');
       
        if(menu_more_fixed.length > 0){
            menu_more_fixed.removeClass('built');
            menu_more_fixed.css('overflow', 'hidden');
            $Behavior.buildMenu();
        }
    } 
  },
  isOnTablet: function() {
    var ww = $(window).width();
    return (ww < 992 && ww >= 768);
  },
  getMaxColumnHeight: function() {
    return Math.max($Material.getColumnHeight('left'), $Material.getColumnHeight('right'), $Material.getColumnHeight('middle'));
  },
  getColumnOffset: function(column, offset) {
    if (typeof $Material.columnOffset[column][offset] != 'undefined') {
      if($Material.columnOffset[column][offset] < 0) {
        $Material.setColumnOffset(column);
      }
      return $Material.columnOffset[column][offset];
    }
    return 0;
  },
  setColumnOffset: function(column) {
    var elem 	= $('#main .layout-' + column);
    if (elem.css('position') == 'fixed') {
      if (column == 'right' && $Material.isOnTablet()) {
        $Material.setBodyData('right', '');
      }
      else {
        return false;
      }
    }
    $Material.columnOffset[column] = elem.length > 0 ? elem.offset() : {};
  },

  setBodyData: function(data, value) {
    if ($('body').attr('data-' + data) != value) {
      $('body').attr('data-' + data, value);
    }
  },

  setColumnFixed: function(left_right, elem){
    $Material.setColumnHeight(left_right);
    $(document).on('click', '#main .layout-' + left_right, function(){
      if ($Material.isOnTablet() && left_right == 'left') {
        setTimeout(function(){
          $Material.setColumnOffset('right');
          $(window).scroll();
        }, 500);
      }
      setTimeout(function(){
        $Material.setColumnHeight(left_right);
        $(window).scroll();
      }, 250);
    });

    if(left_right == 'left') {
      window.handleCheckFixedColumnLeft && $(window).off('scroll', handleCheckFixedColumnLeft);
      handleCheckFixedColumnLeft = function() {
          $Material.checkFixedColumn('left', elem);
      };
      $(window).on('scroll', handleCheckFixedColumnLeft);
    }
    else if(left_right == 'right') {
      window.handleCheckFixedColumnRight && $(window).off('scroll', handleCheckFixedColumnRight);
      handleCheckFixedColumnRight = function () {
          $Material.checkFixedColumn('right', elem);
      };
      $(window).on('scroll', handleCheckFixedColumnRight);
    }
  },
  checkFixedColumn: function(left_right, elem) {
    if ($Core.exists('body#page_core_index-visitor') || $('body').css('position') == 'fixed') {
      if ($Core.exists('body#page_core_index-visitor')) {
        $Material.setBodyData(left_right, '')
      }
      return false;
    }
    var offset_top = $Material.getColumnOffset(left_right, 'top');
    var offset_left = $Material.getColumnOffset(left_right, 'left');
    var elem_height = $Material.getColumnHeight(left_right);
    var wd_height = $(window).height();
    var scroll = $(window).scrollTop();
    var offset_footer = 0;
    var height_footer = 0;
    var bottom_elems = ['._block.location_8', '#bottom_placeholder', '#section-footer'];

    $.each(bottom_elems, function(i, ele) {
      if ($(ele).length && $(ele).html()) {
        offset_footer = $(ele).offset().top;
        height_footer = $(ele).height();
        return false;
      }
    });
    var new_offset_top = elem.offset().top;
    var space = 24;
    var top_fix = 0;
    var offset_bot = elem_height + offset_top;
    var bottom = 0;
    var max_height = $Material.getMaxColumnHeight();
    var is_tablet = $Material.isOnTablet();
    if (wd_height + scroll >= offset_footer) {
      bottom = wd_height + scroll - offset_footer;
    }

    var fix_elems = ['#section-header .sticky-bar', '#js_block_border_core_menusub', '._is_profile_view .profiles-menu'];
    $.each(fix_elems, function(i, ele) {
      if ($(ele).length > 0 && $(ele).css('position') == 'fixed') {
        top_fix += $(ele).height();
      }
    });

    var top_space = top_fix + space;

    if (is_tablet && left_right == 'left' && !$('body').hasClass('empty-right')) {
      return false;
    }

    if(elem_height < max_height && !$('#main').hasClass('empty-' + left_right)) {
      if(left_right == 'right' && is_tablet) {
        offset_bot = offset_bot - $Material.getColumnHeight('left');
        elem_height = elem_height - $Material.getColumnHeight('left');
      }
      var elem_total = elem_height + new_offset_top;
      if (elem_total >= offset_footer) {
        var top = offset_footer - (scroll + elem_height) - space;
        elem.css({
          'top': top + 'px',
          'left': offset_left + 'px'
        });
        $Material.setBodyData(left_right, 'fixed');
      }
      else {
        var not_end = ((wd_height + scroll) < (offset_footer + height_footer) || $('body').attr('data-' + left_right) == 'fixed');
        if (elem_height < wd_height - top_space - bottom) {
          var offset_top_compare = offset_top - 2*space;
          if(($('._is_profile_view  .profiles-menu').length && $('._is_profile_view  .profiles-menu').css('position') !== 'fixed')
              || ($('#js_block_border_core_menusub').length && $('#js_block_border_core_menusub').css('position') !== 'fixed')) {
            offset_top_compare = offset_top;
          }
          if ((scroll + top_fix > offset_top_compare) && not_end) {
            elem.css({'top': top_space + 'px', 'left': offset_left + 'px'});
            $Material.setBodyData(left_right, 'fixed');
          }
          else {
            $Material.setBodyData(left_right, '');
          }
        }

        else {
          if (scroll + wd_height > offset_bot + space) {
            var top = wd_height - elem_height - space - bottom;
            elem.css({'top': top + 'px', 'left': offset_left + 'px'});
            $Material.setBodyData(left_right, 'fixed');
          } else {
            $Material.setBodyData(left_right, '');
          }
        }
      }
    }
    else{
      $Material.setBodyData(left_right, '');
    }
  }
};

$Ready(function() {
  //Header form search. clear search
  $.fn.clearSearch = function(options) {
    var settings = $.extend({
      'clearClass' : 'clear_input',
      'focusAfterClear' : true,
      'linkText' : '<span class="ico ico-close"></span>'
    }, options);
    return this.each(function() {
      var $this = $(this), btn,
          divClass = settings.clearClass + '_div';

      if (!$this.parent().hasClass(divClass)) {
        $this.wrap('<div style="position: relative;" class="' + divClass + '"></div>');
        $this.after('<a style="position: absolute; cursor: pointer;" class="'
            + settings.clearClass + '">' + settings.linkText + '</a>');
      }
      btn = $this.next();

      function clearField() {
        $this.val('').change();
        triggerBtn();
        if (settings.focusAfterClear) {
          $this.focus();
        }
        if (typeof (settings.callback) === 'function') {
          settings.callback();
        }
      }

      function triggerBtn() {
        if (hasText()) {
          btn.show();
        } else {
          btn.hide();
        }
        update();
      }

      function hasText() {
        return $this.val().replace(/^\s+|\s+$/g, '').length > 0;
      }

      function update() {
      }

      if ($this.prop('autofocus')) {
        $this.focus();
      }

      btn.on('click', clearField);
      $this.on('keyup keydown change focus', triggerBtn);
      triggerBtn();
    });
  };

  $('#header_sub_menu_search_input').clearSearch({});
  //scale auto text area in block invite friend
  var textarea = document.getElementById('personal_message');
  if(textarea){
    textarea.addEventListener('keydown', autosize);
    function autosize(){
      var el = this;
      setTimeout(function(){
        el.style.cssText = 'height:auto; padding:8px';
        // for box-sizing other than "content-box" use:
        // el.style.cssText = '-moz-box-sizing:content-box';
        el.style.cssText = 'height:' + el.scrollHeight + 'px';
      },0);
    }
  };

  $('.btn-nav-toggle').on("click", function(){
    $(".nav-mask-modal").addClass("in");
    $(".main-navigation").addClass("in");
    $('body').addClass("overlap");
    if (typeof $Core.disableScroll !== 'undefined') {
      $Core.disableScroll();
    }
  });

  $('.nav-mask-modal').on("click touchend", function(){
    $(this).removeClass("in");
    $(".main-navigation").removeClass("in");
    $('body').removeClass("overlap");
    if (typeof $Core.enableScroll !== 'undefined') {
      $Core.enableScroll();
    }
  });

  $(".site-menu-small .ajax_link, .site-logo-link").on("click", function(){
    $(".nav-mask-modal").removeClass("in");
    $(".main-navigation").removeClass("in");
    $('body').removeClass("overlap");
    if (typeof $Core.enableScroll !== 'undefined') {
      $Core.enableScroll();
    }
  });

  //Sticky bar search on mobile
  $('.btn-mask-action').on("click", function(){
    $('.sticky-bar-inner').addClass('overlap');
    $('#header_sub_menu_search_input').focus();
  });

  //return search on mobile
  $('.btn-globalsearch-return').on("click", function(){
    $('.sticky-bar-inner').removeClass('overlap');
  });

  //Add class when input focus
  $('.form-control').focus( function() {
    var parent = $(this).parent('.input-group');
    if(parent){
      parent.addClass('focus');
    }
  });

  $('.form-control').blur( function() {
    $('.input-group').removeClass('focus');
  });

  // Just init custom scrollbar on desktop view.
  if(!(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )){
    //Init scrollbar
    $(".fixed-main-navigation .dropdown-menu, .user-sticky-bar .panel-items, .friend-search-invite-container, #js_main_mail_thread_holder .mail_messages, .js_box_content .item-membership-container, #welcome_message .custom_flavor_content, .dropdown-menu-limit, .attachment-form-holder .js_attachment_list").mCustomScrollbar({
      theme: "minimal-dark",
    }).addClass('dont-unbind-children');

    $("#div_compare_wrapper").mCustomScrollbar({
      theme: "minimal-dark",
      axis:"x" // horizontal scrollbar
    }).addClass('dont-unbind-children');

    $(".attachment_holder_view").mCustomScrollbar({
      theme: "dark"
    }).addClass('dont-unbind-children');

    PF.event.on('before_cache_current_body', function() {
      $('.mCustomScrollbar').mCustomScrollbar('destroy');
    });
  }

  if ($('.js-btn-collapse-main-nav:not(.built)').length > 0) {
    $Material.updateMainNav();
    $('.js-btn-collapse-main-nav').addClass('built');
  }

  //add class for category when collapse
  $(".core-block-categories ul.collapse").on('shown.bs.collapse', function(){
    $(this).closest('li.category').addClass('opened');
  });

  $(".core-block-categories ul.collapse").on('hidden.bs.collapse', function(){
    $(this).closest('li.category').removeClass('opened');
  });
});

//toggle for sign-up/sign-in form in landing page
$(document).on('click', '.js-slide-visitor-form a.js-slide-btn', function(){
  $('.js-slide-visitor-form').toggle();
  var parent = $('.js-slide-visitor-form:visible:first'),
      block_title = parent.data('title');

  if (block_title && $('#js_block_border_user_register').length > 0) {
    $('#js_block_border_user_register').find('.title:first').html(block_title);
  }
});

$(document).on('click', '[data-action="submit_search_form"]', function() {
  $(this).closest('form').submit();
});

$(document).on('click', '#hd-notification [data-dismiss="alert"]', function(evt) {
  evt.stopPropagation();
});


function page_scroll2top(){
  $('html,body').animate({
    scrollTop: 0
  }, 'fast');
}

$Core.updateCommentCounter = function(module_id, item_id, str) {
  var sId = '#js_feed_like_holder_' + module_id + '_' + item_id + ', #js_feed_mini_action_holder_' + module_id + '_' + item_id;
  if ($(sId).length && $(sId).find('.feed-comment-link .counter').length) {
    $(sId).each(function(){
      var count = $(this).find('.feed-comment-link .counter').first().text();
      if (!count) {
        count = 0;
      }
      if (str == '+') {
        count = parseInt(count) + 1;
      }
      else {
        count = parseInt(count) - 1;
      }
      count = count <= 0 ? '' : count;
      $(this).find('.feed-comment-link .counter').first().text(count);
    })
  }
};

$(window).on('resize', function(){
  $Material.initFixedElement();
  $Material.fixedColumnLR();
  setTimeout(function () {
      $(window).scroll();
  }, 500);
});

$(document).on('scroll', window, function () {
  if($(window).width() > 767) {
      $Material.setSectionFixed($Material.profileMenu, 'profile', $Material.headerTopSpace);
      $Material.setSectionFixed($Material.subMenu, 'submenu', $Material.headerTopSpace);
      $Material.setSectionFixed($Material.mainMenu, 'mainmenu', $Material.headerTopSpace);
      if ($(window).scrollTop() >= 10) {
          $('.btn-scrolltop').fadeIn();
      } else {
          $('.btn-scrolltop').fadeOut();
      }
  }
});

PF.event.on('on_document_ready_end', function() {
  $Material.initFixedElement();
  $Material.fixedColumnLR();
  setTimeout(function () {
      $(window).scroll();
  }, 500);
});

PF.event.on('on_page_change_end', function() {
  $Material.updateMainNav();
  $Material.initFixedElement();
  $Material.fixedColumnLR();
  setTimeout(function () {
      $(window).scroll();
  }, 500);
  if (!$Core.exists('body#page_core_index-visitor')) {
    $('div#index-visitor-error').hide();
  }
  else {
    $('div#index-visitor-error').show();
  }
});

$Core.FriendRequest = {
  panel: {
    accept: function(requestId, message) {
      var requestRow = $('#drop_down_' + requestId, '#request-panel-body');

      $('.info', requestRow).text(message);
      $('.panel-actions', requestRow).remove();
      requestRow.addClass('friend-request-accepted');

      // update counter
      $Core.FriendRequest.panel.descreaseCounter();

      setTimeout(function() {
        $('.panel-item-content', requestRow).slideUp(200, function() {
          requestRow.remove();
          $Core.FriendRequest.panel.checkAndClosePanel();
        });
      }, 2e3);
    },

    deny: function(requestId) {
      var requestRow = $('#drop_down_' + requestId, '#request-panel-body');

      // update counter
      $Core.FriendRequest.panel.descreaseCounter();

      $('.panel-item-content', requestRow).fadeOut(400, function() {
        requestRow.remove();
        $Core.FriendRequest.panel.checkAndClosePanel();
      });
    },

    descreaseCounter: function() {
      var friendRequestCounter = $('#js_total_friend_requests');
      if (friendRequestCounter.length === 0) {
        return;
      }

      var total = friendRequestCounter.text().match(/\(([0-9]*)\)/);
      if (typeof total === 'object' && typeof total[1] !== 'undefined') {
        total = total[1] - 1;
        if (total > 0) {
          friendRequestCounter.text('(' + total + ')');
          $('#request-view-all-count').text(total);
        } else {
          friendRequestCounter.remove();
        }
      }
    },

    checkAndClosePanel: function() {
      if ($('li', '#request-panel-body').length === 0) {
        $('#hd-request').trigger('click');
      }
    }
  },

  manageAll: {
    accept: function(requestId, message) {
      var requestRow = $('#request-' + requestId);

      $('.moderation_row', requestRow).remove();
      $('.item-info', requestRow).text(message);
      $('#drop_down_' + requestId, requestRow).remove();
      requestRow.addClass('friend-request-accepted');
      setTimeout(function() {
        requestRow.fadeOut(400, function() {
          $(this).remove();
          $Core.FriendRequest.manageAll.checkReload();
        });
      }, 2e3);
    },

    deny: function(requestId) {
      $('#request-' + requestId).slideUp(400, function() {
        $('#request-' + requestId).remove();
        $Core.FriendRequest.manageAll.checkReload();
      });
    },

    checkReload: function() {
      if ($('#collection-friends-incoming').children().length === 0) {
        window.location.reload();
      }
    }
  }
};
