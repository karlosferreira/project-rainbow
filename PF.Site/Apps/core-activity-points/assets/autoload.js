$Ready(function () {
  $(document).tooltip({
    selector: '.core-activitypoint-statistic [data-toggle="tooltip"]'
  });
});
$Behavior.core_activitypoint = function () {
  if ($('#js_point_packages_selection').length) {
    coreActivityPointPointPackage.init();
  }
  if ($('#js_core_activitypoint_admincp_transaction').length) {
    coreActivityPointAdmincpTransaction.init();
  }
  if ($('#js_core_activitypoint_admincp_member_points').length) {
    coreActivityPointAdmincpMemberPoints.init();
  }
  if ($('#js_core_activitypoint_admincp_index').length || $('#js_core_activitypoint_information').length) {
    coreActivityAdmincpIndex.init();
  }
}

var coreActivityPointPointPackage = {
  init: function () {
    coreActivityPointPointPackage.selectPackage();
    coreActivityPointPointPackage.showPurchasePaymentBlock();
  },
  selectPackage: function () {
    $('.js_item_click', $('#js_point_packages_selection')).click(function () {
      var oThis = $(this);
      $('.js_item_click').find('.js_item_detail:first').removeClass('is_selected_package').find('.js_radio_package_selection').prop('checked', false);
      oThis.find('.js_item_detail:first').addClass('is_selected_package').find('.js_radio_package_selection').prop('checked', true);
    });
  },
  showPurchasePaymentBlock: function () {
    $('#js_show_payment_gateway', $('#js_point_packages_selection')).click(function () {
      var iSelectedPackageId = $('input[name="package-selection"]:checked').val();
      tb_show(oTranslations['activitypoint_select_payment_method'], $.ajaxBox('activitypoint.purchasePackage', 'height=300&width=450&package_id=' + iSelectedPackageId));
      js_box_remove(this);
    });
  }
}

var coreActivityPointAdmincpTransaction = {
  init: function () {
    $('.user_profile_link_span a', '#js_core_activitypoint_admincp_transaction').attr('target', '_blank');
  },
  resetForm: function () {
    let parent = $('#js_core_activitypoint_admincp_transaction');
    if (parent.length) {
      let userInput = parent.find('#user'),
          fromDateInput = parent.find('input[name="js_from__datepicker"]'),
          toDateInput = parent.find('input[name="js_to__datepicker"]'),
          selectEle = parent.find('.js_core_init_selectize_form_group select'),
          defaultFromDate = parent.find('#js_date_from_default').val(),
          defaultToDate = parent.find('#js_date_to_default').val(),
          defaultFromDateArray = defaultFromDate.split('/'),
          defaultToDateArray = defaultToDate.split('/');
      userInput.val('');
      fromDateInput.val(defaultFromDate);
      parent.find('#from_day').val(defaultFromDateArray[1]);
      parent.find('#from_month').val(defaultFromDateArray[0]);
      parent.find('#from_year').val(defaultFromDateArray[2]);
      toDateInput.val(defaultToDate);
      parent.find('#to_day').val(defaultToDateArray[1]);
      parent.find('#to_month').val(defaultToDateArray[0]);
      parent.find('#to_year').val(defaultToDateArray[2]);
      if (selectEle.length) {
        selectEle.each(function () {
          ($(this).selectize())[0].selectize.setValue('');
        });
      }
    }
  }
}

