<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/test')]
class TestController extends AbstractController
{
    #[Route('/', name: 'app_test', methods: ["GET"])]
    public function login(Request $request): JsonResponse
    {
        return $this->json(
            ['message' => "yes"],
            Response::HTTP_CREATED
        );
    }
}

