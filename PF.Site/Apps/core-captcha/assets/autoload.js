$Core.captcha = {
  loadRecaptchaApi: function () {
    var ele = $('#g-recaptcha').not('.build');
    if (!ele.length) {
      return;
    }
    var siteKey = ele.data('sitekey');
    var recaptchaType = ele.data('type');

    if (typeof grecaptcha == 'undefined') {
      var recaptchaApi = 'https://www.google.com/recaptcha/api.js?hl=' + oParams.sLanguage;
      if (recaptchaType == 3) {
        recaptchaApi += '&render=' + siteKey;
      }
      $("<script/>", {src: recaptchaApi}).appendTo("body");

      if (recaptchaType == 3) {
        setTimeout(function () {
          $Core.captcha.addRecaptchaToken(siteKey);
        }, 300);
      }

    } else { // show captcha on popup
      if (recaptchaType == 3) {
        // V3
        this.addRecaptchaToken(siteKey);
      } else {
        // V2
        grecaptcha.render(ele.get(0), {
          'sitekey': siteKey
        });
      }
    }
    ele.addClass('build');
  },
  addRecaptchaToken: function (siteKey) { // only for V3
    if (typeof grecaptcha !== 'undefined') {
      grecaptcha.ready(function () {
        grecaptcha.execute(siteKey, {action: 'submit_form'}).then(function (token) {
          if ($('#g-recaptcha-response').length) {
            $('#g-recaptcha-response').val(token);
          }
        });
      });
    }
  }
};