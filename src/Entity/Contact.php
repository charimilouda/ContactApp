<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    private ?Utilisateur $utilisateurid = null;

    public function getId(): ?int
    {return $this->id;}

    public function getNom(): ?string
    {return $this->nom;}

    public function setNom(string $nom): static
    {$this->nom = $nom;
    return $this;}

    public function getPrenom(): ?string
    {return $this->prenom; }

    public function setPrenom(?string $prenom): static
    {$this->prenom = $prenom;
    return $this;}

    public function getEmail(): ?string
    {return $this->email;}

    public function setEmail(?string $email): static
    {$this->email = $email;
    return $this;}

    public function getTelephone(): ?string
    {return $this->telephone;}

    public function setTelephone(?string $telephone): static
    {$this->telephone = $telephone;
    return $this;
    }

    public function getEntreprise(): ?string
    {return $this->entreprise; }

    public function setEntreprise(?string $entreprise): static
    {$this->entreprise = $entreprise;
    return $this;}

    public function getUtilisateurid(): ?Utilisateur
    {return $this->utilisateurid;}

    public function setUtilisateurid(?Utilisateur $utilisateurid): static
    {$this->utilisateurid = $utilisateurid;return $this;}
}
