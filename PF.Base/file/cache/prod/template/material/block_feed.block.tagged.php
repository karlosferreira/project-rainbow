<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php

?>
<div class="feed-table-tagging">
    <div class="js_feed_compose_extra feed_compose_extra js_feed_compose_tagging dont-unbind-children" style="display: none;">
        <div class="feed-box">
            <div class="feed-with"><?php echo _p('with'); ?></div>
            <div class="feed-tagging-input-box">
                <input type="hidden" class="close_warning" data-feedid="<?php if (isset ( $this->_aVars['iFeedId'] )):  echo $this->_aVars['iFeedId'];  else: ?>0<?php endif; ?>" id="feed_input_tagged<?php if (isset ( $this->_aVars['iFeedId'] )): ?>_<?php echo $this->_aVars['iFeedId'];  else: ?>_0<?php endif; ?>" name="val[tagged_friends]" value="<?php if (isset ( $this->_aVars['aForms']['tagged_friends'] )):  echo $this->_aVars['aForms']['tagged_friends'];  endif; ?>">
                <span class="js_feed_tagged_items feed_tagged_items"></span>
                <span class="js_feed_input_tagging_wrapper feed_input_tagging_wrapper">
                    <input type="text" class="js_input_tagging" placeholder="<?php echo _p('who_is_with_you'); ?>">
                </span>
            </div>
        </div>
    </div>
</div>

