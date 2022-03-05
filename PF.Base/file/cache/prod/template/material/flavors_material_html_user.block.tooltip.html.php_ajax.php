<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 11:58 pm */ ?>
<?php 
    
?>
<div class="user_tooltip_cover" style="background-image:url('<?php echo $this->_aVars['aUser']['cover_photo_link']; ?>')"></div>
    <div class="user_tooltip_inner">
    <div class="user_tooltip_image">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aUser'],'suffix' => '_120_square','max_width' => 50,'max_height' => 50)); ?>
    </div>
    <div class="user_tooltip_info">
<?php (($sPlugin = Phpfox_Plugin::get('user.template_block_tooltip_1')) ? eval($sPlugin) : false); ?>

        <div class="user_tooltip_info_up">
            <a href="<?php echo $this->_aVars['aUser']['profile_link']; ?>" class="user_tooltip_info_user">
<?php if (Phpfox ::getParam('user.display_user_online_status')): ?>
                <div class="user_tooltip_status">
<?php if ($this->_aVars['aUser']['is_online']): ?>
                    <span class="user_is_online" title="<?php echo _p('online'); ?>"><i class="fa fa-circle js_hover_title"></i></span>
<?php else: ?>
                    <span class="user_is_offline" title="<?php echo _p('offline'); ?>"><i class="fa fa-circle js_hover_title"></i></span>
<?php endif; ?>
                </div>
<?php endif; ?>
<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aUser']['full_name'])); ?>
            </a>

<?php (($sPlugin = Phpfox_Plugin::get('user.template_block_tooltip_3')) ? eval($sPlugin) : false); ?>

<?php if ($this->_aVars['bIsPage']): ?>
                <ul>
                    <li><?php echo Phpfox::getLib('locale')->convert($this->_aVars['aUser']['page']['category_name']); ?></li>
                    <li>
<?php if ($this->_aVars['aUser']['page']['page_type'] == '1'): ?>
<?php if ($this->_aVars['aUser']['page']['total_like'] == 1): ?>
<?php echo _p('1_member'); ?>
<?php elseif ($this->_aVars['aUser']['page']['total_like'] > 1): ?>
<?php echo _p('total_members', array('total' => number_format($this->_aVars['aUser']['page']['total_like'])));  endif; ?>
<?php else: ?>
<?php if ($this->_aVars['aUser']['page']['total_like'] == 1): ?>
<?php echo _p('1_person_likes_this'); ?>
<?php elseif ($this->_aVars['aUser']['page']['total_like'] > 1): ?>
<?php echo _p('total_people_like_this', array('total' => number_format($this->_aVars['aUser']['page']['total_like']))); ?>
<?php endif; ?>
<?php endif; ?>
                    </li>
                </ul>
<?php else: ?>
<?php if ($this->_aVars['aUser']['total_friend'] > 0): ?>
                <div class="top-info total-friends">
<?php if ($this->_aVars['aUser']['total_friend'] == 1): ?>
<?php echo _p('total_friend', array('total' => $this->_aVars['aUser']['total_friend'])); ?>
<?php else: ?>
<?php echo _p('total_friends', array('total' => $this->_aVars['aUser']['total_friend'])); ?>
<?php endif; ?>
                </div>
<?php endif; ?>
        </div>
            <ul class="bottom-info">
<?php if ($this->_aVars['aUser']['location']): ?>
                <li><span><?php echo _p('lives_in'); ?></span> <?php echo $this->_aVars['aUser']['location']; ?></li>
<?php endif; ?>

<?php if ($this->_aVars['aUser']['gender_name']): ?>
                <li><?php echo $this->_aVars['aUser']['gender_name']; ?></li>
<?php endif; ?>

<?php if (! empty ( $this->_aVars['aUser']['birthdate_display'] )): ?>
                <li>
<?php if ($this->_aVars['aUser']['dob_setting'] == '2'): ?>
                        <span><?php echo _p('age'); ?>:</span>
<?php else: ?>
                        <span><?php echo _p('birthday'); ?>:</span>
<?php endif; ?>
<?php if (count((array)$this->_aVars['aUser']['birthdate_display'])):  foreach ((array) $this->_aVars['aUser']['birthdate_display'] as $this->_aVars['sAgeType'] => $this->_aVars['sBirthDisplay']): ?>
<?php if ($this->_aVars['aUser']['dob_setting'] == '2'): ?>
<?php if ($this->_aVars['sBirthDisplay'] == 1): ?>
<?php echo _p('1_year_old'); ?>
<?php else: ?>
<?php echo _p('age_years_old', array('age' => $this->_aVars['sBirthDisplay'])); ?>
<?php endif; ?>
<?php else: ?>
<?php if ($this->_aVars['aUser']['dob_setting'] != '3'): ?>
<?php echo $this->_aVars['sBirthDisplay']; ?>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; endif; ?>
                </li>
<?php endif; ?>

<?php if (! empty ( $this->_aVars['aUser']['relationship'] )): ?>
                <li><span><?php echo _p('relationship'); ?>:</span> <?php echo $this->_aVars['aUser']['relationship']; ?></li>
