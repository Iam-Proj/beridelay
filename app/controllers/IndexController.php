<?php namespace BeriDelay\Controllers;

use System\Controllers\Controller;
use BeriDelay\Models\User;
use BeriDelay\Models\Tag;
use System\Models\File;

class IndexController extends Controller
{

    public function indexAction()
    {
        /*$this->view->users = User::find();
        $this->view->tags = Tag::find();

        //$user = User::findFirst(1);

        $user = User::findFirst(3);
        $user->name = 'sadsa';

        $user->auth();

        $user->save();

        if ($this->request->hasFiles() == true) {
            foreach ($this->request->getUploadedFiles() as $upload) {
                $file = new File();
                $file->data = $upload;

                $user->attachFiles($file);
            }
        }*/
    }

}

