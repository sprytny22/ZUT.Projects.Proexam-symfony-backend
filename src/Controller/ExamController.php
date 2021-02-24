<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Entity\Question;
use App\Entity\Test;
use App\Entity\User;
use App\Repository\ExamRepository;
use App\Repository\GroupRepository;
use App\Repository\QuestionRepository;
use App\Repository\TestRepository;
use App\Repository\UserRepository;
use App\Request\GroupRequest;
use App\Request\PasswordRequest;
use App\Request\QuestionRequest;
use App\Request\TestRequest;
use App\Request\UserRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ExamController extends AbstractFOSRestController
{
    public function showExams(ExamRepository $examRepository): Response
    {
        $exams = $examRepository->findAll();
        return $this->handleView($this->view($exams, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("exam", options={"mapping": {"id": "id"}})
     * @param ExamRepository $examRepository
     * @param Exam $exam
     * @return Response
     */
    public function showExam(ExamRepository $examRepository, Exam $exam): Response
    {;
        return $this->handleView($this->view($exam, Response::HTTP_OK));
    }
}