<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7_4\Service;

use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Service\PhotoApi as BasePhotoApi;
use Phpfox;

class PhotoApi extends BasePhotoApi implements ActivityFeedInterface
{
    public function getFeedDisplay($feed, $item)
    {
        $extraPhotoId = intval($item['extra_photo_id']);
        $sFeedTable = 'feed';
        $iFeedId = isset($feed['feed_id']) ? $feed['feed_id'] : 0;
        $cache = storage()->get('photo_parent_feed_' . $iFeedId);
        if ($cache) {
            $iFeedId = $cache->value;
        } elseif (!empty($item['photo_id'])) {
            $parentModule = db()->select('module_id, group_id')
                ->from(':photo')
                ->where([
                    'photo_id' => $item['photo_id']
                ])->executeRow();
            if (!empty($parentModule['module_id'])
                && !empty($parentModule['group_id'])
                && Phpfox::isModule($parentModule['module_id'])
                && Phpfox::hasCallback($parentModule['module_id'], 'getFeedDetails')) {
                $feedDetail = Phpfox::callback($parentModule['module_id'] . '.getFeedDetails', $parentModule['group_id']);
                if (!empty($feedDetail['table_prefix'])) {
                    $iFeedId = (int)db()->select('feed_id')
                        ->from(':' . $feedDetail['table_prefix'] . 'feed')
                        ->where([
                            'type_id' => 'photo',
                            'item_id' => $item['photo_id']
                        ])->executeField();
                }
            }
        }

        if (!empty($feed['parent_feed_id'])) {
            $iFeedId = $feed['parent_feed_id'];
        }
        $aPhotos = [];
        $limitPhoto = 4;
        $totalPhoto = 1;
        $aPhotoIte = db()->select('p.photo_id, p.module_id, p.group_id')
            ->from(':photo', 'p')
            ->where('p.photo_id = ' . (int)$feed['item_id'])
            ->execute('getSlaveRow');
        if (empty($aPhotoIte['photo_id'])) {
            return [];
        }
        if (isset($aPhotoIte['module_id']) && $aPhotoIte['module_id'] && !Phpfox::isModule($aPhotoIte['module_id'])) {
            return [];
        }

        (($sPlugin = \Phpfox_Plugin::get('photo.component_service_callback_getactivityfeed__get_item_before')) ? eval($sPlugin) : false);

        $aSizes = Phpfox::getService('photo')->getPhotoPicSizes();

        if ($extraPhotoId) {
            $totalPhoto = $this->database()->select('count(*)')
                ->from(Phpfox::getT('photo_feed'), 'pfeed')
                ->join(Phpfox::getT('photo'), 'p',
                    'p.photo_id = pfeed.photo_id' . (!empty($feed['module_id']) ? ' AND p.module_id = \'' . db()->escape($feed['module_id']) . '\'' : '') . ' AND pfeed.feed_table = \'' . $sFeedTable
                    . '\'')
                ->where('pfeed.feed_id = ' . (isset($iFeedId) ? (int)$iFeedId : 0) . ' AND p.album_id = ' . (int)$item['album_id'])
                ->executeField();

            $totalPhoto = intval($totalPhoto) + 1;

            db()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature, pi.width, pi.height')
                ->from(':photo', 'p')
                ->join(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
                ->where(['p.photo_id' => $item['photo_id']])
                ->union();
            db()->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature, pi.width, pi.height')
                ->from(Phpfox::getT('photo_feed'), 'pfeed')
                ->join(Phpfox::getT('photo'), 'p',
                    'p.photo_id = pfeed.photo_id' . (!empty($item['module_id']) ? ' AND p.module_id = \'' . db()->escape($item['module_id']) . '\'' : '') . ' AND pfeed.feed_table = \'' . $sFeedTable . '\'')
                ->join(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
                ->where('pfeed.feed_id = ' . (isset($iFeedId) ? (int)$iFeedId : 0) . ' AND pfeed.feed_time = 0 AND p.album_id = ' . (int)$item['album_id'])
                ->union()
                ->unionFrom('main_photo');
            $aRows = db()->select('*')
                ->limit($limitPhoto)
                ->order('main_photo.photo_id DESC')
                ->group('main_photo.photo_id')
                ->execute('getSlaveRows');

            $aPhotos = array_map(function ($aPhoto) use ($aSizes) {
                if ($aPhoto['mature'] == 0 || ($this->getUser()->getId() && $this->getSetting()->getUserSetting('photo.photo_mature_age_limit') <= $this->getUser()->getAge()) || $aPhoto['user_id'] == Phpfox::getUserId()) {
                    $photoUrl = Image::createFrom([
                        'file' => $aPhoto['destination'],
                        'server_id' => $aPhoto['server_id'],
                        'path' => 'photo.url_photo'
                    ], $aSizes, false)->toArray();

                } else {
                    $photoUrl = Phpfox::getLib('image.helper')->display([
                        'theme'      => 'misc/mature.jpg',
                        'return_url' => true
                    ]);
                }
                return [
                    'id'            => intval($aPhoto['photo_id']),
                    'resource_name' => 'photo',
                    'module_name'   => 'photo',
                    'href'          => "photo/{$aPhoto['photo_id']}",
                    'mature'        => intval($aPhoto['mature']),
                    'width'         => isset($aPhoto['width']) ? (int)$aPhoto['width'] : 0,
                    'height'        => isset($aPhoto['height']) ? (int)$aPhoto['height'] : 0,
                    'user'          => [
                        'id'            => intval($aPhoto['user_id']),
                        'resource_name' => 'user'
                    ],
                    'image'         => $photoUrl,
                ];
            }, $aRows);
        } else {
            if (($item['mature'] == 0 || (($item['mature'] == 1 || $item['mature'] == 2) && $this->getUser()->getId() && $this->getSetting()->getUserSetting('photo.photo_mature_age_limit') <= $this->getUser()->getAge())) || $item['user_id'] == Phpfox::getUserId()) {
                $itemUrl = Image::createFrom([
                    'file' => $item['destination'],
                    'server_id' => $item['server_id'],
                    'path' => 'photo.url_photo'
                ], $aSizes, false)->toArray();
            } else {
                $itemUrl = Phpfox::getLib('image.helper')->display([
                    'theme'      => 'misc/mature.jpg',
                    'return_url' => true
                ]);
            }
            $photoInfo = $this->database()->select('width, height')->from(':photo_info')->where('photo_id =' . (int)$item['photo_id'])->execute('getRow');

            $aItemPhoto = [
                'id'            => intval($item['photo_id']),
                'module_name'   => 'photo',
                'resource_name' => 'photo',
                'mature'        => intval($item['mature']),
                'width'         => isset($photoInfo['width']) ? (int)$photoInfo['width'] : 0,
                'height'        => isset($photoInfo['height']) ? (int)$photoInfo['height'] : 0,
                'user'          => [
                    'id'            => $item['user_id'],
                    'resource_name' => 'user'
                ],
                'image'         => $itemUrl,
            ];

            array_unshift($aPhotos, $aItemPhoto);
        }

        return [
            'module_name'   => 'photo',
            'resource_name' => PhotoResource::RESOURCE_NAME,
            'total_photo'   => $totalPhoto,
            'module_id'     => $aPhotoIte['module_id'],
            'item_id'       => intval($aPhotoIte['group_id']),
            'feed_item_id'  => intval($feed['item_id']),
            'feed_id'       => intval($iFeedId),
            'remain_photo'  => $totalPhoto > $limitPhoto ? $totalPhoto - $limitPhoto : 0,
            'photos'        => $aPhotos
        ];
    }
}