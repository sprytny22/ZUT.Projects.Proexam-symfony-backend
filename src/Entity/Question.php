<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $a;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $b;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $c;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $d;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $correct;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Test", inversedBy="questions")
     */
    private $tests;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTests()
    {
        return $this->tests;
    }

    /**
     * @param mixed $tests
     */
    public function setTests($tests): void
    {
        $this->tests = $tests;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @param mixed $a
     */
    public function setA($a): void
    {
        $this->a = $a;
    }

    /**
     * @return mixed
     */
    public function getB()
    {
        return $this->b;
    }

    /**
     * @param mixed $b
     */
    public function setB($b): void
    {
        $this->b = $b;
    }

    /**
     * @return mixed
     */
    public function getC()
    {
        return $this->c;
    }

    /**
     * @param mixed $c
     */
    public function setC($c): void
    {
        $this->c = $c;
    }

    /**
     * @return mixed
     */
    public function getD()
    {
        return $this->d;
    }

    /**
     * @param mixed $d
     */
    public function setD($d): void
    {
        $this->d = $d;
    }

    /**
     * @return mixed
     */
    public function getCorrect()
    {
        return $this->correct;
    }

    /**
     * @param mixed $correct
     */
    public function setCorrect($correct): void
    {
        $this->correct = $correct;
    }

    public function toResponse(): array
    {
        return [
            'title' => $this->getContent(),
            'A' => $this->getA(),
            'B' => $this->getB(),
            'C' => $this->getC(),
            'D' => $this->getD()
        ];
    }
}
