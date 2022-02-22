$Ready(function () {
    if ($('#js_poll_form').length) {
        $('#js_poll_form').find('textarea[name="val[description]"]').prop('tabindex', 2);
    }
    $('.yn-dropdown-not-hide-poll').find('span[data-dismiss="dropdown"]').on('click', function () {
        $(this).parents('.dropdown').trigger('click');
    });

    $('.vote-member-inner').on('show.bs.dropdown', function () {
        $(this).parents('.answers_container').addClass('active');
    });
    $('.vote-member-inner').on('hide.bs.dropdown', function () {
        $(this).parents('.answers_container').removeClass('active');
    });
    $('.js_poll_expire').off('click').on('click',function(){
       $('.js_poll_expire_select_time').toggleClass('hide');
    });
    $('.js_feed_poll_more_answer').off('click').click(function () {
        $(this).closest('.poll-app.feed').addClass('show-full-answer');
    });
});
var iMaxAnswers = 0;
var iMinAnswers = 0;

$Behavior.buildSortableAnswers = function () {
    $('.js_answers').each(function () {
        var sVal = $(this).val();
        var sOriginal = $(this).val();
        sVal = (sVal.replace(/\D/g, ""));
        // dummy check
        if ("Answer " + sVal + "..." == sOriginal) {
            // this is a default answer
            $(this).addClass('default_value');
            $(this).focus(function () {
                if ($(this).val() == sOriginal) {
                    $(this).val('');
                    $(this).removeClass('default_value');
                }
            });
            $(this).blur(function () {
                if ($(this).val() == '') {
                    $(this).val(sOriginal);
                    $(this).addClass('default_value');
                }
            });
        }

    });
}


function appendAnswer(sId) {
    iCnt = 0;
    $('.js_answers').each(function () {
        if ($(this).parents('.placeholder:visible').length)
            iCnt++;
    });
    if (iCnt >= iMaxAnswers) {
        tb_show(oTranslations['notice'], '', '', oTranslations['you_have_reached_your_limit']);
        $('#' + $sCurrentId).find('.js_box_close').show();
        return false;
    }


    //iCnt++;
    var oCloned = $('.placeholder:first').clone();
    oCloned.find('.js_answers').val(oTranslations['answer'] + ' ' + iCnt + '...');
    oCloned.find('.js_answers').addClass('default_value close_warning');
    oCloned.find('.hdnAnswerId').remove();

    var sInput = '<input type="text" class="form-control js_answers" size="30" value="" name="val[answer][][answer]"/>';
    oCloned.find('.class_answer').html(sInput);
    oCloned.find('.js_answers').attr('name', 'val[answer][' + (iCnt + 1) + '][answer]');
    var oFirst = oCloned.clone();

    var firstAnswer = oFirst.html();

    $(sId).closest('.poll-app').find('.sortable').append('<div class="placeholder ui-sortable-handle">' + firstAnswer + '</div>')

    if($Core.poll.reloadValidation.bCanUseCoreReloadValidation) {
        $Core.reloadValidation.init();
    }
    return false;
}

/**
 * Uses JQuery to count the answers and validate if user is allowed one less answer
 * Effect used fadeOut(1200)
 */
function removeAnswer(sId) {
    /* Take in count hidden input */
    iCnt = -1;

    $('.js_answers').each(function () {
        iCnt++;
    });

    if (iCnt == iMinAnswers) {
        tb_show(oTranslations['notice'], '', '', oTranslations['you_must_have_a_minimum_of_total_answers'].replace('{total}', iMinAnswers));
        $('#' + $sCurrentId).find('.js_box_close').show();
        return false;
    }

    if($Core.poll.reloadValidation.bCanUseCoreReloadValidation) {
        var placeHolderId = $(sId).parents('.placeholder').attr('id');
        if(typeof placeHolderId !== 'undefined'){
            var answerName = $(sId).parents('.placeholder').find('input.js_answers ').attr('name');
            $Core.reloadValidation.changedEleData.js_poll_form[answerName] = true;
        }
        $Core.reloadValidation.preventReload();
    }

    $(sId).parents('.placeholder').remove();

    if($Core.poll.reloadValidation.bCanUseCoreReloadValidation) {
        $Core.poll.reloadValidation.onPollAnswerChangeOrder();
    }

    return false;
}

