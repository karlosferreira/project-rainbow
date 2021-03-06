<?php

/**
 * Mux PHP - Copyright 2019 Mux Inc.
 * NOTE: This file is auto generated. Do not edit this file manually.
 */

namespace MuxPhp\Models;

use ArrayAccess;
use MuxPhp\ObjectSerializer;

/**
 * DeliveryReport Class Doc Comment
 *
 * @category Class
 * @package  MuxPhp
 */
class DeliveryReport implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'DeliveryReport';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'live_stream_id' => 'string',
        'asset_id' => 'string',
        'passthrough' => 'string',
        'created_at' => 'string',
        'asset_state' => 'string',
        'asset_duration' => 'double',
        'delivered_seconds' => 'double'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPIFormats = [
        'live_stream_id' => null,
        'asset_id' => null,
        'passthrough' => null,
        'created_at' => null,
        'asset_state' => null,
        'asset_duration' => 'double',
        'delivered_seconds' => 'double'
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'live_stream_id' => 'live_stream_id',
        'asset_id' => 'asset_id',
        'passthrough' => 'passthrough',
        'created_at' => 'created_at',
        'asset_state' => 'asset_state',
        'asset_duration' => 'asset_duration',
        'delivered_seconds' => 'delivered_seconds'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'live_stream_id' => 'setLiveStreamId',
        'asset_id' => 'setAssetId',
        'passthrough' => 'setPassthrough',
        'created_at' => 'setCreatedAt',
        'asset_state' => 'setAssetState',
        'asset_duration' => 'setAssetDuration',
        'delivered_seconds' => 'setDeliveredSeconds'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'live_stream_id' => 'getLiveStreamId',
        'asset_id' => 'getAssetId',
        'passthrough' => 'getPassthrough',
        'created_at' => 'getCreatedAt',
        'asset_state' => 'getAssetState',
        'asset_duration' => 'getAssetDuration',
        'delivered_seconds' => 'getDeliveredSeconds'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['live_stream_id'] = isset($data['live_stream_id']) ? $data['live_stream_id'] : null;
        $this->container['asset_id'] = isset($data['asset_id']) ? $data['asset_id'] : null;
        $this->container['passthrough'] = isset($data['passthrough']) ? $data['passthrough'] : null;
        $this->container['created_at'] = isset($data['created_at']) ? $data['created_at'] : null;
        $this->container['asset_state'] = isset($data['asset_state']) ? $data['asset_state'] : null;
        $this->container['asset_duration'] = isset($data['asset_duration']) ? $data['asset_duration'] : null;
        $this->container['delivered_seconds'] = isset($data['delivered_seconds']) ? $data['delivered_seconds'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets live_stream_id
     *
     * @return string|null
     */
    public function getLiveStreamId()
    {
        return $this->container['live_stream_id'];
    }

    /**
     * Sets live_stream_id
     *
     * @param string|null $live_stream_id live_stream_id
     *
     * @return $this
     */
    public function setLiveStreamId($live_stream_id)
    {
        $this->container['live_stream_id'] = $live_stream_id;

        return $this;
    }

    /**
     * Gets asset_id
     *
     * @return string|null
     */
    public function getAssetId()
    {
        return $this->container['asset_id'];
    }

    /**
     * Sets asset_id
     *
     * @param string|null $asset_id asset_id
     *
     * @return $this
     */
    public function setAssetId($asset_id)
    {
        $this->container['asset_id'] = $asset_id;

        return $this;
    }

    /**
     * Gets passthrough
     *
     * @return string|null
     */
    public function getPassthrough()
    {
        return $this->container['passthrough'];
    }

    /**
     * Sets passthrough
     *
     * @param string|null $passthrough passthrough
     *
     * @return $this
     */
    public function setPassthrough($passthrough)
    {
        $this->container['passthrough'] = $passthrough;

        return $this;
    }

    /**
     * Gets created_at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->container['created_at'];
    }

    /**
     * Sets created_at
     *
     * @param string|null $created_at created_at
     *
     * @return $this
     */
    public function setCreatedAt($created_at)
    {
        $this->container['created_at'] = $created_at;

        return $this;
    }

    /**
     * Gets asset_state
     *
     * @return string|null
     */
    public function getAssetState()
    {
        return $this->container['asset_state'];
    }

    /**
     * Sets asset_state
     *
     * @param string|null $asset_state asset_state
     *
     * @return $this
     */
    public function setAssetState($asset_state)
    {
        $this->container['asset_state'] = $asset_state;

        return $this;
    }

    /**
     * Gets asset_duration
     *
     * @return double|null
     */
    public function getAssetDuration()
    {
        return $this->container['asset_duration'];
    }

    /**
     * Sets asset_duration
     *
     * @param double|null $asset_duration asset_duration
     *
     * @return $this
     */
    public function setAssetDuration($asset_duration)
    {
        $this->container['asset_duration'] = $asset_duration;

        return $this;
    }

    /**
     * Gets delivered_seconds
     *
     * @return double|null
     */
    public function getDeliveredSeconds()
    {
        return $this->container['delivered_seconds'];
    }

    /**
     * Sets delivered_seconds
     *
     * @param double|null $delivered_seconds delivered_seconds
     *
     * @return $this
     */
    public function setDeliveredSeconds($delivered_seconds)
    {
        $this->container['delivered_seconds'] = $delivered_seconds;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }
}


