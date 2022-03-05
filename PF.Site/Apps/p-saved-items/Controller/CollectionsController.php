<?php

namespace Apps\P_SavedItems\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class CollectionsController
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Controller
 */
class CollectionsController extends Phpfox_Component
{
    public function process()
    {

        $bIsProfile = $this->getParam('bIsProfile');
        $aUser = $this->getParam('aUser');
        $sCond = '';
        if ($bIsProfile === true) {
            $iUserId = $aUser['user_id'];
            $sCond = " AND collection.privacy IN(%PRIVACY%)";
            if (Phpfox::getUserId() != $aUser['user_id']) {
                $sCond .= ' AND collection.total_item > 0';
            }
            $this->template()->assign([
                'bIsProfile' => $bIsProfile,
            ]);
        } else {
            $iUserId = Phpfox::getUserId();
            Phpfox::isUser(true);
        }

        $browseParams = [
            'module_id' => 'saveditems.collection',
            'alias' => 'collection',
            'field' => 'collection_id',
            'table' => Phpfox::getT('saved_collection'),
            'hide_view' => ['my'],
        ];

        $this->search()->set([
            'filters' => [
                'display' => [
                    'default' => 10,
                ],
                'sort' => [
                    'alias' => 'collection',
                    'default' => 'updated_time',
                ],
                'sort_by' => [
                    'default' => 'DESC',
                ],
            ],
        ]);

        $this->search()->setCondition($sCond . ' AND (collection.user_id = ' . $iUserId . ' or cf.friend_id = ' . $iUserId . ')');

        $this->search()->browse()->setPagingMode('loadmore');
        $this->search()->browse()->params($browseParams)->execute();

        $collections = $this->search()->browse()->getRows();
        if (!empty($collections)) {
            foreach ($collections as &$collection) {
                $collection['link'] = Phpfox_Url::instance()->makeUrl('saved.collection.' . $collection['collection_id']);
                if (isset($collection['privacy'])) {
                    $sIconClass = 'ico ';
                    switch ((int)$collection['privacy']) {
                        case 0:
                            $sIconClass .= 'ico-globe';
                            break;
                        case 1:
                            $sIconClass .= 'ico-user3-two';
                            break;
                        case 2:
                            $sIconClass .= 'ico-user-man-three';
                            break;
                        case 3:
                            $sIconClass .= 'ico-lock';
                            break;
                        case 4:
                            $sIconClass .= 'ico-gear-o';
                            break;
                        case 6:
                            $sIconClass .= 'ico-user-circle-alt-o';
                            break;
                    }
                    $collection['privacy_icon_class'] = $sIconClass;
                }
            }
        }
        // Set pager
        $aParamsPager = [
            'page' => $this->search()->getPage(),
            'size' => $this->search()->getDisplay(),
            'count' => $this->search()->browse()->getCount(),
            'paging_mode' => $this->search()->browse()->getPagingMode(),
        ];

        Phpfox::getLib('pager')->set($aParamsPager);

        $canPaging = true;

        if (($this->search()->getCount() <= ($this->search()->getDisplay() * $this->search()->getPage()))) {
            $canPaging = false;
        }

        if (!$bIsProfile || Phpfox::getUserId() == $aUser['user_id']) {
            if (Phpfox::getUserParam('saveditems.can_create_collection')) {
                sectionMenu(_p('saveditems_create_collection'), '#',
                    'onclick="tb_show(\'' . _p('saveditems_new_collection') . '\', $.ajaxBox(\'saveditems.showCreateCollectionPopup\')); return false;"');
            }
        }

        $sTitle = $bIsProfile ? _p('saveditems_users_collection', ['users' => $aUser['full_name']]) : _p('saveditems_my_collections');
        $sUrl = $bIsProfile ? $aUser['user_name'] . '.saveditems' : 'saved.collections';

        $this->template()
            ->setTitle($sTitle)
            ->setBreadCrumb($sTitle,
                $this->url()->makeUrl($sUrl))
            ->setPhrase([
                'saveditems_new_collection',
                'saveditems_are_you_sure_you_want_to_delete_this_collection',
            ])->assign([
                'collections' => $collections,
                'defaultPhoto' => Phpfox::getParam('saveditems.default_collection_photo'),
                'hasCollection' => !empty($this->search()->browse()->getCount()),
                'canPaging' => $canPaging,
            ]);
        if (!$bIsProfile) {
            Phpfox::getService('saveditems')->buildSectionMenu();
        }
    }
}