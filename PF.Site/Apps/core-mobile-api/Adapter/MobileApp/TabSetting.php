<?php

namespace Apps\Core_MobileApi\Adapter\MobileApp;

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Localization\PhpfoxLocalization;
use Phpfox;

class TabSetting
{
    /**
     * initialScreen: This field is screen name from screen_setting of API site-setting or it will get default from mobile codes.
     * Currently, we only support default screen mentioned above and screen from 3 types: listing, home screen of module and detail screen.
     * If API return specific screen which is not belong to above types, app may not work as expected.
     * Default screens do not need to add initialParams.
     */
    const HOME_STACK = 'HomeStack';
    const FRIEND_STACK = 'FriendStack';
    const MESSAGE_STACK = 'MessageStack';
    const NOTIFICATION_STACK = 'NotificationStack';
    const MENU_STACK = 'MenuStack';

    //Location of badge information in code (Only affects for default tabs, for custom tabs - Needs update mobile source code)
    const BADGE_FEED = 'feed';
    const BADGE_FRIEND = 'friend';
    const BADGE_MESSAGE = 'message';
    const BADGE_NOTIFICATION = 'notification';

    //Event name to handle onTabPress event
    const FEED_PRESS = '@feed/tabBarOnPress';
    const FRIEND_PRESS = '@friend/tabBarOnPress';
    const MESSAGE_PRESS = '@message/tabBarOnPress';
    const NOTIFICATION_PRESS = '@notification/tabBarOnPress';
    const MENU_PRESS = '@menu/tabBarOnPress';

    //Initial tab screen
    const SCREEN_HOME = 'home';
    const SCREEN_FRIEND = 'mainFriendRequest';
    const SCREEN_MESSAGE = 'mainMessage';
    const SCREEN_CHATPLUS_MESSAGE = 'chatplusMessage';
    const SCREEN_NOTIFICATION = 'mainNotification';
    const SCREEN_MENU = 'mainMenu';

    protected $initialTab;
    //List default stack name will be ignore
    protected $listStack;
    protected $disabledStack = [];
    protected $showLabel = false;
    protected $defaultTabList = [];
    protected $customTabList = [];
    /**
     * @var LocalizationInterface
     */
    protected $local;

    /**
     * Example for add a tab to show home screen of module blog:
     * {
     * name: 'ModuleBlog',
     * label: 'Blogs',
     * initialScreen: 'moduleBlog',
     * initialParams: { module_name: 'blog', resource_name: 'blog' },
     * iconName: 'newspaper-o'
     * }
     */
    public function __construct()
    {
        $this->initialTab = self::HOME_STACK;
        $this->listStack = [self::HOME_STACK, self::FRIEND_STACK, self::MESSAGE_STACK, self::NOTIFICATION_STACK, self::MENU_STACK];
        $this->setLocal();
    }

    /**
     * @return mixed
     */
    public function getDisabledStack()
    {
        return $this->disabledStack;
    }

    /**
     * @param mixed $disabledStack
     *
     * @codeCoverageIgnore
     */
    public function setDisabledStack($disabledStack)
    {
        $this->disabledStack = is_array($disabledStack) ? $disabledStack : [$disabledStack];
    }

