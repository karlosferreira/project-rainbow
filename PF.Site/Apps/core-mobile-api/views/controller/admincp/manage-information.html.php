<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div id="mobile-api-manage-information">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {_p var='Logo'}
            </div>
        </div>
        <form action="{url link='admincp.mobile.manage-information'}" method="post" enctype="multipart/form-data">
            <div class="panel-body">
                <div class="form-group">
                    <label>{_p var='site_logo_on_mobile_application'}:</label>
                    {if !empty($sLogo)}
                    <div class="{if !empty($sLogo)}mobile_current_logo{/if}">
                        <input name="val[current_logo]" value="{$sLogo}" type="hidden"/>
                        <img src="{$sLogo}" alt="" width="64px">
                    </div>
                        {if !$bIsDefault}
                            <div class="ml-1 mb-2">
                                <a href="{url link='admincp.mobile.manage-information' delete=true}" class="sJsConfirm">{_p var='remove_current_logo'}</a>
                            </div>
                        {/if}
                    {/if}
                    <input type="file" class="form-control" id="logo" name="logo"/>
                    <div class="help-block">
                        {_p var='choose_logo_for_your_application_it_should_be_in_w_h_for_the_best_layout_system_will_choose_a_default_logo_if_you_not_upload_yours' w='64' h='56'}
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button name="val[submit]" class="btn btn-primary">{_p var='update'}</button>
            </div>
        </form>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {_p var='mobile_app_banner'}
            </div>
        </div>
        <form action="{url link='admincp.mobile.manage-information'}" method="post" enctype="multipart/form-data">
            <div class="panel-body">
                <input type="hidden" name="val[app_banner]" value="1">
                <div class="help-block">{_p var='mobile_app_banner_description'}</div>
                <div class="form-group">
                    <label for="banner_logo">{required}{_p var='logo'}</label>
                    <div class="{if !empty($aForms.banner_logo)}mobile_current_logo{/if}">
                        <input name="val[current_logo]" value="{if !empty($aForms.banner_logo)}{$aForms.banner_log}{/if}" type="hidden"/>
                        <img src="{if !empty($aForms.banner_logo)}{$aForms.banner_logo}{/if}" alt="" width="64px">
                    </div>
                    <input type="file" class="form-control" id="banner_logo" name="banner_logo"/>
                    <div class="help-block">{_p var='banner_logo_description'}</div>
                </div>
                <div class="form-group">
                    <label for="banner_title">{required}{_p var='title'}</label>
                    <input class="form-control" required id="banner_title" name="val[banner_title]" maxlength="255" value="{value type='input' id='banner_title'}"/>
                    <div class="help-block">{_p var='banner_title_description'}</div>
                </div>
                <div class="form-group">
                    <label for="banner_author">{required}{_p var='author'}</label>
                    <input class="form-control" required id="banner_author" name="val[banner_author]" maxlength="255" value="{value type='input' id='banner_author'}"/>
                    <div class="help-block">{_p var='banner_author_description'}</div>
                </div>
                <div class="form-group">
                    <label for="banner_price">{_p var='price'}</label>
                    <input class="form-control" id="banner_price" name="val[banner_price]" maxlength="255" value="{value type='input' id='banner_price'}"/>
                    <div class="help-block">{_p var='banner_price_description'}</div>
                </div>
                <div class="form-group">
                    <label for="banner_apple_store_id">{required}{_p var='apple_app_store_id'}</label>
                    <input class="form-control" required id="banner_apple_store_id" name="val[banner_apple_store_id]" maxlength="255" value="{value type='input' id='banner_apple_store_id'}"/>
                    <div class="help-block">{_p var='apple_app_store_id_description'}</div>
                    <div class="alert alert-warning alert-labeled">
                        <div class="alert-labeled-row">
                            <p class="alert-body alert-body-right alert-labelled-cell">{_p var='mobile_app_banner_safari_note'}</p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="banner_google_store_id">{required}{_p var='google_app_store_id'}</label>
                    <input class="form-control" required id="banner_google_store_id" name="val[banner_google_store_id]" maxlength="255" value="{value type='input' id='banner_google_store_id'}"/>
                    <div class="help-block">{_p var='google_app_store_id_description'}</div>
                </div>
            </div>
            <div class="panel-footer">
                <button name="val[submit]" class="btn btn-primary">{_p var='update'}</button>
            </div>
        </form>
    </div>
</div>