$Behavior.poll_poll_appendClick = function () {
    $('.append_answer').click(function () {
        return false;
    });
};


$Core.poll =
    {
        aParams: {},
        iTotalQuestions: 1,

        init: function (aParams) {
            this.aParams = aParams;
        },

        build: function () {
        },

        deleteImage: function (iPoll) {
            $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
                $.ajaxCall('poll.deleteImage', 'iPoll=' + iPoll);
            }, function () {
            });
            return false;
        },

        showFormForEditAgain: function ($answerId, iPollId) {
            $('.poll_question input.js_poll_answer').each(function () {
                if ($('#js_answer_' + $(this).val()).hasClass('user_answered_this'))
                    $(this).prop('checked', true);
                else
                    $(this).prop('checked', false);
            });
            if ($('#vote_list_' + iPollId)) {
                $('.poll_answer_button').hide();
                $('#vote_list_' + iPollId).hide();
            }
            if ($('#vote_' + iPollId)) {
                $('#vote_' + iPollId).show();
            }
        },

        hideFormForEditAgain: function (iPollId) {
            if ($('#vote_' + iPollId)) {
                $('#vote_' + iPollId).hide();
                $('.poll_answer_button').show();
            }
            if ($('#vote_list_' + iPollId)) {
                $('#vote_list_' + iPollId).show();
            }
        },

        submitPoll: function (bCanChangePoll, iPollId) {
            // check select poll
            let formId = '#js_poll_form_' + iPollId,
                formObject = $(formId),
                bIsInFeed = $(formId).find('input[name="val[is_feed]"]').length,
                _this = $(this),
                btn = formObject.find('[onclick*="$Core.poll.submitPoll"]');
            btn.prop('disabled', true);
            if (!bIsInFeed) {
                if (bCanChangePoll) {
                    _this.parent().hide();
                }
                _this.parents('.p_4:first').find('.js_poll_image_ajax:first').show();
                $.fn.ajaxCall(bIsInFeed ? 'poll.addVoteInFeed' : 'poll.addVote', formObject.serialize(), null, null, function() {
                    btn.prop('disabled', false);
                });
            }
            else {
                $.fn.ajaxCall('poll.addVoteInFeed', formObject.serialize(), null, null, function() {
                    btn.prop('disabled', false);
                });
            }
            return false;
        },
        processVoteInFeed: function(iPollId, sContent){
            if(empty(sContent))
            {
                return false;
            }
            if($('#js_feed_content').length)
            {
                var sParsedContent = $Core.b64DecodeUnicode(sContent);
                var sFormContent = $(sParsedContent).find('#js_poll_form_' + iPollId).detach().html();
                var sClass = $(sParsedContent).attr('class');
                var oFeed = $('#js_feed_content').find('#js_poll_feed_item_' + iPollId);
                oFeed.find('#js_poll_form_' + iPollId).html(sFormContent);
                oFeed.attr('class', sClass);
                $Core.loadInit();
            }
        },
        submitCustomPoll: function (ele) {
            if (typeof CKEDITOR != "undefined" && CKEDITOR.instances.description != "undefined") {
                CKEDITOR.instances.description.updateElement();
            }
            $('.js_poll_submit_button').addClass('disabled');

            $(ele).ajaxCall('poll.addCustom');
            return false;
        },
        dropzoneOnSuccess: function (ele, file, response) {
            $Core.poll.processResponse(ele, file, response);
        },

        dropzoneOnAddedFile: function (ele) {
            if ($Core.dropzone.instance['poll'].files.length > 1) {
                $Core.dropzone.instance['poll'].removeFile($Core.dropzone.instance['poll'].files[0]);
            }
        },
        processResponse: function (ele, file, response) {
            response = JSON.parse(response);

            // process error
            if (typeof response.error !== 'undefined') {
                tb_show(oTranslations['notice'], '', null, response.error);
                $('.js_poll_submit_button').removeAttr('disabled');
                return $Core.dropzone.setFileError(file, response.error);
            }

            // upload successfully
            if (typeof response.file !== 'undefined') {
                $('#image_path').val(response.file);
                $('#server_id').val(response.server_id);
                $('#js_poll_form').submit();
            }
        },
    };


