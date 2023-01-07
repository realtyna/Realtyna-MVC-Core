<?php

namespace Realtyna\MvcCore;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Collection;
use Realtyna\MvcCore\Exception\ModelApiException;
use WP_REST_Request;

class Model extends BaseModel
{


    /**
     * @var mixed|string
     */
    public static $apiRoute;
    /**
     * @var mixed
     */
    public static $method;
    /**
     * @var mixed
     */
    public static $conditions = [];
    /**
     * @var mixed
     */
    public static $headers;
    /**
     * @var array|mixed
     */
    public static $apiResponse;

    public static function api(string $apiRoute): Model
    {
        static::$apiRoute = $apiRoute;
        return new static();
    }

    /**
     * @param $method
     * @return Model
     * @throws ModelApiException
     */
    public static function method($method): Model
    {

        $method = strtoupper($method);
        if(!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])){
            throw new ModelApiException('Entered method is not valid.');
        }
        static::$method = $method;
        return new static();
    }



    public static function where($key, $value = '')
    {
        if (is_array($key)) {
            foreach ($key as $arrayItemKey => $arrayItemValue) {
                static::$conditions [$arrayItemKey] = $arrayItemValue;
            }
            return new static();
        }
        static::$conditions [$key] = $value;
        return new static();
    }

    public static function header(array $headers)
    {
        static::$headers = $headers;
        return new static();
    }


    public static function send($return = false)
    {
        $request = new WP_REST_Request( static::$method, static::$apiRoute );
        if(static::$method == 'GET'){
            $request->set_query_params( static::$conditions );
        }elseif (static::$method == 'POST'){
            $request->set_body_params( static::$conditions );
        }
        $request->set_headers( static::$headers );

        $response = rest_do_request( $request );
        $server = rest_get_server();
        $data = $server->response_to_data( $response, true );

        if($return){
            return $data;
        }
        static::$apiResponse = $data;
        return new static();

    }

    /**
     * @return Collection|Model
     */
    public static function toObject()
    {
        $collection = [];
        $data = static::$apiResponse;

        if(count($data) != count($data, COUNT_RECURSIVE)){
            foreach ($data as $item){
                $collection[] = (new static())->fill($item);
            }
            return collect($collection);
        }
        
        return (new static())->fill($data);
    }
}