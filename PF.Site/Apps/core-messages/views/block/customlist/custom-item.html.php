<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="custom-list-parent js_customlist_item_click {if (int)$key == 0}is_selected_thread{/if} d-flex align-items-center pl-1 pr-2 py-2" id="custom-list-{$aCustom.folder_id}" data-id="{$aCustom.folder_id}">
	<div class="core-messages__checkbox">
        <label class="item-checkbox pr-1 mb-0">
            <input type="checkbox" class="js_custom_item_check" value="{$aCustom.folder_id}"/>
            <i class="ico ico-square-o" aria-hidden="true"></i>
        </label>
    </div>
    <div class="item-outer edit-custom-list">
        <p class="mb-0 custom-list-name js_customlist_name_{$aCustom.folder_id} fw-bold">{$aCustom.name}</p>
        <p class="text-gray fz-12 mt-1 mb-0">{if (int)$aCustom.total_contacts == 1}{_p var='mail_contact' number=$aCustom.total_contacts}{else}{_p var='mail_contacts' number=$aCustom.total_contacts}{/if}</p>
    </div>
</div>