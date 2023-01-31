<?php

namespace Realtyna\MvcCore;


use Realtyna\MvcCore\Auth;
use Realtyna\MvcCore\Models\APIResponse;

class API
{
    public string $baseRoute;
    public Validator $validator;
    public string $version;
    public StartUp $main;
    public $namespace;
    public array $publicRoutes = [];
    public Auth $auth;

    public function __construct(StartUp $main, string $version, string $baseRoute)
    {
        $this->baseRoute = $baseRoute;
        $this->version = $version;
        $this->main = $main;
        $this->namespace = 'realtyna/' . $main->config->get('api.namespace');
        $this->validator = $main->container->get(Validator::class);
        $this->auth = $main->container->get(Auth::class);
        $requested_url = sanitize_url($_SERVER['REQUEST_URI']);
        if (strpos($requested_url, '/'.$this->baseRoute.'/')){
            add_filter('determine_current_user', [$this, 'determineCurrentUser']);
        }
    }

    public function determineCurrentUser($user)
    {
        $rest_api_slug = rest_get_url_prefix() . '/' . $this->namespace . '/' . $this->version;
        $requested_url = sanitize_url($_SERVER['REQUEST_URI']);

        if ((strpos($rest_api_slug, $requested_url) === false && strpos(
                    $requested_url,
                    $rest_api_slug
                ) === false) || $user) {
            return $user;
        }

        $wantedRoute = str_replace($rest_api_slug, '', $requested_url);
        $wantedRoute = ltrim($wantedRoute, '/');
        $wantedRoute = rtrim($wantedRoute, '/');


        if ($wantedRoute == '' || in_array($wantedRoute, $this->publicRoutes)) {
            return $user;
        }
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ? sanitize_text_field($_SERVER['HTTP_AUTHORIZATION']) : false;
        /* Double check for different auth header string (server dependent) */
        if (!$auth_header) {
            $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ? sanitize_text_field(
                $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ) : false;
        }

        if (!$auth_header) {
            $this->returnUnauthenticatedResponse();
        }


        $token = str_replace('Bearer ', '', $auth_header);

        try {
            $user = $this->auth->getUser($token);

            if ($user) {
                return $user->ID;
            }
        } catch (\Exception $e) {
            $this->returnUnauthenticatedResponse();
        }
        $this->returnUnauthenticatedResponse();
        return false;
    }

    public function sendResponse($success, $data, $statusCode)
    {
        $response = new APIResponse($success, $data, $statusCode);
        wp_send_json($response, $statusCode);
        exit();
    }

    protected function returnValidationErrorMessages($data)
    {
        $data = [
            'message' => __('One or more of parameters was not valid!'),
            'errors' => $data
        ];
        $this->sendResponse(false, $data, 400);
    }

    protected function returnUnauthenticatedResponse()
    {
        $this->sendResponse(false, [
            'message' => 'Unauthenticated.'
        ], 403);
    }

    /**
     * @param $request
     * @return array
     */
    public function requestHandler(\WP_REST_Request $request): array
    {
        return array_merge(
            $request->get_params(),
            json_decode($request->get_body(), true) ?
                json_decode($request->get_body(), true) : [],
            $request->get_body_params() ? $request->get_body_params() : [],
            $request->get_json_params() ? $request->get_json_params() : []
        );
    }

}