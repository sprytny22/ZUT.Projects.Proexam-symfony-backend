<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserRequest
{
    /**
     * @Assert\NotBlank()
     */
    public $email;

    /**
     * @Assert\NotBlank()
     */
    public $password;

    /**
     * @Assert\NotBlank()
     */
    public $role;

}