<?php
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<select class="form-control close_warning" placeholder="{_p var='select_a_category_dot'}" {if $bMultiple}name="val{if isset($aForms.photo_id)}[{$aForms.photo_id}]{/if}[category_id][]" multiple="multiple" size="8"{else}name="val[parent_id]"{/if} onchange="$Core.Photo.toggleEditAction(this,'category');">
    {if !$bMultiple}
        <option value="">{_p var='select'}:</option>
    {/if}
    {$sCategories}
</select>