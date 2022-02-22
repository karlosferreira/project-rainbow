$Behavior.addReactionPage = function () {
// Colorpicker
  $('#p_reaction_add_reaction ._colorpicker:not(.built)').each(function () {
    var t = $(this),
      h = t.parent().find('._colorpicker_holder');

    t.addClass('built');
    h.css('background-color', '#' + t.val());

    h.colpick({
      layout: 'hex',
      submit: false,
      onChange: function (hsb, hex, rgb, el, bySetColor) {
        t.val(hex);
        h.css('background-color', '#' + hex);
        var rel = t.data('rel');
      },
      onHide: function () {
        t.trigger('change');
      }
    });

  });
};