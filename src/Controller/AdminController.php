<?php

namespace App\Controller;

use App\Entity\Tuto;
use App\Repository\TutoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    private $tutorialRepository;
    private $userRepository;

    public function __construct(TutoRepository $tutorialRepository, UserRepository $userRepository)
    {
        $this->tutorialRepository = $tutorialRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/admin/tuto', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        $tutorials = $this->tutorialRepository->findAllWithChaptersAndContent();
    
        return $this->json($tutorials, 200, [], ['groups' => 'tutorial:admin']);
    }

    #[Route('/admin/tuto/{id}', name: 'app_admin_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function show($id): JsonResponse
    {
        $tutorial = $this->tutorialRepository->findCustom($id);
        return $this->json($tutorial, 200, [], ['groups' => 'tutorial:admin']);
    }

    #[Route('/admin/tuto/{id}/update', name: 'app_admin_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update($id, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $tutorial = $this->tutorialRepository->find($id);
        if (!$tutorial) {
            return $this->json(['error' => 'Tutorial not found'], 404);
        }
        $data = json_decode($request->getContent(), true);
        $tutorial->setTitle($data['title']);
        $tutorial->setDescription($data['description']);
        $entityManager->persist($tutorial);
        $entityManager->flush();
        return $this->json($tutorial, 200, [], ['groups' => 'tutorial:admin']);
    }

    #[Route('/admin/tuto/{id}', name: 'app_admin_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $tutorial = $this->tutorialRepository->find($id);
        if (!$tutorial) {
            return $this->json(['error' => 'Tutorial not found'], 404);
        }
        $entityManager->remove($tutorial);
        $entityManager->flush();
        return $this->json(null, 204);
    }

    #[Route('/admin/user', name: 'app_admin_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function user(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        return $this->json($users, 200, [], ['groups' => 'user:admin']);
    }

    #[Route('/admin/user/{id}', name: 'app_admin_user_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function userShow($id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        return $this->json($user, 200, [], ['groups' => 'user:admin']);
    }

    #[Route('/admin/user/{id}/delete', name: 'app_admin_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userDelete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->json(null, 204);
    }

    #[Route('/admin/user/{id}/update', name: 'app_admin_user_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function userUpdate($id, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        $data = json_decode($request->getContent(), true);
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json($user, 200, [], ['groups' => 'user:admin']);
    }
}
