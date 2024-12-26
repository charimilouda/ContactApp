<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Utilisateur;
use Psr\Log\LoggerInterface;
use App\Form\AddContactType;

class ContactController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Ajout d'un contact
     */
    #[Route('/contact/add', name: 'app_contact_add')]
    public function create(Request $request, SessionInterface $session, LoggerInterface $logger): Response
    {
        $contact = new Contact();

        // Récupérer l'ID de l'utilisateur depuis la session
        $userId = $session->get('user_id');

        if (!$userId) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un contact.');
        }

        // Récupérer l'utilisateur depuis la base de données
        $user = $this->entityManager->getRepository(Utilisateur::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Associer l'utilisateur au contact
        $contact->setUtilisateurid($user);
        $logger->info('Utilisateur associé au contact : ' . $contact->getUtilisateurid()->getId());

        // Utiliser la classe AddContactType pour générer le formulaire
        $form = $this->createForm(AddContactType::class, $contact);
        $form->handleRequest($request);
        
        // Traiter le formulaire lorsqu'il est soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setUtilisateurid($user);

            // Persister le contact
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            // Affichage du contact pour débogage
            $logger->info('Contact ajouté : ' . $contact->getId());

            // Message de succès
            $this->addFlash('success', 'Contact ajouté avec succès !');

            // Rediriger vers la liste des contacts
            return $this->redirectToRoute('app_contact_list');
        }

        return $this->render('contact/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Liste des contacts
     */
    #[Route('/contact',name:'app_contact_list')]
    public function ListContacts(SessionInterface $session): Response
    {
        // Récupérer l'ID de l'utilisateur depuis la session
        $userId = $session->get('user_id');

        if (!$userId) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer l'utilisateur depuis la base de données
        $user =  $this->entityManager->getRepository(Utilisateur::class)->find($userId);

        // Récupérer les contacts de l'utilisateur connecté
        $contactRespository=$this->entityManager->getRepository(Contact::class);
        $contacts=$contactRespository->findBy(['utilisateurid' => $user]);

        // Transformations pour la sortie JSON ou affichage
        $contactData = [];
        foreach ($contacts as $contact) {
            $contactData[] = [
                'id' => $contact->getId(),
                'nom' => $contact->getNom(),
                'prenom' => $contact->getPrenom(),
                'email' => $contact->getEmail(),
                'telephone' => $contact->getTelephone(),
                'entreprise' => $contact->getEntreprise(),
            ];
        }

        // Rendre la vue Twig
        return $this->render('contact/index.html.twig', [
            'contacts' => $contactData,
        ]); 
    }

    /**
     * Liste des contacts par ID
     */
    #[Route('/contact/{id}',name:'app_contact_byId',methods: ['GET'])]
    public function getContactById(int $id): JsonResponse
    {
        $contact = $this->entityManager->getRepository(Contact::class)->find($id);

        if (!$contact) {
            return new JsonResponse(['error' => 'Contact non trouvé'], 404);
        }

        $contactData = [
                'id' => $contact->getId(),
                'nom' => $contact->getNom(),
                'prenom' => $contact->getPrenom(),
                'email' => $contact->getEmail(),
                'telephone' => $contact->getTelephone(),
                'entreprise' => $contact->getEntreprise(),
        ];

        return new JsonResponse($contactData);
    }

    /**
     * Liste des contacts par nom
     */
    #[Route('/contact/list/{nom}',name:'app_contact_byName',methods: ['GET'])]
    public function getContactsByNom(string $nom): JsonResponse
    {
        $contacts = $this->entityManager->getRepository(Contact::class)->findBy(['nom' => $nom]);

        if (!$contacts) {
            return new JsonResponse(['error' => 'Aucun contact trouvé avec ce nom'], 404);
        }

        $contactData = [];
        foreach ($contacts as $contact) {
            $contactData[] = [
                'id' => $contact->getId(),
                'nom' => $contact->getNom(),
                'prenom' => $contact->getPrenom(),
                'email' => $contact->getEmail(),
                'telephone' => $contact->getTelephone(),
                'entreprise' => $contact->getEntreprise(),
            ];
        }

        return new JsonResponse($contactData);
    }

    /**
     * Suppression d'un contact
     */
    #[Route('/contact/delete/{id}', name: 'app_contact_delete',methods: ['POST'])]
     public function delete(int $id): Response
     {
         $contact = $this->entityManager->getRepository(Contact::class)->find($id);
 
         if (!$contact) {
 
            $this->addFlash('error', 'Contact non trouvé');
            return $this->redirectToRoute('app_contact_list');
        }

        $this->entityManager->remove($contact);
        $this->entityManager->flush();
 
        $this->addFlash('success', 'Contact supprimé avec succès');
        return $this->redirectToRoute('app_contact_list');
    }

    /**
     * Modification d'un contact
     */
    #[Route('/contact/update/{id}', name: 'app_contact_update_process', methods: ['POST'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        // Trouver le contact dans la base de données
        $contact = $this->entityManager->getRepository(Contact::class)->find($id);

        if (!$contact) {
            //return new JsonResponse(['error' => 'Contact non trouvé'], 404);
            throw $this->createNotFoundException('Contact non trouvé');
        }
        
        // Récupérer les données du formulaire
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $email = $request->request->get('email');
        $telephone = $request->request->get('telephone');
        $entreprise = $request->request->get('entreprise');

        // Mettre à jour les propriétés
        if ($nom) {
            $contact->setNom($nom);
        }
        if ($prenom) {
            $contact->setPrenom($prenom);
        }
        if ($email) {
            $contact->setEmail($email);
        }
        if ($telephone) {
            $contact->setTelephone($telephone);
        }
        if ($entreprise) {
            $contact->setEntreprise($entreprise);
        }

        // Sauvegarder les modifications
        $this->entityManager->flush();

        // Rediriger vers la liste des contacts avec un message de succès
        $this->addFlash('success', 'Contact modifié avec succès');
        return $this->redirectToRoute('app_contact_list');  // Remplacez par la route de la liste des contacts
    }

    /**
     * Affichage du formulaire de modification
     */
    #[Route('/contact/update/{id}', name: 'app_contact_update', methods: ['GET'])]
    public function showUpdateForm(int $id): Response
    {
        // Trouver le contact dans la base de données
        $contact = $this->entityManager->getRepository(Contact::class)->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Contact non trouvé');
        }

        // Passer le contact à la vue pour pré-remplir le formulaire
        return $this->render('contact/contact_update.html.twig', [
            'contact' => $contact
        ]);
    }

    /**
     * Affichage des détails d'un contact
     */
    #[Route('/contact/details/{id}', name: 'app_contact_details')]
    public function details(int $id): Response
    {
        $contact = $this->entityManager->getRepository(Contact::class)->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Contact introuvable.');
        }
    
        return $this->render('contact/details.html.twig', [
            'contact' => $contact,
        ]);
    }

}
