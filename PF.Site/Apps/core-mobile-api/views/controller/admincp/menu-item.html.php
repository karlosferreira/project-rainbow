<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<link href="{param var='core.path_file'}static/jscript/colorpicker/css/colpick.css" rel="stylesheet">
{if !$iTotalMenu}
<div class="alert alert-danger">
    {_p var='no_menu_found'}
</div>
{else}
    {module name='mobile.admincp.menu-by-type' type='header'}
    {module name='mobile.admincp.menu-by-type' type='item'}
    {module name='mobile.admincp.menu-by-type' type='helper'}
    {module name='mobile.admincp.menu-by-type' type='footer'}
{/if}

{literal}
<script type="application/javascript">
    $(document).on('change', '.mobile_api_menu_form', function(e) {
        if (e.target.tagName.toUpperCase() == 'INPUT') {
            $(this).find('.btn_submit').show();
        }
    })
</script>
{/literal}