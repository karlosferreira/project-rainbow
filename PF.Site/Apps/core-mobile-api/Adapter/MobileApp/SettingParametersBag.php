<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Adapter\MobileApp;

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;


class SettingParametersBag
{
    /**
     * @var array
     */
    protected $bag = [];

    /**
     * ParameterBag constructor.
     *
     * @param array $bag
     */
    public function __construct(array $bag)
    {
        $this->bag = $bag;
    }

    public function toArray()
    {
        return $this->bag;
    }

    /**
     * @param string     $name
     * @param mixed|null $default_value
     *
     * @return mixed|null
     */
    public function getParam($name, $default_value = null)
    {
        return array_key_exists($name, $this->bag) ? $this->bag[$name] : $default_value;
    }

    /**
     * @param array $params
     */
    public function addParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->bag[$name] = $value;
        }
    }

    /**
     * @param $params
     *
     * @return SettingParametersBag
     */
    public static function createForResource($params)
    {
        $bag = new static([
            'acl'          => [
                'can_add'    => true,
                'can_edit'   => true,
                'can_delete' => true,
                'can_report' => true,
                'can_like'   => true,
                'can_search' => true,
                'can_sort'   => true,
                'can_filter' => true,
            ],
            'add.icon'     => 'plus',
            'add.label'    => \Phpfox::getService(LocalizationInterface::class)->translate('Add'),
            'search_input' => ['placeholder' => \Phpfox::getService(LocalizationInterface::class)->translate('search_dot'),]
        ]);

        $bag->addParams($params);

        $urlRoute = $bag->getParam('urls.base');

        if (!$urlRoute) {
            $urlRoute = "mobile/" . str_replace('_', '-', $bag->getParam('resource_name'));
        }

        $bag->addParams([
            'apiUrl' => "{$urlRoute}",
        ]);


        return $bag;
    }
}