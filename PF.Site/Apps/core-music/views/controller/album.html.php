<?php 

 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<div id="js_actual_upload_form">
	{$sCreateJs}
	<form method="post" action="{url link='current'}" enctype="multipart/form-data" id="js_music_add_album_form" onsubmit="{$sGetJsForm}{if $bIsPopup};return $Core.music.onSubmitAddAlbum(this);{/if}">
		{if $bIsEdit}
		    <div><input type="hidden" name="album_edit_id" value="{$aForms.album_id}" /></div>
		{/if}
        {if isset($sModule) && $sModule}
            <div><input type="hidden" name="val[callback_module]" value="{$sModule}" /></div>
        {/if}
        {if isset($iItem) && $iItem}
            <div><input type="hidden" name="val[callback_item_id]" value="{$iItem}" /></div>
        {/if}
        <div><input type="hidden" name="val[attachment]" class="js_attachment" value="{value type='input' id='attachment'}" /></div>
        <div id="js_custom_privacy_input_holder">
            {if $bIsEdit && empty($sModule) && Phpfox::isModule('privacy')}
                {module name='privacy.build' privacy_item_id=$aForms.album_id privacy_module_id='music_album'}
            {/if}
        </div>

		<div id="js_upload_music_detail" class="js_upload_music page_section_menu_holder">
			<div class="form-group">
				<label>{required}{_p var='name'}:</label>
                <input class="form-control close_warning" type="text" name="val[name]" size="30" value="{value type='input' id='name'}" id="name" />
            </div>
            
			<div class="form-group">
                <label>{required}{_p var='year'}:</label>
                <input class="form-control close_warning" type="text" name="val[year]" size="4" value="{value type='input' id='year'}" id="year" maxlength="4" />
                <p class="help-block">{_p var='eg_1982'}</p>
            </div>
            
			<div class="form-group close_warning">
                <label>{_p var='description'}:</label>
                {editor id='text'}
			</div>

            <div class="special_close_warning">
                {if !empty($aForms.current_image) && !empty($aForms.album_id)}
                    {module name='core.upload-form' type='music_album_image' current_photo=$aForms.current_image id=$aForms.album_id}
                {else}
                    {module name='core.upload-form' type='music_album_image'}
                {/if}
            </div>

			{if empty($sModule) && Phpfox::isModule('privacy')}
                <div id="js_album_privacy_holder">
                    <div class="form-group close_warning">
                         <label>{_p var='privacy'}:</label>
                        {module name='privacy.form' privacy_name='privacy' privacy_info='music.control_who_can_see_this_album_and_any_songs_connected_to_it' default_privacy='music.default_privacy_setting_album'}
                    </div>
                </div>
			{/if}
			
			<div class="form-group">
				<button type="submit" id="js_music_album_submit" class="button btn-primary">{if $bIsEdit}{_p var='update'}{else}{_p var='submit'}{/if}</button>
			</div>
		</div>		
    </form>
    
    <div id="js_upload_music_manage" class="js_upload_music page_section_menu_holder" style="display:none;">
        <div class="js_manage_song album-manage-song">
            {if (($bIsEdit && ($aForms.user_id == Phpfox::getUserId() && Phpfox::getUserParam('music.can_edit_own_albums')) || Phpfox::getUserParam('music.can_edit_other_music_albums'))  || !$bIsEdit)}
                {if isset($aSongs) && count($aSongs)}
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thread>
                                <th>{_p('title')}</th>
                                <th>{_p('genres')}</th>
                                <th></th>
                            </thread>
                            <tbody>
                            {foreach from=$aSongs key=iKey item=aSong}
                                <tr id="js_row{$aSong.song_id}">
                                    <td><a href="{permalink module='music' id=$aSong.song_id title=$aSong.title}">{$aSong.title|clean}</a></td>
                                    <td class="item-generes">
                                        {if isset($aSong.genres) && $iTotal = count($aSong.genres)}
                                            {template file='music.block.song-genres'}
                                        {/if}
                                    </td>
                                    <td class="item-actions">
                                        {if $aSong.canDownload}
                                        <a href="{url link='music.download' id=$aSong.song_id}" class="no_ajax_link"><i class="ico ico-download-alt" aria-hidden="true"></i></a>
                                        {/if}
                                        {if $aSong.canEdit}
                                        <a href="{url link='music.upload' id=$aSong.song_id album=$aForms.album_id}" class="popup" onclick="if(typeof CKEDITOR.instances != 'undefined') delete CKEDITOR.instances.description;"><i class="ico ico-pencilline-o" aria-hidden="true"></i></a>
                                        {/if}
                                        {if $aSong.canDelete}
                                        <a onclick="$Core.jsConfirm({l}message:'{_p('are_you_sure_you_want_to_delete_this_song')}'{r},function(){l}window.location.href='{url link='music.delete' id=$aSong.song_id album=$aForms.album_id}'{r},function(){l}{r}); return false;" href="javascript:void(0)"><i class="ico ico-trash-alt-o" aria-hidden="true"></i></a>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                    {pager}
                {else}
                    {_p var='no_songs_have_been_added'}
                {/if}
            {/if}
        </div>
    </div>
    {if $bIsEdit && Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false) && $aForms.user_id == Phpfox::getUserId()}
        <div id="js_upload_music_track" class="js_upload_music page_section_menu_holder" style="display:none;">
            <form method="post" action="{url link='music.upload'}" enctype="multipart/form-data" onsubmit="return $Core.music.validateUploadForm(this);" id="js_music_upload_form">
                <div><input type="hidden" name="val[iframe]" value="1" /></div>
                    <div class="error_message" id="js_error_message" style="display: none">
                    </div>
                    <div>
                        <div id="js_music_upload_song">
                            {template file='music.block.upload'}
                        </div>
                    </div>
            </form>
        </div>
    {/if}
</div>

{if isset($iPage) && $iPage >= 1}
    {literal}
    <script type="text/javascript">
        var first = true;
        $Behavior.onChangeManageSongPage = function(){
            if(first)
            {
                first = false;
                $('.page_section_menu ul li').find('a[rel=js_upload_music_manage]').trigger('click');
            }
        }
    </script>
    {/literal}
{/if}