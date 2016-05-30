<?php namespace BeriDelay\Controllers;

use System\Controllers\ApiController;

class AuthController extends ApiController
{

    public $actions = [
        'signin' => [
            'fields' => [
                'email' => 'required|email',
                'password' => 'required',
            ]

        ]
    ];

    public function signinAction()
    {
        return ['dsfdsfdsf'];
    }
}

