$Ready(function () {
  $(document).off('click', '[data-toggle="event_rsvp"]').on('click', '[data-toggle="event_rsvp"]', function () {
    var element = $(this),
      event_id = element.data('event-id'),
      parent = $('#js_event_rsvp_action_' + event_id),
      bIsPublic = element.data('public'),
      container = (bIsPublic ? parent.find('.js_event_rsvp_action_dropdown') : element.closest('.open')),
      button = container.find('[data-toggle="dropdown"]'),
      rel = element.attr('rel');
    container.removeClass('is_attending');
    container.find('.is_active_image').removeClass('is_active_image');

    //check attending option is selected
    if (rel == 1) {
      container.addClass('is_attending');
    }

    var $sContent = element.html();
    $.ajaxCall('event.addRsvp', 'id=' + event_id + '&inline=1' + '&rsvp=' + rel);
    button.find('span.txt-label').html($sContent);

    if (bIsPublic) {
      $('.js_event_rsvp_action_dropdown [data-toggle="event_rsvp"][rel="' + rel + '"]', parent).addClass('is_active_image');
      parent.find('.js_event_rsvp_action_btn').addClass('hide');
      parent.find('.js_event_rsvp_action_dropdown').removeClass('hide');
    }
    else {
      if (rel == 0) {
        parent.find('.js_event_rsvp_action_btn').removeClass('hide');
        parent.find('.js_event_rsvp_action_dropdown').addClass('hide');
      }
      else {
        element.addClass('is_active_image');
      }
    }

    if ($('#event_rsvp').length) {
      var iCurrentRsvp = parseInt(parent.data('current-rsvp'));
      var isInvited = parent.data('invited');
      var oEventRsvp = $('#event_rsvp');
      if (iCurrentRsvp == 0 && rel != 0) {
        oEventRsvp.find('.js_btn_rsvp_actions').addClass('hide');
        oEventRsvp.find('.js_dropdown_rsvp_actions').removeClass('hide');
        oEventRsvp.find('#js_dropdown_rsvp_text').html($sContent);
        oEventRsvp.find('.js_dropdown_rsvp_actions .item-event-option').removeClass('active');
        oEventRsvp.find('.js_dropdown_rsvp_actions [data-rsvp-dropdown="' + rel + '"]').addClass('active');
      }
      else {
        oEventRsvp.find('.js_dropdown_rsvp_actions .item-event-option').removeClass('active');
        if (isInvited || (!isInvited && rel != 0)) {
          oEventRsvp.find('#js_dropdown_rsvp_text').html($sContent);
          oEventRsvp.find('.js_dropdown_rsvp_actions [data-rsvp-dropdown="' + rel + '"]').addClass('active');
        }
        else {
          oEventRsvp.find('#js_dropdown_rsvp_text').html('');
          oEventRsvp.find('.js_btn_rsvp_actions').removeClass('hide');
          oEventRsvp.find('.js_dropdown_rsvp_actions').addClass('hide');
        }
      }
      parent.data('current-rsvp', rel);
    }

  });
  $Core.event.toggleMapCollapse();
  $Core.event.toggleViewContentCollapse();
});


$Behavior.addNewEvent = function () {
  $('.js_event_change_group').click(function () {
    if ($(this).parent().hasClass('locked')) {
      return false;
    }

    aParts = explode('#', this.href);

    $('.js_event_block').hide();
    $('#js_event_block_' + aParts[1]).show();
    $(this).parents('.header_bar_menu:first').find('li').removeClass('active');
    $(this).parent().addClass('active');
    $('#js_event_add_action').val(aParts[1]);
  });

  $('.js_mp_category_list').change(function () {
    var iParentId = parseInt(this.id.replace('js_mp_id_', ''));

    $('.js_mp_category_list').each(function () {
      if (parseInt(this.id.replace('js_mp_id_', '')) > iParentId) {
        $('#js_mp_holder_' + this.id.replace('js_mp_id_', '')).hide();

        this.value = '';
      }
    });

    $('#js_mp_holder_' + $(this).val()).show();
  });
};