    public function getTabSetting()
    {
        $data = [
            'showLabel'  => $this->isShowLabel(),
            'initialTab' => $this->getInitialTab(),
            'tabList'    => []
        ];
        $defaultTabList = $this->getDefaultTabList();
        $customTabList = $this->getCustomTabList();
        $allTabList = array_merge($defaultTabList, $customTabList);
        $activateTab = [];
        foreach ($this->listStack as $stack) {
            if (in_array($stack, $this->getDisabledStack())) {
                continue;
            }
            if (isset($allTabList[$stack])) {
                $activateTab[] = $stack;
                $data['tabList'][] = $allTabList[$stack];
            }
        }
        //Reset initialTab if default tab doesn't exists in stack list
        if (!in_array($this->getInitialTab(), $activateTab)) {
            $data['initialTab'] = '';
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function getInitialTab()
    {
        return $this->initialTab;
    }

    /**
     * @param mixed $initialTab
     *
     * @codeCoverageIgnore
     */
    public function setInitialTab($initialTab)
    {
        $this->initialTab = $initialTab;
    }

    /**
     * @return bool
     */
    public function isShowLabel()
    {
        return $this->showLabel;
    }

    /**
     * @param bool $showLabel
     *
     * @codeCoverageIgnore
     */
    public function setShowLabel($showLabel)
    {
        $this->showLabel = $showLabel;
    }

    /**
     * @return string[]
     *
     * @codeCoverageIgnore
     */
    public function getListStack()
    {
        return $this->listStack;
    }

    /**
     * @param string[] $listStack
     * @param bool     $merge
     *
     * @codeCoverageIgnore
     */
    public function setListStack($listStack, $merge = true)
    {
        $listStack = is_array($listStack) ? $listStack : [$listStack];
        if ($merge) {
            $this->listStack = array_merge($this->listStack, $listStack);
        } else {
            $this->listStack = $listStack;
        }
    }

    /**
     * @return mixed
     */
    public function getCustomTabList()
    {
        return $this->customTabList;
    }

    /**
     * @param mixed $customTabList
     *
     * @codeCoverageIgnore
     */
    public function setCustomTabList($customTabList)
    {
        /**
         * Single tablist item sample: {
         * name: string,
         * label?: string, // If not provided, name will be used as tab label
         * initialScreen: string,
         * initialParams?: object, // Params for initial tab screen
         * iconName: string, // Name of lineficon
         * activeIconName?: string, // If not provided, active tab will use same icon name as inactive
         * activeColor?: string, // If not provided, app will use primary color
         * inactiveColor?: string, // Gray color if not provided
         * badgeModule?: string, // location of badge information in code (Only affects for default tabs, for custom tabs - Needs update mobile source code)
         * tabPressEvent?: string // event name to handle onTabPress event
         * }
         **/
        $this->customTabList = is_array($customTabList) ? $customTabList : [$customTabList];
    }

    public function getDefaultTabList()
    {
        if (!count($this->defaultTabList)) {
            $this->defaultTabList = [];
            if (Phpfox::isModule('feed') || Phpfox::isAppActive('Core_Announcement')) {
                $this->defaultTabList[self::HOME_STACK] = [
                    'name'           => self::HOME_STACK,
                    'label'          => $this->getLocal()->translate('home'),
                    'initialScreen'  => self::SCREEN_HOME,
                    'iconName'       => 'home-o-alt',
                    'activeIconName' => 'home-alt',
                    'badgeModule'    => self::BADGE_FEED,
                    'tabPressEvent'  => self::FEED_PRESS
                ];
            }
            if (Phpfox::isModule('friend')) {
                $this->defaultTabList[self::FRIEND_STACK] = [
                    'name'           => self::FRIEND_STACK,
                    'label'          => $this->getLocal()->translate('requests'),
                    'initialScreen'  => self::SCREEN_FRIEND,
                    'iconName'       => 'user1-two-o',
                    'activeIconName' => 'user1-two',
                    'badgeModule'    => self::BADGE_FRIEND,
                    'tabPressEvent'  => self::FRIEND_PRESS
                ];
            }
            if (Phpfox::isAppActive('PHPfox_IM') || Phpfox::isApps('P_ChatPlus')) {
                $this->defaultTabList[self::MESSAGE_STACK] = [
                    'name'           => self::MESSAGE_STACK,
                    'label'          => $this->getLocal()->translate('messages'),
                    'initialScreen'  => Phpfox::isApps('P_ChatPlus') && setting('p_chatplus_server') ? self::SCREEN_CHATPLUS_MESSAGE : self::SCREEN_MESSAGE,
                    'iconName'       => 'comment-o',
                    'activeIconName' => 'comment',
                    'badgeModule'    => self::BADGE_MESSAGE,
                    'tabPressEvent'  => self::MESSAGE_PRESS
                ];
            }
            if (Phpfox::isModule('notification')) {
                $this->defaultTabList[self::NOTIFICATION_STACK] = [
                    'name'           => self::NOTIFICATION_STACK,
                    'label'          => $this->getLocal()->translate('notifications'),
                    'initialScreen'  => self::SCREEN_NOTIFICATION,
                    'iconName'       => 'bell2-o',
                    'activeIconName' => 'bell2',
                    'badgeModule'    => self::BADGE_NOTIFICATION,
                    'tabPressEvent'  => self::NOTIFICATION_PRESS
                ];
            }
            $this->defaultTabList[self::MENU_STACK] = [
                'name'          => self::MENU_STACK,
                'label'         => $this->getLocal()->translate('menu'),
                'initialScreen' => self::SCREEN_MENU,
                'iconName'      => 'navbar',
                'tabPressEvent' => self::MENU_PRESS
            ];
        }
        return $this->defaultTabList;
    }

    /**
     * @return LocalizationInterface
     */
    public function getLocal()
    {
        return $this->local;
    }

    private function setLocal()
    {
        $this->local = (new PhpfoxLocalization());
    }

}