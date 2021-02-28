<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Entity\Test;
use App\Repository\ExamRepository;
use App\Repository\TestRepository;
use App\Repository\UserRepository;
use App\Request\ExamRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Validator\Validator\ValidatorInterface;


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
        TestRepository $testRepository,
        UserRepository $userRepository
    )
    {
        $this->validator = $validator;
        $this->testRepository = $testRepository;
        $this->userRepository = $userRepository;
    }

    public function showExams(ExamRepository $examRepository): Response
    {
        $exams = $examRepository->findAll();

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

        return $this->handleView($this->view($exam, Response::HTTP_OK));
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

        return $this->handleView($this->view('OK', Response::HTTP_OK));
    }

    /**
     * @ParamConverter("exam", options={"mapping": {"id": "id"}})
     * @param Exam $exam
     * @return Response
     */
    public function startExam(Exam $exam): Response
    {
        $users = $exam->getUsers();
        $user = $this->getUser();

        if (!in_array($user, $users) && $this->isGranted('ROLE_USER')) {
            throw new BadRequestException('Bad Request!');
        }

        $response = $exam->toResponse();
        return $this->handleView($this->view($response, Response::HTTP_OK));
    }

    public function addExam(ExamRequest $request): Response
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request!');
        }

        /** @var Test $test */
        $test = $this->testRepository->find($request->testId);
        $users = $this->userRepository->findBy($request->users);

        $exam = new Exam();
        $exam->setTest($test);
        $exam->setUsers($users);

        $exam->setStartDataTime($request->startDataTime);
        $exam->setEndDataTime($request->endDataTime);

        $this->entityManager->persist($test);
        $this->entityManager->flush();

        return $this->handleView($this->view('OK', Response::HTTP_OK));
    }
}