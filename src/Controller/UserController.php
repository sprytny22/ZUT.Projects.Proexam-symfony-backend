<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Request\UserRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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

    /** @var UserPasswordEncoderInterface $encoder */
    private $encoder;

    public function __construct(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder
    )
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    public function showAll(): Response
    {
        $users = $this->userRepository->findAll();
        return$this->handleView($this->view($users, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("user", options={"mapping": {"id": "id"}}
     */
    public function show(User $user): Response
    {
        return$this->handleView($this->view($user, Response::HTTP_OK));
    }

    public function add(Request $request): Response
    {
        $data = $request->getContent();

        try {
            $userRequest = $this->serializer->deserialize($data, UserRequest::class, 'json');
            $this->validator->validate($userRequest);
        } catch (NotEncodableValueException $e) {

            return $this->handleView($this->view(['status' => 'bad request'], Response::HTTP_BAD_REQUEST));
        }
        $user = User::fromDto($userRequest);
        $encoded = $this->encoder->encodePassword($user, $userRequest->password);
        $user->setPassword($encoded);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
    }

    /**
     * @ParamConverter("user", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function update(Request $request, User $user): Response
    {
        $data = $request->getContent();

        try {
            $passwordRequest = $this->serializer->deserialize($data, PasswordRequest::class, 'json');
            $this->validator->validate($passwordRequest);
        } catch (NotEncodableValueException $e) {

            return $this->handleView($this->view(['status' => 'bad request'], Response::HTTP_BAD_REQUEST));
        }

        $encoded = $this->encoder->encodePassword($user, $passwordRequest->password);
        $user->setPassword($encoded);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }

    public function delete(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'removed'], Response::HTTP_OK));
    }

    /**
     * @ParamConverter("user", options={"mapping": {"id": "id"}})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function addGroup(Request $request, User $user): Response
    {
        $data = $request->getContent();

        try {
            $groupsRequest = $this->serializer->deserialize($data, GroupsRequest::class, 'json');
            $this->validator->validate($groupsRequest);
        } catch (NotEncodableValueException $e) {

            return $this->handleView($this->view(['status' => 'bad request'], Response::HTTP_BAD_REQUEST));
        }

        foreach($groupsRequest->groups as $group) {
            $user->addGroup($group);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'removed'], Response::HTTP_OK));
    }
}
