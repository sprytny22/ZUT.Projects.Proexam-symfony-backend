<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Exam;
use App\Entity\Result;
use App\Entity\Test;
use App\Entity\User;
use App\Repository\ExamRepository;
use App\Repository\ResultRepository;
use App\Repository\TestRepository;
use App\Repository\UserRepository;
use App\Request\ExamRequest;
use App\Request\UpdateRequest;
use Cron\Job\ShellJob;
use Cron\Schedule\CrontabSchedule;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\WebLink\Link;

class ExamController extends AbstractFOSRestController
{
    /** @var ValidatorInterface $validator */
    private $validator;

    /** @var TestRepository $testRepository */
    private $testRepository;

    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var PublisherInterface $publisher */
    private $publisher;

    public function __construct(
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        TestRepository $testRepository,
        UserRepository $userRepository,
        PublisherInterface $publisher
    )
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->testRepository = $testRepository;
        $this->userRepository = $userRepository;
        $this->publisher = $publisher;
    }

    public function showExams(ExamRepository $examRepository): Response
    {
        if ($this->isGranted('ROLE_EXAMER')) {
            $exams = $examRepository->findAll();
        }
        else {
            /** @var User $user */
            $user = $this->getUser();
            if ($user === null) {
                throw new BadRequestException('Need confirm!');
            }

            $exams = $examRepository->findByUser($user);
        }

        $rows = [];
        foreach($exams as $exam) {
            $row = [
                'examId' => $exam->getId(),
                'title' => $exam->getTitle(),
                'startTime' => $exam->getStartDataTime(),
                'time' => $exam->getTime(),
                'pass' => $exam->getPass(),
                'status' => $exam->getStatus()
            ];

            $rows[] = $row;
        }

        return $this->handleView($this->view($rows, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("exam", options={"mapping": {"id": "id"}})
     * @param ExamRepository $examRepository
     * @param Exam $exam
     * @return Response
     */
    public function showExam(ExamRepository $examRepository, Exam $exam): Response
    {;
        $row = [
            'examId' => $exam->getId(),
            'title' => $exam->getTitle(),
            'startTime' => $exam->getStartDataTime(),
            'time' => $exam->getTime(),
            'pass' => $exam->getPass(),
            'status' => $exam->getStatus()
        ];

        return $this->handleView($this->view($row, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("exam", options={"mapping": {"id": "id"}})
     * @param Exam $exam
     * @return Response
     */
    public function confirmExam(Exam $exam): Response
    {
        if ($exam->getStatus() !== Exam::STATUS_NON_CONFIRM) {
            throw new BadRequestException('Bad Request!');
        }

        $exam->setStatus(Exam::STATUS_CONFIRMED);
        $this->entityManager->persist($exam);
        $this->entityManager->flush();


        return $this->handleView($this->view(['status'=> 'OK'], Response::HTTP_OK));
    }

    /**
     * @ParamConverter("exam", options={"mapping": {"id": "id"}})
     * @param Exam $exam
     */
    public function userWatchList(Exam $exam)
    {
        $users = $exam->getUsers();
        $response = [];

        /** @var User $user */
        foreach($users as $user) {
            $response[] = $user->toResponse();
        }

        return $this->handleView($this->view(['users'=> $response], Response::HTTP_OK));
    }

    /**
     * @IsGranted("ROLE_EXAMER")
     * @ParamConverter("exam", options={"mapping": {"examId": "id"}})
     * @ParamConverter("user", options={"mapping": {"userId": "id"}})
     * @param Exam $exam
     * @param User $user
     * @param Request $request
     * @param ResultRepository $resultRepository
     * @return Response
     */
    public function watchExam(Exam $exam, User $user, Request $request, ResultRepository $resultRepository): Response
    {
        if ($exam->getStatus() !== Exam::STATUS_PENDING) {
            throw new BadRequestException('Need confirm!');
        }

        /** @var Result $result */
        $result = $resultRepository->findBy([
            'exam' => $exam->getId(),
            'user' => $user->getId()
        ]);

        if ($result === null) {
            throw new BadRequestException('Użytkownik nie rozpoczął jeszcze egzaminu!');
        }
        if (empty($result)) {
            throw new BadRequestException('Użytkownik nie rozpoczął jeszcze egzaminu!');
        }

        $result = $result[0];

        $hubUrl = $this->getParameter('mercure.default_hub');
        $this->addLink($request, new Link('mercure', $hubUrl));

        $response = $result->toResponse();
        return $this->handleView($this->view($response, Response::HTTP_OK));
    }

    /**
     * @IsGranted("ROLE_EXAMER")
     * @ParamConverter("exam", options={"mapping": {"id": "id"}})
     * @param Exam $exam
     * @return Response
     */
    public function startExam(Exam $exam): Response
    {
//        $user = $this->getUser();
//        $users = $exam->getUsers();
//
//        if (!in_array($user, $users)) {
//            throw new BadRequestException('Bad Request!');
//        }
//
//        if ($exam->getStatus() !== Exam::STATUS_CONFIRMED) {
//            throw new BadRequestException('Need confirm!');
//        }

        $exam->setStatus(Exam::STATUS_PENDING);

        $this->entityManager->persist($exam);
        $this->entityManager->flush();

        return $this->handleView($this->view('OK', Response::HTTP_OK));
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param Exam $exam
     * @return Response
     */
    public function joinExam(Exam $exam, ResultRepository $resultRepository, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $found = $resultRepository->findCurrentResult($user, $exam);

        if ($found && $found[0]->getStatus() === Result::STATUS_CLOSE) {
            throw new BadRequestException('Egzamin zakonczony!');
        }

        if (!$exam->hasUser($user)) {
            throw new BadRequestException('Bad Request!');
        }

        if ($exam->getStatus() !== Exam::STATUS_PENDING) {
            throw new BadRequestException('Cant join!');
        }

        if ($found) {
            $response = $found[0]->toResponse();
            return $this->handleView($this->view($response, Response::HTTP_OK));
        }

        $result = new Result();
        $result->setUser($this->getUser());
        $result->setExam($exam);

        $answers = [];

        $questions = $exam->getTest()->getQuestions()->toArray();

        foreach($questions as $question)
        {
            $answer = new Answer();
            $answer->setUser($user);
            $answer->setQuestion($question);
            $this->entityManager->persist($answer);
            $this->entityManager->flush();

            $answers[] = $answer;
        }

        $result->setAnswers($answers);

        $this->entityManager->persist($result);
        $this->entityManager->flush();

        $response = $result->toResponse();
        return $this->handleView($this->view($response, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("result", options={"mapping": {"id": "id"}})
     * @param Result $result
     * @return Response
     */
    public function closeResult(Result $result): Response
    {
        if ($result->getUser() !== $this->getUser()) {
            throw new BadRequestException('Bad Request!');
        }

        $result->setStatus(Result::STATUS_CLOSE);

        $this->entityManager->persist($result);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }

    /**
     * @ParamConverter("result", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param Result $result
     * @return Response
     */
    public function updateResult(Request $request, Result $result): Response
    {
        $user = $this->getUser();
        if ($result->getUser() !== $user) {
            throw new BadRequestException('Bad Request!');
        }

        if ($result->getStatus() === Result::STATUS_CLOSE) {
            throw new BadRequestException('Egzamin zakonczony!');
        }

        $data = json_decode($request->getContent());
        $answers = $result->getAnswers()->toArray();

        /** @var Answer $answer */
        foreach ($answers as $answer) {
            foreach ($data as $datum){
                if ($answer->getId() === $datum->answerId) {
                    if($datum->answer === 'None') {
                        continue;
                    }
                    if ($answer->getQuestion()->getType() === 'close') {
                        $answer->setCloseAnswer($datum->answer);
                    } else {
                        $answer->setOpenAnswer($datum->answer);
                    }
                }
            }
            $this->entityManager->persist($answer);
            $this->entityManager->flush();
        }

        $updated = [];

        /** @var Answer $answer */
        foreach ($result->getAnswers() as $answer)
        {
            $updated[] = $answer->toResponse();
        }

        $update = new Update(
            sprintf("result/%s", $result->getId()),
            json_encode(["data" => $updated])
        );

        $this->publisher->__invoke($update);


//        $update = new Update(
//            [
//                sprintf("/conversations/%s", $conversation->getId()),
//                sprintf("/conversations/%s", $recipient->getUser()->getUsername()),
//            ],
//            $messageSerialized,
//            [
//                sprintf("/%s", $recipient->getUser()->getUsername())
//            ]
//        );


        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }

    /**
     * @ParamConverter("request", converter="fos_rest.request_body")
     * @param ExamRequest $request
     * @return Response
     * @throws \Exception
     */
    public function addExam(ExamRequest $request): Response
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request!');
        }

        $userIds = $request->users;
        $testId = $request->test;

        /** @var Test $test */
        $test = $this->testRepository->find($testId);
        $users = $this->userRepository->findBy(['id' => $userIds]);

        $exam = new Exam();
        $exam->setTitle($request->title);
        $exam->setStatus("NON_CONFIRM");
        $exam->setPass($request->pass);
        $exam->setTime($request->time);
        $exam->setTest($test);
        $exam->setUsers($users);

        $exam->setStartDataTime(new DateTime($request->start));

        $this->entityManager->persist($exam);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }
}