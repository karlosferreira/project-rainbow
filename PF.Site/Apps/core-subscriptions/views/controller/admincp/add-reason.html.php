<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: add.html.php 4554 2012-07-23 08:44:50Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="panel panel-default">
    <div class="panel-body">
        <form id="admincp-reason-action" method="post" action="{url link='admincp.subscribe.add-reason'}">
            <div><input type="hidden" id="default-language-id" value="{$sDefaultLanguage}"></div>
            {if $bIsEdit}
            <div><input type="hidden" name="id" value="{$aForms.reason_id}" /></div>
            {/if}
            {if !empty($isAjaxPopup)}
            <div><input type="hidden" name="ajax_popup" value="1" /></div>
            {/if}
            <div class="form-group">
                {field_language phrase='sPhraseTitle' label='reason' field='title' format='val[title][' maxlength=100 required=true type='textarea'}
                <div class="max-character">
                    <span class="warning">{_p var='subscribe_max_numbers_character' number='100'}. </span>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success">{_p var='save'}</button>
                {if !empty($isAjaxPopup)}
                <button class="btn btn-default" onclick="js_box_remove(this); return false;">{_p var='cancel'}</button>
                {/if}
            </div>
        </form>
    </div>
</div>

