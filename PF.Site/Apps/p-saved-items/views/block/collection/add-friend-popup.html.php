<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="form-group">
<div class="js_error_add_friend"></div>
<form class="form" method="post" action="#" onsubmit="return appSavedItem.addFriendToCollection(this);">
    <input type="hidden" name="val[collection_id]" value="{$iId}">
    {module name='friend.search-small' input_name=$sInputName input_type=$sInputType current_values=$aCurrentValues include_current_user=$bIncludeCurrentUser}
    <button type="submit" class="btn btn-primary my-2">{_p var='update'}</button>
</form>
</div>