var coreActivityPointAdmincpMemberPoints = {
  init: function () {
    $('.user_profile_link_span a', $('#js_core_activitypoint_admincp_member_points')).attr('target', '_blank');
    coreActivityPointAdmincpMemberPoints.selectMember();
  },
  selectMember: function () {
    $('#js_select_all_member_points', $('#js_core_activitypoint_admincp_member_points')).click(function () {
      if ($(this).prop('checked')) {
        $('.js_select_member_points', $('#js_core_activitypoint_admincp_member_points')).prop('checked', true);
      }
      else {
        $('.js_select_member_points', $('#js_core_activitypoint_admincp_member_points')).prop('checked', false);
      }
    });
    $('.js_select_member_points', $('#js_core_activitypoint_admincp_member_points')).click(function () {
      if ($('.js_select_member_points:checked', $('#js_core_activitypoint_admincp_member_points')).length == $('.js_select_member_points', $('#js_core_activitypoint_admincp_member_points')).length) {
        $('#js_select_all_member_points', $('#js_core_activitypoint_admincp_member_points')).prop('checked', true);
      }
      else {
        $('#js_select_all_member_points', $('#js_core_activitypoint_admincp_member_points')).prop('checked', false);
      }
    });
    $('#js_adjust_all_member_points', $('#js_core_activitypoint_admincp_member_points')).click(function () {
      if ($('.js_select_member_points:checked', $('#js_core_activitypoint_admincp_member_points')).length) {
        var sUserIds = '';
        $('.js_select_member_points:checked', $('#js_core_activitypoint_admincp_member_points')).each(function () {
          sUserIds += $(this).data('id') + ',';
        });
        sUserIds = trim(sUserIds, ',');
        tb_show(oTranslations['activitypoint_point_actions'], $.ajaxBox('activitypoint.adjustPoint', 'height=400&width=600&user_id=' + sUserIds));
      }
    });
    $('#js_view_all_transactions_member_points', $('#js_core_activitypoint_admincp_member_points')).click(function () {
      if ($('.js_select_member_points:checked', $('#js_core_activitypoint_admincp_member_points')).length) {
        var sUserIds = '';
        $('.js_select_member_points:checked', $('#js_core_activitypoint_admincp_member_points')).each(function () {
          sUserIds += $(this).data('id') + ',';
        });
        sUserIds = trim(sUserIds, ',');
        var sUrl = $(this).data('url') + '?val[user_id]=' + sUserIds;
        window.location.href = sUrl;
      }
    });
  }
}

var coreActivityAdmincpIndex = {
  init: function () {
    $('.js_toggle_header_module:not(.built)').addClass('dont-unbind built')
        .on('click',function () {
          var sModule = $(this).data('module');
          $('.js_toggle_module_' + sModule).toggleClass('open');
          $(this).toggleClass('open');
        });
    $('.js_toggle_header_all:not(.built)').addClass('dont-unbind built')
        .on('click',function () {
          if ($(this).hasClass('open')) {
            if ($('.js_toggle_header_module').length) {
              $('.js_toggle_header_module').removeClass('open');
              $('.core-activitypoint-tr-module-content').removeClass('open');
            }
            if ($('.js_admincp_table_header_toggle').length) {
              $('.js_admincp_table_header_toggle').removeClass('open');
              $('.core-table-content-toggle').removeClass('open');
            }
          } else {
            if ($('.js_toggle_header_module').length) {
              $('.js_toggle_header_module').addClass('open');
              $('.core-activitypoint-tr-module-content').addClass('open');
            }
            if ($('.js_admincp_table_header_toggle').length) {
              $('.js_admincp_table_header_toggle').addClass('open');
              $('.core-table-content-toggle').addClass('open');
            }
          }
          $(this).toggleClass('open');
        });
    coreActivityAdmincpIndex.changeSettingValue();
  },
  changeSettingValue: function () {
    $('.js_change_value', $('#js_core_activitypoint_admincp_point_setting')).blur(
      function () {
        var sNewValue = $(this).val();
        $(this).closest('.js_point_setting').find('.js_value_text:first').html(sNewValue).removeClass('hide_it');
        $(this).attr('type', 'hidden');
      });

    $('.js_point_setting', $('#js_core_activitypoint_admincp_point_setting')).mouseup(
      function () {
        $(this).find('.js_value_text:first').addClass('hide_it');
        var oInput = $(this).find('.js_change_value:first');
        oInput.attr('type', 'number');
        setTimeout(function () {
          oInput.focus();
        }, 100);
      });
    $('.js_check_all_module', $('#js_core_activitypoint_admincp_point_setting')).click(function (e) {
      e.stopPropagation();
      var sModule = $(this).data('module');
      if (!$(this).prop('checked')) {
        $('.js_check_setting_' + sModule).prop({checked: false, disabled: true});
      }
      else {
        $('.js_check_setting_' + sModule).prop({checked: true, disabled: false});
      }
      $('.js_check_setting_' + sModule).trigger('change');
    });
    $('#js_core_activitypoint_choose_usergroup', $('#js_core_activitypoint_admincp_index')).on('change', function () {
      var sUrl = $(this).val();
      window.location.href = sUrl;
    });
  },
  validateFieldForAddingPackage: function () {
    var bIsPointValid = true;
    var bIsPriceValid = true;
    var sError = '';

    var iPoints = $('#points', $('#js_admincp_add_package')).val();

    if (empty(iPoints) || (!empty(iPoints) && !$.isNumeric(iPoints))) {
      bIsPointValid = false;
      sError += '<div class="error_message">' + oTranslations['activitypoint_invalid_points_add_package'] + '</div>';
    }

    $('.js_add_package_currency', $('#js_admincp_add_package')).find('input[type="text"]').each(function () {
      var sValue = $(this).val();
      if (!$.isNumeric(sValue) || ($.isNumeric(sValue) && parseFloat(sValue) <= 0)) {
        bIsPriceValid = false;
        sError += '<div class="error_message">' + oTranslations['activitypoint_invalid_price_add_package'] + '</div>';
      }
    });
    if (bIsPointValid && bIsPriceValid) {
      $('.js_add_package_error').html('').addClass('hide');
    }
    else {
      $('.js_add_package_error').html(sError).removeClass('hide');
    }
    return (bIsPointValid && bIsPriceValid);
  }
}

