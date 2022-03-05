<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		Module_Page
 * @version 		$Id: index.html.php 1194 2009-10-18 12:43:38Z phpFox LLC $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{if count($aPages)}
<form class="form" method="post" action="{url link='admincp.page'}">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead>
                <th class="w60">ID</th>
                <th>{_p var='title'}</th>
                <th class="w100">{_p var='options'}</th>
            </thead>
            <tbody>
                {foreach from=$aPages key=iKey item=aPage}
                <tr class="checkRow{if is_int($iKey/2)} tr{else}{/if}">
                    <td>
                        {$aPage.page_id}
                    </td>
                    <td><a href="{url link=$aPage.title_url}" class="targetBlank {if !$aPage.is_active}inactive_page{/if}" {if !$aPage.is_active} title="{_p var='inactive_page'}"{/if}>{if $aPage.is_phrase}{_p var=$aPage.title}{else}{$aPage.title}{/if}</a></td>
                    <td>
                        <a href="{url link='admincp.page.add.' id=$aPage.page_id}" class="is_edit">{_p var='edit'}</a>
                        {if !in_array($aPage.page_id, [1,2])}
                        &middot;
                        <a href="{url link='admincp.page.' delete=$aPage.page_id}" class="is_delete sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_page_permanently'}">{_p var='delete'}</a>
                        {/if}
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</form>
{else}
{_p var='no_pages_have_been_added'}
{/if}