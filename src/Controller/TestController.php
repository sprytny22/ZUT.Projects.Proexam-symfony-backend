<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Test;
use App\Entity\User;
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

class TestController extends AbstractFOSRestController
{
    /**
     * @IsGranted("ROLE_ADMIN", "ROLE_EXAMER")
     */
    public function showTests(TestRepository $testRepository): Response
    {
        $tests = $testRepository->findAll();
        return $this->handleView($this->view($tests, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("test", options={"mapping": {"id": "id"}})
     * @param TestRepository $testRepository
     * @param Test $test
     * @return Response
     */
    public function showTest(TestRepository $testRepository, Test $test): Response
    {
        $tests = $testRepository->findAll();
        return $this->handleView($this->view($tests, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("request", converter="fos_rest.request_body")
     */
    public function addTest(TestRequest $request, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $errors = $validator->validate($request);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request!');
        }

        $test = new Test();
        $test->setName($request->name);
        $test->setCategory($request->category);
        $test->setQuestions($request->questions);

        $entityManager->persist($test);
        $entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }

    /**
     * @IsGranted("ROLE_ADMIN", "ROLE_EXAMER")
     */
    public function showQuestions(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();
        return $this->handleView($this->view($questions, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("test", options={"mapping": {"id": "id"}})
     * @param QuestionRepository $questionRepository
     * @param Question $question
     * @return Response
     */
    public function showQuestion(QuestionRepository $questionRepository, Question $question): Response
    {
        $question = $questionRepository->findAll();
        return $this->handleView($this->view($question, Response::HTTP_OK));
    }

    public function addQuestion(QuestionRequest $request, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $errors = $validator->validate($request);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request!');
        }

        $question = new Question();
        $question->setType($request->type);
        $question->setContent($request->content);

        $question->setA($request->a);
        $question->setB($request->b);
        $question->setC($request->c);
        $question->setD($request->d);

        $question->setTests($request->tests);

        $entityManager->persist($question);
        $entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }
}