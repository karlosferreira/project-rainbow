<?php
if (setting('m9_facebook_enabled')) {
    echo '<div class="fb_login">';
    echo '<span class="fb_login_go"><span class="core-facebook-item-fb-icon"><img src="' . Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-facebook/assets/images/fb-logo-white.png"></img></span>' . _p('sign_in_with_facebook') . '</span>';
    echo '</div>';
}
