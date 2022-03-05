<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 19/6/18
 * Time: 1:42 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;

class AttachmentType extends GeneralType
{

    protected $componentName = "Attachment";

    public function getStructure(LocalizationInterface $trans = null)
    {
        $structure = parent::getStructure($trans);
        $structure['upload_endpoint'] = UrlUtility::makeApiUrl('attachment');
        return $structure;
    }

    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            'item_id',
            'item_type',
            'current_attachments',
            'upload_endpoint',
            'returnKeyType'
        ];
    }

    public function getMetaValueFormat()
    {
        return "[1,2,3]";
    }

    public function getMetaDescription()
    {
        return "Upload/Manage attachment";
    }

}