var PStatusBg = {
    sListCollectionId: '#js_p_statusbg_collection_list',
    sToggleBtn: '.js_status_bg_toggle',
    sBackgroundPost: '.js_textarea_background',
    sBackgroundId: '#js_p_statusbg_background_id',
    bHasPreview: false,
    bHasEgift: false,
    sInputDiv: '#js_activity_feed_form textarea[name="val[user_status]"]',
    sDivInputDiv: '#js_activity_feed_form #global_attachment_status div.contenteditable',
    sEditDiv: '#js_activity_feed_edit_form textarea[name="val[user_status]"]',
    sEditDivContent: '#js_activity_feed_edit_form #global_attachment_status div.contenteditable',
    sSubmitEdit: '#js_activity_feed_edit_form #activity_feed_submit',
    sEditForm: '#js_activity_feed_edit_form',
    bIsStatus: true,
    bReset: false,
    bSubmitting: false,
    bDisabling: false,
    initStatusBg: function () {
        PStatusBg.checkStatusOversize();
        if ($('#global_attachment_status').length && !$(PStatusBg.sListCollectionId).length) {
            $('#global_attachment_status').ajaxCall('pstatusbg.loadCollectionsList', '', 'post', null, function () {
                PStatusBg.initCollectionSelection();
            });
        }
        if (!PStatusBg.bReset) {
            PStatusBg.initCollectionSelection();
        }
        $('.activity_feed_form_attach li a').on('click',function () {
            if ($(this).attr('rel') !== 'global_attachment_status') {
                PStatusBg.bIsStatus = false;
                $(PStatusBg.sBackgroundPost).removeClass('has-background').removeAttr('style');
                $(PStatusBg.sListCollectionId).hide();
                $(PStatusBg.sToggleBtn).parent().hide();
                $(PStatusBg.sBackgroundId).val(0);
            } else {
                PStatusBg.bIsStatus = true;
                if (PStatusBg.bDisabling) {
                    return true;
                }
                if ($(PStatusBg.sToggleBtn).hasClass('active')) {
                    $(PStatusBg.sListCollectionId + ':not(.force-hide)').show();
                }
                $(PStatusBg.sToggleBtn + ':not(.force-hide)').parent().show().css('display', 'flex');
                var sHref = $('.js_switch_collection_li.active').find('.js_switch_collection').attr('href'),
                    oSelected = $(sHref).find('.collection-item.active');
                PStatusBg.selectBackground(oSelected[0]);
            }
        });

        //Reset status form
        $ActivityFeedCompleted.background = function () {
            PStatusBg.clearBackground();
            PStatusBg.resizeTextarea(false, 0, 0);
            $(PStatusBg.sBackgroundId).val(0);
            $(PStatusBg.sBackgroundPost).addClass('p-statusbg-container').removeClass('has-background').removeAttr('style');
            $(PStatusBg.sToggleBtn).removeClass('active').removeClass('force-hide').parent().show();
            $(PStatusBg.sListCollectionId).removeClass('force-hide').hide();
            $(PStatusBg.sInputDiv).closest('.activity-feed-status-form-active').removeClass('activity-feed-status-form-active');
            PStatusBg.bReset = true;
            PStatusBg.bIsStatus = true;
            PStatusBg.bHasPreview = false;
            PStatusBg.bHasEgift = false;
            PStatusBg.bDisabling = false;
        };
    },
    clearBackground: function () {
        $('.js_switch_collection_li').removeClass('active');
        $('.js_switch_collection_li:first').addClass('active');
        $('.p-statusbg-collection-content .tab-pane').removeClass('active');
        $('.p-statusbg-collection-content .tab-pane:first').addClass('active');
        $('.p-statusbg-collection-content .collection-item').removeClass('active');
        $('.p-statusbg-collection-content .collection-item:first').addClass('active');
    },
    initCollectionSelection: function () {
        setTimeout(function () {
            if ($(PStatusBg.sListCollectionId).length) {
                var oToggleIcon = '<div class="p-statusbg-toggle-holder" style="display: none;"><span class="p-statusbg-toggle-collection ' + PStatusBg.sToggleBtn.replace('.', '') + ' active" onclick="$(\'' + PStatusBg.sListCollectionId + '\').toggle(400);$(\'' + PStatusBg.sToggleBtn + '\').toggleClass(\'active\');"><i class="ico ico-color-palette"></i></span></div>';
                var oInput = $('#global_attachment_status textarea');
                var oDivInput = $('#global_attachment_status div.contenteditable');
                if (!oInput.closest(PStatusBg.sBackgroundPost).length) {
                    var sInput = oInput[0],
                        oContainer = document.createElement('div'),
                        oParent = sInput.parentNode;
                    oParent.replaceChild(oContainer, sInput);
                    oContainer.appendChild(sInput);
                    if (oDivInput.length) {
                        var sDivInput = oDivInput[0];
                        oContainer.appendChild(sDivInput);
                    }
                    $(oContainer).addClass(PStatusBg.sBackgroundPost.replace('.', '') + ' p-statusbg-container');
                    if (!$(oContainer).find(PStatusBg.sToggleBtn).length && !$(oContainer).closest('#js_activity_feed_edit_form').length) {
                        $(oContainer).append(oToggleIcon);
                    }
                    setTimeout(function () {
                        $(oContainer).find('textarea').addClass('dont-unbind');
                        $Core.attachFunctionTagger($(oContainer).find('textarea')[0]);
                    }, 100);
                }
                $(PStatusBg.sBackgroundPost).off('append').on('append', function (event) {
                    var obj = event.target;
                    if ($(obj).hasClass('js_preview_link_attachment_custom_form') && $(obj).html().length) {
                        PStatusBg.bDisabling = true;
                        PStatusBg.disableBackground();
                        PStatusBg.bHasPreview = true;

                    }
                });
                if ($(PStatusBg.sInputDiv).closest('.activity-feed-status-form-active').length && $('.activity_feed_form_attach li a.active').attr('rel') === 'global_attachment_status') {
                    PStatusBg.showCollections();
                }
                $(document).on('keyup change paste DOMSubtreeModified', PStatusBg.sEditDiv, function () {
                    var _this = this;
                    setTimeout(function () {
                        PStatusBg.handlePaste(_this, true);
                    }, 100);
                });
                if ($(PStatusBg.sEditDivContent).length) {
                    $(document).on('keyup change paste DOMSubtreeModified click', PStatusBg.sEditDivContent, function () {
                        var _this = this;
                        setTimeout(function () {
                            PStatusBg.handlePaste(_this, true);
                        }, 100);
                    });
                }
                $(document).on('DOMSubtreeModified', '.activity_feed_form .js_tagged_review', function () {
                    if ($(this).html().length) {
                        $('#js_location_feedback').addClass('has-tagged');
                    } else {
                        $('#js_location_feedback').removeClass('has-tagged');
                    }
                });

                $(document).on('mousedown', PStatusBg.sSubmitEdit, function () {
                    if (PStatusBg.bSubmitting) return true;
                    PStatusBg.bSubmitting = true;
                    var oForm = $(this).closest('form'),
                        oStatusText = oForm.find('textarea[name="val[user_status]"]'),
                        oModule = oForm.find('input[name="val[callback_module]"]'),
                        oTypeId = oForm.find('input[name="val[type_id]"]'),
                        oDisabled = oForm.find('input[name="val[disabled_status_background]"]'),
                        sAjax = oForm.find('#custom_ajax_form_submit').text();
                    if (oStatusText.length && oStatusText.val().length && (oModule.length || oTypeId.length || sAjax == 'feed.updatePost') && oDisabled.length) {
                        $(this).ajaxCall('pstatusbg.editStatusBackground', $.param({
                            'module': oModule.val(),
                            'feed_id': oForm.find('input[name="val[feed_id]"]').val(),
                            'item_id': oForm.find('input[name="val[parent_user_id]"]').val(),
                            'is_disabled': oDisabled.val(),
                            'url_ajax': sAjax
                        }), 'post', null, function () {
                            PStatusBg.bSubmitting = false;
                            if ($('.activity_feed_link_form_ajax').text().match(/addFeedComment/)) {
                                setTimeout(function () {
                                    window.location.reload();
                                }, 2000);
                            }
                        })
                    }
                    return true;
                });

                $(document).on('focus click', PStatusBg.sInputDiv, function () {
                    PStatusBg.showCollections();
                });

                $(document).on('keydown paste keyup DOMSubtreeModified', PStatusBg.sInputDiv, function () {
                    var _this = this;
                    setTimeout(function () {
                        PStatusBg.handlePaste(_this);
                    }, 100);
                });

                if ($(PStatusBg.sDivInputDiv).length) {
                    $(document).on('focus click', PStatusBg.sDivInputDiv, function () {
                        PStatusBg.showCollections();
                    });

                    $(document).on('keydown paste keyup DOMSubtreeModified', PStatusBg.sDivInputDiv, function () {
                        var _this = this;
                        setTimeout(function () {
                            PStatusBg.handlePaste(_this);
                        }, 100);
                    });
                }

                $('.js_p_statusbg_header_nav .item-next').off('click').on('click', function () {
                    var parent_statusbg = $(this).closest('.p-statusbg-collection-header'),
                        statusbg_active = parent_statusbg.find('li.active');
                    if (statusbg_active.is(':last-of-type')) {
                        return false;
                    } else {
                        var oNext = statusbg_active.next();
                        oNext.addClass('active');
                        $('.p-statusbg-collection-content .tab-pane').removeClass('active');
                        $(oNext.find('a').attr('href')).addClass('active');
                        oNext.find('a').trigger('click');
                        if (oNext.is(':last-of-type')) {
                            $(this).addClass('disabled');
                            $('.js_p_statusbg_header_nav .item-prev').removeClass('disabled');
                        }
                        statusbg_active.removeClass('active');
                    }
                });
                $('.js_p_statusbg_header_nav .item-prev').off('click').on('click', function () {
                    var parent_statusbg = $(this).closest('.p-statusbg-collection-header'),
                        statusbg_active = parent_statusbg.find('li.active');
                    if (statusbg_active.is(':first-of-type')) {
                        return false;
                    } else {
                        var oPrev = statusbg_active.prev();
                        oPrev.addClass('active');
                        $('.p-statusbg-collection-content .tab-pane').removeClass('active');
                        $(oPrev.find('a').attr('href')).addClass('active');
                        oPrev.find('a').trigger('click');
                        if (oPrev.is(':first-of-type')) {
                            $(this).addClass('disabled');
                            $('.js_p_statusbg_header_nav .item-next').removeClass('disabled');
                        }
                        statusbg_active.removeClass('active');
                    }
                });
                if ($('#js_activity_feed_form #js_core_egift_preview').length) {
                    $(document).on('DOMSubtreeModified', '#js_activity_feed_form #js_core_egift_preview', function () {
                        setTimeout(function () {
                            if ($('#js_activity_feed_form #js_core_egift_id').val() > 0) {
                                PStatusBg.bDisabling = true;
                                PStatusBg.disableBackground();
                                PStatusBg.bHasEgift = true;
                            } else {
                                PStatusBg.bHasEgift = false;
                                PStatusBg.handlePaste($(PStatusBg.sInputDiv)[0]);
                            }
                        }, 100);
                    });
                }
            }
        }, 500);
    },
    showFullCollection: function (ele) {
        var oEle = $(ele),
            iCollectionId = oEle.data('collection_id');
        if (!iCollectionId) {
            return false;
        }
        $('.js_bg_hide_' + iCollectionId).removeClass('hide');
        oEle.hide();
        return true;
    },
    selectBackground: function (ele) {
        var pointer = $(PStatusBg.sInputDiv).prop('selectionEnd'),
            oEle = $(ele);
        if (!oEle.length) {
            // try get current active
            oEle = $('.collection-item.active');
        }
        if (!oEle.length) {
            return false;
        }
        var iBgId = oEle.data('background_id'),
            sImage = oEle.data('image_url').replace('_48', '_1024').replace('-sm', '-min') || '',
            oContainer = oEle.closest('#js_activity_feed_form').find(PStatusBg.sBackgroundPost),
            iCurrentBg = parseInt($(PStatusBg.sBackgroundId).val());
        if (!oContainer.length) return false;
        $('.p-statusbg-collection-listing').find('.collection-item').removeClass('active');
        oEle.addClass('active');
        if (iBgId == 0) {
            oContainer.removeClass('has-background').removeAttr('style');
        } else {
            oContainer.addClass('has-background').css('background-image', 'url(' + sImage + ')');
        }
        PStatusBg.resizeTextarea(false, iCurrentBg, iBgId);
        $(PStatusBg.sBackgroundId).val(iBgId);
        $(PStatusBg.sInputDiv).focus();
        $(PStatusBg.sInputDiv)[0].setSelectionRange(pointer, pointer);
        return true;
    },
    showCollections: function () {
        if (!PStatusBg.bIsStatus || $(PStatusBg.sInputDiv).closest('#js_activity_feed_edit_form').length) return false;
        if ($(PStatusBg.sListCollectionId).length) {
            if (PStatusBg.bReset) {
                $(PStatusBg.sToggleBtn).addClass('active');
                PStatusBg.bReset = false;
            }
            if ($(PStatusBg.sToggleBtn + ':not(.force-hide)').hasClass('active')) {
                $(PStatusBg.sListCollectionId + ':not(.force-hide)').show(400);
            }
            $(PStatusBg.sToggleBtn + ':not(.force-hide)').parent().show().css('display', 'flex');
        }
    },
    handlePaste: function (oObj, bEdit) {
        if(PStatusBg.bSubmitting && bEdit) PStatusBg.bSubmitting = false;
        if (!$(PStatusBg.sListCollectionId).length || !PStatusBg.bIsStatus) return false;
        var isContenteditable = $(oObj).hasClass('contenteditable');
        var value = isContenteditable ? $(oObj).text() : $(oObj).val();
        var regrex_mention_1 = /\[user=(\d+)\](.+?)\[\/user\]/g,
            value_actual = value.replace(regrex_mention_1, '$2'),
            break_line = value_actual.match(/\n/g) || [],
            break_line_1 = [],
            break_line_contenteditable = isContenteditable ? ($(oObj).parent().find('textarea[name="val[user_status]"]').val().replace(regrex_mention_1, '$2').match(/\n/g) || []) : [],
            bPass = true;
        //Check input length
        if (value_actual.length > 150 || (break_line.length + break_line_1.length) > 3 || break_line_contenteditable.length > 3 || ($('.js_preview_link_attachment_custom_form').length && $('.js_preview_link_attachment_custom_form').html().length) || PStatusBg.bHasEgift) {
            if (PStatusBg.bDisabling) return false;
            PStatusBg.bDisabling = true;
            bPass = false;
            if (bEdit) {
                if (!$('#js_p_statusbg_check_edit').length) {
                    $(PStatusBg.sEditForm).append('<input type="hidden" id="js_p_statusbg_check_edit" name="val[disabled_status_background]" value="1"/>');
                } else {
                    $(PStatusBg.sEditForm).find('#js_p_statusbg_check_edit').val(1);
                }
            } else {
                PStatusBg.disableBackground();
            }
        } else {
            if (!PStatusBg.bDisabling) return false;
            PStatusBg.bDisabling = false;
            bPass = true;
            if (bEdit) {
                if (!$('#js_p_statusbg_check_edit').length) {
                    $(PStatusBg.sEditForm).append('<input type="hidden" id="js_p_statusbg_check_edit" name="val[disabled_status_background]" value="0"/>');
                } else {
                    $(PStatusBg.sEditForm).find('#js_p_statusbg_check_edit').val(0);
                }
            } else {
                $(PStatusBg.sBackgroundPost).addClass('p-statusbg-container');
                if ($('.p-statusbg-original-emoji').length) {
                    $('.p-statusbg-emoji').show();
                }
                if ((!$('.js_preview_link_attachment_custom_form').length || !$('.js_preview_link_attachment_custom_form:first').html().length) && !PStatusBg.bReset) {
                    $(PStatusBg.sToggleBtn).removeClass('force-hide').addClass('active').parent().show().css('display', 'flex');
                    $(PStatusBg.sListCollectionId).removeClass('force-hide').show();
                    $('.js_switch_collection_li.active > a').trigger('click');
                    var sHref = $('.js_switch_collection_li.active').find('.js_switch_collection').attr('href'),
                        oSelected = $(sHref).find('.collection-item.active');
                    PStatusBg.selectBackground(oSelected[0]);
                }
            }
        }
        setTimeout(function () {
            PStatusBg.checkStatusFormOversize();
        }, 100);

        return bPass;
    },
    resizeTextarea: function (bForce, iCurrentBg, iBgId) {
        if ($('#js_activity_feed_edit_form').length) return false;
        if (cacheShadownInfo !== false && shadow !== null) {
            shadow.css('word-break', 'break-word');
        }
        if (iCurrentBg == 0 || (iCurrentBg > 0 && iBgId == 0) || bForce) {
            setTimeout(function () {
                var oInput = $(PStatusBg.sInputDiv);
                if (cacheShadownInfo !== false && shadow !== null) {
                    shadow.css('font-size', oInput.css('font-size'));
                    shadow.css('line-height', oInput.css('line-height'));
                    shadow.css('width', oInput.width());
                }
                $Core.resizeTextarea(oInput);
            }, 100);
        }
    },
    disableBackground: function () {
        $(PStatusBg.sToggleBtn).addClass('force-hide').removeClass('active').parent().hide();
        $(PStatusBg.sListCollectionId).addClass('force-hide').hide();
        $('#js_activity_feed_form').find(PStatusBg.sBackgroundPost).removeClass('has-background').removeAttr('style');
        if ($(PStatusBg.sBackgroundId).val()) {
            setTimeout(function () {
                PStatusBg.resizeTextarea(true);
            }, 50);
        }
        $(PStatusBg.sBackgroundId).val(0);
        $(PStatusBg.sBackgroundPost).removeClass('p-statusbg-container');
        if ($('.p-statusbg-original-emoji').length) {
            $('.p-statusbg-emoji').hide();
        }
    },
    appendCollectionList: function (sHtml) {
        if (!$(PStatusBg.sListCollectionId).length) {
            $('#js_activity_feed_form .activity_feed_form_button').before(sHtml);
        }
    },
    checkStatusOversize: function () {
        var compare_height = parseFloat($('.p-statusbg-feed').width()) * 0.5625;
        $('.p-statusbg-feed:not(.statusbg-built)').each(function () {
            if ($(this).length > 0) {
                if ($(this).find('.activity_feed_content_status').outerHeight() > compare_height) {
                    $(this).addClass('statusbg-bigsize');
                } else {
                    $(this).removeClass('statusbg-bigsize');
                }
            }
            $(this).find('.activity_feed_content_status').css('opacity', '1');
            $(this).addClass('statusbg-built');
        });
    },
    checkStatusFormOversize: function () {
        var height_bg = $('.p-statusbg-container.has-background textarea').outerHeight();
        if (height_bg >= $('.p-statusbg-container.has-background').outerHeight()) {
            $('.p-statusbg-container.has-background').css('min-height', height_bg);
        } else {
            $('.p-statusbg-container.has-background').css('min-height', 'auto');
        }
    },
};

//check when content status overlap size 16/9
$(window).resize(function () {
    $('.p-statusbg-feed').removeClass('statusbg-built');
    PStatusBg.checkStatusOversize();
    PStatusBg.checkStatusFormOversize();
    PStatusBg.resizeTextarea(true);
});

PF.event.on('on_page_change_end', function () {
    PStatusBg.initStatusBg();
});

$Ready(PStatusBg.initStatusBg);

(function ($) {
    var origAppend = $.fn.append;
    $.fn.append = function () {
        return origAppend.apply(this, arguments).trigger("append");
    };
})(jQuery);

