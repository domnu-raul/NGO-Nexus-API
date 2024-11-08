<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Exception\InvalidFieldValueException;
use App\Exception\MissingFieldsException;
use App\Exception\UniqueFieldConflictException;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
        private readonly JWTTokenManagerInterface $JWTManager,
    ) {}

    #[Route('/auth/login', name: 'app_auth_login', methods: ["POST"])]
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

    #[Route('/auth/register', name: 'app_auth_register', methods: ["POST"])]
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
}