<?php endif; ?>

<?php if (! empty ( $this->_aVars['aUser']['joined'] )): ?>
                <li><span><?php echo _p('registered_at'); ?></span> <?php echo Phpfox::getLib('date')->convertTime($this->_aVars['aUser']['joined']); ?></li>
<?php endif; ?>
            </ul>

<?php (($sPlugin = Phpfox_Plugin::get('user.template_block_tooltip_5')) ? eval($sPlugin) : false); ?>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('user.template_block_tooltip_2')) ? eval($sPlugin) : false); ?>

    </div>
</div>
<?php if ($this->_aVars['aUser']['user_id'] != Phpfox ::getUserId() && ! $this->_aVars['bIsPage']): ?>
<?php if ($this->_aVars['iMutualTotal'] > 0): ?>
        <ul class="user_tooltip_mutual mutual-friends-list">
<?php if ($this->_aVars['iMutualTotal'] == 1): ?>
<?php echo _p('1_mutual_friend'); ?>:
<?php else: ?>
<?php echo _p('total_mutual_friends', array('total' => $this->_aVars['iMutualTotal'])); ?>:
<?php endif; ?>
<?php if (count((array)$this->_aVars['aMutualFriends'])):  foreach ((array) $this->_aVars['aMutualFriends'] as $this->_aVars['iKey'] => $this->_aVars['aMutualFriend']): ?>
                <li id="js_user_name_link_<?php echo $this->_aVars['aMutualFriend']['user_name']; ?>">
                    <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aMutualFriend']['user_name'], [], false, false); ?>"><?php echo $this->_aVars['aMutualFriend']['full_name']; ?></a>
                </li>
<?php endforeach; endif; ?>
<?php if ($this->_aVars['iRemainFriends'] > 0): ?>
                <span><?php echo _p('and'); ?></span>
                <a href="#" class="user_viewmore" onclick="$Core.box('friend.getMutualFriends', 450, 'user_id=<?php echo $this->_aVars['aUser']['user_id']; ?>');return false;"><?php if ($this->_aVars['iRemainFriends'] == 1):  echo _p('1_other');  else:  echo _p('total_others', array('total' => $this->_aVars['iRemainFriends']));  endif; ?></a>
<?php endif; ?>
        </ul>
<?php endif; ?>
    <div class="user_tooltip_action_user">
<?php if (isset ( $this->_aVars['aUser']['is_friend'] ) && $this->_aVars['aUser']['is_friend'] === 2): ?>
            <div class="friend_request_sent">
                <span class="ico ico-arrow-right mr-1"></span><?php echo _p('friend_request_sent'); ?>
            </div>
<?php endif; ?>
        <ul>
<?php if (empty ( $this->_aVars['aUser']['is_ignore_request'] ) && ! $this->_aVars['bLoginAsPage'] && Phpfox ::isUser() && Phpfox ::isModule('friend') && Phpfox ::getUserParam('friend.can_add_friends') && empty ( $this->_aVars['aUser']['is_friend'] ) && Phpfox ::getService('user.privacy')->hasAccess('' . $this->_aVars['aUser']['user_id'] . '' , 'friend.send_request' )): ?>
                <li class="py-1"><a class="btn btn-primary btn-sm btn-icon" href="#" onclick="$(this).closest('.js_user_tool_tip_holder').hide();return $Core.addAsFriend('<?php echo $this->_aVars['aUser']['user_id']; ?>');" title="<?php echo _p('add_to_friends'); ?>"><span class="ico ico-user1-plus-o"></span><?php echo _p('add_friends'); ?></a></li>
<?php endif; ?>
<?php if ($this->_aVars['bShowBDay']): ?>
                <li class="py-1"><a class="btn btn-default btn-sm btn-icon" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aUser']['user_name'], [], false, false); ?>"><span class="ico ico-birthday-cake-alt mr-1"></span><?php echo _p('birthday_wishes'); ?></a></li>
<?php if ($this->_aVars['bCanSendMessage']): ?>
                    <li class="py-1">
                        <div class="item-tooltip-viewmore-button dropup">
                            <a href="" data-toggle="dropdown" class=""><span class="ico ico-dottedmore-o"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a  href="#" onclick="$Core.composeMessage({user_id: <?php echo $this->_aVars['aUser']['user_id']; ?>});$(this).closest('.js_user_tool_tip_holder').hide(); return false;"><span class="ico ico-comment-o"></span><?php echo _p('send_message'); ?></a></li>
                            </ul>
                        </div>
                    </li>
<?php endif; ?>
<?php elseif ($this->_aVars['bCanSendMessage']): ?>
                <li class="py-1"><a class="btn btn-default btn-sm" href="#" onclick="$Core.composeMessage({user_id: <?php echo $this->_aVars['aUser']['user_id']; ?>});$(this).closest('.js_user_tool_tip_holder').hide(); return false;"><?php echo _p('send_message'); ?></a></li>
<?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