$Core.event =
  {
    sUrl: '',
    canCheckReloadValidate: ($Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined"),

    url: function (sUrl) {
      this.sUrl = sUrl;
    },

    action: function (oObj, sAction) {
      aParams = $.getParams(oObj.href);

      $('.dropContent').hide();

      switch (sAction) {
        case 'edit':
          window.location.href = this.sUrl + 'add/id_' + aParams['id'] + '/';
          break;
        case 'delete':
          var url = this.sUrl;
          $Core.jsConfirm({}, function () {
            window.location.href = url + 'delete_' + aParams['id'] + '/';
          }, function () {
          });
          break;
        default:

          break;
      }

      return false;
    },

    deleteImage: function (iEventId) {
      $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
        $.ajaxCall('event.deleteImage', 'id=' + iEventId);
      }, function () {

      });
      return false;
    },
    deleteEvent: function (ele) {

      if (!ele.data('id')) return false;

      $Core.jsConfirm({message: ele.data('message')}, function () {
        $.ajaxCall('event.delete', 'id=' + ele.data('id') + '&is_detail=' + ele.data('is-detail'));
      }, function () {
      });

      return false;
    },
    init_drag: function (ele) {
      Core_drag.init({table: ele.data('table'), ajax: ele.data('ajax')});
    },
    toggleMapCollapse: function () {
      $('.js_core_event_toggle_map a').off('click').click(function () {
        $(this).siblings('a.hide').removeClass('hide');
        $(this).addClass('hide');
        $('.js_core_event_map_collapse').toggleClass('hide');
      });
    },
    toggleViewContentCollapse: function () {
      //viewmore less content in detail
      if ($('.js_core_events_view_content_collapse').length) {
        var collapse_desc = $('.js_core_events_view_content_collapse .event-item-content');

        if (collapse_desc.length) {
          if (55 < collapse_desc.height()) {
            collapse_desc.addClass('truncate-text');
            $('.js_core_events_view_content_collapse').addClass('collapsed');
            $('.core-events-view-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
          }
        }
        $('.js-core-event-action-collapse .js-item-btn-toggle-collapse').off('click').on('click', function () {
          $('.js_core_events_view_content_collapse').toggleClass('collapsed');
          if ($(this).hasClass('item-viewmore-btn')) {
            $(this).closest('.js-core-event-action-collapse').removeClass('has-viewmore').addClass('has-viewless');
          } else if ($(this).hasClass('item-viewless-btn')) {
            $(this).closest('.js-core-event-action-collapse').removeClass('has-viewless').addClass('has-viewmore');
          }
        });
      }
    },
    processEditFeedStatus: function (feed_id, status, deleteLink) {
      if ($('#js_item_feed_' + feed_id).length) {
        var parent = $('#js_item_feed_' + feed_id).find('.activity_feed_content_text:first');
        if (parent.find('.activity_feed_content_status:first').length) {
          parent.find('.activity_feed_content_status:first').html(status);
        }
        else {
          parent.prepend('<div class="activity_feed_content_status">' + status + '</div>');
        }
        if (typeof deleteLink !== 'undefined') {
          $("#js_item_feed_" + feed_id).find(".activity_feed_content_link").remove();
        }
      }
    },

    blockInviteReloadValidation: {
      init: function () {
        this.validate();
      },

      validate: function () {
        function _checkValidate(parentFormId) {
          if (!empty(parentFormId) && parseInt(trim($('#js_event_form #deselect_all_friends').children().text())) > 0) {
            if (!$Core.reloadValidation.changedEleData.hasOwnProperty(parentFormId)) {
              $Core.reloadValidation.changedEleData[parentFormId] = {};
            }

            $Core.reloadValidation.changedEleData[parentFormId]['invite_friend'] = true;
          } else {
            delete $Core.reloadValidation.changedEleData[parentFormId]['invite_friend'];
          }

          $Core.reloadValidation.preventReload();
        }

        $(document).on('click', '#js_event_form #selected_friends_list li[data-id]',function () {
          _checkValidate('js_event_form');
        })

        $('#js_event_form #js_friend_search_content input[type="checkbox"], #js_event_form #deselect_all_friends').on('click', function () {
          _checkValidate('js_event_form');
        })
      }
    },

    datepickerReloadValidation: {
      init: function () {
        var parentObject = this;

        $('.js_date_picker').each(function () {
          var _this = $(this)
          var prevOptions = _this.datepicker('option', 'all');
          var prevEventOnSelect = prevOptions.onSelect;

          delete prevOptions.onSelect;

          _this.datepicker('destroy').datepicker($.extend(prevOptions, {
            onSelect: function(dateText) {
              prevEventOnSelect(dateText)
              parentObject.validate(_this, dateText)
            }
          }));
        });

        this.store();
        this.validateTime();
      },

      store: function () {
        $('.js_date_picker, .js_datepicker_selects select').each(function () {
          var _this = $(this);
          var eleName = _this.attr('name');
          var parentFormId = _this.closest('form').attr('id');

          if (!empty(eleName) && !empty(parentFormId)) {
            if (!$Core.reloadValidation.initEleData.hasOwnProperty(parentFormId)) {
              $Core.reloadValidation.initEleData[parentFormId] = {};
            }

            $Core.reloadValidation.initEleData[parentFormId][eleName] = _this.val();
          }
        });
      },

      validate: function (ele, value) {
        var isNotChange = true;
        var eleName = ele.attr('name');
        var parenFormId = ele.closest('form').attr('id');

        if (!empty(eleName) && !empty(parenFormId)) {
          if (typeof $Core.reloadValidation.initEleData[parenFormId] !== 'undefined' && typeof $Core.reloadValidation.initEleData[parenFormId][eleName] !== 'undefined'
            && value !== $Core.reloadValidation.initEleData[parenFormId][eleName]) {
            isNotChange = false;

            if (!$Core.reloadValidation.changedEleData.hasOwnProperty(parenFormId)) {
              $Core.reloadValidation.changedEleData[parenFormId] = {};
            }

            $Core.reloadValidation.changedEleData[parenFormId][eleName] = true;
          }

          if (isNotChange) {
            delete $Core.reloadValidation.changedEleData[parenFormId][eleName];
          }

          $Core.reloadValidation.preventReload();
        }
      },

      validateTime: function () {
        var parentObject = this;

        $('.js_datepicker_selects select').on('change', function () {
          var _this = $(this);
          parentObject.validate(_this, _this.val());
        });
      }
    }
  };

