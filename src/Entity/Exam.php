<?php

namespace App\Entity;

use App\Repository\ExamRepository;
use DateTime;
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
    const STATUS_ARCHIVED = 'ARCHIVED';


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
     * @ORM\Column(type="datetime")
     */
    private $startDataTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $time = 0;


    /**
     * @ORM\Column(type="integer")
     */
    private $pass = 0;

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
    public function getStartDataTime(): DateTime
    {
        return $this->startDataTime;
    }

    /**
     * @param mixed $startDataTime
     */
    public function setStartDataTime(\DateTime $startDataTime): void
    {
        $this->startDataTime = $startDataTime;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time): void
    {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getPass(): int
    {
        return $this->pass;
    }

    /**
     * @param int $pass
     */
    public function setPass(int $pass): void
    {
        $this->pass = $pass;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function hasUser(User $user): bool
    {
        foreach ($this->getUsers() as $examUser) {
            if ($user === $examUser) {
                return true;
            }
        }

        return false;
    }

    public function toResponse(): array
    {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'test' => $this->test->toResponse(),
        ];
    }
}
