<?php

namespace Apps\Core_MobileApi\Controller;


use Apps\Core_MobileApi\Service\NameResource;
use Phpfox_Component;

class DocsController extends Phpfox_Component
{
    const VERSION = "mobile";

    public function process()
    {
        $allApi = (new NameResource())->getRoutingTable(self::VERSION);
        $this->buildApiDocument($allApi);

        d($allApi);
        die;
    }

    private function buildApiDocument(&$apiTable)
    {
        foreach ($apiTable as $route => $apiMap) {
            // TODO: Generate smart structure for building Document at view
        }
    }
}