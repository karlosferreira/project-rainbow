<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 2:19 am */ ?>
<?php

?>

<div class="content-text" id="js_comment_text_<?php echo $this->_aVars['aComment']['comment_id']; ?>"><?php echo Phpfox::getLib('phpfox.parse.output')->shorten(comment_parse($this->_aVars['aComment']['text']), '300', 'comment.view_more', true); ?></div>
<?php if (! empty ( $this->_aVars['aComment']['extra_data'] )): ?>
<?php if ($this->_aVars['aComment']['extra_data']['extra_type'] == 'photo'): ?>
        <div class="content-photo">
            <span class="item-photo">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('server_id' => $this->_aVars['aComment']['extra_data']['server_id'],'path' => 'core.url_pic','file' => "comment/".$this->_aVars['aComment']['extra_data']['image_path'],'suffix' => '_500','thickbox' => true)); ?>
            </span>
        </div>
<?php elseif ($this->_aVars['aComment']['extra_data']['extra_type'] == 'sticker'): ?>
        <div class="content-sticker">
            <span class="item-sticker">
<?php echo $this->_aVars['aComment']['extra_data']['full_path']; ?>
            </span>
        </div>
<?php elseif ($this->_aVars['aComment']['extra_data']['extra_type'] == 'preview' && ! Phpfox ::getParam('core.disable_all_external_urls')): ?>
        <div class="comment-link" id="js_link_preview_<?php echo $this->_aVars['aComment']['comment_id']; ?>">
            <div class="content-link-<?php if (! empty ( $this->_aVars['aComment']['extra_data']['params']['is_image_link'] )): ?>photo<?php else: ?>normal<?php endif; ?>">
<?php if (! empty ( $this->_aVars['aComment']['extra_data']['params']['default_image'] )): ?>
                    <a href="<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aComment']['extra_data']['params']['actual_link'])); ?>" class="item-image no_ajax <?php if (isset ( $this->_aVars['aComment']['extra_data']['params']['custom_css'] )):  echo $this->_aVars['aComment']['extra_data']['params']['custom_css'];  endif; ?>" target="_blank"><img src="<?php echo $this->_aVars['aComment']['extra_data']['params']['default_image']; ?>" alt="<?php if (isset ( $this->_aVars['aComment']['extra_data']['params']['title'] )):  echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aComment']['extra_data']['params']['title']));  endif; ?>"></a>
<?php endif; ?>
<?php if (empty ( $this->_aVars['aComment']['extra_data']['params']['is_image_link'] )): ?>
                    <div class="item-inner">
                        <a href="<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aComment']['extra_data']['params']['actual_link'])); ?>" class="item-title no_ajax <?php if (isset ( $this->_aVars['aComment']['extra_data']['params']['custom_css'] )):  echo $this->_aVars['aComment']['extra_data']['params']['custom_css'];  endif; ?>" target="_blank">
<?php if (isset ( $this->_aVars['aComment']['extra_data']['params']['title'] )): ?>
<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aComment']['extra_data']['params']['title'])); ?>
<?php else: ?>
<?php echo $this->_aVars['aComment']['extra_data']['params']['link']; ?>
<?php endif; ?>
                        </a>
<?php if (isset ( $this->_aVars['aComment']['extra_data']['params']['host'] )): ?>
                            <a href="<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aComment']['extra_data']['params']['actual_link'])); ?>" class="item-info no_ajax <?php if (isset ( $this->_aVars['aComment']['extra_data']['params']['custom_css'] )):  echo $this->_aVars['aComment']['extra_data']['params']['custom_css'];  endif; ?>" target="_blank">
<?php echo $this->_aVars['aComment']['extra_data']['params']['host']; ?>
                            </a>
<?php endif; ?>
                    </div>
<?php endif; ?>
            </div>
        </div>
<?php endif;  endif; ?>

