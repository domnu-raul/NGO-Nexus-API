<?php

namespace App\Controller;

use App\Exception\InvalidFieldValueException;
use App\Exception\MissingFieldsException;
use App\Exception\UniqueFieldConflictException;
use App\Repository\UserRepository;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

#[Route('/auth')]
class AuthenticationController extends AbstractController
{
    public function __construct(
        private readonly UserService    $userService,
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $JWTManager,
    ) {}

    #[Route('/available', name: 'app_available_fields_check', methods: ["GET"])]
    public function available(Request $request): JsonResponse
    {
        $field = $request->query->get('field');
        $value = $request->query->get('value');

        if ($field != 'username' && $field != 'email') {
            return $this->json([
                'error' => 'Incorrect data was sent.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userRepository->findOneBy([$field => $value]);
            if ($user == null) {
                return $this->json([
                    'available' => 'true',
                ], Response::HTTP_OK);
            }
            return $this->json([
                'available' => 'false',
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return $this->json(
                ['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/register', name: 'app_register', methods: ["POST"])]
    public function register(Request $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request);
            $this->userRepository->save($user);
        } catch (MissingFieldsException | InvalidFieldValueException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (UniqueFieldConflictException $e) {
            return $this->json([
                'error' => 'Some fields are already taken',
                'conflictingFields' => $e->getConflictingFields()
            ], Response::HTTP_CONFLICT);
        } catch (Throwable $e) {
            return $this->json(
                ['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json(
            ['message' => 'User successfully registered!'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'app_login', methods: ["POST"])]
    public function login(Request $request): JsonResponse
    {
        try {
            $user = $this->userService->checkCredentials($request);
        } catch (AuthenticationException | MissingFieldsException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        } catch (Throwable $e) {
            return $this->json(
                ['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json(
            ['token' => $this->JWTManager->create($user)],
            Response::HTTP_CREATED
        );
    }
}
