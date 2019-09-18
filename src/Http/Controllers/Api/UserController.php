<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\Api;

use BristolSU\Support\Authentication\Contracts\Authentication;

class UserController extends Controller
{
    public function me(Authentication $authentication)
    {
        return $authentication->getUser();
    }
}