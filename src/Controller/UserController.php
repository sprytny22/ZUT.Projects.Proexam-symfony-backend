<?php

namespace App\Controller;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Request\PasswordRequest;
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
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\WebLink\Link;

class UserController extends AbstractFOSRestController
{
    const METHOD_ADD = 'add';
    const METHOD_REMOVE = 'remove';

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

    /**
     * @param Request $request
     * @return Response
     */
    public function details(Request $request): Response
    {
        $user = $this->getUser();
        $username = $this->getUser()->getUsername();

        if ($user === null) {
            throw new BadRequestException('Bad Request');
        }

        $details = [
          'user' => $user->getUsername(),
          'roles' => $user->getRoles(),
        ];

        $response = $this->handleView($this->view($details, Response::HTTP_OK));

        $hubUrl = $this->getParameter('mercure.default_hub');
        $this->addLink($request, new Link('mercure', $hubUrl));

        $key = Key\InMemory::plainText('aVerySecretKey'); // don't forget to set this parameter! Test value: !ChangeMe!
        $configuration = Configuration::forSymmetricSigner(new Sha256(), $key);

        $token = $configuration->builder()
            ->withClaim('mercure', ['subscribe' => [sprintf("/%s", $username)]])
            ->getToken($configuration->signer(), $configuration->signingKey())
            ->toString();

        $cookie = Cookie::create('mercureAuthorization')
            ->withValue($token)
            ->withPath('/.well-known/mercure')
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withSameSite('strict')
        ;
        $response->headers->setCookie($cookie);

        return $response;
    }

    public function showAll(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->handleView($this->view($users, Response::HTTP_OK));
    }

    /**
     * @IsGranted("ROLE_ADMIN", "ROLE_EXAMER")
     * @ParamConverter("user", options={"mapping": {"id": "id"}})
     */
    public function show(User $user): Response
    {
        return $this->handleView($this->view($user, Response::HTTP_OK));
    }

    /**
     * @ParamConverter("request", converter="fos_rest.request_body")
     */
    public function add(UserRequest $request): Response
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request');
        }

        $user = new User();
        $user->setEmail($request->email);
        $user->setRolesByCode($request->role);

        $encoded = $this->encoder->encodePassword($user, $request->password);
        $user->setPassword($encoded);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request');
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @ParamConverter("user", options={"mapping": {"id": "id"}})
     * @ParamConverter("request", converter="fos_rest.request_body")
     */
    public function changePassword(PasswordRequest $request, User $user): Response
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request!');
        }
        $new = $request->password;

        $encoded = $this->encoder->encodePassword($user, $new);
        $user->setPassword($encoded);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(User $user): Response
    {
        //TODO: delete
    }

    /**
     * @ParamConverter("user", options={"mapping": {"id": "id"}})
     * @ParamConverter("request", converter="fos_rest.request_body")
     */
    public function group(GroupRequest $request, User $user, GroupRepository $groupRepository): Response
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            throw new BadRequestException('Bad Request!');
        }
        $groups = $request->groups;
        $method = $request->method;

        if ($groups === null) {
            throw new \RuntimeException('null given');
        }

        foreach($groups as $groupId) {
            $group = $groupRepository->find($groupId);
            if ($group === null) {
                throw new BadRequestException('Group not found!');
            }

            if ($method === self::METHOD_REMOVE) {
                /** @var array $userGroups */
                $userGroups = $user->getGroups();

                if (!in_array($groupId, $userGroups)) {
                    throw new BadRequestException('Group not found!');
                }

                $user->removeGroup($group);
            }
            else if ($method === self::METHOD_ADD) {
                $user->addGroup($group);
            }
            else {
                throw new BadRequestException('Method not found!');
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK));
    }
}
