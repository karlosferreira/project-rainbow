<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;

/**
 * Class FileType
 * Re-upload file type. File will be uploaded to temporary storage
 * @package Apps\Core_MobileApi\Api\Form\Type
 */
class FriendPickerType extends GeneralType
{
    protected $componentName = 'FriendPicker';

    public function getStructure(LocalizationInterface $trans = null)
    {
        $structure = parent::getStructure($trans);
        $structure['api_endpoint'] = UrlUtility::makeApiUrl('friend/search');

        return $structure;
    }

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        $value = $this->getValue();
        if (!$this->isRequiredField() && $value == null) {
            return true;
        }
        if (!empty($value) && is_array($value)) {
            foreach ($value as $val) {
                if (!is_numeric($val)) {
                    return false;
                }
            }
        }

        return true;
    }


    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            "item_type", // Module/App id, required
            "item_id", //Item id, required
            "api_endpoint", //URL call api,
            'returnKeyType',
        ];
    }

    public function getMetaValueFormat()
    {
        return "[1,2,3]"; //Array user_id
    }


    public function getMetaDescription()
    {
        return "Friend picker field";
    }
}