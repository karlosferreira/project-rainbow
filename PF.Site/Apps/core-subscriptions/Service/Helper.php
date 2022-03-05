<?php
namespace Apps\Core_Subscriptions\Service;

use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Helper extends Phpfox_Service
{
    private $gatewayApiUrls;
    private $gatewayApiEndpoints;

    public function __construct()
    {
        $this->gatewayApiUrls = [
            'paypal' => [
                'test' => 'https://api-m.sandbox.paypal.com/v1/',
                'live' => 'https://api-m.paypal.com/v1/'
            ],
        ];
        $this->gatewayApiEndpoints = [
            'paypal' => [
                'token' => 'oauth2/token',
                'subscription/cancel' => 'billing/subscriptions/{id}/cancel'
            ]
        ];
    }

    public function getApiUrl($action, $gateway = 'paypal', $segmentParams = [])
    {
        if (empty($action) || empty($this->gatewayApiEndpoints[$gateway][$action]) || empty($this->gatewayApiUrls[$gateway])) {
            return false;
        }

        $gatewayItem = db()->select('gateway_id, is_test')
                    ->from(':api_gateway')
                    ->where([
                        'gateway_id' => $gateway,
                    ])->executeRow();

        if (empty($gatewayItem['gateway_id'])) {
            return false;
        }

        $url = $this->gatewayApiUrls[$gateway][$gatewayItem['is_test'] ? 'test' : 'live'] . $this->gatewayApiEndpoints[$gateway][$action];

        if (!empty($segmentParams)) {
            foreach ($segmentParams as $segmentParam => $segmentValue) {
                $url = str_replace($segmentParam, $segmentValue, $url);
            }
        }

        return $url;
    }

    public function getPaypalAccessToken($clientId, $clientSecret)
    {
        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }

        $apiEndpoint = $this->getApiUrl('token');
        $tokenParams = [
            'grant_type' => 'client_credentials'
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-Language: en_US',
        ]);
        curl_setopt($curl, CURLOPT_USERPWD, $clientId . ':' . $clientSecret);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($tokenParams));

        $data = curl_exec($curl);
        $data = json_decode(trim($data), true);

        if (empty($data['access_token'])) {
            return false;
        }

        return $data['access_token'];
    }

    /** generate a random transaction id
     * @param int $size (default is 17,which is length of paypal transaction
     * @return string
     */
    public function generateTransactionId($size = 17) {
        $sAlpha = '';
        $keys = range('A', 'Z');

        for ($i = 0; $i < 2; $i++) {
            $sAlpha .= $keys[array_rand($keys)];
        }

        $length = $size - 2;

        $sNumber = '';
        $keys = range(0, 9);

        for ($i = 0; $i < $length; $i++) {
            $sNumber .= $keys[array_rand($keys)];
        }

        return $sAlpha.$sNumber;
    }
}