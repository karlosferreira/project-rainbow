<?php

namespace Apps\phpFox_Shoutbox\Service;

use Apps\phpFox_Shoutbox\Service\Shoutbox as sb;
use Phpfox;

/**
 * Class Get
 * @package Apps\phpFox_Shoutbox\Service
 */
class Get
{
    private $_sTable;

    public function __construct()
    {
        $this->_sTable = sb::sbTable();
    }

    public function getPermissions(&$aShoutbox)
    {
        if (!empty($aShoutbox)) {
            $parentModuleId = $aShoutbox['parent_module_id'];

            switch ($parentModuleId) {
                case 'pages':
                    $granted = Phpfox::isAppActive('Core_Pages');
                    break;
                case 'groups':
                    $granted = Phpfox::isAppActive('PHPfox_Groups');
                    break;
                default:
                    $granted = true;
                    break;
            }

            if ($granted) {
                $aShoutbox['canDeleteOwn'] = (Phpfox::getUserId() == $aShoutbox['user_id']) && Phpfox::getUserParam('shoutbox.shoutbox_can_delete_own_message');
                $aShoutbox['canDeleteAll'] = false;
                $aShoutbox['canEdit'] = false;
                if (in_array($parentModuleId, ['pages', 'groups']) && ($aItem = (Phpfox::getService($parentModuleId)->getPage($aShoutbox['parent_item_id'])))) {
                    if (Phpfox::getService($parentModuleId)->isAdmin($aItem)) {
                        $aShoutbox['canDeleteAll'] = true;
                        $aShoutbox['canEdit'] = true;
                    }
                }
                if (!$aShoutbox['canDeleteAll'] && Phpfox::getUserParam('shoutbox.shoutbox_can_delete_others_message')) {
                    $aShoutbox['canDeleteAll'] = true;
                }
                if (!$aShoutbox['canEdit'] && (((int)Phpfox::getUserBy('user_group_id') === (int)ADMIN_USER_ID) || (Phpfox::getUserId() == $aShoutbox['user_id']) && Phpfox::getUserParam('shoutbox.shoutbox_can_edit_own_message'))) {
                    $aShoutbox['canEdit'] = true;
                }
            } else {
                $aShoutbox['canEdit'] = $aShoutbox['canDeleteOwn'] = $aShoutbox['canDeleteAll'] = false;
            }

            $aShoutbox['canShowAction'] = $aShoutbox['canEdit'] || $aShoutbox['canDeleteOwn'] || $aShoutbox['canDeleteAll'] || Phpfox::isUser();
        }
    }

