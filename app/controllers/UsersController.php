<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use System\Exceptions\BaseException;

class UsersController extends ApiBaseController
{
    public $actions = [
        'registration' => [
            'fields' => [
                'email' => 'required|email',
                'name' => 'required|alpha|between:2,50',
                'surname' => 'required|alpha|between:2,50',
                'patronim' => 'required|alpha|between:2,50',
                'phone' => 'required|between:6,15',
                'age' => 'required|integer|between:1,99',
                'gender' => 'required|in:0,1',
                'city' => 'required',
                'salary' => 'required|in:1,2,3',
                'invite' => 'alpha_num',
                'password' => 'required|min:3'
            ]
        ],
        'get' => [
            'isPrivate' => true,
            'fields' => [

            ]
        ],
        'create' => [
            'isPrivate' => true,
            'fields' => [

            ]
        ],
        'edit' => [
            'isPrivate' => true,
            'fields' => [

            ]
        ],
        'delete' => [
            'isPrivate' => true,
            'fields' => [
                'id' => 'integer',
                'ids' => 'array'
            ]
        ]
    ];

    public function registrationAction()
    {
        try {

            $user = User::registration($this->request->getPost());
            User::signin($this->request->getPost('email'), $this->request->getPost('password'));
            $token = Token::add($user->id);

        } catch (BaseException $e) {
            return $this->errorException($e);
        }


        //TODO: Сгенерировать новое задание и передать его в ответе

        return ['token_access' => $token->value];
    }

    public function getAction()
    {

    }

    public function createAction()
    {

    }

    public function editAction()
    {

    }

    public function deleteAction()
    {
        return $this->delete('BeriDelay\Models\User');
    }
}

