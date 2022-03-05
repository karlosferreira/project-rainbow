{if isset($bNoChatPlus)}
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="alert alert-danger">
                {_p var='chat_plus_is_not_enabled'}
            </div>
        </div>
    </div>
{elseif !empty($error)}
    <div class="alert alert-info">{$error}</div>
{else}
    <form method="post">
        <div class="panel panel-default">
            <div class="error_message" id="js_export_error" style="display: none"></div>
            <div class="panel-heading">
                <div class="panel-title">{_p var='export_data_to_chat_plus'}</div>
            </div>
            <div class="panel-body">
                <p class="help-block">{_p var='export_data_from_im_to_chat_plus_instruction'}</p>
                <div class="alert alert-warning alert-labeled" style="display: none" id="js_export_warning">
                    <div class="alert-labeled-row">
                        <p class="alert-body alert-body-right alert-labelled-cell">
                            <strong>{_p var='Warning'}:</strong>&nbsp;&nbsp;{_p var='data_is_exporting_please_dont_close_this_window'}
                        </p>
                    </div>
                </div>
                {if $server == 'nodejs'}
                    <button name="submit" type="button" class="btn btn-primary" id="js-submit_export_all_nodejs">{_p var='submit'}</button>
                {else}
                    {$sticky_bar}
                    <button name="submit" type="button" class="btn btn-primary" id="js-submit_export_all_firebase">{_p var='submit'}</button>
                {/if}
            </div>
        </div>
    </form>
{/if}