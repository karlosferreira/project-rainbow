<?php

namespace Apps\Core_MobileApi\Api\Mapping;

use Apps\Core_MobileApi\Api\Exception\ErrorException;

/**
 * Class ResourceMetadata
 * @package Apps\Core_MobileApi\Api\Mapping
 *
 * Use for mapping Database schema to Resource object
 *
 * Rule:
 *
 * * Each resource has ResourceMetadata definition itself
 * * This used for when set value for each field or manually called at sometime
 *
 */
class ResourceMetadata
{
    const STRING = "string";
    const INTEGER = "integer";
    const FLOAT = "float";
    const BOOL = "bool";

    /**
     * @var array Holding fields mapping definition
     */
    protected $metadata = [];

    protected $allowedTypes = [self::STRING, self::INTEGER, self::FLOAT, self::BOOL];

    /**
     * Add field definition
     *
     * @param $fieldName
     * @param $map
     *
     * @return $this
     * @throws ErrorException
     */
    public function mapField($fieldName, $map)
    {
        $default = [
            'nullable' => false,
            'type'     => 'string'
        ];
        foreach ($default as $key => $option) {
            if (!isset($map[$key])) {
                $map[$key] = $option;
            }
        }
        if (!in_array($map['type'], $this->allowedTypes)) {
            throw new ErrorException("Invalid metadata " . $map['type']);
        }
        if (empty($fieldName) || !is_string($fieldName)) {
            throw new ErrorException("FieldName is required");
        }
        $this->metadata[$fieldName] = $map;
        return $this;
    }

    /**
     * Convert given data into the correct type format base on field metadata setup
     *
     * @param $fieldName
     * @param $data
     *
     * @return mixed
     */
    public function convert($fieldName, $data)
    {
        $metadata = $this->getMetadata();
        if (!isset($metadata[$fieldName])) {
            return $data;
        }
        return self::convertValue($data, $metadata[$fieldName]);
    }

    /**
     * Convert given data into the correct type format
     *
     * @param $data
     * @param $metadata
     *
     * @return mixed
     */
    public static function convertValue($data, $metadata)
    {
        switch ($metadata['type']) {
            case self::INTEGER:
                $data = (int)$data;
                break;
            case self::STRING:
                $data = (string)$data;
                break;
            case self::FLOAT:
                $data = (float)$data;
                break;
            case self::BOOL:
                $data = (bool)$data;
                break;
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get key map from draw data to resource
     *
     * @param $fieldName
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function getDataFieldMap($fieldName)
    {
        if (!isset($this->metadata[$fieldName]) || !isset($this->metadata[$fieldName]['data_map'])) {
            return $fieldName;
        }
        return $this->metadata[$fieldName]['data_map'];
    }
}