<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class MultiFileType extends GeneralType
{

    protected $componentName = "MultiFile";

    const PROPERTY_NEW = "new";
    const PROPERTY_REMOVE = "remove";
    const PROPERTY_ORDER = "order";
    const PROPERTY_DEFAULT = "default";
    protected $multiple = true;

    public function getStructure(LocalizationInterface $trans = null)
    {
        $structure = parent::getStructure($trans);
        if (!$this->getAttr('upload_endpoint')) {
            $structure['upload_endpoint'] = UrlUtility::makeApiUrl('file');
        }
        return $structure;
    }

    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            'file_type', // (photo, video, file)
            'item_type', // Module/App id
            'upload_endpoint', // URL for upload/delete Temporary File by ID,
            'current_files',  // {[id, url, default], [id, url, default]}
            'min_files',
            'multiple',
            'max_files',
            'returnKeyType',
            'allow_temp_default' //Enable to allow user set a new upload image as default
        ];
    }

    public function getMetaValueFormat()
    {
        return strtr("{':new' : [4,5,6], ':remove' : [1,2,3], ':order' : [3,2,1], ':default' : 2}", [
            ':new'     => self::PROPERTY_NEW,
            ':remove'  => self::PROPERTY_REMOVE,
            ':order'   => self::PROPERTY_ORDER,
            ':default' => self::PROPERTY_DEFAULT
        ]); // new : [tem_file_id], remove : [object_id]
    }

    public function getMetaDescription()
    {
        return "Multiple file upload";
    }

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        $value = $this->getValue();
        if ($this->isRequiredField() && empty($value[self::PROPERTY_NEW])) {
            //Must have photo if required
            return false;
        }
        if (!empty($value)) {
            $valid = new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC);

            if ((!empty($value[self::PROPERTY_NEW]) && !$valid->validate($value[self::PROPERTY_NEW]))
                || (!empty($value[self::PROPERTY_REMOVE]) && !$valid->validate($value[self::PROPERTY_REMOVE]))
                || (!empty($value[self::PROPERTY_ORDER]) && !$valid->validate($value[self::PROPERTY_ORDER]))
                || (!empty($value[self::PROPERTY_DEFAULT]) && !is_numeric($value[self::PROPERTY_DEFAULT]))) {

                return false;
            }
        }
        return true;
    }
}