$Ready(function () {
    if (typeof mobileApiSmartbannerConfig !== "undefined") {
        $.smartbannerConfig = mobileApiSmartbannerConfig;
        var metaApp = $('body meta[name="apple-itunes-app"]');
        if (metaApp.length) {
            $('head').append('<meta name="apple-itunes-app" content="' + metaApp.attr('content') + '">');
            metaApp.remove();
        }
    }
})