<?php

namespace Apps\P_SavedItems\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;
use Phpfox_Url;

class AllCollectionsController extends Phpfox_Component
{
    public function process()
    {

        Phpfox::isUser(true);

        $iPage = $this->request()->get('page', 1);
        $iLimit = Phpfox::getParam('core.items_per_page', 20);
        $collections = Phpfox::getService('saveditems.collection')->getAllCollectionsOfUser($iPage, $iLimit);
        $aPager = [
            'page' => $iPage,
            'size' => $iLimit,
            'count' => count($collections),
            'paging_mode' => "loadmore",
        ];
        Phpfox_Pager::instance()->set($aPager);

        if (empty($collections) && !Phpfox::getUserParam('saveditems.can_create_collection')) {
            $this->url()->send('saved');
        }

        if (!empty($collections)) {
            foreach ($collections as &$collection) {
                Phpfox::getService('saveditems.collection')->getPermissions($collection);
                $collection['link'] = Phpfox_Url::instance()->makeUrl('saved.collection.' . $collection['collection_id']);
                if (isset($collection['privacy'])) {
                    $sIconClass = 'ico ';
                    switch ((int)$collection['privacy']) {
                        case 0:
                            $sIconClass .= 'ico-globe';
                            break;
                        case 3:
                            $sIconClass .= 'ico-lock';
                            break;
                    }
                    $collection['privacy_icon_class'] = $sIconClass;
                }
            }
        }

        $canPaging = true;

        if ((count($collections) <= $iLimit * $iPage)) {
            $canPaging = false;
        }

        $this->template()
            ->setTitle(_p('saveditems_all_collections'))
            ->setBreadCrumb(_p('saveditems_all_collections'),
                $this->url()->makeUrl('saved.all-collections'))
            ->setPhrase([
                'saveditems_new_collection',
                'saveditems_are_you_sure_you_want_to_delete_this_collection',
            ])->assign([
                'collections' => $collections,
                'defaultPhoto' => Phpfox::getParam('saveditems.default_collection_photo'),
                'hasCollection' => count($collections),
                'canPaging' => $canPaging,
            ]);

        Phpfox::getService('saveditems')->buildSectionMenu();
    }
}