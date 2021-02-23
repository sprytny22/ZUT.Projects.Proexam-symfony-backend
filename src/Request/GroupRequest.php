<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class GroupRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("array")
     *
     * @var array $groups
     */
    public $groups = [];

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @var string $method
     */
    public $method;
}