    public function getTextForNotification($srcText, $shorten = 30)
    {
        if ($shorten == 0) {
            $shorten = 30;
        }

        $parseOutput = Phpfox::getLib('parse.output');
        $emoji = '8ball|a|ab|abc|abcd|accept|aerial_tramway|airplane|alarm_clock|alien|ambulance|anchor|angel|anger|angry|anguished|ant|apple|aquarius|aries|arrow_backward|arrow_double_down|arrow_double_up|arrow_down|arrow_down_small|arrow_forward|arrow_heading_down|arrow_heading_up|arrow_left|arrow_lower_left|arrow_lower_right|arrow_right|arrow_right_hook|arrow_up|arrow_up_down|arrow_up_small|arrow_upper_left|arrow_upper_right|arrows_clockwise|arrows_counterclockwise|art|articulated_lorry|astonished|atm|b|baby|baby_bottle|baby_chick|baby_symbol|baggage_claim|balloon|ballot_box_with_check|bamboo|banana|bangbang|bank|bar_chart|barber|baseball|basketball|bath|bathtub|battery|bear|bee|beer|beers|beetle|beginner|bell|bento|bicyclist|bike|bikini|bird|birthday|black_circle|black_joker|black_nib|black_square|black_square_button|blossom|blowfish|blue_book|blue_car|blue_heart|blush|boar|boat|bomb|book|bookmark|bookmark_tabs|books|boom|boot|bouquet|bow|bowling|bowtie|boy|bread|bride_with_veil|bridge_at_night|briefcase|broken_heart|bug|bulb|bullettrain_front|bullettrain_side|bus|busstop|bust_in_silhouette|busts_in_silhouette|cactus|cake|calendar|calling|camel|camera|cancer|candy|capital_abcd|capricorn|car|card_index|carousel_horse|cat|cat2|cd|chart|chart_with_downwards_trend|chart_with_upwards_trend|checkered_flag|cherries|cherry_blossom|chestnut|chicken|children_crossing|chocolate_bar|christmas_tree|church|cinema|circus_tent|city_sunrise|city_sunset|cl|clap|clapper|clipboard|clock1|clock10|clock1030|clock11|clock1130|clock12|clock1230|clock130|clock2|clock230|clock3|clock330|clock4|clock430|clock5|clock530|clock6|clock630|clock7|clock730|clock8|clock830|clock9|clock930|closed_book|closed_lock_with_key|closed_umbrella|cloud|clubs|cn|cocktail|coffee|cold_sweat|collision|computer|confetti_ball|confounded|confused|congratulations|construction|construction_worker|convenience_store|cookie|cool|cop|copyright|corn|couple|couple_with_heart|couplekiss|cow|cow2|credit_card|crocodile|crossed_flags|crown|cry|crying_cat_face|crystal_ball|cupid|curly_loop|currency_exchange|curry|custard|customs|cyclone|dancer|dancers|dango|dart|dash|date|de|deciduous_tree|department_store|diamond_shape_with_a_dot_inside|diamonds|disappointed|dizzy|dizzy_face|do_not_litter|dog|dog2|dollar|dolls|dolphin|door|doughnut|dragon|dragon_face|dress|dromedary_camel|droplet|dvd|e-mail|ear|ear_of_rice|earth_africa|earth_americas|earth_asia|egg|eggplant|eight|eight_pointed_black_star|eight_spoked_asterisk|electric_plug|elephant|email|end|envelope|es|euro|european_castle|european_post_office|evergreen_tree|exclamation|expressionless|eyeglasses|eyes|facepunch|factory|fallen_leaf|family|fast_forward|fax|fearful|feelsgood|feet|ferris_wheel|file_folder|finnadie|fire|fire_engine|fireworks|first_quarter_moon|first_quarter_moon_with_face|fish|fish_cake|fishing_pole_and_fish|fist|five|flags|flashlight|floppy_disk|flower_playing_cards|flushed|foggy|football|fork_and_knife|fountain|four|four_leaf_clover|fr|free|fried_shrimp|fries|frog|frowning|fuelpump|full_moon|full_moon_with_face|game_die|gb|gem|gemini|ghost|gift|gift_heart|girl|globe_with_meridians|goat|goberserk|godmode|golf|grapes|green_apple|green_book|green_heart|grey_exclamation|grey_question|grimacing|grin|grinning|guardsman|guitar|gun|haircut|hamburger|hammer|hamster|hand|handbag|hankey|hash|hatched_chick|hatching_chick|headphones|hear_no_evil|heart|heart_decoration|heart_eyes|heart_eyes_cat|heartbeat|heartpulse|hearts|heavy_check_mark|heavy_division_sign|heavy_dollar_sign|heavy_exclamation_mark|heavy_minus_sign|heavy_multiplication_x|heavy_plus_sign|helicopter|herb|hibiscus|high_brightness|high_heel|hocho|honey_pot|honeybee|horse|horse_racing|hospital|hotel|hotsprings|hourglass|hourglass_flowing_sand|house|house_with_garden|hurtrealbad|hushed|ice_cream|icecream|id|ideograph_advantage|imp|inbox_tray|incoming_envelope|information_desk_person|information_source|innocent|interrobang|iphone|it|izakaya_lantern|jack_o_lantern|japan|japanese_castle|japanese_goblin|japanese_ogre|jeans|joy|joy_cat|jp|key|keycap_ten|kimono|kiss|kissing|kissing_cat|kissing_closed_eyes|kissing_face|kissing_heart|kissing_smiling_eyes|koala|koko|kr|large_blue_circle|large_blue_diamond|large_orange_diamond|last_quarter_moon|last_quarter_moon_with_face|laughing|leaves|ledger|left_luggage|left_right_arrow|leftwards_arrow_with_hook|lemon|leo|leopard|libra|light_rail|link|lips|lipstick|lock|lock_with_ink_pen|lollipop|loop|loudspeaker|love_hotel|love_letter|low_brightness|m|mag|mag_right|mahjong|mailbox|mailbox_closed|mailbox_with_mail|mailbox_with_no_mail|man|man_with_gua_pi_mao|man_with_turban|mans_shoe|maple_leaf|mask|massage|meat_on_bone|mega|melon|memo|mens|metal|metro|microphone|microscope|milky_way|minibus|minidisc|mobile_phone_off|money_with_wings|moneybag|monkey|monkey_face|monorail|moon|mortar_board|mount_fuji|mountain_bicyclist|mountain_cableway|mountain_railway|mouse|mouse2|movie_camera|moyai|muscle|mushroom|musical_keyboard|musical_note|musical_score|mute|nail_care|name_badge|neckbeard|necktie|negative_squared_cross_mark|neutral_face|new|new_moon|new_moon_with_face|newspaper|ng|nine|no_bell|no_bicycles|no_entry|no_entry_sign|no_good|no_mobile_phones|no_mouth|no_pedestrians|no_smoking|non-potable_water|nose|notebook|notebook_with_decorative_cover|notes|nut_and_bolt|o|o2|ocean|octocat|octopus|oden|office|ok|ok_hand|ok_woman|older_man|older_woman|on|oncoming_automobile|oncoming_bus|oncoming_police_car|oncoming_taxi|one|open_file_folder|open_hands|open_mouth|ophiuchus|orange_book|outbox_tray|ox|page_facing_up|page_with_curl|pager|palm_tree|panda_face|paperclip|parking|part_alternation_mark|partly_sunny|passport_control|paw_prints|peach|pear|pencil|pencil2|penguin|pensive|performing_arts|persevere|person_frowning|person_with_blond_hair|person_with_pouting_face|phone|pig|pig2|pig_nose|pill|pineapple|pisces|pizza|plus1|point_down|point_left|point_right|point_up|point_up_2|police_car|poodle|poop|post_office|postal_horn|postbox|potable_water|pouch|poultry_leg|pound|pouting_cat|pray|princess|punch|purple_heart|purse|pushpin|put_litter_in_its_place|question|rabbit|rabbit2|racehorse|radio|radio_button|rage|rage1|rage2|rage3|rage4|railway_car|rainbow|raised_hand|raised_hands|ram|ramen|rat|recycle|red_car|red_circle|registered|relaxed|relieved|repeat|repeat_one|restroom|revolving_hearts|rewind|ribbon|rice|rice_ball|rice_cracker|rice_scene|ring|rocket|roller_coaster|rooster|rose|rotating_light|round_pushpin|rowboat|ru|rugby_football|runner|running|running_shirt_with_sash|sa|sagittarius|sailboat|sake|sandal|santa|satellite|satisfied|saxophone|school|school_satchel|scissors|scorpius|scream|scream_cat|scroll|seat|secret|see_no_evil|seedling|seven|shaved_ice|sheep|shell|ship|shipit|shirt|shit|shoe|shower|signal_strength|six|six_pointed_star|ski|skull|sleeping|sleepy|slot_machine|small_blue_diamond|small_orange_diamond|small_red_triangle|small_red_triangle_down|smile|smile_cat|smiley|smiley_cat|smiling_imp|smirk|smirk_cat|smoking|snail|snake|snowboarder|snowflake|snowman|sob|soccer|soon|sos|sound|space_invader|spades|spaghetti|sparkler|sparkles|sparkling_heart|speak_no_evil|speaker|speech_balloon|speedboat|squirrel|star|star2|stars|station|statue_of_liberty|steam_locomotive|stew|straight_ruler|strawberry|stuck_out_tongue|stuck_out_tongue_closed_eyes|stuck_out_tongue_winking_eye|sun_with_face|sunflower|sunglasses|sunny|sunrise|sunrise_over_mountains|surfer|sushi|suspect|suspension_railway|sweat|sweat_drops|sweat_smile|sweet_potato|swimmer|symbols|syringe|tada|tanabata_tree|tangerine|taurus|taxi|tea|telephone|telephone_receiver|telescope|tennis|tent|thought_balloon|three|thumbsdown|thumbsup|ticket|tiger|tiger2|tired_face|tm|toilet|tokyo_tower|tomato|tongue|top|tophat|tractor|traffic_light|train|train2|tram|triangular_flag_on_post|triangular_ruler|trident|triumph|trolleybus|trollface|trophy|tropical_drink|tropical_fish|truck|trumpet|tshirt|tulip|turtle|tv|twisted_rightwards_arrows|two|two_hearts|two_men_holding_hands|two_women_holding_hands|u5272|u5408|u55b6|u6307|u6708|u6709|u6e80|u7121|u7533|u7981|u7a7a|uk|umbrella|unamused|underage|unlock|up|us|v|vertical_traffic_light|vhs|vibration_mode|video_camera|video_game|violin|virgo|volcano|vs|walking|waning_crescent_moon|waning_gibbous_moon|warning|watch|water_buffalo|watermelon|wave|wavy_dash|waxing_crescent_moon|waxing_gibbous_moon|wc|weary|wedding|whale|whale2|wheelchair|white_check_mark|white_circle|white_flower|white_square|white_square_button|wind_chime|wine_glass|wink|wink2|wolf|woman|womans_clothes|womans_hat|womens|worried|wrench|x|yellow_heart|yen|yum|zap|zero|zzz';
        $text = strip_tags($parseOutput->clean($srcText));
        $countEmoji = 0;
        $storeEmojiTags = [];
        $tempText = preg_replace_callback('/:(' . $emoji . '):/', function ($match) use (&$countEmoji, &$storeEmojiTags) {
            if (!empty($match) && count($match) == 2) {
                $storeEmojiTags[] = $parseEmoji = '<i class="twa twa-' . str_replace('_', '-', $match[1]) . '"></i>';
                $countEmoji++;
                return $parseEmoji;
            }
            return '';
        }, $text);

        if ($countEmoji > 0) {
            $realTextLen = strlen(strip_tags($tempText)) + $countEmoji;
            if ($realTextLen <= $shorten) {
                $text = $tempText;
            } else {
                $realText = '';
                $countString = 0;
                $countAllowEmoji = 0;
                while ($countString < $shorten) {
                    if ($tempText[$countString] == '<' && $tempText[$countString + 1] == 'i') {
                        $tempText = substr_replace($tempText, '_', $countString, (strpos($tempText, '</i>') + 3) - ($countString - 1));
                        $realText .= $storeEmojiTags[$countAllowEmoji];
                        $countAllowEmoji++;
                    } else {
                        $realText .= $tempText[$countString];
                    }
                    $countString++;
                }
                $text = $realText . '...';
            }
        } else {
            $text = $parseOutput->shorten($srcText, $shorten, '...');
        }
        return $text;
    }