$Behavior.event_initCategoryOrder = function () {
  $('[data-app="core_events"][data-action-type="init"]').each(function () {
    var t = $(this);
    if (t.data('action-type') === 'init' &&
      $Core.event.hasOwnProperty(t.data('action')) &&
      typeof $Core.event[t.data('action')] === 'function') {
      $Core.event[t.data('action')](t);
    }
  });
};

$Behavior.initViewEvent = function () {
  var bDisable = true;
  $('.js_core_event_detail_guest_list').off('click').click(function () {
    var sTab = $(this).data('tab');
    var iEventId = $('#js_core_event_detail_member').data('event-id');
    tb_show(oTranslations['event_guests_title'], $.ajaxBox('event.showGuestList', 'tab=' + sTab + '&event_id=' + iEventId));
  });
};

$Behavior.selectPeriodRepeat = function () {
  $('#event_repeat_select').change(function(){
    if($(this).val() !== '-1') {
      $('#event_end_repeat').css('display', 'block');
    } else {
      $('#event_end_repeat').css('display', 'none');
    }
  });
}

//Map JS

var oMarker;
var oGeoCoder;
var sQueryAddress;
var oMap;
var oLatLng;
var bDoTrigger = false;

/* This function takes the information from the input fields and moves the map towards that location*/
function inputToMap() {
  var sQueryAddress = $('#address').val() + ' ' + $('#postal_code').val() + ' ' + $('#city').val();
  if ($('#js_country_child_id_value option:selected').val() > 0) {
    sQueryAddress += ' ' + $('#js_country_child_id_value option:selected').text();

    //$.ajaxCall('core.getChildre','country_iso=' + $('#country_iso option:selected').val());
  }
  sQueryAddress += ' ' + $('#country_iso option:selected').text();
  oGeoCoder.geocode({
      'address': sQueryAddress
    }, function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        oLatLng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
        oMarker.setPosition(oLatLng);
        oMap.panTo(oLatLng);
        $('#input_gmap_latitude').val(oMarker.position.lat());
        $('#input_gmap_longitude').val(oMarker.position.lng());
      }
    }
  );
  if (bDoTrigger) {
    google.maps.event.trigger(oMarker, 'dragend');
    bDoTrigger = false;
  }
}

