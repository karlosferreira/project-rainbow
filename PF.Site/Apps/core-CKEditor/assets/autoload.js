/**
 * @returns {string}
 * @constructor
 */
function CKEditor_wysiwyg_getContent() {
  if (typeof CKEDITOR.instances[Editor.getId()] === 'undefined') {
    return undefined;
  }

  var data = CKEDITOR.instances[Editor.getId()].getData();
  if (data.indexOf('core-ckeditor-content') === -1) {
    data = '<div class="core-ckeditor-content">' + data + '</div>';
  }

  return data;
}

/**
 * @param mValue
 * @constructor
 */
function CKEditor_wysiwyg_insert(mValue) {

  var sValue = '',
      ckeditorInstance = CKEDITOR.instances[Editor.getId()];

  switch (mValue['type']) {
    case 'emoticon':
      sValue = '' + mValue['text'] + '';
      break;
    case 'image':
      sValue = '<img class="parsed_image" src="' + mValue['path'] + '" alt="' + mValue['name'] +
          '">';
      break;
    case 'attachment':
      sValue = '[attachment="' + mValue['id'] + '"]' + mValue['name'] +
          '[/attachment]';
      break;
    case 'video':
      sValue = '[video]' + mValue['id'] + '[/video]';
      break;
  }

  if (typeof ckeditorInstance == 'undefined') {
    var myField = document.getElementById(Editor.getId());
    if (document.selection) {
      myField.focus();
      sel = document.selection.createRange();
      sel.text = sValue;
    }
    else if (myField.selectionStart || myField.selectionStart == '0') {
      var startPos = myField.selectionStart;
      var endPos = myField.selectionEnd;
      myField.value = myField.value.substring(0, startPos)
          + sValue
          + myField.value.substring(endPos, myField.value.length);
      myField.focus();
    }
    else {
      myField.value += sValue;
    }
  } else {
    ckeditorInstance.insertHtml(sValue);
  }
}

function CKEditor_wysiwyg_remove() {
  CKEDITOR.instances[Editor.getId()].setData('');
}

/**
 *
 */
function CKEditor_wysiwyg_setContent(mValue) {
  CKEDITOR.instances[Editor.getId()].setData(mValue);
}

// remove CKEDITOR element when close popup
$(document).off('click', '.js_box_close').on('click', '.js_box_close', function() {
  var textarea = $(this).next().find('textarea');
  (typeof textarea !== 'undefined') && (typeof CKEDITOR !== 'undefined') &&
  (typeof CKEDITOR.instances[textarea.attr('id')] !== 'undefined') &&
  (!close_warning_checked) &&
  CKEDITOR.instances[textarea.attr('id')].destroy();
});

function pckeditorDisableCkeditorAutoInline() {
  if(typeof CKEDITOR !== 'undefined') {
    CKEDITOR.disableAutoInline = true;
  }
}

PF.event.on('on_document_ready_end', function () {
  pckeditorDisableCkeditorAutoInline();
});

PF.event.on('on_page_change_end', function () {
  pckeditorDisableCkeditorAutoInline();
});



