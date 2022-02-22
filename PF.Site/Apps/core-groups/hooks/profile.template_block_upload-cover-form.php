<?php
if (defined('PHPFOX_IS_PAGES_VIEW')
    && defined('PHPFOX_PAGES_ITEM_TYPE')
    && PHPFOX_PAGES_ITEM_TYPE == 'groups'
    && !empty($this->_aVars['aPage'])
    && !empty($this->_aVars['bCanChangeCover'])) {
    echo '<input type="hidden" name="val[groups_id]" value="' . $this->_aVars['aPage']['page_id'] . '">';
}