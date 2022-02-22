<?php

namespace Apps\Core_Pages\Service;

use Phpfox;
use Phpfox_Pages_Facade;

class Facade extends Phpfox_Pages_Facade
{

    /**
     * @return \Phpfox_Pages_Pages
     */
    public function getItems()
    {
        return new Pages();
    }

    /**
     * @return \Phpfox_Pages_Category
     */
    public function getCategory()
    {
        return new Category();
    }

    /**
     * @return \Phpfox_Pages_Process
     */
    public function getProcess()
    {
        return new Process();
    }

    /**
     * @return \Phpfox_Pages_Type
     */
    public function getType()
    {
        return new Type();
    }

    /**
     * @return \Phpfox_Pages_Browse
     */
    public function getBrowse()
    {
        return new Browse();
    }

    /**
     * @return \Apps\Core_Pages\Service\Callback
     */
    public function getCallback()
    {
        return new Callback();
    }

    public function getItemType()
    {
        return 'pages';
    }

    public function getItemTypeId()
    {
        return 0;
    }

    public function getPhrase($name, $params = [], $languageId = '')
    {
        if (empty($params)) {
            return _p('' . $name, [], $languageId);
        }

        return _p('' . $name, $params, $languageId);

    }

    public function getPhraseName($name){
        return $name;
    }

    public function getUserParam($name)
    {
        return Phpfox::getUserParam('pages.' . $name);
    }
}
