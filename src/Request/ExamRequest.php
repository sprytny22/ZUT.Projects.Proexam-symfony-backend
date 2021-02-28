<?php

namespace App\Request;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class ExamRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("integer")
     *
     * @var int $testId
     */
    public $testId;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("DataTime")
     *
     * @var DateTime $startDataTime
     */
    public $startDataTime;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("array")
     *
     * @var array $users
     */
    public $users;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Type("DataTime")
     *
     * @var DateTime $endDataTime
     */
    public $endDataTime;

}