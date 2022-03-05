<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/7/18
 * Time: 3:45 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Api\Form\TransformerInterface;
use Phpfox;

class LocationType extends GeneralType implements TransformerInterface
{

    protected $componentName = "Location";

    public function getMetaValueFormat()
    {
        return "['address', 'lat', 'lng']";
    }

    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            'returnKeyType',
            'address',
            'lat',
            'lng'
        ];
    }

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }

        $isValid = true;
        $values = $this->getValue();

        $isMissingLatLng = $values == null || (!isset($values['lat']) || !isset($values['lng']) || empty($values['address']));

        if ($this->isRequiredField()) {
            $isValid = !$isMissingLatLng;
        }

        return $isValid;
    }

    public function transform($value)
    {
        $result = [];
        if (is_array($value) && !empty($this->getAttr('use_transform'))) {
            if (!empty($this->getAttr('group_transform'))) {
                $result[$this->getName()] = [
                    'location_lat' => isset($value['lat']) ? $value['lat'] : 0,
                    'location_lng' => isset($value['lng']) ? $value['lng'] : 0,
                    'location' => isset($value['address']) ? $value['address'] : ''
                ];
            } else {
                $result = [
                    'location_lat' => isset($value['lat']) ? $value['lat'] : 0,
                    'location_lng' => isset($value['lng']) ? $value['lng'] : 0,
                    'location' => isset($value['address']) ? $value['address'] : ''
                ];
            }
        }
        return $this->getCountryIso($result);
    }

    public function reverseTransform($data)
    {
        $localNameField = !empty($this->getAttr('location_name_field')) ? $this->getAttr('location_name_field') : 'location';
        $localCoordinateField = !empty($this->getAttr('location_coordinate_field')) ? $this->getAttr('location_coordinate_field') : 'coordinate';
        $locationName = isset($data[$localNameField]) ? $data[$localNameField] : '';
        $locationCoordinate = isset($data[$localCoordinateField]) ? $data[$localCoordinateField] : [];

        return [
            'address' => $locationName,
            'lat' => isset($locationCoordinate['latitude']) ? $locationCoordinate['latitude'] : 0,
            'lng' => isset($locationCoordinate['longitude']) ? $locationCoordinate['longitude'] : 0,
        ];
    }

    public function getCountryIso($value)
    {
        $apiKey = $this->getSetting()->getAppSetting('core.google_api_key');
        if ($this->getAttr('include_country_iso')) {
            $groupTransformCond = !empty($this->getAttr('group_transform')) && isset($value[$this->getName()]);
            $condition = $groupTransformCond
                ? isset($value[$this->getName()]['location_lat'], $value[$this->getName()]['location_lng'])
                : isset($value['location_lat'], $value['location_lng']);
            if ($apiKey && $condition) {
                $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json?';
                $sLatLng = $groupTransformCond
                    ? $value[$this->getName()]['location_lat'] . ',' . $value[$this->getName()]['location_lng']
                    : $value['location_lat'] . ',' . $value['location_lng'];
                $param = [
                    'key' => $apiKey,
                    'latlng' => $sLatLng
                ];
                $response = Phpfox::getLib('request')->send($baseUrl . http_build_query($param), [], 'GET', $_SERVER['HTTP_USER_AGENT']);
                $response = json_decode($response, true);
                $results = isset($response['results']) ? reset($response['results']) : [];
                if (is_array($results) && count($results) && isset($response['status'])
                    && $response['status'] == 'OK' && !empty($results['address_components'])) {
                    $country = array_filter($results['address_components'], function($component) {
                        return isset($component['types'][0]) && $component['types'][0] == 'country' && isset($component['short_name']);
                    });
                    $country = end($country);
                    if ($groupTransformCond) {
                        $value[$this->getName()]['country_iso'] = isset($country['short_name']) ? $country['short_name'] : 0;
                    } else {
                        $value['country_iso'] = isset($country['short_name']) ? $country['short_name'] : 0;
                    }
                }
            } else {
                if ($groupTransformCond) {
                    $value[$this->getName()]['country_iso'] = 0;
                } else {
                    $value['country_iso'] = 0;
                }
            }
        }
        return $value;
    }
}