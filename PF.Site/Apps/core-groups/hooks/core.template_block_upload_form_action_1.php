<?php
defined('PHPFOX') or exit('NO DICE!');

if (defined('PHPFOX_IS_GROUPS_ADD') && PHPFOX_IS_GROUPS_ADD && Phpfox::getService('groups')->isAdmin($this->_aVars['aForms'])) {
    echo '<a role="button" class="text-uppercase fw-bold change_photo" onclick="tb_show(\'' . _p('groups_edit_thumbnail') . '\', $.ajaxBox(\'groups.cropme\', \'height=400&width=500&id=' . $this->_aVars['aForms']['page_id'] . '\'))"><i class="ico ico-text-file-edit"></i>&nbsp;&nbsp;&nbsp;' . _p('groups_edit_thumbnail') . '</a>';
}
