<?php
if (isset(self::$_aParams['fromForumApp']) && self::$_aParams['core']['call'] == 'user.browse') {
    $sXml.= $this->_ajaxSafe('<script type="text/javascript">if ($("#js_user_loader").length) { $("#js_user_loader").find(\'input[name="cancel"]\').attr("onclick", "tb_remove();"); }</script>');
}