<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): RedirectResponse
    {
        return $this->redirectToRoute('app_utilisateur_login');
    }

    /**
     * Afficher la page de Login
     */
    #[Route('/login', name: 'app_utilisateur_login', methods: ['GET'])]
    public function loginForm(): Response
    {
        return $this->render('login/index.html.twig');
    }
  
    /**
     * Traiter la connexion
     */
    #[Route('/utilisateur/authenticate', name: 'app_utilisateur_authenticate', methods: ['POST'])]
    public function authenticate(Request $request,SessionInterface $session): Response
    {
        // Récupération des données envoyées par le formulaire
        $email = $request->request->get('email');
        $motDePasse = $request->request->get('motDePasse');

        // Vérifier si un utilisateur existe avec cet email
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if (!$utilisateur) {
            // Si l'utilisateur n'existe pas
            $this->addFlash('error', 'Données erronées');
            return $this->redirectToRoute('app_utilisateur_login');
        }

        // Vérifier si le mot de passe est correct
        if ($utilisateur->getMotDePasse() !== $motDePasse) {
            // Si le mot de passe ne correspond pas
            $this->addFlash('error', 'Données erronées');
            return $this->redirectToRoute('app_utilisateur_login');
        }

        // Si les informations sont correctes, rediriger vers la page de liste des contacts
        $session->set('user_id', $utilisateur->getId());

        return $this->redirectToRoute('app_contact_list');
    }

    /**
     * Route pour la déconnexion
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        // Supprimer l'ID utilisateur de la session
        $session->remove('user_id');

        // Redirection après déconnexion
        return $this->redirectToRoute('app_utilisateur_login');
    }

}
