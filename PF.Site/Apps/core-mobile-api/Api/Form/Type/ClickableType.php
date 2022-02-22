<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


class ClickableType extends GeneralType
{
    protected $componentName = 'Clickable';

    public function getMetaDescription()
    {
        return "Clickable link";
    }

    public function getMetaValueFormat()
    {
        return null;
    }

    public function getAvailableAttributes()
    {
        return ['label'];
    }

    public function getServerAttributes()
    {
        return null;
    }

}