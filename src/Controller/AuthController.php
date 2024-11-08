<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $JWTManager,
    ) {}

    #[Route('/auth/login', name: 'app_auth_login', methods: ["POST"])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $password = $data['password'];

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (!$user || $password !== $user->getPassword()) {
            return $this->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $this->JWTManager->create($user);
        return $this->json(['token' => $token]);
    }

    #[Route('/auth/register', name: 'app_auth_register', methods: ["POST"])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $password = $data['password'];

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($password);

        $this->userRepository->save($user);

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AuthController.php',
        ]);
    }
}
