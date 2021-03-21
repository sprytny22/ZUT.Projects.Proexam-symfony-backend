<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class PasswordRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $password
     */
    public $password;

}