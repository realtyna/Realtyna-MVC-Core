<?php

namespace Realtyna\MvcCore;

class Request extends \WP_REST_Request
{

    public static function get_raw_data()
    {
        // phpcs:disable PHPCompatibility.Variables.RemovedPredefinedGlobalVariables.http_raw_post_dataDeprecatedRemoved
        global $HTTP_RAW_POST_DATA;

        // $HTTP_RAW_POST_DATA was deprecated in PHP 5.6 and removed in PHP 7.0.
        if (!isset($HTTP_RAW_POST_DATA)) {
            $HTTP_RAW_POST_DATA = file_get_contents('php://input');
        }

        return $HTTP_RAW_POST_DATA;
        // phpcs:enable
    }

    public function __construct()
    {
        if (empty($path)) {
            if (isset($_SERVER['PATH_INFO'])) {
                $path = $_SERVER['PATH_INFO'];
            } else {
                $path = '/';
            }
        }

        parent::__construct($_SERVER['REQUEST_METHOD'], $path);

        $this->set_query_params(wp_unslash($_GET));
        $this->set_body_params(wp_unslash($_POST));
        $this->set_file_params($_FILES);
        $this->set_headers($this->get_headers(wp_unslash($_SERVER)));
        $this->set_body(self::get_raw_data());

        if (isset($_GET['_method'])) {
            $this->set_method($_GET['_method']);
        } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $this->set_method($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }
    }

}