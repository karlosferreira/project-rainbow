<?php

namespace Apps\Core_MobileApi\Service\Helper;


/**
 * Class ParametersBag
 * This class help transform parameters between services in current request
 * @package Apps\Core_MobileApi\Service\Helper
 */
class ParametersBag
{

    protected $bag;

    protected static $ins;

    /**
     * Add parameter to bag
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function add($name, $value)
    {
        $this->bag[$name] = $value;
        return $this;
    }

    /**
     * Get added parameter value in the bag
     *
     * @param $name
     *
     * @return null
     */
    public function get($name)
    {
        return (isset($this->bag[$name]) ? $this->bag[$name] : null);
    }

    /**
     * Singleton instance
     * @return ParametersBag
     * @codeCoverageIgnore - Ignore coverage because it only created once in api build, not in unit test.
     */
    public static function instance()
    {
        if (self::$ins == null) {
            self::$ins = new self();
        }
        return self::$ins;
    }
}