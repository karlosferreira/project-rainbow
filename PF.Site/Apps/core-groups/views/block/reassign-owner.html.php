<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="group-reassign-owner" id="js_reassign_owner_group">
    <div class="item-info mt-1 mb-1">
        {_p var='reassign_owner_group_notice'}
        <hr>
        <div class="mt-1 mb-1">
            {_p var='current_owner'}: {$aOwner|user}
        </div>
    </div>
    {module name='friend.search-small' input_name='owner' input_type='single' include_current_user=$bIncludeCurrentUser}
    <div class="mt-2">
        <button class="btn btn-primary" id="js_group_reassign_submit" onclick="return $Core.Groups.reassignOwner(this);" data-id="{$iPageId}" data-message="{_p var='groups_are_you_sure_you_want_to_choose_this_friend_and_transfer_ownership_of_the_group_to_them'}">{_p var='submit'}</button>
        <i id="js_group_reassign_loading" style="display: none" class="ml-2 fa fa-spin fa-circle-o-notch"></i>
    </div>
</div>

{literal}
<script type="text/javascript">
    $(document).on('DOMSubtreeModified','#js_reassign_owner_group', function() {
        setTimeout(function(){
            var value = $('#js_reassign_owner_group #search_friend_single_input').val();
            if (value != "") {
                $('#js_reassign_owner_group #js_custom_search_friend').hide();
            } else {
                $('#js_reassign_owner_group #js_custom_search_friend').show();
            }
        }, 0);
    });
</script>
{/literal}