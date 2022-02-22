<?php

/**
 * Define RestAPI services
 */

$this->apiNames['mobile.pstatusbg_collection_api'] = \Apps\P_StatusBg\Service\Api\PStatusBgCollectionApi::class;
$this->apiNames['mobile.pstatusbg_background_api'] = \Apps\P_StatusBg\Service\Api\PStatusBgBackgroundApi::class;

/**
 * Register Resource Name, This help auto generate routing for the resource
 * Note: resource name must be mapped correctly to resource api
 */

$this->resourceNames[\Apps\P_StatusBg\Api\Resource\PStatusBgCollectionResource::RESOURCE_NAME] = 'mobile.pstatusbg_collection_api';
$this->resourceNames[\Apps\P_StatusBg\Api\Resource\PStatusBgBackgroundResource::RESOURCE_NAME] = 'mobile.pstatusbg_background_api';
