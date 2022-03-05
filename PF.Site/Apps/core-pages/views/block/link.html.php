<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{plugin call='pages.block_link_actions_1'}
{if $aPage.canApprove}
    <li><a href="#" onclick="$.ajaxCall('pages.approve', 'page_id={$aPage.page_id}');"><span class="ico ico-check-square-alt mr-1"></span>{_p var='approve'}</a></li>
{/if}
{if $aPage.canEdit}
    <li>
        <a href="{url link='pages.add' id=$aPage.page_id}">
            <span class="ico ico-gear-form-o mr-1"></span>
            {_p var='manage'}
        </a>
    </li>
{/if}
{if $aPage.canFeature}
    <li><a id="js_feature_{$aPage.page_id}" {if $aPage.is_featured} style="display:none;"{/if} href="#" title="{_p var='feature'}" onclick="$(this).hide(); $('#js_unfeature_{$aPage.page_id}').show(); $.ajaxCall('pages.feature', 'page_id={$aPage.page_id}&amp;type=1'); return false;"><span class="ico ico-diamond mr-1"></span>{_p var='feature'}</a></li>
    <li><a id="js_unfeature_{$aPage.page_id}" {if !$aPage.is_featured} style="display:none;"{/if} href="#" title="{_p var='un_feature'}" onclick="$(this).hide(); $('#js_feature_{$aPage.page_id}').show(); $.ajaxCall('pages.feature', 'page_id={$aPage.page_id}&amp;type=0'); return false;"><span class="ico ico-diamond-o mr-1"></span>{_p var='unfeature'}</a></li>
{/if}
{if $aPage.canSponsor}
    <li>
        <a href="#" id="js_sponsor_{$aPage.page_id}" onclick="$('#js_sponsor_{$aPage.page_id}').hide();$('#js_unsponsor_{$aPage.page_id}').show();$.ajaxCall('pages.sponsor','page_id={$aPage.page_id}&type=0', 'GET'); return false;" style="{if $aPage.is_sponsor != 1}display:none;{/if}">
            <span class="ico ico-sponsor mr-1"></span>{_p var='unsponsor'}
        </a>
        <a href="#" id="js_unsponsor_{$aPage.page_id}" onclick="$('#js_sponsor_{$aPage.page_id}').show();$('#js_unsponsor_{$aPage.page_id}').hide();$.ajaxCall('pages.sponsor','page_id={$aPage.page_id}&type=1', 'GET'); return false;" style="{if $aPage.is_sponsor == 1}display:none;{/if}">
            <span class="ico ico-sponsor mr-1"></span>{_p var='sponsor'}
        </a>
    </li>
{elseif $aPage.canPurchaseSponsor}
    <li>
        <a id="js_unsponsor_{$aPage.page_id}" href="{permalink module='ad.sponsor' id=$aPage.page_id}section_pages/" style="{if $aPage.is_sponsor == 1}display:none;{/if}">
            <span class="ico ico-sponsor mr-1"></span>{_p var='sponsor_this_page'}
        </a>
        <a href="#" id="js_sponsor_{$aPage.page_id}" onclick="$('#js_sponsor_{$aPage.page_id}').hide();$('#js_unsponsor_{$aPage.page_id}').show();$.ajaxCall('pages.sponsor','page_id={$aPage.page_id}&type=0', 'GET'); return false;" style="{if $aPage.is_sponsor != 1}display:none;{/if}">
            <span class="ico ico-sponsor mr-1"></span>{_p var='unsponsor'}
        </a>
    </li>
{/if}
{if Phpfox::isAdmin() || Phpfox::getUserId() == $aPage.user_id}
    <li>
        <a href="#" onclick="tb_show('', $.ajaxBox('pages.showReassignOwner', 'height=400&amp;width=600&amp;page_id={$aPage.page_id}')); return false;">
            <span class="ico ico-user2-next-o mr-1"></span>{_p var='reassign_owner'}
        </a>
    </li>
{/if}
{plugin call='pages.block_link_actions_2'}
{if $aPage.canDelete}
    <li class="item_delete">
        <a href="{url link='pages' delete=$aPage.page_id}" data-message="{_p var='are_you_sure_you_want_to_delete_this_page_permanently'}" class="no_ajax_link sJsConfirm">
            <span class="ico ico-trash-alt-o mr-1"></span>
            {_p var='delete'}
        </a>
    </li>
{/if}
{plugin call='pages.block_link_actions_3'}