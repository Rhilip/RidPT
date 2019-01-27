<?php

namespace apps\models;

use \Mix\User\UserInterface;
use \Mix\User\UserTrait;

class User implements UserInterface
{
    use UserTrait;

    public function __construct($id = null)
    {
        $this->loadUserContentById($id);
    }

}
