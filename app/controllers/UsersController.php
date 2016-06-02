<?php namespace BeriDelay\Controllers;

use System\Controllers\Controller;
use System\Models\Log;
use BeriDelay\Models\User;

class UsersController extends Controller
{
    public function indexAction()
    {
        $this->view->disable();
        //$this->view->logs = Log::find();

        $user = new User();
        $user->name = 's';
        $user->surname = 'Бабушкин';
        $user->patronim = 'Павлович';
        $user->age = 0;
        $user->email = 'darkdarin@mail.ru';
        $user->password = '12345';
        $user->phone = 89655387278;
        $user->gender = 0;
        $user->city = 'Екатеринбург';
        $user->salary = 2;
        $user->is_admin = true;

        //$user->validation();

        //if (!$user->validation()) echo 'dsdfg';

        if ($user->save() == false) {
            foreach ($user->getMessages() as $message) {
                echo "<br>Message: ", $message->getMessage();
                echo "<br>Field: ", $message->getField();
                echo "<br>Type: ", $message->getType();
            }
        } else {
            echo 'Success!';
            $user->delete();
        }


    }
}