var coreActivityPointActionsBlock = {
  aMemberList: {},
  init: function () {
    $('.js_delete_member').click(function () {
      var oThis = $(this);
      var iMemberId = oThis.closest('.js_member_item').data('id');
      oThis.closest('.js_member_item').slideToggle();
      coreActivityPointActionsBlock.deleteMember(iMemberId);
      if (!empty(coreActivityPointActionsBlock.aMemberList)) {
        var sUserId = '';
        $.each(coreActivityPointActionsBlock.aMemberList, function (index, value) {
          sUserId += index + ',';
        });
        sUserId = trim(sUserId, ',');
        $.ajaxCall('activitypoint.getMaximumPointsForReduce', 'user_id=' + sUserId + '&action=' + $('input[name="point-action"]:checked', $('#core-activitypoint__adjust_member_points_block')).val());
      }
      else {
        var sErrorMessage = "<div class='error_message'>" + oTranslations['activitypoint_error_message_admincp_adjust_point'] + '</div>';
        $('#js_adjust_point_block_error', $('#core-activitypoint__adjust_member_points_block')).html(sErrorMessage).removeClass('hide_it');
        $('#js_adjust_point_button', $('#core-activitypoint__adjust_member_points_block')).prop('disabled', true);
        $('input[name="point-action"]', $('#core-activitypoint__adjust_member_points_block')).prop('disabled', true);
        $('#js_point_number', $('#core-activitypoint__adjust_member_points_block')).prop('disabled', true);
        $('.js_selected_members', $('#core-activitypoint__adjust_member_points_block')).addClass('hide_it');
        $('.js_maximum_points', $('#core-activitypoint__adjust_member_points_block')).addClass('hide_it');
        return false;
      }
      $('#js_adjust_point_block_error', $('#core-activitypoint__adjust_member_points_block')).html('').addClass('hide_it');
    });
    var sUserId = $('#js_member_list', $('#core-activitypoint__adjust_member_points_block')).val();
    if (sUserId.length) {
      coreActivityPointActionsBlock.aMemberList = {};
      var temp = sUserId.split(',');
      $.each(temp, function (index, value) {
        coreActivityPointActionsBlock.aMemberList[value] = true;
      });

    }
    $('#js_adjust_point_button', $('#core-activitypoint__adjust_member_points_block')).click(function () {
      if (!empty(coreActivityPointActionsBlock.aMemberList)) {
        var iMemberNumber = Object.keys(coreActivityPointActionsBlock.aMemberList).length;

        var iCurrenPoint = parseInt($('#js_point_number', $('#core-activitypoint__adjust_member_points_block')).val());
        var sAction = $('input[name="point-action"]:checked', $('#core-activitypoint__adjust_member_points_block')).val();
        var iMaximumPointForReduce = parseInt($('#js_maximum_point_for_reduce', $('#core-activitypoint__adjust_member_points_block')).val());
        var iMaximumPointForSend = parseInt($('#js_maximum_point_for_send', $('#core-activitypoint__adjust_member_points_block')).val());
        if (sAction == "reduce" && iCurrenPoint > iMaximumPointForReduce) {
          var sErrorMessage = "<div class='error_message'>" + oTranslations['activitypoint_error_message_can_not_reduce_point'] + '</div>';
          $('#js_adjust_point_block_error', $('#core-activitypoint__adjust_member_points_block')).html(sErrorMessage).removeClass('hide_it');
          return false;
        }
        else if (sAction == "send") {
          if ((iCurrenPoint * iMemberNumber) > iMaximumPointForSend) {
            var sErrorMessage = "<div class='error_message'>" + oTranslations['activitypoint_error_message_can_not_send_point'] + '</div>';
            $('#js_adjust_point_block_error', $('#core-activitypoint__adjust_member_points_block')).html(sErrorMessage).removeClass('hide_it');
            return false;
          }
          else if ((iCurrenPoint * iMemberNumber) <= 0) {
            var sErrorMessage = "<div class='error_message'>" + oTranslations['activitypoint_cannot_send_negative_point_number'] + '</div>';
            $('#js_adjust_point_block_error', $('#core-activitypoint__adjust_member_points_block')).html(sErrorMessage).removeClass('hide_it');
            return false;
          }
        }

        $('#js_adjust_point_block_error', $('#core-activitypoint__adjust_member_points_block')).html('').addClass('hide_it');
        var sUserId = '';
        $.each(coreActivityPointActionsBlock.aMemberList, function (index, value) {
          sUserId += index + ',';
        });
        sUserId = trim(sUserId, ',');
        $.ajaxCall('activitypoint.executeAdjustAction', 'action=' + sAction + '&user_id=' + sUserId + '&points=' + iCurrenPoint);
      }
    });
    $('input[name="point-action"]', $('#core-activitypoint__adjust_member_points_block')).click(function () {
      var sPhraseText = $(this).data('phrase');
      $('#js_adjust_point_button', $('#core-activitypoint__adjust_member_points_block')).html(sPhraseText);
      if ($(this).val() == "reduce") {
        $('.js_point_title', $('#core-activitypoint__adjust_member_points_block')).html(oTranslations['activitypoint_notify_maximum_point_for_reduce']);
        $('#point-number', $('#core-activitypoint__adjust_member_points_block')).html($('#js_maximum_point_for_reduce', $('#core-activitypoint__adjust_member_points_block')).val() + '.');
      }
      else {
        $('.js_point_title', $('#core-activitypoint__adjust_member_points_block')).html(oTranslations['activitypoint_notify_maximum_point_for_send']);
        $('#point-number', $('#core-activitypoint__adjust_member_points_block')).html($('#js_maximum_point_for_send', $('#core-activitypoint__adjust_member_points_block')).val() + '.');
      }
    });
    $('.core-activitypoint__adjust-point-block-members-avatar a:first', $('#core-activitypoint__adjust_member_points_block')).attr('target', '_blank');
  },
  deleteMember: function (iMemberId) {
    delete coreActivityPointActionsBlock.aMemberList[iMemberId];
  }
}