$Behavior.design_page = function () {
    $('.js_cancel_change_poll_question').click(function () {
        if (document.getElementById('js_current_poll_question').style.display == '' || document.getElementById('js_current_poll_question').style.display == 'inline') {
            $('#js_current_poll_question').hide();
            $('#js_update_poll_question').show();
        }
        else {
            $('#js_current_poll_question').show();
            $('#js_update_poll_question').hide();
        }

        return false;
    });

    $('.js_current_poll_question').click(function () {
        // hide the label
        $('#js_current_poll_question').hide();
        // show the input field
        $('#js_update_poll_question').show();

        return false;
    });

    // Colorpicker
    $('#js_poll_design_wrapper ._colorpicker:not(.built)').each(function () {
        var t = $(this),
            h = t.parent().find('._colorpicker_holder');

        t.addClass('built');
        h.css('background-color', '#' + t.val());

        h.colpick({
            layout: 'hex',
            submit: false,
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                t.val(hex);
                h.css('background-color', '#' + hex);
                var rel = t.data('rel');
                switch (rel) {
                    case 'backgroundChooser':
                        $('.poll_answer_container').css('backgroundColor', '#' + hex);
                        break;
                    case 'percentageChooser':
                        $('.poll_answer_percentage').css('backgroundColor', '#' + hex);
                        break;
                    default:
                        $('.poll_answer_container').css('border', '1px solid #' + hex);
                        break;
                }
            },
            onHide: function () {
                t.trigger('change');
            }
        });

    });

    // Answers
    $('.js_update_answer').click(function () {
        var iId = $(this).get(0).id.replace('js_text_answer_', '');
        $('#js_text_answer_' + iId).hide();
        $('#js_input_answer_' + iId).show();
    });

    $('.js_cancel_change_answer').click(function () {
        // get the id of the answer
        var iId = $(this).get(0).id.replace('js_cancel_change_answer_', '');

        // set the value of the input to the current value of the 'label', this step should not be needed
        // $('#js_input_answer_text_' + iId).val(trim($('#js_text_answer_' + iId).html()));

        // hide the input field
        $('#js_input_answer_' + iId).hide();

        // show the 'label' field
        $('#js_text_answer_' + iId).show();

        return false;
    });

    // this function cancels editing an answer
    $('.js_commit_change_answer').off('click').click(function () {
        // get the id of the answer
        var iId = $(this).get(0).id.replace('js_commit_change_answer_', '');

        // hide the input field
        $('#js_input_answer_' + iId).hide();
        // commit the changes with a beautiful ajax call
        $.ajaxCall('poll.changeAnswer', 'iId=' + iId + '&sTxt=' + $('#js_input_answer_' + iId).val());

        // show the 'label'
        $('#js_text_answer_' + iId).html(trim($('#js_input_answer_text_' + iId).val()));
        $('#js_text_answer_' + iId).show();

        // we need nothing else because the input is still there
        return false;
    });

}

function approvePoll(iPoll) {
    $Core.jsConfirm({}, function () {
        $.ajaxCall('poll.moderatePoll', 'iResult=0&iPoll=' + iPoll);
    }, function () {
    });
    return false;

}

function deletePoll(iPoll) {
    $Core.jsConfirm({
        message: oTranslations['are_you_sure_you_want_to_delete_this_poll']
    }, function () {
        $.ajaxCall('poll.moderatePoll', 'iResult=2&iPoll=' + iPoll);
    }, function () {
    });
    return false;
}

