<?php
if (Phpfox::isAppActive('P_SavedItems')) {
    /**
     * Define RestAPI services
     */

    $this->apiNames['mobile.saveditems_api'] = \Apps\P_SavedItems\Service\Api\SavedItemsApi::class;
    $this->apiNames['mobile.saveditems_collection_api'] = \Apps\P_SavedItems\Service\Api\SavedItemsCollectionApi::class;

    /**
     * Register Resource Name, This help auto generate routing for the resource
     * Note: resource name must be mapped correctly to resource api
     */

    $this->resourceNames[\Apps\P_SavedItems\Api\Resource\SavedItemsResource::RESOURCE_NAME] = 'mobile.saveditems_api';
    $this->resourceNames[\Apps\P_SavedItems\Api\Resource\SavedItemsCollectionResource::RESOURCE_NAME] = 'mobile.saveditems_collection_api';
}


