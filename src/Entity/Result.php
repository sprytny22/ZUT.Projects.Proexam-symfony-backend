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
    const STATUS_CLOSE_MARKED = 'close_marked';

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
     * @ORM\ManyToMany(targetEntity="App\Entity\Logger", inversedBy="Result")
     */
    private $logs;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pass;

//    /**
//     * @ORM\Column(type="string", length=255)
//     */
//    private $marked;
//
//    /**
//     * @return mixed
//     */
//    public function getMarked()
//    {
//        return $this->marked;
//    }
//
//    /**
//     * @param mixed $marked
//     */
//    public function setMarked($marked): void
//    {
//        $this->marked = $marked;
//    }



    /**
     * @return mixed
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Logger $log)
    {
        $this->logs->add($log);
    }


    /**
     * @param mixed $logs
     */
    public function setLogs($logs): void
    {
        $this->logs = $logs;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param mixed $pass
     */
    public function setPass(int $pass): void
    {
        $this->pass = $pass;
    }


    public function __construct()
    {
        $this->status = self::STATUS_OPEN;
        $this->logs = new ArrayCollection();
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


    public function toResponse()
    {
        $answers = $this->answers;

        $questions = [];

        foreach($answers as $answer) {
            $answerResponse = [
              'content' => $answer->getQuestion()->getContent(),
              'type' => $answer->getQuestion()->getType(),
              'category' => $answer->getQuestion()->getCategory(),
              'answerId' => $answer->getId(),
              'A' => $answer->getQuestion()->getA(),
              'B' => $answer->getQuestion()->getB(),
              'C' => $answer->getQuestion()->getC(),
              'D' => $answer->getQuestion()->getD(),
              'answerData' => $answer->getAnswer()
            ];

            $questions[] = $answerResponse;
        }

        return [
            'result' => $this->getId(),
            'exam' => $this->exam->getTitle(),
            'test' => $this->exam->getTest()->getName(),
            'user' => $this->getUser()->toResponse(),
            'questions' => $questions,
            'status' => 'ok',
        ];
    }

    public function getOpenAnswers(): array
    {
        $answers = [];

        /** @var Answer $answer */
        foreach($this->getAnswers() as $answer) {
            if ($answer->getQuestion()->getType() === 'open') {
                $answers[] = $answer;
            }
        }

        return $answers;
    }

    public function logsToResponse(): array
    {
        $logs = $this->getLogs();
        $nodes = [];

        /** @var Logger $log */
        foreach($logs as $log) {
            $node = [
                'id' => $log->getAnswer()->getId(),
                'from' => $log->getFromAnswer(),
                'to' => $log->getToAnswer(),
            ];

            $nodes[] = $node;
        }

        return $nodes;
    }

    public function calcaulateIfSuspect(): bool
    {
        $answers = $this->getAnswers();
        $logs = $this->getLogs()->toArray();
        $counter = 0;


        /** @var Logger $log */
        foreach($logs as $log) {
            $status = false;

            foreach($answers as $answer) {
                if($answer->getId() === $log->getAnswer()->getId()) {

                    $status = $log->getIsSuspect();
                }
            }
            if ($status) {
                $counter++;
            }
        }

        $len = count($answers);

        if ((($counter/$len) * 100) >= 50) {
            return true;
        }

        return false;
    }

    public function calculateResult(): float
    {
        $len = count($this->getAnswers());
        $counter = 0;
        $hasOpenedAnswers = false;
        $debuger = [];

        /** @var Answer $answer */
        foreach ($this->getAnswers() as $answer)
        {
            /** @var Question $question */
            $question = $answer->getQuestion();
            if ($question->getType() === 'close') {
                $correct = $question->getCorrect();
                $actual = $answer->getAnswer();

                if ($actual === "" || $actual === null)  {
                    continue;
                }

                $letter = explode( ',', $actual);
                if (count($letter) > 1) {
                    continue;
                }

                if ($letter[0] === $correct) {
                    $counter++;
                }

                continue;
            }
            else {
                $hasOpenedAnswers = true;
            }
        }

        if (!$hasOpenedAnswers) {
            $this->status = self::STATUS_CLOSE_MARKED;
        }

        $result = ($counter/$len) * 100;
        $this->pass = $result;

        return $result;
    }
}
