<div class="js_group_members_block">
    {foreach from=$aUsers item=aUser}
    <div class="group-member">
        <div class="group-member-avatar">
            {img user=$aUser suffix='_120_square' max_width=50 max_height=50}
        </div>
        <div class="group-member-name" title="{$aUser.full_name}">
            {$aUser.full_name}
        </div>
    </div>
    {/foreach}
</div>

{literal}
<script type="text/javascript">
    $Behavior.mail_group_member = function () {
        $('.js_group_members_block .group-member-avatar a').each(function(){
            $(this).off('click').attr('target', '_blank');
        });
    }
</script>
{/literal}