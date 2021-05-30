<?php

namespace App\Entity;

use App\Repository\LoggerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LoggerRepository::class)
 */
class Logger
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fromAnswer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $toAnswer;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSuspect;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Answer")
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id")
     */
    private $answer;


    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param mixed $answer
     */
    public function setAnswer($answer): void
    {
        $this->answer = $answer;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromAnswer(): ?string
    {
        return $this->fromAnswer;
    }

    public function setFromAnswer(?string $fromAnswer): self
    {
        $this->fromAnswer = $fromAnswer;

        return $this;
    }

    public function getToAnswer(): ?string
    {
        return $this->toAnswer;
    }

    public function setToAnswer(?string $toAnswer): self
    {
        $this->toAnswer = $toAnswer;

        return $this;
    }

    public function getIsSuspect(): ?bool
    {
        return $this->isSuspect;
    }

    public function setIsSuspect(?bool $isSuspect): self
    {
        $this->isSuspect = $isSuspect;

        return $this;
    }
}
