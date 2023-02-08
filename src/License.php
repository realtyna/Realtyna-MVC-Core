<?php

namespace Realtyna\MvcCore;

class License
{
    private Config $config;
    private $leadProductID;

    public function __construct(Config $config)
    {
        $this->config        = $config;
        $this->leadProductID = $this->config->get('license.product_id');
    }

    public function isValid(): bool
    {
        if (get_option('realtyna_license_' . $this->leadProductID, false)) {
            return true;
        }

        return false;
    }

    public function checkLicense($email, $license): bool
    {
        if (get_option('realtyna_license_' . $this->leadProductID, false)) {
            return true;
        }
        $domain        = $_SERVER['SERVER_NAME'];
        $licenseApiUrl = 'https://realtyna.com/wp-json/license_checker/checkuser?email=' . $email . '&license=' . $license . '&domain=' . $domain;
        $response      = wp_remote_get($licenseApiUrl, [
            'timeout' => 30,
            'sslverify' => false
        ]);
        $responseBody  = json_decode(wp_remote_retrieve_body($response), true);
        if ( ! empty($responseBody['license_data'])) {
            $productStatus = $responseBody['license_data']['product_status'];
            $userStatus    = $responseBody['license_data']['user_status'];
            $productID     = $responseBody['license_data']['product_id'];
            $orderStatus   = $responseBody['order_status'];
            if ($productID != $this->leadProductID) {
                return false;
            } elseif ($orderStatus == 'completed' && $productStatus == 0 && $userStatus == 0) {
                $license_data_arr   = array(
                    'email'              => $email,
                    'license_validation' => true,
                    'license_data'       => $responseBody['license_data'],
                );
                update_option('realtyna_license_' . $this->leadProductID, json_encode($license_data_arr));

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

}