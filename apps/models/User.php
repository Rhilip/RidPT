<?php

namespace apps\models;

use \Rid\User\UserInterface;
use \Rid\User\UserTrait;

class User implements UserInterface
{
    use UserTrait;

    public function __construct($id = null)
    {
        $this->loadUserContentById($id);
    }

}
