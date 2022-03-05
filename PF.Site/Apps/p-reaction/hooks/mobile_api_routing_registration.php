<?php

/**
 * Define RestAPI services
 */

$this->apiNames['mobile.preaction_api'] = Apps\P_Reaction\Service\Api\PReactionApi::class;

/**
 * Register Resource Name, This help auto generate routing for the resource
 * Note: resource name must be mapped correctly to resource api
 */

$this->resourceNames[Apps\P_Reaction\Api\Resource\PReactionResource::RESOURCE_NAME] = 'mobile.preaction_api';
