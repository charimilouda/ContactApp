<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityManagerInterface;


class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Affichage du formulaire d'inscription
     */
    #[Route('/inscription', name: 'app_utilisateur_register')]
    public function index(): Response
    {
        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
        ]);
    }
    
    /**
     *    Méthode pour créer un utilisateur via un formulaire
     */
    #[Route('/signup', name: 'app_user_register')]
    public function create(Request $request): Response
    {
        // Crée une nouvelle instance de l'entité Utilisateur
        $utilisateur = new Utilisateur();

        // Créer le formulaire
        $form = $this->createFormBuilder($utilisateur)
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', EmailType::class)
            ->add('motDePasse', PasswordType::class)
            ->add('save', SubmitType::class, ['label' => 'S\'inscrire'])
            ->getForm();

        // Traiter la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persister l'utilisateur dans la base de données (sans hachage du mot de passe)
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();

            // Ajouter un message flash pour indiquer que l'inscription a réussi
            $this->addFlash('success', 'Utilisateur créé avec succès !');
            
            // Rediriger vers la page de connexion ou vers une autre page
            return $this->redirectToRoute('app_contact_list');
        }
        
        // Rendre la vue Twig avec le formulaire
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