    /**
     * @param string $sModuleId
     * @param int $iItemId
     * @param int $iLimit
     * @return array|int|string
     */
    public function getShoutboxes($sModuleId = 'index', $iItemId = 0, $iLimit = 10)
    {
        $sExtra = $this->getExtraCondition($sModuleId, $iItemId);
        $aShoutboxes = db()
            ->select('s.*, sqm.text as quoted_text,' . Phpfox::getUserField('u') . ' , u2.full_name AS quoted_full_name, l.like_id AS is_liked')
            ->from($this->_sTable, 's')
            ->join(':user', 'u', 'u.user_id = s.user_id')
            ->leftJoin(Phpfox::getT('shoutbox_quoted_message'), 'sqm', 'sqm.shoutbox_id = s.shoutbox_id')
            ->leftJoin(Phpfox::getT('user'), 'u2', 'u2.user_id = sqm.user_id')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.item_id = s.shoutbox_id AND l.user_id = ' . Phpfox::getUserId() . ' AND l.type_id = "shoutbox"')
            ->where("s.parent_module_id='" . $sModuleId . "'" . $sExtra)
            ->order("s.shoutbox_id DESC")
            ->limit($iLimit)
            ->execute('getSlaveRows');
        foreach ($aShoutboxes as $sKey => $aShoutbox) {
            if ($aShoutbox['user_id'] == Phpfox::getUserId()) {
                $aShoutboxes[$sKey]['type'] = 's';
            } else {
                $aShoutboxes[$sKey]['type'] = 'r';
            }
            $this->getPermissions($aShoutboxes[$sKey]);
        }
        $aShoutboxes = array_reverse($aShoutboxes);
        return $aShoutboxes;
    }

