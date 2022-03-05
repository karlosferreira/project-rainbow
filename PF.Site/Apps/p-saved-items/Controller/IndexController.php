<?php

namespace Apps\P_SavedItems\Controller;

use Phpfox;
use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class IndexController
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Controller
 */
class IndexController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);

        $requestObject = $this->request();

        //update total items of user's collections after a limit time
        Phpfox::getService('saveditems.collection')->getTotalItemsForCollections();

        if (($savedId = $requestObject->get('saved_id'))) {
            $isUnopened = $requestObject->get('unopened');
            ($isUnopened ? ($link = Phpfox::getService('saveditems.process')->markAsOpened($savedId,
                true)) : ($link = Phpfox::getService('saveditems')->getLinkById($savedId)));
            if (!empty($link)) {
                $this->url()->send($link);
            }
        }

        $collectionId = 0;
        $collection = [];
        if (($requestObject->get('req2') == 'collection' && ($collectionId = $requestObject->get('req3')))) {
            if (($collection = Phpfox::getService('saveditems.collection')->getForEdit($collectionId))
                || ($collection = Phpfox::getService('saveditems.collection')->getByFriend($collectionId))
                || ($collection = Phpfox::getService('saveditems.collection')->getByCommunity($collectionId))) {
                Phpfox::getService('privacy')->check('saveditems', $collection['collection_id'], $collection['user_id'], $collection['privacy']);
                Phpfox::getService('saveditems.collection')->getPermissions($collection);
                $this->template()->assign([
                   'bInCollectionView' => true
                ]);
            } else {
                return Phpfox::getLib('module')->setController('error.404');
            }
        }

        if (empty($collection)) {
            $this->search()->set([
                    'search_tool' => [
                        'table_alias' => 'saveditems',
                        'search' => [
                            'action' => $collectionId ? $this->url()->makeUrl('saved.collection.' . $collectionId) : $this->url()->makeUrl('saved'),
                            'default_value' => _p('saveditems_search_your_saved_items_dot'),
                            'name' => 'text',
                        ],
                        'sort' => [
                            'latest' => ['saveditems.time_stamp', _p('latest')],
                            'oldest' => ['saveditems.time_stamp', _p('saveditems_oldest'), 'ASC'],
                        ],
                        'custom_filters' => [
                            _p('status') => [
                                'param' => 'status',
                                'default_phrase' => _p('saveditems_unopened'),
                                'data' => [
                                    [
                                        'link' => '',
                                        'phrase' => _p('all')
                                    ],
                                    [
                                        'link' => 'unopened',
                                        'phrase' => _p('saveditems_unopened')
                                    ],
                                    [
                                        'link' => 'opened',
                                        'phrase' => _p('saveditems_opened')
                                    ]
                                ]
                            ]
                        ],
                        'no_filters' => [_p('show')]
                    ]
                ]);
        } else {
            $this->search()->set([
                    'search_tool' => [
                        'table_alias' => 'saveditems',
                        'no_filters' => [_p('sort'), _p('show'), _p('when')]
                    ]
                ]);
        }

        $limit = 10;

        list($count, $items) = Phpfox::getService('saveditems')->query($limit);

        // Set pager
        $this->search()->browse()->setPagingMode('loadmore');
        $aParamsPager = array(
            'page' => $this->search()->getPage(),
            'size' => $limit,
            'count' => (int)$count,
            'paging_mode' => $this->search()->browse()->getPagingMode()
        );

        Phpfox::getLib('pager')->set($aParamsPager);

        $canPaging = true;

        if (($count <= ($limit * $this->search()->getPage()))) {
            $canPaging = false;
        }

        $this->template()->setTitle(
            !empty($collection) ? $collection['name'] : _p('saveditems_all_saved_items'))
            ->setBreadCrumb(
                !empty($collection) ? $collection['name'] : _p('saveditems_all_saved_items'),
                $this->url()->current())
            ->setPhrase([
                'saveditems_unsave_from_collection_notice',
                'saveditems_are_you_sure_you_want_to_delete_this_collection'
            ])->assign([
                'items' => $items,
                'searchByCollection' => !empty($collection),
                'searchCollection' => $collection,
                'isListingPage' => true,
                'collections' => Phpfox::getService('saveditems.collection')->getMyCollections(),
                'canPaging' => $canPaging
            ]);

        Phpfox::getService('saveditems')->buildSectionMenu();
    }
}