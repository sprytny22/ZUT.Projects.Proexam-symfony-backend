<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class ExamRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("integer")
     *
     * @var int $test
     */
    public $test;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $title
     */
    public $title;

    /**
     * @Type("array")
     *
     * @var arrat $users
     */
    public $users = [];

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $start
     */
    public $start;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("string")
     *
     * @var string $end
     */
    public $end;


}