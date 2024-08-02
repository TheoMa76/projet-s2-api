<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ChapterRepository;
use App\Repository\TutoRepository;
use App\Repository\UserRepository;
use App\Service\EntityFetcher;
use App\Service\JWTDecoderService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/user")]
class UserController extends BaseController
{
    protected $entityClass = User::class;
    private $jwtDecoder;
    private $userRepository;
    private $mailer;
    private $entityManager;
    private $chapterRepository;
    private $tutorialRepository;


    public function __construct(JWTDecoderService $jwtDecoder, UserRepository $userRepository,EntityFetcher $entityFetcher,MailerInterface $mailer,EntityManagerInterface $entityManager, ChapterRepository $chapterRepository, TutoRepository $tutorialRepository)
    {
        parent::__construct($entityFetcher, $this->entityClass);
        $this->jwtDecoder = $jwtDecoder;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->chapterRepository = $chapterRepository;
        $this->tutorialRepository = $tutorialRepository;

    }

    #[Route('/connected', methods: ['GET'])]
    public function getConnectedUser(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Token not provided'], 401);
        }

        $token = $matches[1];

        try {
            $data = $this->jwtDecoder->decode($token);
            $email = $data['username'];
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            return new JsonResponse([
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt(),
                'updatedAt' => $user->getUpdatedAt(),
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'Invalid Token'], 401);
        }
    }

    #[Route('/delete', methods: ['POST'])]
    public function deinscription(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Token not provided'], 401);
        }

        $token = $matches[1];

        try {
            $data = $this->jwtDecoder->decode($token);
            $email = $data['username'];
            $user = $this->userRepository->findWithProgress($email);
            
            $progressList = $user->getProgress();
            foreach($progressList as $progress){
                $this->entityManager->remove($progress);
            }

            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], 404);
            }
            

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'User deleted',
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'Invalid Token'], 401);
        }
    }

    #[Route('/reset', methods: ['POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $baseUrl = $_ENV['FRONT_URL'];


        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $user->setToken($this->generateTokenWithExpiration());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $mailHtml = $this->renderView('emails/reset_password.html.twig', ['token' => $user->getToken(),'resetUrl'=> $baseUrl."/passwordreset/".$user->getToken()."?token=".$user->getToken()]);
        $email = (new Email())
            ->from('easymod.noreply@gmail.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html($mailHtml);
       
        $this->mailer->send($email);


        return new JsonResponse(['message' => 'Email envoyé']);
    }

    #[Route('/reset/{token}', methods: ['POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function resetPasswordWithToken(Request $request, $token): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $password = $data['password'];
        $confirmPassword = $data['confirmPassword'];

        if($password !== $confirmPassword){
            return new JsonResponse(['error' => 'Passwords do not match'], 400);
        }

        if(!$this->isTokenValid($token)){
            return new JsonResponse(['error' => 'Token expired'], 401);
        }
        $user = $this->userRepository->findOneBy(['token' => $token]);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $user->setPassword($password);
        $user->setToken(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Mot de passe modifié']);
    }

    #[Route('/progress', methods: ['GET'])]
    public function getProgress(Request $request): JsonResponse
{
    $authHeader = $request->headers->get('Authorization');
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return new JsonResponse(['error' => 'Token not provided'], 401);
    }

    $token = $matches[1];

    try {
        $data = $this->jwtDecoder->decode($token);
        $email = $data['username'];
        $user = $this->userRepository->findWithProgress($email);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $tutorials = $this->tutorialRepository->findAllWithChaptersAndContent();
        $idChaptersList = [];
        $nbChaptersList = [];
        $tutorialTitles = [];
        $chapterTitles = [];

        foreach($tutorials as $tutorial) {
            $tutorialId = $tutorial->getId();
            $tutorialTitles[$tutorialId] = $tutorial->getTitle();
            $idChapters = [];
            $nbChapters = 0;

            foreach($tutorial->getChapters() as $chapter) {
                $chapterId = $chapter->getId();
                $idChapters[] = $chapterId;
                $nbChapters++;
                $chapterTitles[$chapterId] = $chapter->getTitle();
            }

            $idChaptersList[$tutorialId] = $idChapters;
            $nbChaptersList[$tutorialId] = $nbChapters;
        }

        $progress = $user->getProgress();
        $jsonProgress = $this->json($progress, 200, [], ['groups' => 'progress.index']);
        $jsonProgress = json_decode($jsonProgress->getContent());

        return new JsonResponse([
            'progress' => $jsonProgress,
            'nbChapters' => $nbChaptersList,
            'idChapters' => $idChaptersList,
            'tutorialTitles' => $tutorialTitles, // Ajouter les titres des tutoriels
            'chapterTitles' => $chapterTitles   // Ajouter les titres des chapitres
        ]);
    } catch (\RuntimeException $e) {
        return new JsonResponse(['error' => 'Invalid Token'], 401);
    }
}


    function generateTokenWithExpiration($expiresInSeconds = 3600) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + $expiresInSeconds; 
    
        $tokenData = json_encode([
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
    
        // Vous pouvez aussi encoder en Base64 pour rendre le token plus compact
        return base64_encode($tokenData);
    }

    function isTokenValid($encodedToken) {
        $decodedData = base64_decode($encodedToken);
        $tokenData = json_decode($decodedData, true);
    
        if (!$tokenData) {
            return false;
        }
    
        $currentTimestamp = time();
        return $currentTimestamp < $tokenData['expires_at'];
    }
}
