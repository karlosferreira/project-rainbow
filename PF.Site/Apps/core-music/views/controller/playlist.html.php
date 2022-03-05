<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{$sCreateJs}
<form method="post" action="{url link='current'}" enctype="multipart/form-data" id="js_music_add_playlist_form" onsubmit="{$sGetJsForm}">
    {if $bIsEdit}
        <input type="hidden" name="edit_id" value="{$aForms.playlist_id}" />
    {/if}
    <input type="hidden" name="val[attachment]" class="js_attachment" value="{value type='input' id='attachment'}" />
    <input type="hidden" name="val[current_tab]" value="" id="current_tab">

    <div id="js_music_playlist_detail" class="js_music_playlist page_section_menu_holder" {if !empty($sActiveTab) && $sActiveTab != 'detail'}style="display:none;"{/if}>
        <div class="form-group">
            <label>{required}{_p var='name'}:</label>
            <input class="form-control close_warning" type="text" name="val[name]" size="30" value="{value type='input' id='name'}" id="name" />
        </div>

        <div class="form-group">
            <label>{_p var='description'}:</label>
            {editor id='description'}
        </div>

        <div class="special_close_warning">
            {if !empty($aForms.current_image) && !empty($aForms.playlist_id)}
                {module name='core.upload-form' type='music_playlist_image' current_photo=$aForms.current_image id=$aForms.playlist_id}
            {else}
                {module name='core.upload-form' type='music_playlist_image'}
            {/if}
        </div>
        <div class="special_close_warning">
            {if Phpfox::isModule('privacy')}
                <div class="form-group">
                    <label>{_p var='privacy'}:</label>
                    {module name='privacy.form' privacy_name='privacy' privacy_info='music.music_control_who_can_see_this_playlist_and_any_songs_connected_to_it' default_privacy='music.default_privacy_setting_playlist' privacy_no_custom=true}
                </div>
            {/if}
        </div>
        <div class="form-group">
            <button type="submit" name="val[save_info]" value="1" id="js_music_album_submit" class="button btn-primary">{if $bIsEdit}{_p var='update'}{else}{_p var='submit'}{/if}</button>
        </div>
    </div>

    <div id="js_music_playlist_manage" class="js_music_playlist page_section_menu_holder music-manage-song-container" {if empty($sActiveTab) || $sActiveTab != 'manage'}style="display:none;"{/if}>
        {if $bIsEdit}
            {if !count($aSongs)}
                <div class="music-playlist-empty">
                    <span class="ico ico-music-list"></span>
                    <div class="extra_info">{_p var='you_not_yet_added_any_song_to_this_playlist'}</div>
                    <a href="{url link='music'}" class="btn btn-default">{_p var='find_your_favorite_songs'}</a>
                </div>
            {else}
                <div class="music-manage-song-total">{if $aForm.total_track == 1}{_p var='total_one_song'}{else}{_p var='total_number_songs' number=$aForms.total_track}{/if}</div>
                <input type="hidden" class="close_warning" name="val[remove_song]" id="js_music_playlist_song_remove" />
                <input type="hidden" class="close_warning" name="val[current_sort]" id="js_music_playlist_song_sorting" />
                <div class="music-manage-table-container">
                    <div class="table-responsive flex-sortable">
                        <table class="table" id="music-sortable">
                            <tbody>
                            {foreach from=$aSongs key=iKey item=aSong}
                                <tr id="js_music_song_row_{$aSong.song_id}" class="js_music_song_row {if is_int($iKey/2)} tr{else}{/if}" data-song-id="{$aSong.song_id}">
                                    <td class="item-sort-icon">
                                        <i class="ico ico-arrows-move js-drag-sort"></i>
                                        <input type="hidden" name="val[ordering][{$aSong.song_id}]" class="js_mp_order" value="{$aSong.ordering}">
                                    </td>
                                    <td class="td-flex item-title">
                                        <a href="{permalink module='music' id=$aSong.song_id title=$aSong.title}">{$aSong.title|clean}</a>
                                    </td>
                                    <td class="item-generes">
                                        <div class="generes-wrapper">
                                            {if isset($aSong.genres) && $iTotal = count($aSong.genres)}
                                                {template file='music.block.song-genres'}
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="item-actions">
                                        <div class="button-action-wrapper">
                                            <a href="{permalink module='music' id=$aSong.song_id title=$aSong.title}"><i class="ico ico-external-link"></i></a>
                                            {if $aSong.canDownload}
                                            <a href="{url link='music.download' id=$aSong.song_id}" class="no_ajax_link" title="{_p('download')}">
                                                <i class="ico ico-download-alt"></i>
                                            </a>
                                            {/if}
                                            <a onclick="musicAddToRemove($(this)); return false;" data-song-id="{$aSong.song_id}" href="javascript:void(0)"><i class="ico ico-trash-alt-o" aria-hidden="true"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group mange-button-submit-group">
                    <button type="submit" name="val[save_manage]" value="1" id="" class="button btn-primary">{_p var='save_change'}</button>
                    <a href="{permalink module='music.playlist' id=$aForms.playlist_id title=$aForms.name}" type="button" id="" class="button btn-default">{_p var='cancel'}</a>
                </div>
            {/if}
        {/if}
    </div>
</form>

{if !empty($aForms) && !empty($aSongs)}
{literal}
<script type="text/javascript">
    var musicFixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };
    var musicAddToRemove = function (ele) {
        var iSongId = ele.data('song-id'),
            removeInput = $('#js_music_playlist_song_remove'),
            currentRemove = $('#js_music_playlist_song_remove').val();
        removeInput.val(currentRemove + ',' + iSongId).trigger('change');
        $('#js_music_song_row_' + iSongId).remove();
    };
    var musicUpdateCurrentSongSort = function () {
        var songEle = $('.js_music_song_row');
        if (songEle.length) {
            var valueSort = '';
            songEle.each(function() {
                valueSort += $(this).data('song-id') + ',';
            });
            $('#js_music_playlist_song_sorting').val(valueSort).trigger('change');
        }
    }
    $Behavior.sortManagePlaylistSongs = function() {
        $('#music-sortable tbody').sortable({
            handle: '.js-drag-sort',
            helper: musicFixHelper,
            axis: 'y',
            stop: function (event, ui) {
                var ids = '';
                $('#music-sortable tr').removeClass('tr');
                $('#music-sortable tr').each(function (i, el) {
                    var t = $(this);
                    if (!t.data('song-id')) {
                        return;
                    }

                    if (i % 2 !== 0) {
                        t.addClass('tr');
                    }

                    ids += t.data('sort-id') + ',';
                });
                musicUpdateCurrentSongSort();
            }
        });
        musicUpdateCurrentSongSort();
    };
</script>
{/literal}
{/if}