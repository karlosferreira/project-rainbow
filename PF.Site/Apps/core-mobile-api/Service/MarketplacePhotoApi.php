<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_Marketplace\Service\Category\Category;
use Apps\Core_Marketplace\Service\Marketplace;
use Apps\Core_Marketplace\Service\Process;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Marketplace\MarketplacePhotoForm;
use Apps\Core_MobileApi\Api\Resource\MarketplacePhotoResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Security\Marketplace\MarketplaceAccessControl;
use Phpfox;

/**
 * Class MarketplaceCategoryApi
 * @package Apps\Core_MobileApi\Service
 */
class MarketplacePhotoApi extends AbstractResourceApi
{
    /**
     * @var Category
     */
    private $categoryService;
    /**
     * @var Marketplace
     */
    private $marketplaceService;

    /**
     * @var Process
     */
    private $processService;

    public function __construct()
    {
        parent::__construct();
        $this->categoryService = Phpfox::getService('marketplace.category');
        $this->processService = Phpfox::getService('marketplace.process');
        $this->marketplaceService = Phpfox::getService('marketplace');
    }

    function findAll($params = [])
    {
        return null;
    }

    function findOne($params)
    {
        return null;
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveId($params);
        /** @var MarketplacePhotoForm $form */
        $form = $this->createForm(MarketplacePhotoForm::class, [
            'title'  => 'manage_photos',
            'action' => UrlUtility::makeApiUrl('marketplace-photo/:id', $editId),
            'method' => 'PUT'
        ]);
        /** @var MarketplaceResource $listing */
        $listing = NameResource::instance()->getApiServiceByResourceName(MarketplaceResource::RESOURCE_NAME)->loadResourceById($editId);
        if (empty($listing)) {
            return $this->notFoundError();
        }
        $images = $this->getImages($editId, $listing['image_path']);
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::EDIT, MarketplaceResource::populate($listing));
        $form->setMaxFiles($this->getLimit($editId));
        if (!empty($images)) {
            $form->assignValues($images);
        }

        return $this->success($form->getFormStructure());
    }

    private function getImages($id, $mainPath)
    {
        $images = $this->marketplaceService->getImages($id);
        $result = [];
        if (!empty($images)) {
            foreach ($images as $key => $image) {
                if (!empty($mainPath) && $image['image_path'] == $mainPath) {
                    $image['main'] = true;
                } else {
                    $image['main'] = false;
                }
                $result[] = MarketplacePhotoResource::populate($image)->toArray();
            }
        }
        return $result;
    }

    private function getLimit($id)
    {
        $iTotalImage = $this->marketplaceService->countImages($id);
        return $this->setting->getUserSetting('marketplace.total_photo_upload_limit') - $iTotalImage;
    }

    function create($params)
    {
        // TODO: Implement create() method.
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var MarketplacePhotoForm $form */
        $form = $this->createForm(MarketplacePhotoForm::class);
        $listing = NameResource::instance()->getApiServiceByResourceName(MarketplaceResource::RESOURCE_NAME)->loadResourceById($id, true);
        if (empty($listing)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::EDIT, $listing);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => (int)$id,
                    'resource_name' => MarketplaceResource::populate([])->getResourceName()
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        if (empty($values['files'])) {
            //Do nothing
            return true;
        }
        $files = $values['files'];
        $newIds = [];
        $lastNewId = 0;
        if (!empty($files['new'])) {
            $maxFiles = $this->getLimit($id);
            if (isset($files['remove'])) {
                $maxFiles = $maxFiles + count($files['remove']);
            }
            //Check limit
            if ($maxFiles < count($files['new'])) {
                return $this->permissionError($this->getLocalization()->translate('maximum_photos_you_can_upload_is_number', ['number' => $maxFiles]));
            }
            foreach ($files['new'] as $tempId) {
                $temp = Phpfox::getService('core.temp-file')->get($tempId);
                if (!empty($temp)) {
                    $lastNewId = $newIds[] = db()->insert(':marketplace_image', [
                        'listing_id' => $id,
                        'image_path' => $temp['path'],
                        'server_id'  => $temp['server_id']
                    ]);
                }
                Phpfox::getService('core.temp-file')->delete($tempId);
            }
        }
        if (!empty($files['order'])) {
            $i = 1;
            if (count($newIds)) {
                $orderList = array_merge($files['order'], $newIds);
            } else {
                $orderList = $files['order'];
            }
            foreach ($orderList as $imageId) {
                $oImage = $this->loadResourceById($imageId, false, $id);
                if ($oImage) {
                    $this->database()->update(':marketplace_image', ['ordering' => $i], 'image_id = ' . (int)$imageId);
                    $i++;
                }
            }
        }
        if (!empty($files['remove'])) {
            //Remove image
            foreach ($files['remove'] as $imageId) {
                $oImage = $this->loadResourceById($imageId, false, $id);
                //only download image of this listing
                if ($oImage) {
                    $this->processService->deleteImage($imageId);
                }
                //Can't set removed photo as default
                if ($files['default'] == $imageId) {
                    $files['default'] = 0;
                }
            }
        }
        if (!empty($files['default'])) {
            $oImage = $this->loadResourceById($files['default'], false, $id);
            if (!empty($oImage)) {
                $this->database()->update(':marketplace', ['image_path' => $oImage['image_path'], 'server_id' => $oImage['server_id']], 'listing_id = ' . (int)$id);
            }
        } else if ($lastNewId) {
            //Check set default
            $this->processService->setDefault($lastNewId);
        }
        return true;
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    function delete($params)
    {

    }

    function loadResourceById($id, $returnResource = false, $listingId = null)
    {
        return $this->database()->select('*')
            ->from(':marketplace_image')
            ->where('image_id = ' . (int)$id . ($listingId != null ? ' AND listing_id =' . (int)$listingId : ''))
            ->execute('getSlaveRow');
    }

    public function processRow($item)
    {
        return MarketplacePhotoResource::populate($item)->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MarketplaceAccessControl($this->getSetting(), $this->getUser());
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }
}