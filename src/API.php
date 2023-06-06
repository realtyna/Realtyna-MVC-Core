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

    public function __construct(StartUp $main, string $version, string $baseRoute)
    {
        $this->baseRoute = $baseRoute;
        $this->version = $version;
        $this->main = $main;
        $this->namespace = 'realtyna/' . $main->config->get('api.namespace');
        $this->validator = $main->container->get(Validator::class);
    }

    public function response($success, $data, $statusCode): \WP_REST_Response
    {
        $response = [
            'success' => $success,
            'data' => $data
        ];
        return new \WP_REST_Response($response, $statusCode);
    }

    protected function returnValidationErrorMessages($data)
    {
        $data = [
            'message' => __('One or more of parameters was not valid!'),
            'errors' => $data
        ];
        return $this->response(false, $data, 400);
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