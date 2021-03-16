<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Entity\Test;
use App\Entity\User;
use App\Repository\ExamRepository;
use App\Repository\TestRepository;
use App\Repository\UserRepository;
use App\Request\ExamRequest;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


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

    public function __construct(
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        TestRepository $testRepository,
        UserRepository $userRepository
    )
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->testRepository = $testRepository;
        $this->userRepository = $userRepository;
    }

    public function showExams(ExamRepository $examRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user === null) {
            throw new \RuntimeException('ERRUR');
        }

        $exams = $examRepository->findByUser($user);

        if ($this->isGranted('ROLE_EXAM')) {
            $exams = $examRepository->findALL();
        }

        $rows = [];
        foreach($exams as $exam) {
            $row = [
                'examId' => $exam->getId(),
                'title' => $exam->getTitle(),
                'startTime' => $exam->getStartDataTime(),
                'endTime' => $exam->getEndDataTime(),
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
            'endTime' => $exam->getEndDataTime(),
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
     * @IsGranted("ROLE_EXMER")
     * @ParamConverter("exam", options={"mapping": {"id": "id"}})
     * @param Exam $exam
     * @return Response
     */
    public function watchExam(Exam $exam): Response
    {

    }

    /**
     * @IsGranted("ROLE_EXMER")
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
     * @return Response
     */
    public function joinExam(Exam $exam): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$exam->hasUser($user)) {
            throw new BadRequestException('Bad Request!');
        }

        if ($exam->getStatus() !== Exam::STATUS_PENDING) {
            throw new BadRequestException('Need confirm!');
        }

        $response = $exam->toResponse();
        return $this->handleView($this->view($response, Response::HTTP_OK));
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
        $test = $this->testRepository->find($request->test);
        $users = $this->userRepository->findBy(['id' => $userIds]);

        $exam = new Exam();
        $exam->setTitle($request->title);
        $exam->setStatus("NON_CONFIRM");
        $exam->setTest($test);
        $exam->setUsers($users);

        $exam->setStartDataTime(new DateTime($request->start));
        $exam->setEndDataTime(new DateTime($request->end));

        $this->entityManager->persist($exam);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }
}