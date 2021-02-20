<?php

namespace App\Controller;

use App\Dto\UserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractFOSRestController
{
    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var SerializerInterface $serializer */
    private $serializer;

    /** @var ValidatorInterface $validator */
    private $validator;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }

    public function showAll(): Response
    {
        return$this->handleView($this->view(['status'=>'ok'],Response::HTTP_CREATED));
    }

    public function add(Request $request): Response
    {
        $data = $request->getContent();

        try {
            $userDto = $this->serializer->deserialize($data, UserDTO::class, 'json');
            $this->validator->validate($userDto);
        } catch (NotEncodableValueException $e) {
            return $this->handleView($this->view(['status' => 'bad request'], Response::HTTP_BAD_REQUEST));
        }

        $user = new User();
        $user->fromDto($userDto);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
    }

    public function update(Request $request): Response
    {

    }

    public function delete(Request $request): Response
    {

    }
}