function initialize() {
  if (typeof(aInfo) == 'undefined') return;
  oGeoCoder = new google.maps.Geocoder();
  oLatLng = new google.maps.LatLng(aInfo.latitude, aInfo.longitude);

  var myOptions = {
    zoom: 11,
    center: oLatLng,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    mapTypeControl: false,
    streetViewControl: false
  };
  oMap = new google.maps.Map(document.getElementById("mapHolder"), myOptions);
  oMarker = new google.maps.Marker({
    draggable: true,
    position: oLatLng,
    map: oMap
  });


  /* Fake the dragend to populate the city and other input fields */
  google.maps.event.trigger(oMarker, 'dragstart');
  google.maps.event.trigger(oMarker, 'dragend');
  google.maps.event.addListener(oMarker, "dragend", function () {
    $('#input_gmap_latitude').val(oMarker.position.lat());
    $('#input_gmap_longitude').val(oMarker.position.lng());
    oLatLng = new google.maps.LatLng(oMarker.position.lat(), oMarker.position.lng());
    oGeoCoder.geocode({
        'latLng': oLatLng
      },
      function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          $('#city').val('');
          $('#postal_code').val('');
          for (var i in results[0]['address_components']) {
            if (results[0]['address_components'][i]['types'][0] == 'locality') {
              $('#city').val(results[0]['address_components'][i]['long_name']);
            }
            if (results[0]['address_components'][i]['types'][0] == 'country') {
              var sCountry = $('#country_iso option:selected').val();
              $('#js_country_iso_option_' + results[0]['address_components'][i]['short_name']).attr('selected', 'selected');
              if (sCountry != $('#country_iso option:selected').val()) {
                $('#country_iso').change();
              }
            }
            if (results[0]['address_components'][i]['types'][0] == 'postal_code') {
              $('#postal_code').val(results[0]['address_components'][i]['long_name']);
            }
            if (results[0]['address_components'][i]['types'][0] == 'street_address') {
              $('#address').val(results[0]['address_components'][i]['long_name']);
            }
            if (isset($('#js_country_child_id_value')) && results[0]['address_components'][i]['types'][0] == 'administrative_area_level_1') {
              $('#js_country_child_id_value option').each(function () {
                if ($(this).text() == results[0]['address_components'][i]['long_name']) {
                  $(this).attr('selected', 'selected');
                  bHasChanged = true;
                }
              });
            }
          }
        }
      });
  });
  /* Sets events for when the user inputs info */
  inputToMap();
}

function loadScript() {
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = 'https://maps.google.com/maps/api/js?callback=initialize';
  document.body.appendChild(script);
}


$(document).ready(function () {
  $('#js_country_child_id_value').change(function () {
    $('#city').val('');
    $('#postal_code').val('');
    $('#address').val('');
  });
  $('#country_iso, #js_country_child_id_value', $('#js_event_block_detail')).change(inputToMap);
  $('#address, #postal_code, #city', $('#js_event_block_detail')).blur(inputToMap);
});

var core_events_onchangeDeleteCategoryType = function (type) {
  if (type == 2)
    $('#category_select').show();
  else
    $('#category_select').hide();
};

$Behavior.process_event = function () {
  if ($('#page_event_add').length) {
    $('#js_event_parent_category').on('change', function () {
      var iSelectedCategory = $(this).val();
      $('.js_event_sub_category').each(function () {
        ($(this).hide().find('select:first').selectize())[0].selectize.setValue('');
      });
      $('#js_event_sub_category_' + iSelectedCategory).show();
    });
  }

};

PF.event.on('on_page_change_end', function () {
  if ($('#page_event_add').length && $('#country_iso').length && $('#country_iso').closest('form').find('input[name="id"]').length) {
    var oCountryIso = $('#country_iso');
    if (oCountryIso.hasClass('selectized') && oCountryIso.parent().hasClass('js_core_init_selectize_form_group')) {
      var sCountryIso = oCountryIso.closest('.js_core_init_selectize_form_group').data('country-iso');
      if (!empty(sCountryIso)) {
        (oCountryIso.selectize())[0].selectize.setValue(sCountryIso);
      }
    }
  }

  if ($Core.event.canCheckReloadValidate && $('#page_event_add').length) {
    $Core.reloadValidation.store('#js_event_block_email #js_send_email');
    $Core.reloadValidation.validate('#js_event_block_email #js_send_email');
    $Core.event.blockInviteReloadValidation.init();
    $Core.event.datepickerReloadValidation.init();
  }
});

PF.event.on('on_document_ready_end', function () {
  if ($Core.event.canCheckReloadValidate && $('#page_event_add').length) {
    $Core.reloadValidation.store('#js_event_block_email #js_send_email');
    $Core.reloadValidation.validate('#js_event_block_email #js_send_email');
    $Core.event.blockInviteReloadValidation.init();
    $Core.event.datepickerReloadValidation.init();
  }
});