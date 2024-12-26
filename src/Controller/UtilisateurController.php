<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class UtilisateurController extends AbstractController
{
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(): Response
    {
        return new JsonResponse("Utilisateurs list");
    }
    
    /**
     * Ajouter Utilisateur
     */
    #[Route('/utilisateur/add', name: 'app_utilisateur_add')]
    public function create(Request $request): Response
    {
        $utilisateur = new Utilisateur();
        
        // Créer le formulaire
            $form = $this->createFormBuilder($utilisateur)
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('motDePasse')
            ->getForm();

        $form->handleRequest($request);

        // Traiter le formulaire lorsqu'il est soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur ajouté avec succès !');

            return $this->redirectToRoute('app_utilisateue_list');
        }

        return $this->render('utilisateur/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Liste des utilisateurs
     */
    #[Route('/utilisateur/list',name:'app_utilisateue_list')]
    public function ListUtilisateurs(): Response
    {
        $utilisateurRespository=$this->entityManager->getRepository(Utilisateur::class);
        $utilisateurs=$utilisateurRespository->findAll();
        
        $utilisateurData = [];
        foreach ($utilisateurs as $utilisateur) {
            $utilisateurData[] = [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
            ];
        }

        // Rendre la vue Twig
        return $this->render('utilisateur/list.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
        
    }

    /**
     * Récupérer utiisateurs par ID
     */
    #[Route('/utilisateur/{id}', name: 'app_utilisateur_byId', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getUtilisateurById(int $id): JsonResponse
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
    
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }
    
        $utilisateurData = [
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
        ];
    
        return new JsonResponse($utilisateurData);
    }
    
    /**
     * Récupérer utilisateurs par nom
     */
    #[Route('/utilisateur/list/{nom}',name:'app_utilisateur_byName',methods: ['GET'])]
    public function getUtilisateursByNom(string $nom): JsonResponse
    {
        $utilisateurs = $this->entityManager->getRepository(Utilisateur::class)->findBy(['nom' => $nom]);

        if (!$utilisateurs) {
            return new JsonResponse(['error' => 'Aucun utilisateur trouvé avec ce nom'], 404);
        }

        $utilisateurData = [];
        foreach ($utilisateurs as $utilisateur) {
            $utilisateurData[] = [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
            ];
        }

        return new JsonResponse($utilisateurData);
    }

    /**
     * Suppression d'un utilisateur
     */
    #[Route('/utilisateur/delete/{id}', name: 'app_utilisateur_delete',methods: ['POST'])]
    public function delete(int $id):Response
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {

            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('app_utilisateue_list');
            //return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }
        $this->entityManager->remove($utilisateur);
        $this->entityManager->flush();

        //return new JsonResponse(['message' => 'Utilisateur supprime avec succes'], 200);
        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('app_utilisateue_list');
    }

    /**
     * Modification d'un utilisateur
     */
    #[Route('/utilisateur/update/{id}', name: 'app_utilisateur_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        // Trouver l'utilisateur dans la base de données
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Décoder le JSON reçu
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        // Mettre à jour les propriétés
        if (isset($data['nom'])) {
            $utilisateur->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $utilisateur->setPrenom($data['prenom']);
        }
        if (isset($data['email'])) {
            $utilisateur->setEmail($data['email']);
        }
        if (isset($data['motDePasse'])) {
            $utilisateur->setMotDePasse($data['motDePasse']);
        }

        // Sauvegarder les modifications
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur modifié avec succès'], 200);
    }

}
