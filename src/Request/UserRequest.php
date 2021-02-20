<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserRequest
{
    /**
    * @Assert\NotBlank()
    */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $surname;

    /**
     * @Assert\NotBlank()
     */
    public $password;

    /**
     * @Assert\NotBlank()
     */
    public $email;

}