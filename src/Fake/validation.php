<?php

return [
    'inputs'   => [
        'email'                 => 'E-Mail Address',
        'password'              => 'Password',
        'confirmation_password' => 'Password Confirmation',
        'username'              => 'Username',
    ],
    'messages' => [
        'required'    => '%s is required.',
        'email'       => 'Entered E-Mail Address is not valid.',
        'min'         => '%s should have more than %d characters.',
        'string'      => '%s should be string.',
        'date_format' => '%s should be date formatted in %s.',
        'date'        => '%s should be date',
        'between'     => '%s should be between %s',
        'json'        => '%s should be in json format',
    ],
];