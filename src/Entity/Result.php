<?php

namespace App\Entity;

use App\Repository\ResultRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResultRepository::class)
 */
class Result
{
    const STATUS_OPEN = 'open';
    const STATUS_CLOSE = 'close';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Exam")
     * @ORM\JoinColumn(name="exam_id", referencedColumnName="id")
     */
    private $exam;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Answer", inversedBy="Result")
     */
    private $answers;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;


    public function __construct()
    {
        $this->status = self::STATUS_OPEN;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getExam()
    {
        return $this->exam;
    }

    /**
     * @param mixed $exam
     */
    public function setExam(Exam $exam): void
    {
        $this->exam = $exam;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }


    public function setAnswers(array $answers): void
    {
        $this->answers = new ArrayCollection($answers);
    }


    public function toJoinResponse()
    {
        $answers = $this->answers;

        $questions = [];

        foreach($answers as $answer) {
            $answerResponse = [
              'content' => $answer->getQuestion()->getContent(),
              'type' => $answer->getQuestion()->getType(),
              'category' => $answer->getQuestion()->getCategory(),
              'answerId' => $answer->getId(),
            ];

            $questions[] = $answerResponse;
        }

        return [
            'result' => $this->getId(),
            'exam' => $this->exam->getTitle(),
            'test' => $this->exam->getTest()->getName(),
            'questions' => $questions
        ];
    }
}
