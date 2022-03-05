<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		App_Core_Comments
 * @version 		$Id: entry.html.php 2525 2011-04-13 18:03:20Z phpFox LLC $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<tr id="js_comment_{$aComment.comment_id}">
    <td class="js_checkbox">
        <input type="checkbox" name="ids[]" class="checkbox" value="{$aComment.comment_id}" id="js_id_row{$aComment.comment_id}" />
    </td>
    <td class="t_center w40">
        <a href="#" class="js_drop_down_link" title="{_p var='Manage'}"></a>
        <div class="link_menu">
            <ul>
                <li><a href="#" onclick="$Core.jsConfirm({l}message:'{_p('are_you_sure_you_want_to_approve_this_comment')}'{r}, function(){l}$.ajaxCall('comment.moderateSpam', 'id={$aComment.comment_id}&amp;action=approve&amp;inacp={if isset($bIsCommentAdminPanel)}1{else}0{/if}'); return false;{r},function(){l}{r}, false);" >{_p var='approve'}</a></li>
                <li><a href="#" onclick="$Core.jsConfirm({l}message:'{_p('are_you_sure_you_want_to_deny_this_comment')}'{r}, function(){l}$.ajaxCall('comment.moderateSpam', 'id={$aComment.comment_id}&amp;action=deny&amp;inacp={if isset($bIsCommentAdminPanel)}1{else}0{/if}'); return false;{r},function(){l}{r}, false);">{_p var='deny'}</a></li>
            </ul>
        </div>
    </td>
    <td class="w180">
        {$aComment.time_stamp}
    </td>
    <td class="w180">
        {$aComment|user}
    </td>
    <td class="w180">
        {if $aComment.item_name}
            {$aComment.item_name}
        {/if}
    </td>
    <td>
        <div class="content-text {if $aComment.view_id == '1'}row_moderate{/if}" id="js_comment_text_{$aComment.comment_id}">{$aComment.text|comment_parse|shorten:'300':'comment.view_more':true}</div>
        {if !empty($aComment.extra_data)}
            {if $aComment.extra_data.extra_type == 'photo'}
                <div class="content-photo">
                    <span class="item-photo">
                        {img server_id=$aComment.extra_data.server_id path='core.url_pic' file="comment/".$aComment.extra_data.image_path suffix='_500'}
                    </span>
                </div>
            {elseif $aComment.extra_data.extra_type == 'sticker'}
                <div class="content-sticker">
                    <span class="item-sticker">
                        {$aComment.extra_data.full_path}
                    </span>
                </div>
            {/if}
        {/if}
    </td>
</tr>