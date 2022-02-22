<?php


namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;

class PrivacyType extends AbstractOptionType implements TransformerInterface
{

    protected $componentName = 'Privacy';

    const EVERYONE = 0;
    const FRIENDS = 1;
    const FRIENDS_OF_FRIENDS = 2;
    const ONLY_ME = 3;
    const CUSTOM = 4;
    const COMMUNITY = 6;

    protected $attrs = [
        'returnKeyType' => 'next'
    ];

    public function getMetaValueFormat()
    {
        return "mixed";
    }

    public function getMetaDescription()
    {
        return "Privacy checkbox";
    }

    public function isValid()
    {
//        if (!parent::isValid()) {
//            return false;
//        }
        if (!$this->isRequiredField() && $this->getValue() == null) {
            return true;
        }
        $values = $this->getValue();
        $isValid = true;

        if (is_array($values)) {
            foreach ($values as $v) {
                if (!is_numeric($v)) {
                    $isValid = false;
                    break;
                }
            }
        } else if (!is_numeric($values)) {
            $isValid = false;
        }

        return $isValid;
    }

    public function transform($value)
    {
        if (is_array($value)) {
            return [
                'privacy'      => self::CUSTOM,
                'privacy_list' => $value
            ];
        }
        return $value;
    }

    public function reverseTransform($value)
    {
        if (isset($value['privacy']) && $value['privacy'] == self::CUSTOM
            && isset($value['resource_name']) && isset($value['id'])) {
            $privacy = \Phpfox::getService('privacy')->get(isset($value['privacy_module']) ? $value['privacy_module'] : $value['resource_name'], $value['id']);
            if (count($privacy)) {
                $result = [];
                foreach ($privacy as $item) {
                    $result[] = (int)$item['friend_list_id'];
                }
                return $result;
            }
        }
        $value = isset($value['privacy']) ? $value['privacy'] : null;
        if ($value !== null && in_array($value, [self::EVERYONE, self::FRIENDS, self::FRIENDS_OF_FRIENDS, self::COMMUNITY])) {
            $privacyOptions = array_column($this->getDefaultPrivacy(), 'value');
            if (count($privacyOptions) && !in_array($value, $privacyOptions)) {
                $value = $privacyOptions[0];
            }
        }
        return $value;
    }

    public function getDefaultPrivacy($disableCustom = true)
    {
        $privacyControls = [];
        if (!$this->getSetting()->getAppSetting('core.friends_only_community', false)) {
            $privacyControls[] = [
                'phrase' => $this->getLocal()->translate('everyone'),
                'label'  => $this->getLocal()->translate('everyone'),
                'value'  => PrivacyType::EVERYONE
            ];
            if (version_compare(\Phpfox::getVersion(), '4.8.6', '>=')) {
                $privacyControls[] = [
                    'phrase' => $this->getLocal()->translate('community'),
                    'label'  => $this->getLocal()->translate('community'),
                    'value'  => PrivacyType::COMMUNITY
                ];
            }
        }
        if (\Phpfox::isModule('friend')) {
            $privacyControls[] = [
                'phrase' => $this->getLocal()->translate('friends'),
                'label'  => $this->getLocal()->translate('friends'),
                'value'  => PrivacyType::FRIENDS
            ];
            $privacyControls[] = [
                'phrase' => $this->getLocal()->translate('friends_of_friends'),
                'label'  => $this->getLocal()->translate('friends_of_friends'),
                'value'  => PrivacyType::FRIENDS_OF_FRIENDS
            ];
        }

        $privacyControls[] = [
            'phrase' => $this->getLocal()->translate('only_me'),
            'label'  => $this->getLocal()->translate('only_me'),
            'value'  => PrivacyType::ONLY_ME
        ];
        if (!$disableCustom) {
            $privacyControls[] = [
                'phrase' => $this->getLocal()->translate('only_me'),
                'label' => $this->getLocal()->translate('custom'),
                'value' => PrivacyType::CUSTOM
            ];
        }
        (($sPlugin = \Phpfox_Plugin::get('mobile.api_form_type_privacy_default_privacy')) ? eval($sPlugin) : false);

        return $privacyControls;
    }
}