<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 3:42 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;

class HierarchyType extends GeneralType implements TransformerInterface
{
    protected $componentName = 'Choice';

    protected $multiple = false;

    protected $valueType = "array";

    private $children_map = [];

    private $parents = [];

    const FIELD_ID = 'category_id';
    const FIELD_NAME = 'name';
    const FIELD_SUB = 'sub';
    const FIELD_TYPE = 'integer';

    private $field_maps;

    public function __construct()
    {
        $this->field_maps = [
            'field_id'   => self::FIELD_ID,
            'field_type' => self::FIELD_TYPE,
            'field_name' => self::FIELD_NAME,
            'field_sub'  => self::FIELD_SUB
        ];
    }


    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this->getAttr('multiple');
    }

    public function setAttrs($attrs)
    {
        if (isset($attrs['field_maps'])) {
            $this->getFieldMaps($attrs['field_maps']);
            unset($attrs['field_maps']);
        }
        if (isset($attrs['rawData'])) {
            //Get from rawData
            foreach ($attrs['rawData'] as $rawData) {
                $this->parents[] = [
                    'value' => $this->getFieldValue($rawData[$this->field_maps['field_id']]),
                    'label' => $this->getLocal()->translate($rawData[$this->field_maps['field_name']]),
                ];
                $this->getChilds($rawData);
            }
            $attrs['options'] = $this->parents;
            $attrs['suboptions'] = $this->children_map;
            //Remove rawData
            unset($attrs['rawData']);
        }

        $attrs['multiple'] = (isset($attrs['multiple']) && $attrs['multiple'] ? true : false);

        if (!isset($attrs['value_type'])) {
            $attrs['value_type'] = $this->valueType;
        }

        $this->attrs = $attrs;
        return $this;
    }

    private function getChilds($data)
    {
        if (isset($data[$this->field_maps['field_sub']])) {
            foreach ($data[$this->field_maps['field_sub']] as $sub) {
                $this->children_map[$data[$this->field_maps['field_id']]][] = [
                    'value' => $this->getFieldValue($sub[$this->field_maps['field_id']]),
                    'label' => $this->getLocal()->translate($sub[$this->field_maps['field_name']])
                ];
                $this->getChilds($sub);
            }
        }
        return $this;
    }

    private function getFieldMaps($maps = null)
    {
        if ($maps !== null) {
            $this->field_maps = array_merge($this->field_maps, $maps);
        }
        return $this;
    }

    private function getFieldValue($value)
    {
        switch ($this->field_maps['field_type']) {
            case 'integer':
                $value = (int)$value;
                break;
            default:
                break;
        }
        return $value;
    }

    public function transform($value)
    {
        return [
            $this->getName() => $value
        ];
    }

    public function reverseTransform($data)
    {
        $value = [];
        $vals = isset($data[$this->getName()]) ? $data[$this->getName()] : null;
        if (!empty($vals) && is_array($vals)) {
            foreach ($vals as $key => $datum) {
                if (isset($datum['id'])) {
                    $value[] = $datum['id'];
                }
            }
        }
        return $value;
    }

}