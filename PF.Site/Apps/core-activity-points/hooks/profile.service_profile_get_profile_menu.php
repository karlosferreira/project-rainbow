<?php
if (isset($aUser, $aMenus)
    && Phpfox::getParam('activitypoint.enable_activity_points')
    && Phpfox::isUser() && Phpfox::getUserId() == $aUser['user_id']
    && (!empty($userPoints = Phpfox::getService('activitypoint')->getTotalPointsOfUser($aUser['user_id'])) || Phpfox::getParam('profile.show_empty_tabs'))) {
    $aMenus = array_merge([
        [
            'phrase' => _p($userPoints == 1 ? 'activitypoint_point_capital' : 'activitypoint_points_capital'),
            'url' => Phpfox::getLib('url')->makeUrl(Phpfox::getUserId() == $aUser['user_id'] ? 'activitypoint' : 'current'),
            'total' => (int)$userPoints,
            'icon_class' => 'ico ico-star-circle-o',
        ],
    ], $aMenus);
}
