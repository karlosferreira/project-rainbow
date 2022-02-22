<form action="{url link='current'}" enctype="multipart/form-data" method="post" onsubmit="{$sGetJsForm}">
    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title" style="font-size: 18px">
                {$message}
            </div>
        </div>
        {if $server == 'nodejs'}
        <div class="panel-body">
            <div class="error_message">
                {_p var='to_continue__please_enter_your_admin_login_details'}.
            </div>
            <div class="form-group">
                <label for="email">{_p var='email'}</label>
                <input class="form-control" type="text" name="email" id="email" value=""/>
            </div>
            <div class="form-group">
                <label for="password">{_p var='password'}</label>
                <input type="password" id="password" name="password" id="password" autocomplete="off" class="form-control">
            </div>
            <div class="alert alert-warning alert-labeled">
                <div class="alert-labeled-row">
                    <p class="alert-body alert-body-right alert-labelled-cell">
                        <strong>{_p('Warning')}:</strong>
                        {_p('make_sure_you_want_to_remove_all_old_messages_because_it_cannot_be_undone')}
                    </p>
                </div>
            </div>
            <div class="alert alert-danger alert-labeled">
                <div class="alert-labeled-row">
                    <p class="alert-body alert-body-right alert-labelled-cell">
                        <strong>{_p('Warning')}:</strong>
                        {_p('by_clicking_submit_all_messages_will_be_deleted_this_cannot_be_undone')}
                    </p>
                </div>
            </div>
            <button name="submit" type="button" class="btn btn-primary" id="js-submit_delete_all_nodejs">{_p var='submit'}</button>
        </div>
        {else}
        <div class="panel-body">
            <div class="alert alert-warning alert-labeled">
                <div class="alert-labeled-row">
                    <p class="alert-body alert-body-right alert-labelled-cell">
                        <strong>Warning</strong>
                        {_p('make_sure_you_want_to_remove_all_old_messages_because_it_cannot_be_undone')}
                    </p>
                </div>
            </div>
            <div class="panel-default">
                <div class="panel-heading">{_p var='firebase_remove_step_one' link='https://console.firebase.google.com'}</div>
                <div class="panel-heading">{_p var='firebase_remove_step_two'}</div>
                <div class="panel-heading">{_p var='firebase_remove_step_three'}</div>
                <div class="panel-heading">{_p var='firebase_remove_step_four'}</div>
                <div class="panel-heading">
                    <div>{_p var='firebase_remove_step_five'}</div>
                    <br>
                    <ul class="listing-group">
                        <li class="list-group-item"><b>notifications</b></li>
                        <li class="list-group-item"><b>rooms</b></li>
                        <li class="list-group-item"><b>users</b></li>
                    </ul>
                </div>
                <div class="panel-heading">{_p var='algolia_remove_step' link='https://www.algolia.com/dashboard'}</div>
            </div>
            <div class="alert alert-info alert-labeled" style="margin-top: 18px">
                <div class="alert-labeled-row">
                    <p class="alert-body alert-body-right alert-labelled-cell">
                        <strong>{_p var='note'}</strong> {_p var='see_our_screen_shot_for_more_detail'}
                    </p>
                </div>
            </div>
            <div style="margin-bottom: 18px;display: inline-block">
                <img src="{$firebaseImage}" style="max-width: 45%"><img src="{$algoliaImage}" style="max-width: 45%;margin-left: 10px">
            </div>
            <a href="https://console.firebase.google.com" class="btn btn-primary" target="_blank">{_p var='go_to_console'}</a>
        </div>
        {/if}
    </div>
</form>
