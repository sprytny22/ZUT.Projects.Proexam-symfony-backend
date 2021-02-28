<?php

namespace App\Entity;

use App\Repository\ExamRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExamRepository::class)
 */
class Exam
{
    const STATUS_NON_CONFIRM = 'NON_CONFIRM';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_FINISHED = 'FINISHED';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Test", inversedBy="Exams")
     */
    private $test;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="Exams")
     */
    private $users;

    /**
     * @Column(type="datetime")
     */
    private $startDataTime;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * @param mixed $test
     */
    public function setTest($test): void
    {
        $this->test = $test;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
    }

    /**
     * @return mixed
     */
    public function getStartDataTime()
    {
        return $this->startDataTime;
    }

    /**
     * @param mixed $startDataTime
     */
    public function setStartDataTime($startDataTime): void
    {
        $this->startDataTime = $startDataTime;
    }

    /**
     * @return mixed
     */
    public function getEndDataTime()
    {
        return $this->endDataTime;
    }

    /**
     * @param mixed $endDataTime
     */
    public function setEndDataTime($endDataTime): void
    {
        $this->endDataTime = $endDataTime;
    }

    /**
     * @Column(type="datetime")
     */
    private $endDataTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function toResponse(): array
    {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'test' => $this->test->toResponse()
        ];
    }
}
