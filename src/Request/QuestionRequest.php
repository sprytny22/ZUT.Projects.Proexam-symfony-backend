<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class QuestionRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $type
     */
    public $type;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $type
     */
    public $category;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $content
     */
    public $content;

    /**
     * @Type("string")
     *
     * @var string $a
     */
    public $a;

    /**
     * @Type("string")
     *
     * @var string $b
     */
    public $b;

    /**
     * @Type("string")
     *
     * @var string $c
     */
    public $c;

    /**
     * @Type("string")
     *
     * @var string $d
     */
    public $d;

    /**
     * @Type("string")
     *
     * @var string $correct
     */
    public $correct;

}