<?php

namespace Apps\PHPfox_Groups\Service;

use Core\Api\ApiServiceBase;
use Phpfox;
use Phpfox_Error;

class Api extends ApiServiceBase
{
    public function __construct()
    {
        $this->setPublicFields([
            'page_id',
            'view_id',
            'title',
            'category',
            'sub_category',
            'user_id',
            'reg_method',
            'total_like',
            'total_comment',
            'privacy',
            'avatar',
            'cover',
            'location',
            'item_type',
            'category',
            'is_featured',
            'is_sponsor',
            'is_liked',
            'link',
            'info'
        ]);
    }

    public function gets()
    {
        $iUserId = $this->request()->getInt('user_id');
        $sView = $this->request()->get('view');
        $iType = $this->request()->getInt('category');
        $iCategory = $this->request()->getInt('sub_category');
        if (!Phpfox::getUserParam('groups.pf_group_browse')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('groups')]));
        }
        $bIsProfile = false;
        $aUser = [];
        if ($iUserId) {
            $aUser = Phpfox::getService('user')->get($iUserId);
            if (!$aUser) {
                return $this->error(_p('The {{ item }} cannot be found.', ['item' => _p('user__l')]));
            }

            if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $iUserId)) {
                return $this->error(_p('Sorry, this content isn\'t available right now'));
            }
            $bIsProfile = true;
            $this->search()->setCondition('AND pages.user_id = ' . $aUser['user_id']);
        }
        $this->initSearchParams();
        $this->search()->set([
            'type' => 'groups',
            'field' => 'pages.page_id',
            'search_tool' => [
                'table_alias' => 'pages',
                'search' => [
                    'default_value' => _p('Search groups'),
                    'name' => 'search',
                    'field' => 'pages.title'
                ],
                'sort' => [
                    'latest' => ['pages.time_stamp', _p('Latest')],
                    'most-liked' => ['pages.total_like', _p('Most Popular')],
                ],
                'show' => [$this->getSearchParam('limit')]
            ]
        ]);

        $aBrowseParams = [
            'module_id' => 'groups',
            'alias' => 'pages',
            'field' => 'page_id',
            'table' => Phpfox::getT('pages'),
            'hide_view' => ['pending', 'my'],
            'select' => 'pages_type.name as type_name, '
        ];

        $aGroupIds = Phpfox::getService('groups')->getAllGroupIdsOfMember($iUserId);
        if (count($aGroupIds)) {
            Phpfox::getService('groups.browse')->groupIds($aGroupIds);
        }
        $bCanBrowse = false;
        switch ($sView) {
            case 'my':
                Phpfox::isUser(true);
                $bCanBrowse = true;
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . Phpfox::getUserId());
                break;
            case 'joined':
            case 'all':
                if (Phpfox::isUser()) {
                    $sGroupIds = '0';
                    $bCanBrowse = true;
                    if (count($aGroupIds)) {
                        $sGroupIds = implode(',', $aGroupIds);
                    }
                    $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.page_id IN (' . $sGroupIds . ')');
                }
                break;
            case 'pending':
                if (Phpfox::isUser()) {
                    if (Phpfox::getService('groups.facade')->getUserParam('can_approve_pages')) {
                        $bCanBrowse = true;
                        $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 1');
                    }
                }
                break;
            default:
                if ($sView != 'friend' || Phpfox::isUser()) {
                    $bCanBrowse = true;
                }
                $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0');
                break;
        }
        if (!$bCanBrowse) {
            return $this->error(_p('You don\'t have permission to browse those {{ items }}.', ['items' => _p('groups')]));
        }
        if (!empty($iType)) {
            $aType = Phpfox::getService('groups.type')->getById($iType);
        }
        if (!empty($iCategory)) {
            $aCategory = Phpfox::getService('groups.category')->getById($iCategory);
        }

        if (isset($aType) && isset($aType['type_id'])) {
            $this->search()->setCondition('AND pages.type_id = ' . (int)$aType['type_id']);
        }

        if (isset($aType) && isset($aType['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aType['category_id']);
        } elseif (isset($aType) && isset($aCategory) && isset($aCategory['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aCategory['category_id']);
        }

        if ($bIsProfile) {
            if ($sView != 'all') {
                $this->search()->setCondition('AND pages.user_id = ' . (int)$aUser['user_id']);
            }
            if ($aUser['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('core.can_view_private_items')) {
                $this->search()->setCondition('AND pages.reg_method <> 2');
            }
        }

        if ($sView != 'pending') {
            $this->search()->setCondition(Phpfox::callback('groups.getExtraBrowseConditions', 'pages'));
        }
        $this->search()->browse()->params($aBrowseParams)->execute(function (\Phpfox_Search_Browse $browse) {
            $browse->database()->join(':pages_type', 'pages_type',
                'pages_type.type_id = pages.type_id AND pages_type.item_type = 1');
        });
        $aPages = $this->search()->browse()->getRows();

        foreach ($aPages as $iKey => $aPage) {
            $aPages[$iKey]['join_requested'] = Phpfox::getService('groups')->joinGroupRequested($aPage['page_id']);
        }

        if (Phpfox_Error::isPassed()) {
            $result = [];
            foreach($aPages as $iKey => $aPage) {
                $aPage['pages_image_path'] = $aPage['image_path'];
                $aPage['parent_category_name'] = $aPage['type_name'];
                Phpfox::getService('groups')->extraGroupInformation($aPage);
                $result[] = $this->getItem($aPage);
            }
            return $this->success($result);
        }

        return $this->error();
    }

    /**
     * @description: get info of a group
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function get($params, $messages = [])
    {
        if (!($aGroup = Phpfox::getService('groups')->canViewItem($params['id'], true))) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                ['action' => _p('view__l'), 'item' => _p('Group')]), true);
        }

        $aItem = $this->getItem($aGroup, 'public');
        return $this->success($aItem, $messages);
    }

    /**
     * @description: add a group
     *
     * @return array|bool
     */
    public function post()
    {
        $this->isUser();

        $aVals = $this->request()->getArray('val');
        $iTypeId = isset($aVals['type_id']) ? (int)$aVals['type_id'] : 0;
        $iCategoryId = isset($aVals['category_id']) ? $aVals['category_id'] : 0;
        if (!empty($iTypeId)) {
            $aType = Phpfox::getService('groups.type')->getById($iTypeId);
            if (empty($aType)) {
                return $this->error(_p('group_category_cannot_be_found'));
            }
        } else {
            return $this->error(_p('group_category_is_required'));
        }
        if (!empty($iCategoryId)) {
            $aCategory = Phpfox::getService('groups.category')->getById($iCategoryId);
            if (empty($aCategory) || $aCategory['type_id'] != $iTypeId) {
                return $this->error(_p('sub_category_you_are_using_for_new_group_cannot_be_found'));
            }
        }
        if (($iId = Phpfox::getService('groups.process')->add($aVals))) {
            return $this->get(['id' => $iId], [_p('{{ item }} successfully added.', ['item' => _p('Group')])]);
        }
        return $this->error();
    }

    /**
     * @description: update a group
     * @param $params
     *
     * @return array|bool
     */
    public function put($params)
    {
        $this->isUser();

        $aVals = $this->request()->getArray('val');

        $iTypeId = isset($aVals['type_id']) ? $aVals['type_id'] : 0;
        $iCategoryId = isset($aVals['category_id']) ? $aVals['category_id'] : 0;
        $sVanityUrl = isset($aVals['vanity_url']) ? $aVals['vanity_url'] : '';

        $aGroup = Phpfox::getService('groups')->getForEdit($params['id']);
        if (Phpfox_Error::isPassed()) {
            if (!empty($iTypeId)) {
                $aType = Phpfox::getService('groups.type')->getById($iTypeId);
                if (empty($aType)) {
                    return $this->error(_p('group_category_cannot_be_found'));
                }
            } else {
                return $this->error(_p('group_category_is_required'));
            }

            if (!empty($iCategoryId)) {
                $aCategory = Phpfox::getService('groups.category')->getById($iCategoryId);
                if (empty($aCategory) || $aCategory['type_id'] != $iTypeId) {
                    return $this->error(_p('sub_category_you_are_using_for_new_group_cannot_be_found'));
                }
            }

            if (!empty($sVanityUrl)) {
                Phpfox::getLib('parse.input')->allowTitle($sVanityUrl, _p('Group name not allowed. Please select another name.'));
                if (!Phpfox::getService('groups.process')->updateTitle($params['id'], $sVanityUrl)) {
                    return $this->error();
                }
            }

            if (($iId = Phpfox::getService('groups.process')->update($params['id'], $aVals, $aGroup))) {
                return $this->get(['id' => $params['id']], [_p('{{ item }} successfully updated.', ['item' => _p('Group')])]);
            }
        }
        return $this->error();
    }

    /**
     * @description: delete a group
     * @param $params
     *
     * @return array|bool
     */
    public function delete($params)
    {
        $this->isUser();

        if (!Phpfox::getService('groups.process')->delete($params['id'])) {
            return $this->error(_p('Cannot {{ action }} this {{ item }}.',
                ['action' => _p('delete__l'), 'item' => _p('Group')]), true);
        }

        return $this->success([], [_p('{{ item }} successfully deleted.', ['item' => _p('Group')])]);
    }
}
