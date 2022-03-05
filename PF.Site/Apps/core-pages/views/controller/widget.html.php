<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
<iframe src="#" id="js_pages_frame" name="js_pages_frame" style="display:none;"></iframe>
<div id="js_pages_widget_error"></div>
<form class="form" method="post" action="{url link='pages.frame'}" target="js_pages_frame" enctype="multipart/form-data" data-app="core_pages" data-action-type="submit" data-action="disable_submit">
    <div><input type="hidden" name="val[page_id]" value="{$iPageId}" /></div>
    {if $bIsEdit}
    <div><input type="hidden" name="widget_id" value="{$aForms.widget_id}" /></div>
    {/if}
    <div><input type="hidden" name="val[is_block]" value="{if $bIsMenu}0{else}1{/if}"/></div>

    <div class="form-group">
        <label for="title">{_p var='title'}</label>
        <input name="val[title]" value="{value type='input' id='title'}" id="title" size="30" class="form-control close_warning" maxlength="64" required/>
    </div>

    <div id="js_pages_widget_block"{if $bIsEdit && $aForms.is_block == '1' || !$bIsMenu} style="display:none;"{/if}>
        <div class="form-group">
            <label for="menu_title">{_p var='menu_title'}</label>
            <input class="form-control close_warning" name="val[menu_title]" id="menu_title" value="{value type='input' id='menu_title'}" size="30" maxlength="64" />
        </div>

        <div class="table form-group">
            <label for="url_title">{_p var='url_title'}</label>
            <input onclick="this.select();" name="val[url_title]" id="url_title" value="{value type='input' id='url_title'}" size="15" class="form-control close_warning" />
            <p class="help-block">{$sPageUrl}</p>
        </div>
    </div>

    <div class="form-group">
        <label for="widget_text">{_p var='content'}</label>
        <p class="help-block">{_p var='we_do_not_support_javascript_in_widgets_content_it_will_be_automatically_removed'}</p>
        {editor id='widget_text' name='text'}
    </div>

    <div class="form-group" id="js_pages_widget_submit_button">
        <input type="submit" value="{_p var='submit'}" class="btn btn-primary" />
    </div>
</form>