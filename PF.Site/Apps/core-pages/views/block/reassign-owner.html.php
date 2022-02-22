<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="group-reassign-owner" id="js_reassign_owner_page">
    <div class="item-info mt-1 mb-1">
        {_p var='reassign_owner_page_notice'}
        <hr>
        <div class="mt-1 mb-1">
            {_p var='current_owner'}: {$aOwner|user}
        </div>
    </div>
    {module name='friend.search-small' input_name='owner' input_type='single' include_current_user=$bIncludeCurrentUser}
    <div class="mt-2">
        <button class="btn btn-primary" id="js_page_reassign_submit" onclick="return Core_Pages.reassignOwner(this);" data-id="{$iPageId}" data-message="{_p var='are_you_sure_you_want_to_choose_this_friend_and_transfer_ownership_to_them'}">{_p var='submit'}</button><i id="js_page_reassign_loading" style="display: none" class="ml-2 fa fa-spin fa-circle-o-notch"></i>
    </div>
</div>

{literal}
<script type="text/javascript">
    $(document).on('DOMSubtreeModified','#js_reassign_owner_page', function() {
        setTimeout(function(){
            var value = $('#js_reassign_owner_page #search_friend_single_input').val();
            if (value != "") {
                $('#js_reassign_owner_page #js_custom_search_friend').hide();
            } else {
                $('#js_reassign_owner_page #js_custom_search_friend').show();
            }
        }, 0);
    });
</script>
{/literal}