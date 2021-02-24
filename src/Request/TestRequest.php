<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class TestRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $name
     */
    public $name;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $category
     */
    public $category;

    /**
     * @Type("array")
     *
     * @var array $questions
     */
    public $questions = [];

}