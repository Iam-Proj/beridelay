<?php namespace BeriDelay\Controllers;

use System\Controllers\Controller;
use System\Models\Log;

class UsersController extends Controller
{
    public function indexAction()
    {
        $this->view->logs = Log::find();
    }

}

