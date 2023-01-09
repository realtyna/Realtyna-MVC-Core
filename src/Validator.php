<?php

namespace Realtyna\MvcCore;

use DateTime;
use Realtyna\MvcCore\Exception\ValidatorException;

class Validator
{


    /**
     * @var mixed
     */
    public array $messages;

    private array $rules = [
        "array",
        "between",
        "boolean",
        "confirmed",
        "date",
        "date_format",
        "digits",
        "digits_between",
        "email",
        "exists",
        "unique",
        "file",
        "filled",
        "image",
        "json",
        "mimes",
        "mimetypes",
        "numeric",
        "regex",
        "required",
        "size",
        "string",
        "timezone",
        "url",
        "min"
    ];

    public function __construct(StartUp $main)
    {
        $this->messages = require($main->config->get('path.validator-messages'));
    }

    /**
     * @param $request
     * @return array
     */
    private function requestHandler(\WP_REST_Request $request): array
    {
        return array_merge(
            $request->get_params(),
            json_decode($request->get_body(), true) ?
                json_decode($request->get_body(), true) : [],
            $request->get_body_params() ? $request->get_body_params() : [],
            $request->get_json_params() ? $request->get_json_params() : []
        );
    }


    private function getErrorMessage(
        $input,
        $rule,
        $args
    ): string {
        return @sprintf(
            __($this->messages['messages'][$rule]),
            __($this->messages['inputs'][$input] ? $this->messages['inputs'][$input] : $input),
            __($args[0]),
            __($args[1]),
            __($args[2])
        );
    }

    public function validate($request, $rules)
    {
        $errors = [];
        $args = [];
        $valid = true;
        $data = $this->requestHandler($request);

        foreach ($rules as $input => $rulesAsArray) {
            foreach ($rulesAsArray as $rule) {
                $rule = explode(':', $rule);
                if (!in_array($rule[0], $this->rules)) {
                    throw new ValidatorException('Entered rule: ' . $rule[0] . ' is not valid');
                }
                if (count($rule) > 1) {
                    $args = $rule;
                    array_shift($args);
                }
                $validateResponse = call_user_func([$this, $rule[0]], $data[$input] ?? '', $args);

                if (!$validateResponse) {
                    $valid = false;
                    $errors [$input][] = $this->getErrorMessage($input, $rule[0], $args);
                }
            }
        }

        return [
            'valid' => $valid,
            'errors' => $errors
        ];
    }

    /**
     * @param $input
     * @return bool
     */
    static function required($input)
    {
        return (bool)$input;
    }

    /**
     * @param $input
     * @return bool
     */
    static function array($input)
    {
        return is_array($input);
    }

    /**
     * @param $input
     * @return bool
     */
    static function email($input)
    {
        return is_email($input);
    }

    /**
     * @param $input
     * @return bool
     */
    static function string($input)
    {
        return is_string($input);
    }

    /**
     * @param $input
     * @return bool
     */
    static function numeric($input)
    {
        return is_numeric($input);
    }


    /**
     * @param $input
     * @param $args
     * @return bool
     */
    static function min($input, $args)
    {
        return strlen($input) > $args[0];
    }

    /**
     * @param $input
     * @param $args
     * @return bool
     */
    static function between($input, $args)
    {
        $args = explode(',', $args[0]);
        return ($args[0] < $input && $args[1] > $input);
    }

    /**
     * @param $input
     * @param $args
     * @return bool
     */
    static function date_format($input, $args)
    {
        $d = DateTime::createFromFormat($args[0], $input);
        return $d && $d->format($args[0]) == $input;
    }

    /**
     * @param $input
     * @param $args
     * @return bool
     */
    static function regex($input, $args)
    {
        preg_match($args[0], $input);
        return preg_last_error() === PREG_NO_ERROR;
    }

    /**
     * @param $input
     * @return bool
     */
    static function date($input)
    {
        if (is_int(strtotime($input))) {
            return true;
        }
        return false;
    }

    /**
     * @param $input
     * @return bool
     */
    static function json($input)
    {
        json_decode($input);
        return json_last_error() === JSON_ERROR_NONE;
    }
}