    /**
     * @param int $iShoutboxId
     * @param int $iLimit
     * @param string $sModuleId
     * @param int $iItemId
     *
     * @return array
     */
    public function getUpdateShoutboxes($iShoutboxId = 0, $iLimit = 1, $sModuleId = 'index', $iItemId = 0)
    {
        $sExtra = $this->getExtraCondition($sModuleId, $iItemId);
        $aShoutboxes = db()
            ->select('"r" AS type, s.*, sqm.text as quoted_text,' . Phpfox::getUserField('u') . ', u2.full_name as quoted_full_name, u2.user_name AS quoted_user_name, l.like_id AS is_liked')
            ->from($this->_sTable, 's')
            ->join(':user', 'u', 'u.user_id = s.user_id')
            ->leftJoin(Phpfox::getT('shoutbox_quoted_message'), 'sqm', 'sqm.shoutbox_id = s.shoutbox_id')
            ->leftJoin(Phpfox::getT('user'), 'u2', 'u2.user_id = sqm.user_id')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.item_id = s.shoutbox_id AND l.user_id = ' . Phpfox::getUserId() . ' AND l.type_id = "shoutbox"')
            ->where('s.parent_module_id="' . $sModuleId . '" AND s.user_id !=' . (int)Phpfox::getUserId() . ' AND s.shoutbox_id >' . (int)$iShoutboxId . $sExtra)
            ->order("s.shoutbox_id DESC")
            ->limit($iLimit)
            ->execute('getSlaveRows');

        $aShoutboxes = array_reverse($aShoutboxes);
        if (count($aShoutboxes)) {
            $aShoutbox = $aShoutboxes[0];
            $parseOutput = Phpfox::getLib('parse.output');
            $aShoutbox['text'] = $parseOutput->parse($aShoutbox['text']);
            if (isset($aShoutbox['quoted_text'])) {
                $aShoutbox['text'] = '<div class="item-quote-content"><div class="quote-user">' . $parseOutput->parse($aShoutbox['quoted_full_name']) . '</div><div class="quote-message">' . $parseOutput->parse($aShoutbox['quoted_text']) . '</div></div>' . $aShoutbox['text'];
            }
            $this->getPermissions($aShoutbox);
            return $aShoutbox;
        }
        return [];
    }