$Core.poll.reloadValidation = {
    bCanUseCoreReloadValidation: $Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined",

    init: function() {
        if(!$('#js_poll_form').length || !$Core.poll.reloadValidation.bCanUseCoreReloadValidation){
            return false;
        }

        $('#close_hour').attr('class', 'form-control close_warning');
        $('#close_minute').attr('class', 'form-control close_warning');
        $Core.reloadValidation.init();
        $Core.poll.reloadValidation.initAnsOrder = $Core.reloadValidation.initEleData.js_poll_form['val[answerOrder]'] = this.getListAnswerIds();
        $Core.reloadValidation.initEleData.js_poll_form['val[ClosedTime][date]'] = '';

        if(this.getCloseTimeOption()) {
            $Core.reloadValidation.initEleData.js_poll_form['val[ClosedTime][date]'] = $('input[name="js_close__datepicker"]').val();

            $('.js_date_picker').each(function () {
                var _this = $(this);
                var prevOptions = _this.datepicker('option', 'all');
                var prevEventOnSelect = prevOptions.onSelect;

                delete prevOptions.onSelect;

                _this.datepicker('destroy').datepicker($.extend(prevOptions, {
                    onSelect: function(dateText) {
                        prevEventOnSelect(dateText);
                        var newDate = $('input[name="js_close__datepicker"]').val();

                        if($Core.reloadValidation.initEleData.js_poll_form['val[ClosedTime][date]'] !== newDate){
                            $Core.reloadValidation.changedEleData.js_poll_form['val[ClosedTime][date]'] = true;
                        } else {
                            delete $Core.reloadValidation.changedEleData.js_poll_form['val[ClosedTime][date]'];
                        }
                        $Core.reloadValidation.preventReload();
                    }
                }));
            });
        }
    },

    getListAnswerIds: function() {
        var aAns = [];
        $('div.placeholder.ui-sortable-handle').each(function(){
            var iAnswerId = typeof $(this).find('.hdnAnswerId').val() === 'undefined' ? '0' : $(this).find('.hdnAnswerId').val();
            aAns.push(iAnswerId);
        });
        return aAns;
    },

    compareEqualArrays: function(a, b){
        if(a.length !== b.length) {
            return false;
        } else {
            for(var i = 0; i < a.length; i++) {
                if(a[i] !== b[i]) {
                    return false;
                }
            }
            return true;
        }
    },

    onPollAnswerChangeOrder: function () {
        var aNewListAns = this.getListAnswerIds();
        aNewListAns = aNewListAns.filter(function(val) {if(val !== "0") return val;});
        if($Core.poll.reloadValidation.bCanUseCoreReloadValidation){
            var OriginAnsIds = $Core.poll.reloadValidation.initAnsOrder;
            if(!$Core.poll.reloadValidation.compareEqualArrays(aNewListAns, OriginAnsIds)){
                $Core.reloadValidation.changedEleData.js_poll_form['val[answerOrder]'] = true;
            } else {
                delete $Core.reloadValidation.changedEleData.js_poll_form['val[answerOrder]'];
            }
            $Core.reloadValidation.preventReload();
        }
    },

    getCloseTimeOption: function() {
        return $('div.form-group.poll-app.create.votes.poll-end-time').find('div.item_is_active_holder').hasClass('item_selection_active');
    }
}

$Core.poll.designReloadValidation = {
    bCanUseCoreReloadValidation: $Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined",
    init: function() {
        if(!$('#page_poll_design').length || !$Core.poll.designReloadValidation.bCanUseCoreReloadValidation){
            return false;
        }

        $('.colpick_hex_field').find(':input').each(function(){
            $(this).on('change', function(e){
                var aDefinedNames = ['val[js_poll_background]', 'val[js_poll_percentage]', 'val[js_poll_border]'];
                $oNewValue = $Core.poll.designReloadValidation.getColors();
                aDefinedNames.forEach(function(name){
                    if($oNewValue[name] !== $Core.reloadValidation.initEleData.js_poll_design_form[name]) {
                        $Core.reloadValidation.changedEleData.js_poll_design_form[name] = true;
                    } else {
                        delete $Core.reloadValidation.changedEleData.js_poll_design_form[name];
                    }
                });
                $Core.reloadValidation.preventReload(false);
            });
        })
    },

    getColors: function() {
        var oValues = {};
        $('#js_poll_design_wrapper').find('li').each(function(){
            var oInput = $(this).find('input.close_warning');
            var sName = oInput.attr('name');
            oValues[sName] = oInput.attr('value');
        });
        return oValues;
    }
}

PF.event.on('on_page_change_end', function () {
    $Core.poll.reloadValidation.init();
    $Core.poll.designReloadValidation.init();
});

PF.event.on('on_document_ready_end', function () {
    $Core.poll.reloadValidation.init();
    $Core.poll.designReloadValidation.init();
});
