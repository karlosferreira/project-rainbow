<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
<iframe src="#" id="js_groups_frame" name="js_groups_frame" style="display:none;"></iframe>
<div id="js_groups_widget_error"></div>
<form class="form" method="post" action="{url link='groups.frame'}" target="js_groups_frame" enctype="multipart/form-data" data-app="core_groups" data-action-type="submit" data-action="disable_submit">
    <div><input type="hidden" name="val[page_id]" value="{$iPageId}" /></div>
    {if $bIsEdit}
    <div><input type="hidden" name="widget_id" value="{$aForms.widget_id}" /></div>
    {/if}
    <div><input type="hidden" name="val[is_block]" value="{if $bIsMenu}0{else}1{/if}"/></div>

    <div class="table form-group">
        <label for="title">{_p var='Title'}</label>
        <input type="text" name="val[title]" value="{value type='input' id='title'}" size="30" class="form-control close_warning" maxlength="64" id="title"/>
    </div>

    <div id="js_groups_widget_block"{if $bIsEdit && $aForms.is_block == '1' || !$bIsMenu} style="display:none;"{/if}>
        <div class="table form-group">
            <label for="menu_title">{_p var='Menu Title'}</label>
            <input class="form-control close_warning" type="text" name="val[menu_title]" value="{value type='input' id='menu_title'}" size="30" maxlength="64" id="menu_title"/>
        </div>

        <div class="table form-group">
            <label for="url_title">{_p var='Url title'}</label>
            <input onclick="this.select();" type="text" name="val[url_title]" value="{value type='input' id='url_title'}" size="15" class="form-control close_warning" id="url_title"/>
            <p class="help-block">{$sPageUrl}</p>
        </div>
    </div>

    <div class="table form-group">
        <label>{_p var='Content'}</label>
        <p class="help-block">{_p var='we_do_not_support_javascript_in_widgets_content_it_will_be_automatically_removed'}</p>
        {editor id='text' name='text'}
    </div>

    <div class="table_clear" id="js_groups_widget_submit_button">
        <ul class="table_clear_button">
            <li><input type="submit" value="{_p var='Submit'}" class="btn btn-primary" /></li>
            <li class="table_clear_ajax"></li>
        </ul>
        <div class="clear"></div>
    </div>
</form>