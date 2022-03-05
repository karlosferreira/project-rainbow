<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 18/6/18
 * Time: 4:48 PM
 */

namespace Apps\Core_MobileApi\Adapter\MobileApp;


class Screen extends BaseView
{
    const LAYOUT_TAB = "tab";
    const LAYOUT_DEFAULT = "";

    const ACTION_ADD = '@app/ADD_ITEM';
    const ACTION_DELETE_ITEM = '@app/DELETE_ITEM';
    const ACTION_EDIT_USER_STATUS = '@feed/EDIT_USER_STATUS';
    const ACTION_DELETE_COMMENT = '@app/DELETE_COMMENT';
    const ACTION_EDIT_ITEM = '@app/EDIT_ITEM';
    const ACTION_EDIT_AVATAR = '@app/EDIT_ITEM_AVATAR';
    const ACTION_EDIT_COVER = '@app/EDIT_ITEM_COVER';
    const ACTION_EDIT_INFO = '@app/EDIT_ITEM_INFO';
    const ACTION_SHOW_APP_MENU = '@app/SHOW_APP_MENU';
    const ACTION_REPORT_ITEM = '@app/REPORT_ITEM';
    const ACTION_APPROVE_ITEM = '@app/APPROVE_ITEM';
    const ACTION_FEATURE_ITEM = '@app/FEATURE_ITEM';
    const ACTION_SPONSOR_ITEM = '@app/SPONSOR_ITEM';
    const ACTION_PURCHASE_SPONSOR_ITEM = '@app/PURCHASE_SPONSOR_ITEM';
    const ACTION_SPONSOR_IN_FEED = '@app/SPONSOR_IN_FEED';
    const ACTION_SHARE_ITEM = '@app/SHARE_ITEM';
    const ACTION_FILTER_BY_CATEGORY = '@app/FILTER_CATEGORY';
    const ACTION_FILTER_BY = '@app/FILTER_BY';
    const ACTION_SORT_BY = '@app/SORT_BY';
    const ACTION_CHAT_WITH = '@message/chatWithUser';

    // mass action
    const ACTION_APPROVE_ITEMS = '@app/APPROVE_ITEMS';
    const ACTION_FEATURE_ITEMS = '@app/FEATURE_ITEMS';
    const ACTION_REMOVE_FEATURE_ITEMS = '@app/REMOVE_FEATURE_ITEMS';
    const ACTION_DELETE_ITEMS = '@app/DELETE_ITEMS';

    const LAYOUT_LIST_VIEW = 'list_view';
    const LAYOUT_LIST_CARD_VIEW = 'list_card_view';
    const LAYOUT_GRID_VIEW = 'grid_view';
    const LAYOUT_GRID_CARD_VIEW = 'grid_card_view';

    const ALIGNMENT_TOP = 'top';
    const ALIGNMENT_RIGHT = 'right';
    const ALIGNMENT_LEFT = 'left';

    /**
     * @return mixed
     */
    public function getParameters()
    {
        if (isset($this->parameters['header_options']) && is_array($this->parameters['header_options'])) {
            foreach ($this->parameters['header_options'] as $key => $option) {
                if (isset($option['allowed']) && $option['allowed'] == false) {
                    unset($this->parameters['header_options'][$key]);
                } else if (isset($option['allowed'])) {
                    unset($this->parameters['header_options'][$key]['allowed']);
                }
            }
            $this->parameters['header_options'] = array_values($this->parameters['header_options']);
        }

        if (isset($this->parameters['header_buttons']) && is_array($this->parameters['header_buttons'])) {
            foreach ($this->parameters['header_buttons'] as $key => $option) {
                if (isset($option['allowed']) && $option['allowed'] == false) {
                    unset($this->parameters['header_buttons'][$key]);
                } else if (isset($option['allowed'])) {
                    unset($this->parameters['header_buttons'][$key]['allowed']);
                }
            }
            $this->parameters['header_buttons'] = array_values($this->parameters['header_buttons']);
        }

        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}