    public function check($iShoutboxId = 0, $sModuleId = 'index', $iItemId = 0)
    {
        //check valid module_id
        $aValidModuleId = [
            'index',
            'pages',
            'groups'
        ];

        if (!in_array($sModuleId, $aValidModuleId)) {
            return [];
        }

        $sExtra = $this->getExtraCondition($sModuleId, $iItemId);
        $iCnt = db()
            ->select('COUNT(*)')
            ->from($this->_sTable, 's')
            ->join(':user', 'u', 'u.user_id = s.user_id')
            ->where('s.parent_module_id="' . $sModuleId . '" AND u.user_id !=' . (int)Phpfox::getUserId() . ' AND s.shoutbox_id > ' . (int)$iShoutboxId . $sExtra)
            ->execute('getSlaveField');
        if ($iCnt) {
            return $this->getUpdateShoutboxes($iShoutboxId, $iCnt, $sModuleId, $iItemId);
        }
        return [];
    }

    public function getLast($iShoutboxId = 0, $sModuleId = 'index', $iItemId = 0)
    {
        //check valid module_id
        $aValidModuleId = [
            'index',
            'pages',
            'groups'
        ];
        if (!in_array($sModuleId, $aValidModuleId)) {
            return [];
        }
        $sExtra = $this->getExtraCondition($sModuleId, $iItemId);
        $aShoutboxes = db()
            ->select('s.*, l.like_id AS is_liked, ' . Phpfox::getUserField('u') . ', sqm.text AS quoted_text, u2.full_name AS quoted_full_name, u2.user_name AS quoted_user_name')
            ->from($this->_sTable, 's')
            ->join(':user', 'u', 'u.user_id=s.user_id')
            ->leftJoin(Phpfox::getT('shoutbox_quoted_message'), 'sqm', 'sqm.shoutbox_id = s.shoutbox_id')
            ->leftJoin(Phpfox::getT('user'), 'u2', 'u2.user_id = sqm.user_id')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.item_id = s.shoutbox_id AND l.user_id = ' . Phpfox::getUserId() . ' AND l.type_id = "shoutbox"')
            ->where('s.parent_module_id="' . $sModuleId . '" AND s.shoutbox_id <' . (int)$iShoutboxId . $sExtra)
            ->order("shoutbox_id DESC")
            ->limit(30)
            ->execute('getSlaveRows');
        foreach ($aShoutboxes as $key => $aShoutbox) {
            $parseOutput = Phpfox::getLib('parse.output');
            $aShoutboxes[$key]['text'] = $aShoutbox['text'] = $parseOutput->parse($aShoutbox['text']);
            if (isset($aShoutbox['quoted_text'])) {
                $aShoutboxes[$key]['text'] = '<div class="item-quote-content"><div class="quote-user">' . $parseOutput->parse($aShoutbox['quoted_full_name']) . '</div><div class="quote-message">' . $parseOutput->parse($aShoutbox['quoted_text']) . '</div></div>' . $aShoutbox['text'];
            }
            $this->getPermissions($aShoutboxes[$key]);
        }
        return $aShoutboxes;
    }

