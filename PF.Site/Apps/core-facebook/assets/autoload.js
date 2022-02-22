$Ready(function () {

    /**
     * Loop thru all profile images with a connection to Facebook
     */
    $('.image_object:not(.fb_built)[data-object="fb"]').each(function () {
        var t = $(this), src = '//graph.facebook.com/' + t.data('src') + '/picture?type=square&width=200&height=200';
        t.addClass('fb_built');
        t.attr('src', src);
    });

    // Add the FB login button
    if (!$('.fb_login_go_cache').length && (typeof(Fb_Login_Disabled) == 'undefined' || !Fb_Login_Disabled)) {
        var l = $('#js_block_border_user_login-block form');
        var logo_href = rtrim(getParam('sBaseURL').replace('index.php', ''), '/')  + '/PF.Site/Apps/core-facebook/assets/images/fb-logo.png';
        if (l.length) {
            l.before(
                '<span class="fb_login_go fb_login_go_cache"><span class="core-facebook-item-fb-icon"><img src="'+ logo_href + '"></img></span>Facebook</span>');
        } else {
            l = $('[data-component="guest-actions"]');
            bootstrapSm = $('.sticky-bar-sm .guest_login_small');
            bootstrapXs = $('.login-menu-btns-xs');
            l.addClass('facebook-login-wrapper');
            bootstrapSm.addClass('facebook-login-wrapper facebook-login-wrapper-sm');
            bootstrapXs.addClass('facebook-login-wrapper facebook-login-wrapper-xs');
            bootstrapSm.append(
                '<div class="facebook-login-header"><span class="fb_login_go fb_login_go_cache"><span class="core-facebook-item-fb-icon"><img src="' + logo_href + '"></img></span> <span class="facebook-login-label">Facebook</span></span></div>');
            l.append(
                '<div class="facebook-login-header"><span class="fb_login_go fb_login_go_cache"><span class="core-facebook-item-fb-icon"><img src="' + logo_href + '"></img></span> <span class="facebook-login-label">Facebook</span></span></div>');
            bootstrapXs.append(
                '<div class="facebook-login-header"><span class="fb_login_go fb_login_go_cache"><span class="core-facebook-item-fb-icon"><img src="' + logo_href + '"></img></span> <span class="facebook-login-label">Facebook</span></span></div>');
        }
    }

    // Click event to send the user to log into Facebook
    $('.fb_login_go').click(function () {
        PF.url.send('/fb/login', true);
    });
});