<?php

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\NotificationResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class NotificationApi extends AbstractResourceApi implements MobileAppSettingInterface
{
    public function __naming()
    {
        return [
            'notification'        => [
                'get' => 'findAll',
                'put' => 'makeAllAsRead',
            ],
            'notification/delete' => [
                'delete' => 'deleteAll'
            ],
            'notification/:id'    => [
                'put' => 'updateOne',
            ],
            'notification/unseen/:id' => [
                'get' => 'getTotalUnSeenByUser'
            ]
        ];
    }


    public function processRow($item)
    {
        try {
            /** @var NotificationResource $notification */
            $notification = $this->populateResource(NotificationResource::class, $item);

            $newLink = (new CoreApi())->parseUrlToRoute($item['link'], true);
            if ($newLink && isset($newLink['params'])) {
                $notification->route = $newLink;
                $param = $newLink['params']['resource_name'] . '/' . $newLink['params']['id'];
                $notification->link = \Phpfox_Url::instance()->makeUrl($param, isset($newLink['params']['query']) ? $newLink['params']['query'] : []);
            } else {
                if (preg_match('/\/link\/(\d+)/', $item['link'], $aMatch)) {
                    $link = Phpfox::getService('link')->getLinkById($aMatch[1]);
                    if (!empty($link['module_id'])) {
                        $url = $link['module_id'] . '/' . $link['item_id'];
                    } else {
                        $url = $link['user_name'];
                    }
                    $notification->link = \Phpfox_Url::instance()->makeUrl($url, ['link-id' => $aMatch[1]]);
                }
            }
            return $notification;
        } catch (\Exception $exception) {

        }

    }

    /**
     * Make all as read
     *
     * @param array $params
     *
     * @return array|bool
     */
    public function makeAllAsRead($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        Phpfox::getService('notification.process')->markAllRead();
        return $this->success([], [], 'marked_all_as_read_successfully');
    }

    /**
     * Make 1 notification as read
     *
     * @param $params
     *
     * @return array|bool
     */
    public function updateOne($params)
    {
        $id = $this->resolver->resolveId($params);

        Phpfox::getService('notification.process')->markAsRead($id);

        return $this->success([], [], '');

    }


    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $params = $this->resolver->setDefined(['page', 'limit'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $notifications = $this->getForBrowse($params);

        if ($notifications) {
            $this->processRows($notifications);
        }

        return $this->success($notifications);
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function findOne($params)
    {
        return null;
    }

    /**
     * Create new document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        return null;
    }

    /**
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function delete($params)
    {
        $id = $this->resolver->resolveId($params);
        $item = $this->loadResourceById($id);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($item['user_id'] == $this->getUser()->getId() && Phpfox::getService('notification.process')->deleteById($id)) {
            return $this->success([], [], $this->getLocalization()->translate('notification_deleted_successfully'));
        }
        return $this->permissionError();
    }

    public function deleteAll()
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $count = $this->database()->select('COUNT(*)')->from(':notification')->where(['user_id' => $this->getUser()->getId()])->executeField();
        if (!$count) {
            return $this->success([], [], $this->getLocalization()->translate('there_are_no_notifications_to_delete'));
        }
        if (Phpfox::getService('notification.process')->deleteAll()) {
            return $this->success([], [], $this->getLocalization()->translate('deleted_all_notifications_successfully'));
        }
        return $this->error();
    }

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    function form($params = [])
    {
        return null;
    }

    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->database()->select('*')
            ->from(':notification')->where('notification_id =' . (int)$id)->executeRow();
        if (empty($item['notification_id'])) {
            return null;
        }

        if ($returnResource) {
            return NotificationResource::populate($item);
        }
        return $item;
    }

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function patchUpdate($params)
    {
        return null;
    }

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function update($params)
    {
        return null;
    }

    protected function getForBrowse($params)
    {
        static $aNotifications = null;

        if (is_array($aNotifications) && !(defined('PHPFOX_UNIT_TEST') && PHPFOX_UNIT_TEST === true)) {
            return $aNotifications;
        }
        $extra = Phpfox::getService('mobile.device')->getExtraConditions('n.type_id');

        $this->database()->select('n_sub.type_id, n_sub.item_id, COUNT(n_sub.notification_id) AS total_extra, MAX(n_sub.time_stamp) AS max_time_stamp')
            ->from(Phpfox::getT("notification"), 'n_sub')
            ->where('n_sub.user_id = ' . Phpfox::getUserId())
            ->group('n_sub.type_id, n_sub.item_id')
            ->union();
        $aGetRows = $this->database()->select('n.*, n_sub.total_extra as total_extra, n.user_id as item_user_id, ' . Phpfox::getUserField())
            ->unionFrom('n_sub')
            ->join(Phpfox::getT("notification"), 'n', 'n_sub.type_id = n.type_id AND n_sub.item_id = n.item_id AND n_sub.max_time_stamp = n.time_stamp')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
            ->where('n.user_id = ' . Phpfox::getUserId() . '' . $extra)
            ->order('n.is_seen ASC, n.time_stamp DESC')
            ->limit($params['page'], $params['limit'])
            ->execute('getSlaveRows');

        $aRows = [];
        foreach ($aGetRows as $aGetRow) {
            $aRows[(int)$aGetRow['notification_id']] = $aGetRow;
        }
        arsort($aRows);

        $aNotifications = [];
        foreach ($aRows as $aRow) {
            $aParts1 = explode('.', $aRow['type_id']);
            $sModule = $aParts1[0];
            if (strpos($sModule, '_')) {
                $aParts = explode('_', $sModule);
                $sModule = $aParts[0];
            }
            $app = null;
            $app_key_name = null;
            if (strpos($aRow['type_id'], '/')) {
                list($app, $app_key_name) = explode('/', $aRow['type_id']);
                if (app()->exists($app)) {
                    $app = app($app);
                }
            }

            if ($app !== null && $app->notifications && isset($app->notifications->{$app_key_name})) {
                $notification = $app->notifications->{$app_key_name};
                \Core\Event::trigger('notification_map_' . $app->id, $app_key_name, $aRow, $notification);
                $aRow['message'] = $this->getLocalization()->translate($notification->message, ['user_full_name' => $aRow['full_name']]);
                $aRow['link'] = url(str_replace(':id', $aRow['item_id'], $notification->url));
                $aRow['custom_icon'] = $notification->icon;

                $aNotifications[] = $aRow;
            } else if (Phpfox::isModule($sModule)) {
                if ((int)$aRow['total_extra'] > 1) {
                    $aExtra = $this->database()->select('n.owner_user_id, n.time_stamp, n.is_seen, u.full_name')
                        ->from(":notification", 'n')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                        ->where('n.type_id = \'' . $this->database()->escape($aRow['type_id']) . '\' AND n.item_id = ' . (int)$aRow['item_id'])
                        ->group('u.user_id', true)
                        ->order('n.time_stamp DESC')
                        ->limit(10)
                        ->execute('getSlaveRows');

                    foreach ($aExtra as $iKey => $aExtraUser) {
                        if ($aExtraUser['owner_user_id'] == $aRow['user_id']) {
                            unset($aExtra[$iKey]);
                        }

                        if (!$aRow['is_seen'] && $aExtraUser['is_seen']) {
                            unset($aExtra[$iKey]);
                        }
                    }

                    if (count($aExtra)) {
                        $aRow['extra_users'] = $aExtra;
                    }
                }

                if (substr($aRow['type_id'], 0, 8) != 'comment_' && !Phpfox::hasCallback($aRow['type_id'], 'getNotification')) {
                    $aCallBack['link'] = '#';
                    $aCallBack['message'] = '2. Notification is missing a callback. [' . $aRow['type_id'] . '::getNotification]';
                } else if (substr($aRow['type_id'], 0, 8) == 'comment_' && substr($aRow['type_id'], 0, 12) != 'comment_feed' && !Phpfox::hasCallback(substr_replace($aRow['type_id'], '', 0, 8), 'getCommentNotification') && Phpfox::isModule(substr_replace($aRow['type_id'], '', 0, 8))) {
                    $aCallBack['link'] = '#';
                    $aCallBack['message'] = 'Notification is missing a callback. [' . substr_replace($aRow['type_id'], '', 0, 8) . '::getCommentNotification]';
                } else {
                    $aCallBack = Phpfox::callback($aRow['type_id'] . '.getNotification', $aRow);
                    if ($aCallBack === false) {
                        if (substr($aRow['type_id'], 0, 8) != 'comment_') {
                            $this->database()->delete(':notification', 'notification_id = ' . (int)$aRow['notification_id']);
                        }

                        continue;
                    }

                    $aRow['final_module'] = \Phpfox_Module::instance()->sFinalModuleCallback;
                    if ($aRow['final_module'] == 'photo') {
                        $aCallBack['link'] = $aCallBack['link'] . 'userid_' . Phpfox::getUserId() . '/';
                    }
                }
                $aNotification = array_merge($aRow, (array)$aCallBack);
                if (!empty($aNotification['message'])) {
                    $aNotification['message'] = Phpfox::getLib('parse.bbcode')->removeTagText($aNotification['message']);
                    $aNotification['message'] = Phpfox::getService('ban.word')->clean($aNotification['message']);
                    $aNotification['message'] = Phpfox::getLib('parse.output')->cleanScriptTag($aNotification['message']);
                    $aNotification['message'] = html_entity_decode($aNotification['message'], ENT_QUOTES);
                }
                $aNotifications[] = $aNotification;
            }
        }

        $this->database()->update(':notification', ['is_seen' => '1'], array_merge([
            'user_id' => Phpfox::getUserId()
        ]));

        return $aNotifications;
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('notification', [
            'title'          => $l->translate('notification'),
            'main_resource'  => new NotificationResource([]),
            'other_resource' => [],
        ]);

        $app->addSetting('menu_more', [
            ['label' => $l->translate('mark_all_read'), 'value' => '@notification/markAllRead'],
            ['label' => $l->translate('delete_all_notifications'), 'value' => '@notification/deleteAllNotifications', 'style' => 'danger']
        ]);

        return $app;
    }

    function approve($params)
    {
        return null;
    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
    }

    public function getUnseenTotal($userId = null, $includeChat = false)
    {
        $userId = $userId != null ? (int)$userId : (int)$this->getUser()->getId();
        $extra = Phpfox::getService('mobile.device')->getExtraConditions('n.type_id');
        $totalUnseen = $this->database()->select('COUNT(*)')
            ->from(':notification', 'n')
            ->where('n.user_id = ' . $userId . ' AND n.is_seen = 0' . $extra)
            ->execute('getSlaveField');

        if ($includeChat && Phpfox::isAppActive('P_ChatPlus')) {
            $server = setting('p_chatplus_server');
            if ($server) {
                $api = rtrim($server, '/') . '/api/v1/chatplus/conversation/unseen?userId=' . $userId;
                $ch = curl_init($api);
                curl_setopt($ch, CURLOPT_URL, $api);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $content = curl_exec($ch);
                curl_close($ch);

                if ($content) {
                    $result = json_decode($content, true);
                    if ($result && !empty($result['success'])) {
                        $totalUnseen = (int)$totalUnseen + (int)$result['data']['total_unseen'];
                    }
                }
            }
        }
        return $totalUnseen;
    }

    public function getScreenSetting($param)
    {
        return null;
    }

    public function getTotalUnSeenByUser($params)
    {
        $id = $this->resolver->setRequired(['id'])->resolveId($params);
        if (empty($id)) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }

        $user = (new UserApi())->loadResourceById((int)$id, false, true);
        if (empty($user['user_id'])) {
            return $this->notFoundError();
        }
        $totalNotification = (int)$this->getUnseenTotal($id);
        $totalFriendRequest = (int)(new FriendRequestApi())->getUnseenTotal($id);
        return $this->success([
            'total_unseen' => (int)($totalNotification + $totalFriendRequest)
        ]);
    }
}