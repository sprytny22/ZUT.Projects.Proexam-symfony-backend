<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class UserRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Email()
     * @Type("string")
     *
     * @var string $email
     */
    public $email;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $password
     */
    public $password;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("integer")
     *
     * @var int $role
     */
    public $role;
}