<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 26/4/18
 * Time: 5:57 PM
 */

namespace Apps\Core_MobileApi\Api\Resource;


abstract class AbstractCategoryResource extends ResourceBase
{
    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "category_id";

    public $name;
    public $ordering;

    public $parent_id;

    /**
     * Get category Name
     * @return mixed
     */
    public function getName()
    {
        return $this->parse->cleanOutput($this->getLocalization()->translate($this->name));
    }
}