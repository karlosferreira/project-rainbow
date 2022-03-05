$Ready(function () {
    var initPlaySongInterval;
    
    //drag drop on mobile
    $Core.music.initTouchHandler();

    if (typeof(mejs) == 'undefined') {
        initPlaySongInterval = window.setInterval(function () {
            $Core.loadStaticFiles(oParams.sJsHome.replace('PF.Base', 'PF.Site') + 'Apps/core-music/assets/jscript/mediaelementplayer/mediaelement-and-player.js');
            if (typeof(mejs) == 'undefined') {
            }
            else {
                $(".js_song_player").each(function () {
                    $Core.music.initPlayInFeed(this);
                });
                window.clearInterval(initPlaySongInterval);
            }
        }, 200);
    }
    else {
        $(".js_song_player").each(function () {
            $Core.music.initPlayInFeed(this);
        });
    }

    $('#js_done_upload').off('click').on('click', function () {
        window.onbeforeunload = null;
        $.ajax({
            url: PF.url.make('music/message'),
            data: {
                valid: $Core.music.iValidFile,
                module: $Core.music.sModule,
                album: $Core.music.iAlbumId,
                item: $Core.music.iItemId,
                uploaded_songs: JSON.stringify($Core.music.aUploadedSong)
            }
        }).success(function (data) {
            var aData = JSON.parse(data);
            $Core.music.aUploadedSong = {};
            if (aData.sUrl != "") {
                window.location.href = aData.sUrl;
            } else {
                window.location.href = getParam('sBaseURL') + 'music';
            }
        });
    });

    $(document).off('click', '.dropzone-clickable').on('click', '.dropzone-clickable', function () {
        var t = $(this);
        if (t.data('dropzone-button-id')) {
            $('#' + t.data('dropzone-button-id')).trigger('click');
        }
        return false;
    });
    $('#js_music_album_select').on('change',function() {
        if (empty(this.value)) {
            $('#js_song_privacy_holder').slideDown();
        } else {
            $('#js_song_privacy_holder').slideUp();
        }
    });
    $('.js_feed_music_action_more').click(function(){
        $(this).closest('.item-feed-music-song').toggleClass('show-action');
    });
    $(document).off('click', '.js_music_dropdown_add_to_playlist').on('click', '.js_music_dropdown_add_to_playlist', function(e) {
        e.stopPropagation();
    });
    // Just init custom scrollbar on desktop view.
    if (!(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )) {
        $Core.music.isMobile = false;
        //Init scrollbar and prevent browser scroll
        $("#page_music_view-album .album-detail-tracks, #page_music_view-playlist .album-detail-tracks").mCustomScrollbar({
            theme: "minimal-dark",
            mouseWheel: {preventDefault: true}
        }).addClass('dont-unbind-children');

        //Init scrollbar default
        $(".music-quick-list-playlist").mCustomScrollbar({
            theme: "minimal-dark",
            mouseWheel: {preventDefault: true}
        }).addClass('dont-unbind-children');

        PF.event.on('before_cache_current_body', function () {
            $('.mCustomScrollbar').mCustomScrollbar('destroy');
        });
    } else {
        $Core.music.isMobile = true;
    }
    if ($('.music_player-inner').length) {
        $(document).on('click',function(e) {
            var ele = '#js_music_add_to_playlist_dropdown';
            if (!$(e.target).closest(ele).length && $(ele).length) {
                $Core.music.iAddPlaylistLastClick = 0;
                $(ele).hide();
            }
        })
    }
    // reset add to playlist check song id
    $Core.music.iAddPlaylistLastClick = 0;
});
document.addEventListener('play', function (e) {
    var audios = $('audio');
    for (var i = 0, len = audios.length; i < len; i++) {
        if (audios[i] != e.target) {
            audios[i].pause();
        }
    }
}, true);
var core_music_onchangeDeleteGenreType = function (type) {
    if (type == 3)
        $('#genre_select').show();
    else
        $('#genre_select').hide();
};
//Upload js
$Core.music =
    {
        canCheckValidate: ($Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined"),
        isMobile: false,
        iValidFile: 0,
        oFeedIds: {},
        oAddFiles: [],
        iAlbumId: 0,
        bRepeatInFeed: false,
        iCurrentPlaying: 0,
        sModule: '',
        iItemId: 0,
        iTotalTimePer: 0,
        iAddPlaylistLastClick: 0,
        aUploadedSong: {},
        dropzoneOnSending: function (data, xhr, formData) {
            $('#js_actual_upload_form').find('input, select').each(function () {
                formData.append($(this).prop('name'), $(this).val());
            });
        },
        dropzoneQueueComplete: function () {
            $Core.music.iTotalTimePer = 0;
        },
        dropzoneAddedFile: function () {
            $Core.music.iTotalTimePer++;
            $('#js_error_message').hide();
            $('#js_total_success_holder').show();
            if (!$('#js_music_uploading').length) {
                var sNoticeHtml = '<div id="js_music_uploading" class="mb-2"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i>&nbsp;' + oTranslations['uploading_three_dot'] + '<span id="dropzone-total-uploading"></span></div><div id="js_uploading_notice" class="mb-2"><b>' + oTranslations['please_do_not_refresh_the_current_page_or_close_the_browser_window'] + '</b></div>';
                $('#music_song-dropzone').find('.dz-default').prepend(sNoticeHtml);
                $('.dropzone-button-music_song').append(' ' + oTranslations['add_more_files']);
            }
            else {
                $('#js_music_uploading').show();
                $('#js_uploading_notice').show();
            }
            $('.dropzone-button-music_song').addClass('uploaded');
        },
        dropzoneOnError: function (ele, file, message) {
            var sErrorHtml = '<li class="js_uploaded_file_holder hide music-item item-outer"><div class="item-inner"><p class="text-danger">' + file.name + '&nbsp;-&nbsp;' + message + '</p><div class="item-actions"><a href="javascript:void(0)" onclick="$(this).parents(\'.js_uploaded_file_holder\').remove();"><i class="ico ico-close"></i></a></div></div>';
            $('#js_music_uploaded_section').prepend(sErrorHtml);
            $Core.music.iTotalTimePer--;
            if (!$Core.music.iTotalTimePer) {
                $Core.music.doneAllFile();
            }
            $Core.dropzone.instance['music_song'].removeFile(file);
        },
        dropzoneOnSuccess: function (ele, file, response) {
            response = JSON.parse(response);
            $Core.music.iAlbumId = 0;
            $Core.music.iTotalTimePer--;
            if ($('#js_album_id').length) {
                $Core.music.iAlbumId = $('#js_album_id').val();
            }
            if (!response.error && response.id) {
                //append edit form
                $.fn.ajaxCall('music.appendAddedSong', 'id='+response.id, true, 'POST', function () {
                    if ($Core.music.canCheckValidate) {
                        $Core.music.reloadMusicValidation.validate();
                    }
                });

            }
            else {
                var sErrorHtml = '<li class="js_uploaded_file_holder hide music-item item-outer"><div class="item-inner"><p class="text-danger">' + file.name + '&nbsp;-&nbsp;' + response.error + '</p><div class="item-actions"><a href="javascript:void(0)" onclick="$(this).parents(\'.js_uploaded_file_holder\').remove();"><i class="ico ico-close"></i></a></div></div>';
                $('#js_music_uploaded_section').prepend(sErrorHtml);
                if (!$Core.music.iTotalTimePer) {
                    $Core.music.doneAllFile();
                }
                $Core.dropzone.instance['music_song'].removeFile(file);
            }

            return true;
        },
        doneAllFile: function () {
            $('.js_uploaded_file_holder').removeClass('hide');
            $('#js_music_uploading').hide();
            $('#js_uploading_notice').hide();
        },
        setName: function (iSong) {
            if ($("#title").val() != '') {
                $.ajaxCall('music.setName', 'sTitle=' + $("#title").val() + '&iSong=' + iSong);
            }
        },

        showForm: function (ele) {
            $(ele).hide();
            $(ele).closest('.js_uploaded_file_holder').find('.js_music_form_holder').fadeIn();
            $(ele).closest('.js_uploaded_file_holder').find('.js_hide_form').show();
        },

        hideForm: function (ele) {
            $(ele).hide();
            $(ele).closest('.js_uploaded_file_holder').find('.js_music_form_holder').fadeOut();
            $(ele).closest('.js_uploaded_file_holder').find('.js_show_form').show();
        },

        editSong: function (ele, isAjax) {
            var song_id = $(ele).data('id'),
                description = $('#description_' + song_id).val();
            $(ele).addClass('disabled');

            if (isAjax) {
                if ($("[name='val[" + song_id + "][title]']").val() == '') {
                    tb_show(oTranslations['notice'], '', null, oTranslations['provide_a_name_for_this_song']);
                    $(ele).removeClass('disabled');
                    return false;
                }
                if (typeof(CKEDITOR) !== 'undefined') {
                    if (typeof(CKEDITOR.instances["description_" + song_id]) !== 'undefined') {
                        description = CKEDITOR.instances["description_" + song_id].getData();
                        $("textarea[name='val[description_" + song_id + "]']").val(description);
                    }
                }
                var temp_file = '',
                    remove_photo = '';
                if ($('#js_upload_form_file_music_song_'+ song_id).length) {
                    temp_file = $('#js_upload_form_file_music_song_'+ song_id).val();
                }
                if ($('#js_upload_remove_file_music_song_' + song_id).length) {
                    remove_photo = $('#js_upload_remove_file_music_song_' + song_id).val();
                }
                $.ajaxCall('music.updateSong', 'song_id=' + song_id + '&description=' + description + '&temp_file=' + temp_file + '&remove_photo=' + remove_photo + '&' + $('#js_music_upload_form').serialize());
                $('#js_song_title_' + song_id).html($("[name='val[" + song_id + "][title]']").val());
            }
            else {
                $('#js_file_holder_' + song_id).closest('form').submit();
            }

        },
        removeTempFile: function (ele) {
            var file_path = $(ele).data('path'),
                index = $(ele).data('index');
            $Core.music.iValidFile = $Core.music.iValidFile == 0 ? 0 : ($Core.music.iValidFile - 1);
            $.ajaxCall('music.removeTempFile', 'path=' + file_path + '&index=' + index);
            return true;
        },
        deleteSongInAddForm: function (ele) {
            var song_id = $(ele).data('id'),
                album_id = $(ele).data('album-id');
            if (song_id > 0) {
                $Core.dropzone.instance['music_song'].files.shift();
                $Core.jsConfirm({message: oTranslations['are_you_sure_you_want_to_delete_this_song']}, function () {
                    $.ajaxCall('music.deleteSong', 'id=' + song_id + '&inline=1&album_id=' + album_id + '&time_stamp=' + $('#js_upload_time_stamp').val());
                }, function () {
                });
            }
        },
        initPlayInFeed: function (divId, bAutoPlay) {
            if ($(divId).hasClass('built')) {
                return;
            }
            $(divId).addClass('built', true);
            var block_content = $('._block_content');
            block_content.find('.music_row.active').each(function () {
                var temp_audio = $(this).find('.music_player .js_player_holder audio');
                if (temp_audio.length > 0) {
                    (temp_audio[0]).pause();
                }
            });
            var css_href = oParams.sJsHome.replace('PF.Base', 'PF.Site') + 'Apps/core-music/assets/jscript/mediaelementplayer/mediaelementplayer.css';
            if (!$("link[href='" + css_href + "']").length) {
                var css = document.createElement('link');
                css.href = css_href;
                css.rel = 'stylesheet';
                css.type = 'text/css';
                document.getElementsByTagName("head")[0].appendChild(css);
            }
            $(divId).mediaelementplayer({
                alwaysShowControls: true,
                features: ['playpause', 'current', 'progress', 'duration', 'volume'],
                audioVolume: 'horizontal',
                startVolume: 0.5,
                setDimensions: false,
                success: function (mediaPlayer, domObject) {
                    if (bAutoPlay) {
                        mediaPlayer.play();
                    }
                    mediaPlayer.addEventListener('play', function () {
                        if ($('.music_row.active').data('songid') != $Core.music.iCurrentPlaying) {
                            $Core.music.iCurrentPlaying = $('.music_row.active').data('songid');
                            $('.js_music_repeat').removeClass('active');
                            $Core.music.bRepeatInFeed = false;
                        }
                    });
                    mediaPlayer.addEventListener('loadedmetadata', function () {
                        $('.js_music_repeat').off('click').on('click', function () {
                            $Core.music.bRepeatInFeed = !$Core.music.bRepeatInFeed;
                            if ($Core.music.bRepeatInFeed) {
                                $(this).addClass('active');
                            }
                            else {
                                $(this).removeClass('active');
                            }
                        });
                    });
                    mediaPlayer.addEventListener('ended', function () {
                        if ($Core.music.bRepeatInFeed) {
                            mediaPlayer.play();
                        }
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        },
        playSongRow: function (obj) {
            var parent = $(obj).closest('.music_row');
            var audio = parent.find('.music_player .js_player_holder audio');
            var song_id = parent.data('songid');
            if (audio.length > 0) {
                audio = audio[0];
            }
            else {
                return false;
            }

            if (parent.hasClass('active')) {
                audio.pause();
                audio.currentTime = 0;
                parent.removeClass('active');
                parent.find('.music_player').slideToggle();
                return true;
            }
            var block_content = $('body').find('._block_content, ._block');
            block_content.find('.music_row.active').each(function () {
                $(this).find('.music_player').slideToggle();
                var temp_audio = $(this).find('.music_player .js_player_holder audio');
                if (temp_audio.length > 0) {
                    (temp_audio[0]).pause();
                    (temp_audio[0]).currentTime = 0;
                }
                $(this).removeClass('active');
            });
            parent.find('.music_player').slideToggle();
            audio.play();
            $.ajaxCall('music.play', 'id=' + song_id, 'POST');
            parent.addClass('active');
        },
        //Submit add/edit album with dropzone
        submitAlbumForm: function(ele) {
            $(ele).attr('disabled', 'disabled');
            if (typeof $Core.dropzone.instance['music-album'] != 'undefined') {
                if ($Core.dropzone.instance['music-album'].getQueuedFiles().length && $('#js_music_add_album_form').find('input[name="val[name]"]').val() != '' && parseInt($('#js_music_add_album_form').find('input[name="val[year]"]').val()) > 0) {
                    $Core.dropzone.instance['music-album'].processQueue();
                }
                else {
                    if (!$('#music-album-dropzone').find('.dz-preview.dz-error').length) {
                        $(ele).closest('form').submit();
                    }
                    else {
                        $(ele).removeAttr('disabled');
                    }
                }
            }
            else {
                $(ele).closest('form').submit();
            }
        },
        deleteSongImage: function (iSongId) {
            $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
                $.ajaxCall('music.deleteSongImage', 'id=' + iSongId);
            }, function () {
            });
            return false;
        },
        deleteAlbumImage: function (iAlbumId) {
            $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
                $.ajaxCall('music.deleteImage', 'id=' + iAlbumId);
            }, function () {
            });
            return false;
        },
        loadUserPlaylist: function (ele, bFixedPosition, bScroll) {
            var oEle = $(ele),
                oParent = oEle.closest('.js_music_dropdown_add_to_playlist'),
                iSongId = oEle.data('song-id'),
                oDropdown = $('#js_music_add_to_playlist_dropdown'),
                oOffset = oEle.offset(),
                itemHeight = $('li.item-music').height(),
                width = oDropdown.width() == 0 ? 216 : oDropdown.width(),
                top = oOffset.top + itemHeight,
                left = oOffset.left - width + 30;
            if ($Core.music.iAddPlaylistLastClick == iSongId) {
                $Core.music.iAddPlaylistLastClick = 0;
                oDropdown.hide();
                return false;
            }
            $Core.music.iAddPlaylistLastClick = iSongId;
            if (bScroll) {
                oDropdown.css('top', top);
                oDropdown.css('left', left);
                return true;
            }
            if (!oParent.hasClass('opened')) {
                if (bFixedPosition) {
                    $('#js_music_playlist_dropdown_menu').html('<div class="form-spin-it p-1 t_center"><i class="fa fa-spin fa-circle-o-notch"></i></div>');
                    //Fix position
                    oDropdown.addClass('opened');
                    oDropdown.css('position', 'absolute');
                    oDropdown.css('top', top);
                    oDropdown.css('right', 'auto');
                    oDropdown.css('left', left);
                    oDropdown.css('z-index', 6);
                    oDropdown.show();
                    $('.layout-middle').css('z-index', '6');
                    $.ajaxCall('music.loadUserPlaylistInDetail','song_id='+ iSongId);
                } else {
                    //Load playlist
                    $.ajaxCall('music.getPlaylistByUser', 'song_id=' + iSongId, 'GET');
                }
            }
            return true;
        },
        toggleAddSongToPlaylist: function(ele) {
            var oEle = $(ele),
                iSongId = oEle.data('song-id'),
                iPlaylistId = oEle.data('playlist-id'),
                isAdded = oEle.prop('checked');
            $.ajaxCall('music.updateSongInPlaylist', $.param({
                song_id: iSongId,
                playlist_id: iPlaylistId,
                is_checked: isAdded ? 1 : 0
            }));
            return true;
        },
        toggleQuickAddPlaylistForm: function(ele) {
            var oEle = $(ele),
                iSongId = oEle.data('song-id'),
                oParent = $('.js_music_add_to_playlist_' + iSongId);

            if (!oParent.prop('opened-form')) {
                oParent.prop('opened-form', true);
                oParent.find('.js_down').hide();
                oParent.find('.js_up').show();
                oParent.find('.js_music_quick_add_playlist_form').slideDown(200);
            } else {
                oParent.prop('opened-form', false);
                oParent.find('.js_down').show();
                oParent.find('.js_up').hide();
                oParent.find('.js_music_quick_add_playlist_form').slideUp(200);
            }
            return false;
        },
        addPlaylist: function(ele) {
            var oEle = $(ele),
                iSongId = $(ele).data('song-id'),
                oForm = oEle.closest('.js_music_quick_add_playlist_form'),
                oInput = oForm.find('.js_music_quick_add_playlist_name');
            if (trim(oInput.val()) === '') {
                oForm.find('.music-error').css('display', 'block');
                oInput.val('');
                oForm.find('.music-error').fadeOut(4000);
                return false
            }
            if (oInput.val() != "") {
                oEle.addClass('disabled');
                $.ajaxCall('music.addPlaylistOnEntry',$.param({
                    song_id: iSongId,
                    name: oInput.val()
                }),'POST');
                oForm.find('.music-error').css('display', 'none')
                oInput.val('');
            }
            return false;
        },
        touchHandler: function(event){
             var touch = event.changedTouches[0];

            var simulatedEvent = document.createEvent("MouseEvent");
                simulatedEvent.initMouseEvent({
                touchstart: "mousedown",
                touchmove: "mousemove",
                touchend: "mouseup"
            }[event.type], true, true, window, 1,
                touch.screenX, touch.screenY,
                touch.clientX, touch.clientY, false,
                false, false, false, 0, null);

            touch.target.dispatchEvent(simulatedEvent);
            event.preventDefault();
        },
        initTouchHandler: function(){
            var arraydrag = $('#music-sortable .js-drag-sort');
            for(var i = 0; i < arraydrag.length; i++) {
                arraydrag[i].addEventListener("touchstart", $Core.music.touchHandler, true);
                arraydrag[i].addEventListener("touchmove", $Core.music.touchHandler, true);
                arraydrag[i].addEventListener("touchend", $Core.music.touchHandler, true);
                arraydrag[i].addEventListener("touchcancel", $Core.music.touchHandler, true);
            }  
        },
        reloadMusicValidation: {
            initEleData: 0,

            init: function () {
                this.reset(true);
                this.store();
            },

            validate: function () {
                let parentObject = this;
                let music_items = $('#js_music_uploaded_section .js_uploaded_file_holder');
                if (music_items.length !== parentObject.initEleData) {
                    parentObject.preventReload(true);
                } else {
                    parentObject.preventReload(false);
                }
            },

            preventReload: function (isPrevent) {
                close_warning_enabled = !!isPrevent;
                close_warning_checked = !!isPrevent;
                window.onbeforeunload = isPrevent ? function () {
                    return false;
                } : null;
            },

            store: function () {
                let parentObject = this
                let music_items = $('#js_music_uploaded_section .js_uploaded_file_holder');
                if (music_items.length) {
                    parentObject.initEleData = music_items.length;
                }
            },
            reset: function (resetVariable) {
                if (resetVariable && typeof close_warning_checked !== "undefined" && typeof close_warning_enabled !== "undefined") {
                    close_warning_enabled = false;
                    close_warning_checked = false;
                }
                this.initEleData = 0;
                window.onbeforeunload = null;
            }
        },
        onSubmitAddAlbum: function (form) {
            var description = $('#text').val();
            if (typeof(CKEDITOR) !== 'undefined') {
                if (typeof(CKEDITOR.instances["text"]) !== 'undefined') {
                    description = CKEDITOR.instances["text"].getData();
                    $(form).find('textarea[name="val[text]"]').val(description);
                }
            }
            $(form).ajaxCall('music.addAlbumInline');
            return false;
        },
        onAddAlbumSuccess: function (id, name) {
            var selectEle = $('#js_music_album_select');
            if (!selectEle.length || !id || !name || selectEle.find('option[value="' + id + '"]').length) {
                return false;
            }
            selectEle.append('<option value="' + id + '" selected>' + name + '</option>');
            selectEle.closest('#js_music_albums').show();
            selectEle.trigger('change');
            js_box_remove("#js_music_add_album_form", true);
            return true;
        }
    };

$Behavior.onCloseEditAtManageSongPage = function () {
    if ($('div.js_manage_song.album-manage-song').length) {
        $('div.js_box_close').find('a').click(function(){
            var oManageSongTab = $('.page_section_menu ul li').find('a[rel=js_upload_music_manage]');
            if (oManageSongTab.length) {
                oManageSongTab.trigger('click');
            }
        });
    }
}

PF.event.on('on_document_ready_end', function () {
    if ($Core.music.canCheckValidate && $('#js_music_upload_song').length) {
        $Core.music.reloadMusicValidation.init();
    }
});

PF.event.on('on_page_change_end', function () {
    if ($Core.music.canCheckValidate && $('#js_music_upload_song').length) {
        $Core.music.reloadMusicValidation.init();
    }
});
