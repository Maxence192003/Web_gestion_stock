<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/users', name: 'api_users')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * GET /api/users - Récupérer tous les utilisateurs
     */
    #[Route('', name: 'get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        
        return $this->json([
            'success' => true,
            'data' => array_map(fn(User $user) => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
            ], $users)
        ]);
    }

    /**
     * GET /api/users/{id} - Récupérer un utilisateur par ID
     */
    #[Route('/{id}', name: 'get_user_by_id', methods: ['GET'])]
    public function getUserById(User $user): JsonResponse
    {
        return $this->json([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
            ]
        ]);
    }

    /**
     * POST /api/users/login - Connexion utilisateur
     */
    #[Route('/login', name: 'login_user', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email et password sont requis'
            ], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 401);
        }

        // Vérifier le mot de passe hashé
        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Mot de passe incorrect'
            ], 401);
        }

        return $this->json([
            'success' => true,
            'message' => 'Connexion réussie !',
            'data' => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
            ]
        ]);
    }

    /**
     * POST /api/users - Créer un nouvel utilisateur
     */
    #[Route('', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validation
        $required = ['nom', 'prenom', 'email', 'password'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return $this->json([
                    'success' => false,
                    'message' => "Le champ '$field' est requis"
                ], 400);
            }
        }

        // Vérifier si l'email existe déjà
        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json([
                'success' => false,
                'message' => 'Cet email existe déjà'
            ], 409);
        }

        // Créer le nouvel utilisateur
        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
            ]
        ], 201);
    }

    /**
     * PUT /api/users/{id} - Mettre à jour un utilisateur
     */
    #[Route('/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour',
            'data' => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
            ]
        ]);
    }

    /**
     * DELETE /api/users/{id} - Supprimer un utilisateur
     */
    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur supprimé'
        ]);
    }
}
