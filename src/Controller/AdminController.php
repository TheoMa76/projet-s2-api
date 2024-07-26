<?php

namespace App\Controller;

use App\Entity\Tuto;
use App\Repository\TutoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    private $tutorialRepository;

    public function __construct(TutoRepository $tutorialRepository)
    {
        $this->tutorialRepository = $tutorialRepository;
    }

    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        $tutorials = $this->tutorialRepository->findAllWithChaptersAndContent();

        return $this->json($tutorials, 200, [], ['groups' => 'tutorial:admin']);
    }
}
