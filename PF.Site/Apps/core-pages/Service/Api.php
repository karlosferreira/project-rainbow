<?php

namespace Apps\Core_Pages\Service;

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
            'landing_page',
            'time_stamp',
            'total_like',
            'total_comment',
            'privacy',
            'avatar',
            'cover',
            'location',
            'item_type',
            'is_featured',
            'is_sponsor',
            'is_liked',
            'link',
            'info'
        ]);
    }

    /**
     * @description: get info of a page
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function get($params, $messages = [])
    {
        if (!($aPage = Phpfox::getService('pages')->canViewItem($params['id'], true))) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                ['action' => _p('view__l'), 'item' => _p('page')]), true);
        }

        $aItem = $this->getItem($aPage, 'public');
        return $this->success($aItem, $messages);
    }

    /**
     * @description: get info of a poll
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function gets($params, $messages = [])
    {
        $iUserId = $this->request()->get('user_id');
        $sView = $this->request()->get('view');
        $iCategory = $this->request()->get('sub_category');
        $iType = $this->request()->get('category');
        if (!Phpfox::getUserParam('pages.can_view_browse_pages')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('pages')]));
        }
        $bIsProfile = false;
        $aUser = [];
        if ($iUserId) {
            if (!is_numeric($iUserId)) {
                return $this->error(_p('pages_parameter_name_is_invalid', ['name' => 'user_id']));
            }
            $iUserId = (int)$iUserId;
            $aUser = Phpfox::getService('user')->get($iUserId);
            if (!$aUser) {
                return $this->error('The {{ item }} cannot be found.', ['item' => _p('user__l')]);
            }

            if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $iUserId)) {
                return $this->error('Sorry, this content isn\'t available right now');
            }
            $bIsProfile = true;
            $this->search()->setCondition('AND pages.user_id = ' . $aUser['user_id']);
        }

        if ($iType && !is_numeric($iType)) {
            return $this->error(_p('pages_parameter_name_is_invalid', ['name' => 'category']));
        } elseif ($iCategory && !is_numeric($iCategory)) {
            return $this->error(_p('pages_parameter_name_is_invalid', ['name' => 'sub_category']));
        }

        $iCategory = (int)$iCategory;
        $iType = (int)$iType;

        $this->initSearchParams();
        $this->search()->set([
            'type' => 'pages',
            'field' => 'pages.page_id',
            'search_tool' => [
                'table_alias' => 'pages',
                'search' => [
                    'default_value' => _p('search_pages'),
                    'name' => 'search',
                    'field' => 'pages.title'
                ],
                'sort' => [
                    'latest' => ['pages.time_stamp', _p('latest')],
                    'most-liked' => ['pages.total_like', _p('most_liked')]
                ],
                'show' => [$this->getSearchParam('limit')]
            ]
        ]);

        $aBrowseParams = [
            'module_id' => 'pages',
            'alias' => 'pages',
            'field' => 'page_id',
            'table' => Phpfox::getT('pages'),
            'hide_view' => ['pending', 'my'],
        ];

        $aPageIds = Phpfox::getService('pages')->getAllPageIdsOfMember($iUserId);
        if(count($aPageIds)) {
            Phpfox::getService('pages.browse')->pageIds($aPageIds);
        }
        $bCanBrowse = false;
        switch ($sView) {
            case 'my':
                if (Phpfox::isUser()) {
                    $bCanBrowse = true;
                    $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . Phpfox::getUserId());
                }
                break;
            case 'liked':
            case 'all':
                if (Phpfox::isUser()) {
                    $bCanBrowse = true;
                    $sPageIds = '0';
                    if (count($aPageIds)) {
                        $sPageIds = implode(',', $aPageIds);
                    }
                    $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.page_id IN (' . $sPageIds . ')');
                }
                break;
            case 'pending':
                if (Phpfox::isUser() && Phpfox::getUserParam('pages.can_approve_pages')) {
                    $bCanBrowse = true;
                    $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 1');
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
            return $this->error('You don\'t have permission to browse those {{ items }}.', ['items' => _p('pages')]);
        }
        if (!empty($iType)) {
            $aType = Phpfox::getService('pages.type')->getById($iType);
            if (empty($aType['type_id'])) {
                return $this->error(_p('pages_category_not_found'));
            }
        }
        if (!empty($iCategory)) {
            $aCategory = Phpfox::getService('pages.category')->getById($iCategory);
            if (empty($aCategory['category_id'])) {
                return $this->error(_p('pages_sub_category_not_found'));
            }
        }

        if (isset($aType) && isset($aType['type_id'])) {
            $this->search()->setCondition('AND pages.type_id = ' . (int)$aType['type_id']);
        }
        if (isset($aType) && isset($aType['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aType['category_id']);
        } elseif (isset($aCategory) && isset($aCategory['category_id'])) {
            $this->search()->setCondition('AND pages.category_id = ' . (int)$aCategory['category_id']);
        }

        if ($bIsProfile && $sView != 'all') {
            $this->search()->setCondition('AND pages.user_id = ' . (int)$aUser['user_id']);
        }

        $this->search()->browse()->params($aBrowseParams)->execute(function (\Phpfox_Search_Browse $browse) {
            $browse->database()->select('pages_type.name as type_name, ')->join(':pages_type', 'pages_type',
                'pages_type.type_id = pages.type_id AND pages_type.item_type = 0');
        });

        $aPages = $this->search()->browse()->getRows();
        if (Phpfox_Error::isPassed()) {
            $result = [];
            foreach($aPages as $iKey => $aPage) {
                $aPage['pages_image_path'] = $aPage['image_path'];
                $aPage['parent_category_name'] = $aPage['type_name'];
                Phpfox::getService('pages')->extraPageInformation($aPage);
                $result[] = $this->getItem($aPage);
            }
            return $this->success($result);
        }
        return $this->error();
    }

    /**
     * @description: delete a page
     * @param $params
     *
     * @return array|bool
     */
    public function delete($params)
    {
        $this->isUser();

        if (!Phpfox::getService('pages.process')->delete($params['id'])) {
            return $this->error(_p('Cannot {{ action }} this {{ item }}.',
                ['action' => _p('delete__l'), 'item' => _p('page')]), true);
        }

        return $this->success([], [_p('{{ item }} successfully deleted.', ['item' => _p('page')])]);
    }


    /**
     * @description: update a page
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
        $aPage = Phpfox::getService('pages')->getForEdit($params['id']);
        if (Phpfox_Error::isPassed()) {
            if (!empty($iTypeId)) {
                $aType = Phpfox::getService('pages.type')->getById($iTypeId);
                if (empty($aType)) {
                    return $this->error(_p('parent_category_you_are_using_for_new_page_cannot_be_found'));
                }
            } else {
                return $this->error(_p('parent_category_is_required'));
            }
            if (!empty($sVanityUrl)) {
                Phpfox::getLib('parse.input')->allowTitle($sVanityUrl, _p('page_name_not_allowed_please_select_another_name'));
                if (!Phpfox::getService('pages.process')->updateTitle($params['id'], $sVanityUrl)) {
                    return $this->error();
                }
            }
            if (!empty($iCategoryId)) {
                $aCategory = Phpfox::getService('pages.category')->getById($iCategoryId);
                if (empty($aCategory) || $aCategory['type_id'] != $iTypeId) {
                    return $this->error(_p('sub_category_you_are_using_for_new_page_cannot_be_found'));
                }
            }
            if (($iId = Phpfox::getService('pages.process')->update($params['id'], $aVals, $aPage))) {
                return $this->get(['id' => $params['id']], [_p('{{ item }} successfully updated.', ['item' => _p('page')])]);
            }
        }
        return $this->error();
    }

    /**
     * @description: add a page
     * @param $params
     *
     * @return array|bool
     */
    public function post()
    {
        $this->isUser();
        $aVals = $this->request()->getArray('val');
        $iTypeId = isset($aVals['type_id']) ? $aVals['type_id'] : 0;
        $iCategoryId = isset($aVals['category_id']) ? $aVals['category_id'] : 0;
        if (!empty($iTypeId)) {
            $aType = Phpfox::getService('pages.type')->getById($iTypeId);
            if (empty($aType)) {
                return $this->error(_p('parent_category_you_are_using_for_new_page_cannot_be_found'));
            }
        } else {
            return $this->error(_p('parent_category_is_required'));
        }
        if (!empty($iCategoryId)) {
            $aCategory = Phpfox::getService('pages.category')->getById($iCategoryId);
            if (empty($aCategory) || $aCategory['type_id'] != $iTypeId) {
                return $this->error(_p('sub_category_you_are_using_for_new_page_cannot_be_found'));
            }
        }
        if (($iId = Phpfox::getService('pages.process')->add($aVals))) {
            return $this->get(['id' => $iId], [_p('{{ item }} successfully added.', ['item' => _p('page')])]);
        }
        return $this->error();
    }
}