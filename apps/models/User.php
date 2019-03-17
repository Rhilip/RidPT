<?php

namespace apps\models;

use \apps\components\User\UserInterface;
use \apps\components\User\UserTrait;

class User implements UserInterface
{
    use UserTrait;

    public function __construct($id = null)
    {
        $this->loadUserContentById($id);
    }

}