    /**
     * @param $iShoutboxId
     * @param bool $bGetQuoted
     * @param bool $keepOriginalText
     * @return array|bool|int|string
     */
    public function getShoutbox($iShoutboxId, $bGetQuoted = false, $keepOriginalText = false)
    {
        $sSelect = 's.*, u.full_name, l.like_id as is_liked';
        if ($bGetQuoted) {
            $sSelect .= ', sqm.text AS quoted_text, u2.user_name AS quoted_user_name, u2.full_name AS quoted_full_name';
            db()->leftJoin(Phpfox::getT('shoutbox_quoted_message'), 'sqm', 'sqm.shoutbox_id = s.shoutbox_id')
                ->leftJoin(Phpfox::getT('user'), 'u2', 'u2.user_id = sqm.user_id');
        }

        $aShoutbox = db()
            ->select($sSelect)
            ->from($this->_sTable, 's')
            ->join(Phpfox::getT('user'), 'u', 's.user_id = u.user_id')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'shoutbox\' AND l.item_id = s.shoutbox_id AND l.user_id = ' . Phpfox::getUserId())
            ->where('s.shoutbox_id=' . (int)$iShoutboxId)
            ->execute('getSlaveRow');

        $parseOutput = Phpfox::getLib('parse.output');
        $aShoutbox['text'] = $parseOutput->parse($aShoutbox['text']);
        if (isset($aShoutbox['quoted_text']) && !$keepOriginalText) {
            $aShoutbox['text'] = '<div class="item-quote-content"><div class="quote-user">' . $parseOutput->parse($aShoutbox['quoted_full_name']) . '</div><div class="quote-message">' . $parseOutput->parse($aShoutbox['quoted_text']) . '</div></div>' . $aShoutbox['text'];
        }
        $this->getPermissions($aShoutbox);
        return isset($aShoutbox['shoutbox_id']) ? $aShoutbox : false;
    }

    public function getPhrases() {
        return [
            'yesterday',
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december',
            'sending_dot_dot_dot',
            'just_now',
            'a_minute_ago',
            'minutes_ago',
            'a_hour_ago',
            'hours_ago',
            'delete',
            'quote'
        ];
    }

    /**
     * @param string $sModuleId
     * @param int $iItemId
     * @return string
     */
    private function getExtraCondition($sModuleId, $iItemId)
    {
        $sExtra = '';
        if ($sModuleId != 'index') {
            $sExtra = " AND s.parent_item_id=" . (int)$iItemId;
        }
        $aBlockedUserIds = Phpfox::getService('user.block')->get(null, true);
        if (!empty($aBlockedUserIds)) {
            $sExtra .= ' AND u.user_id NOT IN (' . implode(',', $aBlockedUserIds) . ')';
        }
        return $sExtra;
    }
}

